<?php
require "database/db_connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $room_id = $_POST['room_id'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE rooms SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $room_id);
    $stmt->execute();

    header("Location: dashboard.php#manage-room");
    exit;
}
?>
