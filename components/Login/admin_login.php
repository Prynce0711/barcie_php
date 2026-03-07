<?php
// Enable error logging but suppress display
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/admin_login_errors.log');

// Start output buffering to catch any accidental output
ob_start();

session_start();

// Clean any output buffer and set JSON header
while (ob_get_level()) {
    ob_end_clean();
}
ob_start();

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Log the request
error_log("[" . date('Y-m-d H:i:s') . "] Admin login request received from " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));

// Set up error and exception handlers
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    error_log("[" . date('Y-m-d H:i:s') . "] PHP Error [$errno]: $errstr in $errfile on line $errline");
    return false;
});

set_exception_handler(function ($exception) {
    error_log("[" . date('Y-m-d H:i:s') . "] Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error occurred', 'error' => $exception->getMessage()]);
    exit;
});

try {
    include __DIR__ . '/db_connect.php';
} catch (Exception $e) {
    error_log("[" . date('Y-m-d H:i:s') . "] DB Connection Exception: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database connection failed', 'error' => $e->getMessage()]);
    exit;
}

$response = ['success' => false, 'message' => 'Invalid request.'];

// Check if database connection failed
if (!isset($conn) || $conn->connect_error) {
    error_log("[" . date('Y-m-d H:i:s') . "] Database connection failed: " . ($conn->connect_error ?? 'Connection object not created'));
    $response['message'] = 'Database connection failed';
    $response['debug'] = [
        'host' => $host ?? 'unknown',
        'dbname' => $dbname ?? 'unknown',
        'error' => $conn->connect_error ?? 'Connection not established'
    ];
    echo json_encode($response);
    exit;
}

try {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($username) || empty($password)) {
            $response['message'] = 'Please fill in all fields.';
            echo json_encode($response);
            exit;
        }

        // Detect whether `role` column exists; fall back if not
        $hasRoleColumn = false;
        try {
            $colRes = $conn->query("SHOW COLUMNS FROM `admins` LIKE 'role'");
            if ($colRes && $colRes->num_rows > 0) {
                $hasRoleColumn = true;
            }
        } catch (Throwable $e) {
            // ignore - assume column missing
        }

        if ($hasRoleColumn) {
            $stmt = $conn->prepare("SELECT id, password, role FROM admins WHERE username = ?");
        } else {
            // Older schema without role
            $stmt = $conn->prepare("SELECT id, password FROM admins WHERE username = ?");
        }

        if (!$stmt) {
            $response['message'] = 'Database query error: ' . $conn->error;
            echo json_encode($response);
            exit;
        }

        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            if ($hasRoleColumn) {
                $stmt->bind_result($id, $storedPassword, $role);
            } else {
                $stmt->bind_result($id, $storedPassword);
                $role = null;
            }
            $stmt->fetch();

            // Ensure $storedPassword is a string before operating on it
            if (!is_string($storedPassword) && !is_numeric($storedPassword)) {
                $response['message'] = 'Failed to retrieve stored password for user.';
            } else {
                $storedPassword = (string) $storedPassword;

                // Support both hashed (bcrypt) and plain text passwords
                $passwordValid = false;

                // Check if password is hashed (bcrypt starts with $2y$ and is 60 chars)
                if (strlen($storedPassword) === 60 && substr($storedPassword, 0, 4) === '$2y$') {
                    // Hashed password - use password_verify
                    $passwordValid = password_verify($password, $storedPassword);
                } else {
                    // Plain text password - use direct comparison
                    $passwordValid = ($password === $storedPassword);
                }

                if ($passwordValid) {
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $id;
                    $_SESSION['admin_username'] = $username;
                    // Store role in session (fallback to 'staff' if null or DB doesn't have column)
                    $_SESSION['admin_role'] = ($hasRoleColumn && !empty($role)) ? $role : 'staff';

                    error_log("[" . date('Y-m-d H:i:s') . "] Login successful for user: $username (ID: $id). Session ID: " . session_id());

                    // Update last login timestamp
                    $update_stmt = $conn->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
                    $update_stmt->bind_param("i", $id);
                    $update_stmt->execute();
                    $update_stmt->close();

                    // Force session to be written immediately
                    session_write_close();
                    session_start(); // Restart for any further operations

                    error_log("[" . date('Y-m-d H:i:s') . "] Session written. Session data: " . print_r($_SESSION, true));

                    $response['success'] = true;
                    $response['message'] = 'Login successful.';
                    $response['redirect'] = 'dashboard.php';
                } else {
                    $response['message'] = 'Invalid password.';
                }
            }
        } else {
            $response['message'] = 'Username not found.';
        }

        $stmt->close();
    }
} catch (Exception $e) {
    error_log("[" . date('Y-m-d H:i:s') . "] Login process exception: " . $e->getMessage());
    $response['success'] = false;
    $response['message'] = 'An error occurred during login';
    $response['error'] = $e->getMessage();
}

$conn->close();

// Clean output buffer and send JSON response
$output = ob_get_clean();
if (!empty($output)) {
    error_log("[" . date('Y-m-d H:i:s') . "] Unexpected output captured: " . $output);
}

echo json_encode($response);
exit;
