<?php
require __DIR__ . '/database/db_connect.php';

echo "=== Fixing Image Paths ===\n\n";

// Get all items
$result = $conn->query('SELECT id, name FROM items');

// Get available images from uploads directory
$uploadDir = __DIR__ . '/uploads/';
$availableImages = glob($uploadDir . '*.jpg');

echo "Found " . count($availableImages) . " images in uploads directory\n\n";

// Update each item with an available image
$imageIndex = 0;
while ($row = $result->fetch_assoc()) {
    $itemId = $row['id'];
    $itemName = $row['name'];
    
    // Use multiple images if available (3-4 per item)
    $itemImages = [];
    for ($i = 0; $i < 3 && $imageIndex < count($availableImages); $i++) {
        $fullPath = $availableImages[$imageIndex];
        $filename = basename($fullPath);
        $itemImages[] = 'uploads/' . $filename;
        $imageIndex++;
        if ($imageIndex >= count($availableImages)) {
            $imageIndex = 0; // Reuse images if we run out
        }
    }
    
    // Set the first image as the main image
    $mainImage = $itemImages[0] ?? 'assets/images/imageBg/barcie_logo.jpg';
    $imagesJson = json_encode($itemImages);
    
    // Update the database
    $stmt = $conn->prepare("UPDATE items SET image = ?, images = ? WHERE id = ?");
    $stmt->bind_param('ssi', $mainImage, $imagesJson, $itemId);
    
    if ($stmt->execute()) {
        echo "✓ Updated item #$itemId ($itemName)\n";
        echo "  image: $mainImage\n";
        echo "  images: $imagesJson\n\n";
    } else {
        echo "✗ Failed to update item #$itemId: " . $conn->error . "\n\n";
    }
}

echo "\n=== Done! ===\n";
echo "Please refresh your guest page to see the images.\n";
