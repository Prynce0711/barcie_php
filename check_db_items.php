<?php
require 'database/db_connect.php';

echo "=== Database Items Check ===" . PHP_EOL;

// Check all items
$result = $conn->query('SELECT id, name, image FROM items ORDER BY id');
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: {$row['id']}, Name: {$row['name']}, Image: " . ($row['image'] ?: 'NULL') . PHP_EOL;
    }
} else {
    echo "Query failed: " . $conn->error . PHP_EOL;
}

// Count items with and without images
$with_images = $conn->query('SELECT COUNT(*) as count FROM items WHERE image IS NOT NULL AND image != ""')->fetch_assoc()['count'];
$without_images = $conn->query('SELECT COUNT(*) as count FROM items WHERE image IS NULL OR image = ""')->fetch_assoc()['count'];

echo PHP_EOL . "=== Summary ===" . PHP_EOL;
echo "Items with images: $with_images" . PHP_EOL;
echo "Items without images: $without_images" . PHP_EOL;
?>