<?php
// filepath: c:\xampp\htdocs\barcie_php\db_connect.php

// Start session


// Database connection details (allow environment overrides for Docker)
$host = getenv('DB_HOST') ?: "10.20.0.2"; // Live database server
$user = getenv('DB_USER') ?: "root";       // Default username
$pass = getenv('DB_PASS') ?: "root";       // Live database password
$dbname = getenv('DB_NAME') ?: "barcie_db";


// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


?>