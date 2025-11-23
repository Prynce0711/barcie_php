<?php
require __DIR__ . '/bootstrap.php';

try {
    if (!table_exists($conn, 'items')) {
        json_error('Items table does not exist', 500);
    }

    // Check if getting single item
    if (isset($_GET['id'])) {
        $item_id = (int)$_GET['id'];
        $sql = "SELECT id, name, item_type, room_number, description, capacity, price, image, images, room_status, 
                       COALESCE(average_rating, 0) as average_rating, 
                       COALESCE(total_reviews, 0) as total_reviews
                FROM items 
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $item_id);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($res && $res->num_rows > 0) {
            $item = $res->fetch_assoc();
            json_ok(['item' => $item]);
        } else {
            json_error('Item not found', 404);
        }
        exit();
    }
    
    $sql = "SELECT id, name, item_type, room_number, description, capacity, price, image, images, room_status,
                   COALESCE(average_rating, 0) as average_rating, 
                   COALESCE(total_reviews, 0) as total_reviews
            FROM items 
            ORDER BY created_at DESC";
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
