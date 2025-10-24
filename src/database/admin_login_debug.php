<?php
/**
 * Debug Admin Login Handler
 * This version logs every step of the login process
 */

// Enable error logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../admin_login_debug.log');

function logDebug($message) {
    $timestamp = date('Y-m-d H:i:s');
    error_log("[$timestamp] $message");
}

logDebug("========== Admin Login Attempt Started ==========");

session_start();
header('Content-Type: application/json');

logDebug("Session started, JSON header set");

include __DIR__ . '/db_connect.php';

$response = ['success' => false, 'message' => 'Invalid request.', 'debug' => []];

// Check database connection
if ($conn->connect_error) {
    logDebug("Database connection FAILED: " . $conn->connect_error);
    $response['message'] = 'Database connection failed: ' . $conn->connect_error;
    $response['debug'][] = 'DB Connection Error';
    echo json_encode($response);
    exit;
}

logDebug("Database connected successfully");
$response['debug'][] = 'DB Connected';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    logDebug("POST request received");
    
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    logDebug("Username received: " . ($username ?: '[EMPTY]'));
    logDebug("Password received: " . (strlen($password) > 0 ? '[' . strlen($password) . ' chars]' : '[EMPTY]'));
    
    $response['debug'][] = "Username: " . $username;
    $response['debug'][] = "Password length: " . strlen($password);

    if (empty($username) || empty($password)) {
        logDebug("Validation FAILED: Empty fields");
        $response['message'] = 'Please fill in all fields.';
        $response['debug'][] = 'Empty fields';
        echo json_encode($response);
        exit;
    }

    $stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE username = ?");
    
    if (!$stmt) {
        logDebug("Query preparation FAILED: " . $conn->error);
        $response['message'] = 'Database query error: ' . $conn->error;
        $response['debug'][] = 'Query prep failed';
        echo json_encode($response);
        exit;
    }
    
    logDebug("Query prepared successfully");
    $response['debug'][] = 'Query prepared';
    
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    
    logDebug("Query executed. Rows found: " . $stmt->num_rows);
    $response['debug'][] = 'Rows found: ' . $stmt->num_rows;

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $dbUsername, $storedPassword);
        $stmt->fetch();
        
        logDebug("Admin found - ID: $id, Username: $dbUsername");
        logDebug("Stored password length: " . strlen($storedPassword));
        logDebug("Stored password first 10 chars: " . substr($storedPassword, 0, 10) . "...");
        
        $response['debug'][] = "Admin ID: $id";
        $response['debug'][] = "DB Username: $dbUsername";
        $response['debug'][] = "Stored password length: " . strlen($storedPassword);

        // Check if password is hashed (bcrypt starts with $2y$)
        $isHashed = (strlen($storedPassword) > 20 && strpos($storedPassword, '$2y$') === 0);
        
        if ($isHashed) {
            logDebug("Password appears to be HASHED (bcrypt)");
            $passwordMatch = password_verify($password, $storedPassword);
            $response['debug'][] = 'Using password_verify()';
        } else {
            logDebug("Password appears to be PLAIN TEXT");
            $passwordMatch = ($password === $storedPassword);
            $response['debug'][] = 'Using direct comparison';
        }
        
        logDebug("Password match result: " . ($passwordMatch ? 'TRUE' : 'FALSE'));
        $response['debug'][] = "Password match: " . ($passwordMatch ? 'YES' : 'NO');

        if ($passwordMatch) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $id;
            $_SESSION['admin_username'] = $dbUsername;

            logDebug("Login SUCCESS - Session set");
            
            $response['success'] = true;
            $response['message'] = 'Login successful.';
            $response['debug'][] = 'Session created';
        } else {
            logDebug("Login FAILED - Invalid password");
            $response['message'] = 'Invalid password.';
            $response['debug'][] = 'Password mismatch';
        }
    } else {
        logDebug("Login FAILED - Username not found: $username");
        $response['message'] = 'Username not found.';
        $response['debug'][] = 'Username not in DB';
    }

    $stmt->close();
} else {
    logDebug("Not a POST request: " . $_SERVER["REQUEST_METHOD"]);
    $response['debug'][] = 'Not POST request';
}

logDebug("Response: " . json_encode($response));
logDebug("========== Admin Login Attempt Ended ==========\n");

$conn->close();
echo json_encode($response);
exit;
?>
