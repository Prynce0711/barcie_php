<?php
// filepath: c:\xampp\htdocs\barcie_php\src\database\db_connect.php


// Database connection details
// Auto-detect environment: use localhost DB when on localhost, remote DB when on server
$is_localhost = in_array($_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost', 
    ['localhost', '127.0.0.1', '::1', 'localhost:80', 'localhost:443']);

if ($is_localhost) {
    // LOCALHOST configuration (XAMPP)
    $host = getenv('DB_HOST') ?: "127.0.0.1";  // or "127.0.0.1"
    $user = getenv('DB_USER') ?: "root";
    $pass = getenv('DB_PASS') ?: "";  // XAMPP default is empty password (change if you set a password)
    $dbname = getenv('DB_NAME') ?: "barcie_db";
} else {
    // REMOTE SERVER configuration
    $host = getenv('DB_HOST') ?: "10.20.0.2";  // Server database IP
    $user = getenv('DB_USER') ?: "root";
    $pass = getenv('DB_PASS') ?: "root";
    $dbname = getenv('DB_NAME') ?: "barcie_db";
}

// Enable MySQLi error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Create connection
try {
    $conn = new mysqli($host, $user, $pass, $dbname);
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    // Log the error for debugging
    error_log("Database connection failed: " . $e->getMessage());
    
    // Return user-friendly error
    if (php_sapi_name() === 'cli') {
        die("Database connection failed: " . $e->getMessage() . "\n");
    } else {
        die("Database connection failed. Please check server configuration.");
    }
}

// Check connection (backup check)
if ($conn->connect_error) {
    error_log("Connection error: " . $conn->connect_error);
    die("Connection failed: " . $conn->connect_error);
}


?>