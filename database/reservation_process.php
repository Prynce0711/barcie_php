<?php
require __DIR__ . "/database/db_connect.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $guestName = $_POST['guest_name'];
    $contact   = $_POST['contact_number'];
    $email     = $_POST['email'];
    $room      = $_POST['room_type'];
    $checkin   = $_POST['checkin'];
    $checkout  = $_POST['checkout'];
    $occupants = $_POST['occupants'];
    $company   = $_POST['company'];

    $stmt = $conn->prepare("INSERT INTO reservations 
        (guest_name, contact_number, email, room_type, checkin, checkout, occupants, company_affiliation) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssis", $guestName, $contact, $email, $room, $checkin, $checkout, $occupants, $company);
    $stmt->execute();

    // Reservation number padded with zeros
    $reservationNumber = str_pad($stmt->insert_id, 4, "0", STR_PAD_LEFT);

    $stmt->close();
    $conn->close();

    echo "<script>
        alert('✅ Reservation successful!\\nYour Reservation Number is: {$reservationNumber}');
        window.location.href = 'guest.php';
    </script>";
} else {
    header("Location: guest.php");
    exit;
}
