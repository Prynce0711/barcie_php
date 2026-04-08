<?php
header('Content-Type: application/json');

include __DIR__ . '/db_connect.php';

function normalize_fetch_item_path($path) {
    if (!is_string($path) || trim($path) === '') {
        return '';
    }
    if (preg_match('/^https?:\/\//i', $path)) {
        return $path;
    }
    $path = str_replace('\\', '/', trim($path));
    return ltrim($path, '/');
}

$sql = "SELECT id, name, item_type, room_number, description, capacity, price, image, images, addons FROM items ORDER BY created_at DESC";
$result = $conn->query($sql);

$items = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if (isset($row['image'])) {
            $row['image'] = normalize_fetch_item_path($row['image']);
        }
        if (!empty($row['images'])) {
            $imgDecoded = json_decode($row['images'], true);
            if (is_array($imgDecoded)) {
                $row['images'] = json_encode(array_values(array_filter(array_map('normalize_fetch_item_path', $imgDecoded))));
            } else {
                $row['images'] = json_encode([]);
            }
        } else {
            $row['images'] = json_encode([]);
        }

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