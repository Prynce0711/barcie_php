<?php
// booking_actions.php
session_start();
require __DIR__ . "/database/db_connect.php";

if (!isset($_SESSION['admin_id'])) {
    die("Unauthorized access!");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingId = intval($_POST['booking_id']);
    $action = $_POST['action'];

    // Map actions to statuses
    $statusMap = [
        "approve" => "confirmed",
        "reject" => "rejected",
        "checkin" => "checked_in",
        "checkout" => "checked_out",
        "cancel" => "cancelled"
    ];

    if (array_key_exists($action, $statusMap)) {
        $newStatus = $statusMap[$action];
        $stmt = $conn->prepare("UPDATE bookings SET status=? WHERE id=?");
        $stmt->bind_param("si", $newStatus, $bookingId);

        if ($stmt->execute()) {
            $_SESSION['msg'] = "Booking #$bookingId updated to $newStatus!";
        } else {
            $_SESSION['msg'] = "Error updating booking!";
        }

        $stmt->close();
    }
}

// Redirect back
header("Location: dashboard.php#bookings");
exit;
