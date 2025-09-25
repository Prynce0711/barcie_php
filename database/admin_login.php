<?php
session_start(); // start session

// ✅ include database connection (make sure db_connect.php is inside /database)
include __DIR__ . '/db_connect.php';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);  
    $password = trim($_POST['password']);

    // Validate input fields
    if (empty($username) || empty($password)) {
        echo "<script>alert('Please fill in all fields.'); window.history.back();</script>";
        exit;
    }

    // ✅ Query the database for the user by username
    $stmt = $conn->prepare("SELECT id, password FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $storedPassword);
        $stmt->fetch();

        // ✅ If passwords are stored as plain text (not recommended)
        if ($password === $storedPassword) {
            // Set session variables and redirect to the dashboard
            $_SESSION['admin_id'] = $id;
            $_SESSION['admin_username'] = $username;
            header("Location: ../dashboard.php");
            exit;
        } else {
            echo "<script>alert('Invalid password.'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Username not found.'); window.history.back();</script>";
    }

    
    $stmt->close();
}

$conn->close();




