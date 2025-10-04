<?php
// filepath: c:\xampp\htdocs\barcie_php\db_connect.php

// Start session


// Database connection details (allow environment overrides for Docker)
$host = getenv('DB_HOST') ?: "10.20.0.2"; // Change to your Docker host IP if needed
$user = getenv('DB_USER') ?: "root";       // Default username in XAMPP / Docker
$pass = getenv('DB_PASS') ?: "root";           // Default password in XAMPP / Docker
$dbname = getenv('DB_NAME') ?: "barcie_db";


// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


?>