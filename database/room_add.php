<?php
require "db_connect.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $room_number = $_POST['room_number'];
  $type = $_POST['type'];

  $stmt = $conn->prepare("INSERT INTO rooms (room_number, type) VALUES (?, ?)");
  $stmt->bind_param("ss", $room_number, $type);
  $stmt->execute();
}
header("Location: dashboard.php");
exit;
