<?php
header('Content-Type: application/json');

include __DIR__ . '/db_connect.php';

$sql = "SELECT id, name, item_type, room_number, description, capacity, price, image FROM items ORDER BY created_at DESC";
$result = $conn->query($sql);

$items = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
}

echo json_encode($items);
$conn->close();
?>
