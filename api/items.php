<?php
require __DIR__ . '/Bootstrap.php';

function normalize_item_image_path($path) {
    if (!is_string($path) || trim($path) === '') {
        return '';
    }
    if (preg_match('/^https?:\/\//i', $path)) {
        return $path;
    }
    $path = str_replace('\\', '/', trim($path));
    return ltrim($path, '/');
}

function normalize_item_row_images(array $item) {
    $item['image'] = normalize_item_image_path($item['image'] ?? '');

    if (!empty($item['images'])) {
        $decoded = json_decode($item['images'], true);
        if (is_array($decoded)) {
            $normalized = array_values(array_filter(array_map('normalize_item_image_path', $decoded)));
            $item['images'] = json_encode($normalized);
            if (empty($item['image']) && !empty($normalized)) {
                $item['image'] = $normalized[0];
            }
        } else {
            $item['images'] = json_encode([]);
        }
    } else {
        $item['images'] = json_encode([]);
    }

    return $item;
}

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
            $item = normalize_item_row_images($item);
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
    while ($r = $res->fetch_assoc()) {
        $items[] = normalize_item_row_images($r);
    }

    json_ok(['items' => $items, 'count' => count($items)]);
} catch (Throwable $e) {
    json_error('Failed to fetch items', 500, ['message' => $e->getMessage()]);
}
