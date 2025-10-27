<?php
// Clean up database entries with missing image files
require 'database/db_connect.php';

echo "=== Cleaning Up Missing Images ===" . PHP_EOL;

$uploadsDir = __DIR__ . '/uploads/';

// Find items with image references
$result = $conn->query("SELECT id, name, image FROM items WHERE image IS NOT NULL AND image != ''");
$cleanedCount = 0;

while ($row = $result->fetch_assoc()) {
    $imagePath = $uploadsDir . basename($row['image']);
    
    if (!file_exists($imagePath)) {
        echo "Cleaning: {$row['name']} - missing file: {$row['image']}" . PHP_EOL;
        
        // Set image to NULL for items with missing files
        $stmt = $conn->prepare("UPDATE items SET image = NULL WHERE id = ?");
        $stmt->bind_param("i", $row['id']);
        $stmt->execute();
        
        $cleanedCount++;
    } else {
        echo "Keeping: {$row['name']} - file exists: {$row['image']}" . PHP_EOL;
    }
}

echo "\n✓ Cleaned up $cleanedCount items with missing images" . PHP_EOL;
echo "These items will now show the fallback logo instead of broken images." . PHP_EOL;
?>