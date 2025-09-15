<?php
// filepath: c:\xampp\htdocs\barcie_php\db_connect.php

// Start session


// Database connection details
$host = "localhost";
$user = "root";       // Default username in XAMPP
$pass = "";           // Default password in XAMPP
$dbname = "barcie_db";

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>