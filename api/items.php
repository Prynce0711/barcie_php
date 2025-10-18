<?php
require __DIR__ . '/bootstrap.php';

try {
    if (!table_exists($conn, 'items')) {
        json_error('Items table does not exist', 500);
    }

    $sql = "SELECT id, name, item_type, room_number, description, capacity, price, image, room_status FROM items ORDER BY created_at DESC";
    $res = $conn->query($sql);
    if (!$res) {
        json_error('Query failed: ' . $conn->error, 500);
    }

    $items = [];
    while ($r = $res->fetch_assoc()) { $items[] = $r; }

    json_ok(['items' => $items, 'count' => count($items)]);
} catch (Throwable $e) {
    json_error('Failed to fetch items', 500, ['message' => $e->getMessage()]);
}
