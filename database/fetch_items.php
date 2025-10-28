<?php
header('Content-Type: application/json');

include __DIR__ . '/db_connect.php';

$sql = "SELECT id, name, item_type, room_number, description, capacity, price, image, addons FROM items ORDER BY created_at DESC";
$result = $conn->query($sql);

$items = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Normalize addons field to array
        if (isset($row['addons']) && $row['addons'] !== null) {
            $decoded = json_decode($row['addons'], true);
            $row['addons'] = ($decoded === null) ? [] : $decoded;
        } else {
            $row['addons'] = [];
        }
        $items[] = $row;
    }
}

echo json_encode($items);
$conn->close();
?>
