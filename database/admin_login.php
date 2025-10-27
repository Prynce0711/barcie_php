<?php
session_start();
header('Content-Type: application/json'); // Ensure response is JSON

include __DIR__ . '/db_connect.php';

$response = ['success' => false, 'message' => 'Invalid request.'];

// Check if database connection failed
if ($conn->connect_error) {
    $response['message'] = 'Database connection failed: ' . $conn->connect_error;
    $response['debug'] = [
        'host' => $host ?? 'unknown',
        'dbname' => $dbname ?? 'unknown',
        'error' => $conn->connect_error
    ];
    echo json_encode($response);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $response['message'] = 'Please fill in all fields.';
        echo json_encode($response);
        exit;
    }

    $stmt = $conn->prepare("SELECT id, password FROM admins WHERE username = ?");
    
    if (!$stmt) {
        $response['message'] = 'Database query error: ' . $conn->error;
        echo json_encode($response);
        exit;
    }
    
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $storedPassword);
        $stmt->fetch();

        // Support both hashed (bcrypt) and plain text passwords
        $passwordValid = false;
        
        // Check if password is hashed (bcrypt starts with $2y$ and is 60 chars)
        if (strlen($storedPassword) == 60 && substr($storedPassword, 0, 4) === '$2y$') {
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

            $response['success'] = true;
            $response['message'] = 'Login successful.';
        } else {
            $response['message'] = 'Invalid password.';
        }
    } else {
        $response['message'] = 'Username not found.';
    }

    $stmt->close();
}

$conn->close();
echo json_encode($response);
exit;
