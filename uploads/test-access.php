<?php
/**
 * DIAGNOSTIC SCRIPT - Delete after testing
 * This checks if uploads directory is accessible
 */

header('Content-Type: text/html; charset=utf-8');

echo "<h2>🔍 Uploads Directory Diagnostics</h2>";

// Check current directory
echo "<h3>Current Directory:</h3>";
echo "<p>" . __DIR__ . "</p>";

// Check if .htaccess exists
$htaccessPath = __DIR__ . '/.htaccess';
echo "<h3>.htaccess Status:</h3>";
if (file_exists($htaccessPath)) {
    echo "<p>✅ .htaccess exists</p>";
    echo "<p>Readable: " . (is_readable($htaccessPath) ? "✅ Yes" : "❌ No") . "</p>";
} else {
    echo "<p>❌ .htaccess NOT found</p>";
}

// List all image files
echo "<h3>Image Files in Directory:</h3>";
$files = glob(__DIR__ . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
if (empty($files)) {
    echo "<p>❌ No image files found</p>";
} else {
    echo "<ul>";
    foreach ($files as $file) {
        $filename = basename($file);
        $perms = substr(sprintf('%o', fileperms($file)), -4);
        $readable = is_readable($file) ? "✅" : "❌";
        $size = filesize($file);
        
        echo "<li>";
        echo "<strong>$filename</strong><br>";
        echo "Permissions: $perms | Readable: $readable | Size: " . number_format($size) . " bytes<br>";
        
        // Try to create web path
        $webPath = '/uploads/' . $filename;
        echo "Web Path: <a href='$webPath' target='_blank'>$webPath</a><br>";
        
        // Show as image
        echo "<img src='$webPath' style='max-width:150px; max-height:100px; margin:5px;' onerror='this.style.border=\"2px solid red\"; this.alt=\"❌ Failed to load\"'>";
        echo "</li><br>";
    }
    echo "</ul>";
}

// Check directory permissions
echo "<h3>Directory Permissions:</h3>";
$dirPerms = substr(sprintf('%o', fileperms(__DIR__)), -4);
echo "<p>Permissions: $dirPerms</p>";
echo "<p>Writable: " . (is_writable(__DIR__) ? "✅ Yes" : "❌ No") . "</p>";

// Check server info
echo "<h3>Server Info:</h3>";
echo "<p>Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</p>";
echo "<p>Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "</p>";

echo "<hr>";
echo "<p><strong>⚠️ DELETE THIS FILE after testing for security!</strong></p>";
?>
