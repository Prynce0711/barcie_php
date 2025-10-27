<?php
// Live server upload diagnostic
header('Content-Type: text/plain');

echo "=== Live Server Upload Diagnostic ===" . PHP_EOL;
echo "Server: " . ($_SERVER['HTTP_HOST'] ?? 'Unknown') . PHP_EOL;
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . PHP_EOL;
echo "Current Directory: " . __DIR__ . PHP_EOL;
echo "Date: " . date('Y-m-d H:i:s') . PHP_EOL;

echo "\n1. PHP Upload Settings:" . PHP_EOL;
echo "  file_uploads: " . (ini_get('file_uploads') ? 'ON' : 'OFF') . PHP_EOL;
echo "  upload_max_filesize: " . ini_get('upload_max_filesize') . PHP_EOL;
echo "  post_max_size: " . ini_get('post_max_size') . PHP_EOL;
echo "  max_file_uploads: " . ini_get('max_file_uploads') . PHP_EOL;
echo "  memory_limit: " . ini_get('memory_limit') . PHP_EOL;

echo "\n2. Uploads Directory:" . PHP_EOL;
$uploadsDir = __DIR__ . '/uploads/';
echo "  Path: $uploadsDir" . PHP_EOL;
echo "  Exists: " . (is_dir($uploadsDir) ? 'YES' : 'NO') . PHP_EOL;
echo "  Writable: " . (is_writable($uploadsDir) ? 'YES' : 'NO') . PHP_EOL;

if (is_dir($uploadsDir)) {
    $files = scandir($uploadsDir);
    $imageFiles = array_filter($files, function($file) {
        return preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file) && $file !== '.' && $file !== '..';
    });
    
    echo "  Image files: " . count($imageFiles) . PHP_EOL;
    foreach (array_slice($imageFiles, 0, 5) as $file) { // Show only first 5
        $filePath = $uploadsDir . $file;
        $size = filesize($filePath);
        $mtime = date('Y-m-d H:i:s', filemtime($filePath));
        echo "    - $file (" . number_format($size) . " bytes, $mtime)" . PHP_EOL;
    }
}

echo "\n3. Database Check:" . PHP_EOL;
try {
    require __DIR__ . '/database/db_connect.php';
    $result = $conn->query("SELECT COUNT(*) as count FROM items WHERE image IS NOT NULL");
    $withImages = $result->fetch_assoc()['count'];
    echo "  Items with images: $withImages" . PHP_EOL;
    
    // Check recent items
    $recent = $conn->query("SELECT name, image, created_at FROM items WHERE image IS NOT NULL ORDER BY created_at DESC LIMIT 3");
    echo "  Recent items with images:" . PHP_EOL;
    while ($row = $recent->fetch_assoc()) {
        $imageFile = $uploadsDir . basename($row['image']);
        $exists = file_exists($imageFile) ? 'EXISTS' : 'MISSING';
        echo "    - {$row['name']}: {$row['image']} [$exists]" . PHP_EOL;
    }
} catch (Exception $e) {
    echo "  Database error: " . $e->getMessage() . PHP_EOL;
}

echo "\n4. Test Write:" . PHP_EOL;
$testFile = $uploadsDir . 'diagnostic_test_' . time() . '.txt';
if (file_put_contents($testFile, 'test write ' . date('Y-m-d H:i:s'))) {
    echo "  Write test: SUCCESS" . PHP_EOL;
    unlink($testFile);
} else {
    echo "  Write test: FAILED" . PHP_EOL;
}

echo "\n=== End Diagnostic ===" . PHP_EOL;
?>