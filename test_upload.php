<!DOCTYPE html>
<html>
<head>
    <title>Upload Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }
        .success { color: #28a745; background: #d4edda; padding: 15px; border-radius: 4px; margin: 10px 0; }
        .error { color: #dc3545; background: #f8d7da; padding: 15px; border-radius: 4px; margin: 10px 0; }
        .info { color: #0c5460; background: #d1ecf1; padding: 15px; border-radius: 4px; margin: 10px 0; }
        input[type="file"] { margin: 10px 0; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .code { background: #f4f4f4; padding: 10px; border-radius: 4px; font-family: monospace; margin: 5px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Image Upload Test</h1>
        
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['test_image'])) {
            echo "<h2>Test Results:</h2>";
            
            $uploadsDir = __DIR__ . '/uploads';
            
            // Check upload errors
            if ($_FILES['test_image']['error'] !== UPLOAD_ERR_OK) {
                $errors = [
                    UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
                    UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
                    UPLOAD_ERR_PARTIAL => 'File partially uploaded',
                    UPLOAD_ERR_NO_FILE => 'No file uploaded',
                    UPLOAD_ERR_NO_TMP_DIR => 'Missing temp directory',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write to disk',
                    UPLOAD_ERR_EXTENSION => 'PHP extension stopped upload'
                ];
                echo "<div class='error'>❌ Upload Error: " . ($errors[$_FILES['test_image']['error']] ?? 'Unknown') . "</div>";
            } else {
                echo "<div class='success'>✅ File uploaded successfully to PHP temp directory</div>";
                
                // File info
                echo "<div class='info'>";
                echo "<strong>File Information:</strong><br>";
                echo "Name: " . htmlspecialchars($_FILES['test_image']['name']) . "<br>";
                echo "Size: " . number_format($_FILES['test_image']['size']) . " bytes<br>";
                echo "Type: " . htmlspecialchars($_FILES['test_image']['type']) . "<br>";
                echo "Temp Path: " . htmlspecialchars($_FILES['test_image']['tmp_name']) . "<br>";
                echo "</div>";
                
                // Check if uploads dir exists
                if (!is_dir($uploadsDir)) {
                    echo "<div class='info'>⚠️ Uploads directory doesn't exist. Creating...</div>";
                    if (mkdir($uploadsDir, 0755, true)) {
                        echo "<div class='success'>✅ Uploads directory created!</div>";
                    } else {
                        echo "<div class='error'>❌ Failed to create uploads directory</div>";
                    }
                }
                
                // Check if writable
                if (!is_writable($uploadsDir)) {
                    echo "<div class='error'>❌ Uploads directory is NOT writable</div>";
                    echo "<div class='code'>chmod 755 " . $uploadsDir . "</div>";
                } else {
                    echo "<div class='success'>✅ Uploads directory is writable</div>";
                    
                    // Try to save file
                    $filename = 'test_' . time() . '_' . basename($_FILES['test_image']['name']);
                    $targetPath = $uploadsDir . '/' . $filename;
                    
                    if (move_uploaded_file($_FILES['test_image']['tmp_name'], $targetPath)) {
                        chmod($targetPath, 0644);
                        echo "<div class='success'>✅ File saved successfully!</div>";
                        echo "<div class='info'>";
                        echo "<strong>Saved to:</strong><br>";
                        echo "Server Path: <code>$targetPath</code><br>";
                        echo "Web Path: <code>/uploads/$filename</code><br>";
                        echo "</div>";
                        
                        // Show the image
                        $webPath = '/uploads/' . $filename;
                        echo "<div class='info'>";
                        echo "<strong>Image Preview:</strong><br>";
                        echo "<img src='$webPath' style='max-width:100%; max-height:300px; border:2px solid #ddd; border-radius:4px; margin:10px 0;' ";
                        echo "onerror='this.style.border=\"3px solid red\"; this.alt=\"❌ FAILED TO LOAD\"'>";
                        echo "<br><a href='$webPath' target='_blank'>Open in new tab</a>";
                        echo "</div>";
                        
                        // Verify file exists
                        if (file_exists($targetPath)) {
                            $fileSize = filesize($targetPath);
                            $perms = substr(sprintf('%o', fileperms($targetPath)), -4);
                            echo "<div class='success'>✅ File verification: EXISTS ($fileSize bytes, permissions: $perms)</div>";
                        }
                    } else {
                        echo "<div class='error'>❌ Failed to move uploaded file to uploads directory</div>";
                        echo "<div class='code'>";
                        echo "Source: " . htmlspecialchars($_FILES['test_image']['tmp_name']) . "<br>";
                        echo "Target: " . htmlspecialchars($targetPath) . "<br>";
                        echo "Uploads dir exists: " . (is_dir($uploadsDir) ? 'YES' : 'NO') . "<br>";
                        echo "Uploads dir writable: " . (is_writable($uploadsDir) ? 'YES' : 'NO') . "<br>";
                        echo "</div>";
                    }
                }
            }
            
            echo "<hr>";
        }
        ?>
        
        <form method="POST" enctype="multipart/form-data">
            <p>Upload a test image to verify the upload system:</p>
            <input type="file" name="test_image" accept="image/*" required>
            <br><br>
            <button type="submit">🚀 Upload Test Image</button>
        </form>
        
        <hr>
        <p class='error'><strong>⚠️ DELETE THIS FILE AFTER TESTING!</strong></p>
        <p>This is for diagnostic purposes only.</p>
    </div>
</body>
</html>
