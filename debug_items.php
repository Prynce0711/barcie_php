<?php
/**
 * Debug script to check items in database
 * Access: http://localhost/barcie_php/debug_items.php
 */

header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/src/database/db_connect.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Items Debug</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #333; border-bottom: 2px solid #667eea; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }
        th { background: #667eea; color: white; }
        tr:nth-child(even) { background: #f9f9f9; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .image-preview { width: 80px; height: 60px; object-fit: cover; border-radius: 4px; }
        .status-badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; }
        .status-available { background: #d4edda; color: #155724; }
        .status-occupied { background: #f8d7da; color: #721c24; }
        .info-box { background: #e7f3ff; padding: 15px; border-left: 4px solid #2196F3; margin: 20px 0; }
    </style>
</head>
<body>
<div class='container'>";

echo "<h1>🔍 Items Database Debug</h1>";

try {
    // Get table info
    $table_check = $conn->query("SHOW TABLES LIKE 'items'");
    
    if ($table_check->num_rows == 0) {
        echo "<p class='error'>❌ Items table does not exist!</p>";
        exit;
    }
    
    echo "<p class='success'>✅ Items table exists</p>";
    
    // Get table structure
    echo "<h2>Table Structure</h2>";
    echo "<table><tr><th>Field</th><th>Type</th><th>Null</th><th>Default</th></tr>";
    
    $structure = $conn->query("DESCRIBE items");
    while ($row = $structure->fetch_assoc()) {
        echo "<tr>";
        echo "<td><strong>{$row['Field']}</strong></td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Get all items
    $result = $conn->query("SELECT * FROM items ORDER BY created_at DESC");
    $total_items = $result->num_rows;
    
    echo "<h2>Total Items: $total_items</h2>";
    
    if ($total_items == 0) {
        echo "<div class='info-box'>ℹ️ No items found in database. Try adding some items through the admin dashboard.</div>";
    } else {
        echo "<table>";
        echo "<tr>
                <th>ID</th>
                <th>Image</th>
                <th>Name</th>
                <th>Type</th>
                <th>Room #</th>
                <th>Capacity</th>
                <th>Price</th>
                <th>Status</th>
                <th>Image Path</th>
                <th>Created</th>
              </tr>";
        
        while ($item = $result->fetch_assoc()) {
            $image_path = $item['image'] ?? '';
            $image_exists = !empty($image_path) && file_exists(__DIR__ . '/' . $image_path);
            $room_status = $item['room_status'] ?? 'available';
            
            echo "<tr>";
            echo "<td>{$item['id']}</td>";
            
            // Image preview
            echo "<td>";
            if ($image_exists) {
                echo "<img src='{$image_path}' class='image-preview' alt='Preview'>";
            } else if (!empty($image_path)) {
                echo "<span style='color: red;'>❌ File not found</span>";
            } else {
                echo "<span style='color: gray;'>No image</span>";
            }
            echo "</td>";
            
            echo "<td><strong>{$item['name']}</strong></td>";
            echo "<td>" . ucfirst($item['item_type']) . "</td>";
            echo "<td>" . ($item['room_number'] ?? '-') . "</td>";
            echo "<td>{$item['capacity']}</td>";
            echo "<td>₱" . number_format($item['price']) . "</td>";
            echo "<td><span class='status-badge status-{$room_status}'>" . ucfirst($room_status) . "</span></td>";
            echo "<td><code>" . ($image_path ?: 'NULL') . "</code>";
            
            if (!empty($image_path)) {
                echo "<br><small>Exists: " . ($image_exists ? '✅ Yes' : '❌ No') . "</small>";
            }
            echo "</td>";
            echo "<td>" . ($item['created_at'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Summary statistics
        $stats = [];
        $stats_query = $conn->query("SELECT 
            item_type,
            COUNT(*) as count,
            SUM(CASE WHEN room_status = 'available' THEN 1 ELSE 0 END) as available_count,
            SUM(CASE WHEN image IS NOT NULL AND image != '' THEN 1 ELSE 0 END) as with_image_count
            FROM items 
            GROUP BY item_type");
        
        echo "<h2>Statistics</h2>";
        echo "<table>";
        echo "<tr><th>Type</th><th>Total</th><th>Available</th><th>With Images</th></tr>";
        
        while ($stat = $stats_query->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . ucfirst($stat['item_type']) . "</td>";
            echo "<td>{$stat['count']}</td>";
            echo "<td>{$stat['available_count']}</td>";
            echo "<td>{$stat['with_image_count']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Check uploads directory
    echo "<h2>Uploads Directory</h2>";
    $uploads_dir = __DIR__ . '/uploads';
    
    if (is_dir($uploads_dir)) {
        $files = array_diff(scandir($uploads_dir), ['.', '..']);
        echo "<p class='success'>✅ Uploads directory exists (" . count($files) . " files)</p>";
        
        if (count($files) > 0) {
            echo "<ul>";
            foreach (array_slice($files, 0, 10) as $file) {
                $file_path = 'uploads/' . $file;
                $file_size = filesize($uploads_dir . '/' . $file);
                echo "<li><code>$file_path</code> (" . number_format($file_size / 1024, 2) . " KB)</li>";
            }
            if (count($files) > 10) {
                echo "<li>... and " . (count($files) - 10) . " more files</li>";
            }
            echo "</ul>";
        }
    } else {
        echo "<p class='error'>❌ Uploads directory does not exist at: $uploads_dir</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<div style='margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 4px;'>
    <h3>Quick Actions</h3>
    <ul>
        <li><a href='dashboard.php#rooms'>Go to Admin Dashboard</a></li>
        <li><a href='Guest.php#rooms'>Go to Guest View</a></li>
        <li><a href='api/items.php' target='_blank'>View API Response</a></li>
        <li><a href='database/ensure_items_table.php'>Run Database Migration</a></li>
    </ul>
</div>";

echo "</div></body></html>";

$conn->close();
?>
