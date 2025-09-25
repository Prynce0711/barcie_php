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

// reservation

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reserve'])) {
    $guest_name     = $_POST['guest_name'];
    $contact_number = $_POST['contact_number'];
    $email          = $_POST['email'];
    $room_type      = $_POST['room_type'];
    $checkin        = $_POST['checkin'];
    $checkout       = $_POST['checkout'];
    $occupants      = $_POST['occupants'];
    $company        = $_POST['company'];

    $sql = "INSERT INTO reservations 
            (guest_name, contact_number, email, room_type, checkin, checkout, occupants, company)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssis", $guest_name, $contact_number, $email, $room_type, $checkin, $checkout, $occupants, $company);

    if ($stmt->execute()) {
        $reservation_id = str_pad($stmt->insert_id, 4, "0", STR_PAD_LEFT);
        echo "<script>alert('✅ Reservation successful! Your reservation number is: $reservation_id');</script>";
    } else {
        echo "<script>alert('❌ Error: Could not complete reservation.');</script>";
    }

    $stmt->close();
}
?>
?>