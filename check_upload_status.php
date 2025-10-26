<?php
/**
 * UPLOAD DIAGNOSTIC TOOL - Deploy this to live server
 * Access via: https://your-domain.com/check_upload_status.php
 * DELETE AFTER TESTING!
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Upload Status Check</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { background: white; padding: 20px; border-radius: 8px; max-width: 900px; margin: 0 auto; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; }
        .code { background: #f4f4f4; padding: 10px; border-radius: 4px; font-family: monospace; margin: 5px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Upload Directory Status Check</h1>
        <p><strong>Server:</strong> <?= $_SERVER['HTTP_HOST'] ?? 'Unknown' ?></p>
        <p><strong>Timestamp:</strong> <?= date('Y-m-d H:i:s') ?></p>
        
        <hr>
        
        <?php
        $uploadsDir = __DIR__ . '/uploads';
        $relativeUploadsPath = '/uploads';
        
        // Check 1: Uploads directory exists
        echo "<h2>1. Directory Existence</h2>";
        if (is_dir($uploadsDir)) {
            echo "<p class='success'>✅ Directory exists: <code>$uploadsDir</code></p>";
        } else {
            echo "<p class='error'>❌ Directory does NOT exist: <code>$uploadsDir</code></p>";
            echo "<p class='warning'>⚠️ Creating directory...</p>";
            if (mkdir($uploadsDir, 0755, true)) {
                echo "<p class='success'>✅ Directory created successfully!</p>";
            } else {
                echo "<p class='error'>❌ Failed to create directory. Check parent folder permissions.</p>";
            }
        }
        
        // Check 2: Directory permissions
        echo "<h2>2. Directory Permissions</h2>";
        if (is_dir($uploadsDir)) {
            $perms = substr(sprintf('%o', fileperms($uploadsDir)), -4);
            echo "<p>Current permissions: <strong>$perms</strong></p>";
            
            if (is_writable($uploadsDir)) {
                echo "<p class='success'>✅ Directory is WRITABLE</p>";
            } else {
                echo "<p class='error'>❌ Directory is NOT WRITABLE</p>";
                echo "<div class='code'>Fix via SSH: chmod 755 $uploadsDir</div>";
            }
            
            if (is_readable($uploadsDir)) {
                echo "<p class='success'>✅ Directory is READABLE</p>";
            } else {
                echo "<p class='error'>❌ Directory is NOT READABLE</p>";
            }
        }
        
        // Check 3: .htaccess file
        echo "<h2>3. .htaccess File</h2>";
        $htaccessFile = $uploadsDir . '/.htaccess';
        if (file_exists($htaccessFile)) {
            echo "<p class='success'>✅ .htaccess exists</p>";
            if (is_readable($htaccessFile)) {
                $perms = substr(sprintf('%o', fileperms($htaccessFile)), -4);
                echo "<p>Permissions: <strong>$perms</strong></p>";
            }
        } else {
            echo "<p class='error'>❌ .htaccess NOT found</p>";
            echo "<p class='warning'>⚠️ Upload the .htaccess file to the uploads folder!</p>";
        }
        
        // Check 4: Existing image files
        echo "<h2>4. Image Files</h2>";
        $imageFiles = glob($uploadsDir . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
        
        if (empty($imageFiles)) {
            echo "<p class='error'>❌ No image files found in uploads directory</p>";
            echo "<p class='warning'>⚠️ Upload your image files to: <code>$uploadsDir</code></p>";
        } else {
            echo "<p class='success'>✅ Found " . count($imageFiles) . " image file(s)</p>";
            echo "<table>";
            echo "<tr><th>Filename</th><th>Size</th><th>Permissions</th><th>Readable</th><th>Web Test</th></tr>";
            
            foreach ($imageFiles as $file) {
                $filename = basename($file);
                $size = filesize($file);
                $perms = substr(sprintf('%o', fileperms($file)), -4);
                $readable = is_readable($file);
                $webPath = $relativeUploadsPath . '/' . $filename;
                
                echo "<tr>";
                echo "<td><code>$filename</code></td>";
                echo "<td>" . number_format($size) . " bytes</td>";
                echo "<td>$perms</td>";
                echo "<td>" . ($readable ? "<span class='success'>✅</span>" : "<span class='error'>❌</span>") . "</td>";
                echo "<td><a href='$webPath' target='_blank'>Test Link</a></td>";
                echo "</tr>";
            }
            echo "</table>";
            
            // Show sample images
            echo "<h3>Sample Images Preview:</h3>";
            foreach (array_slice($imageFiles, 0, 3) as $file) {
                $filename = basename($file);
                $webPath = $relativeUploadsPath . '/' . $filename;
                echo "<div style='display:inline-block; margin:10px;'>";
                echo "<img src='$webPath' style='max-width:200px; max-height:150px; border:2px solid #ddd; border-radius:4px;' ";
                echo "onerror='this.style.border=\"3px solid red\"; this.alt=\"❌ FAILED TO LOAD\"' alt='$filename'>";
                echo "<br><small>$filename</small>";
                echo "</div>";
            }
        }
        
        // Check 5: Database records
        echo "<h2>5. Database Image Records</h2>";
        try {
            require_once __DIR__ . '/src/database/db_connect.php';
            $result = $conn->query("SELECT id, name, image FROM items WHERE image IS NOT NULL ORDER BY created_at DESC LIMIT 5");
            
            if ($result && $result->num_rows > 0) {
                echo "<p class='success'>✅ Found " . $result->num_rows . " items with images in database</p>";
                echo "<table>";
                echo "<tr><th>ID</th><th>Name</th><th>Image Path (DB)</th><th>File Exists?</th><th>Web Test</th></tr>";
                
                while ($row = $result->fetch_assoc()) {
                    $dbImagePath = $row['image'];
                    $fullPath = __DIR__ . '/' . $dbImagePath;
                    $fileExists = file_exists($fullPath);
                    $webPath = '/' . $dbImagePath;
                    
                    echo "<tr>";
                    echo "<td>{$row['id']}</td>";
                    echo "<td>{$row['name']}</td>";
                    echo "<td><code>$dbImagePath</code></td>";
                    echo "<td>" . ($fileExists ? "<span class='success'>✅</span>" : "<span class='error'>❌ MISSING</span>") . "</td>";
                    echo "<td><a href='$webPath' target='_blank'>Test</a></td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p class='warning'>⚠️ No items with images in database</p>";
            }
        } catch (Exception $e) {
            echo "<p class='error'>❌ Database error: " . $e->getMessage() . "</p>";
        }
        
        // Check 6: PHP Upload Settings
        echo "<h2>6. PHP Upload Configuration</h2>";
        echo "<table>";
        echo "<tr><th>Setting</th><th>Value</th></tr>";
        echo "<tr><td>upload_max_filesize</td><td><strong>" . ini_get('upload_max_filesize') . "</strong></td></tr>";
        echo "<tr><td>post_max_size</td><td><strong>" . ini_get('post_max_size') . "</strong></td></tr>";
        echo "<tr><td>max_file_uploads</td><td><strong>" . ini_get('max_file_uploads') . "</strong></td></tr>";
        echo "<tr><td>file_uploads</td><td><strong>" . (ini_get('file_uploads') ? 'Enabled' : 'Disabled') . "</strong></td></tr>";
        echo "</table>";
        
        // Check 7: Server Info
        echo "<h2>7. Server Information</h2>";
        echo "<table>";
        echo "<tr><th>Property</th><th>Value</th></tr>";
        echo "<tr><td>Document Root</td><td><code>" . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "</code></td></tr>";
        echo "<tr><td>Server Software</td><td>" . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</td></tr>";
        echo "<tr><td>PHP Version</td><td>" . PHP_VERSION . "</td></tr>";
        echo "<tr><td>Current Script</td><td><code>" . __FILE__ . "</code></td></tr>";
        echo "</table>";
        
        // Action Items
        echo "<hr>";
        echo "<h2>📋 Action Items</h2>";
        echo "<ol>";
        
        if (!is_dir($uploadsDir)) {
            echo "<li class='error'>Create the uploads directory</li>";
        }
        if (is_dir($uploadsDir) && !is_writable($uploadsDir)) {
            echo "<li class='error'>Set uploads directory permissions to 755</li>";
        }
        if (!file_exists($htaccessFile)) {
            echo "<li class='error'>Upload .htaccess file to uploads folder</li>";
        }
        if (empty($imageFiles)) {
            echo "<li class='error'>Upload image files to uploads folder</li>";
        }
        
        echo "</ol>";
        
        echo "<hr>";
        echo "<p class='error'><strong>⚠️ SECURITY WARNING: DELETE THIS FILE AFTER TESTING!</strong></p>";
        echo "<p>This file exposes sensitive server information.</p>";
        ?>
    </div>
</body>
</html>
