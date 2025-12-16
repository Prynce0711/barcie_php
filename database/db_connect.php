<?php
// filepath: c:\xampp\htdocs\barcie_php\src\database\db_connect.php

// Suppress error output for API responses
error_reporting(0);
ini_set('display_errors', 0);

// Load environment variables from .env file
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        if (!getenv($name)) {
            putenv("$name=$value");
        }
    }
}

// Database connection details
// Auto-detect environment: use localhost DB when on localhost, remote DB when on server
$is_localhost = in_array($_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost', 
    ['localhost', '127.0.0.1', '::1', 'localhost:80', 'localhost:443']);

if ($is_localhost) {
    // LOCALHOST configuration (XAMPP)
    $host = getenv('DB_HOST') ?: "127.0.0.1";
    $user = getenv('DB_USER') ?: "root";
    $pass = getenv('DB_PASS') ?: "";  // XAMPP default is empty password
    $dbname = getenv('DB_NAME') ?: "barcie_db";
} else {
    // REMOTE/LIVE SERVER configuration
    $host = getenv('DB_HOST') ?: "10.20.0.2";  // Server database IP
    $user = getenv('DB_USER') ?: "root";
    $pass = getenv('DB_PASS') ?: "root";
    $dbname = getenv('DB_NAME') ?: "barcie_db";
}

// Enable MySQLi error reporting but don't display errors
mysqli_report(MYSQLI_REPORT_OFF);

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