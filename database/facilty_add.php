<?php
require "db_connect.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['name'];
  $capacity = $_POST['capacity'];
  $price = $_POST['price'];

  $stmt = $conn->prepare("INSERT INTO facilities (name, capacity, price) VALUES (?, ?, ?)");
  $stmt->bind_param("sid", $name, $capacity, $price);
  $stmt->execute();
}
header("Location: dashboard.php");
exit;
