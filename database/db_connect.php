<?php
// filepath: c:\xampp\htdocs\barcie_php\src\database\db_connect.php

require_once __DIR__ . '/config.php';

// Suppress error output for API responses
error_reporting(0);
ini_set('display_errors', 0);

// Load environment variables from .env file
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0)
            continue;
        if (strpos($line, '=') === false)
            continue;

        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        if (!getenv($name)) {
            putenv("$name=$value");
        }
    }
}

// Load local overrides from .env.local if present. These should override .env values.
if (file_exists(__DIR__ . '/../.env.local')) {
    $lines = file(__DIR__ . '/../.env.local', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0)
            continue;
        if (strpos($line, '=') === false)
            continue;

        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        // For local overrides we always set/overwrite the environment variable
        putenv("$name=$value");
    }
}

// Database connection details
// Auto-detect environment: use localhost DB when on localhost, remote DB when on server.
$serverName = strtolower((string) ($_SERVER['SERVER_NAME'] ?? ''));
$httpHostRaw = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
$httpHost = preg_replace('/:\\d+$/', '', $httpHostRaw);
$remoteAddr = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
$serverAddr = (string) ($_SERVER['SERVER_ADDR'] ?? '');

$is_localhost = (
    $serverName === 'localhost' ||
    $httpHost === 'localhost' ||
    $httpHost === '127.0.0.1' ||
    $httpHost === '::1' ||
    $remoteAddr === '127.0.0.1' ||
    $remoteAddr === '::1' ||
    $serverAddr === '127.0.0.1' ||
    $serverAddr === '::1' ||
    php_sapi_name() === 'cli'
);

if ($is_localhost) {
    // LOCALHOST configuration (XAMPP)
    // Use local-specific env keys first; do not inherit live DB_HOST/DB_PASS.
    $host = getenv('DB_HOST_LOCAL') ?: "127.0.0.1";
    $user = getenv('DB_USER_LOCAL') ?: "root";
    $pass = getenv('DB_PASS_LOCAL');
    if ($pass === false) {
        $pass = ""; // XAMPP default is empty password
    }
    $dbname = getenv('DB_NAME_LOCAL') ?: (getenv('DB_NAME') ?: "barcie_db");
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
$conn = @new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_errno) {
    $message = sprintf(
        "Database connection failed (%s/%d): %s",
        $host,
        $conn->connect_errno,
        $conn->connect_error
    );
    error_log($message);

    if (php_sapi_name() === 'cli') {
        die($message . "\n");
    }

    die("Database connection failed. Please check server configuration.");
}

$conn->set_charset("utf8mb4");

// Set MySQL timezone to match PHP timezone (Asia/Manila)
$conn->query("SET time_zone = '+08:00'");


?>