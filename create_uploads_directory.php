<?php
// Create uploads directory and fix permissions
echo "=== Creating Uploads Directory ===" . PHP_EOL;

$uploadsDir = __DIR__ . '/uploads/';
echo "Target directory: $uploadsDir" . PHP_EOL;

// Create directory if it doesn't exist
if (!is_dir($uploadsDir)) {
    echo "Creating uploads directory..." . PHP_EOL;
    if (mkdir($uploadsDir, 0755, true)) {
        echo "✓ Directory created successfully" . PHP_EOL;
    } else {
        echo "✗ Failed to create directory" . PHP_EOL;
        exit(1);
    }
} else {
    echo "✓ Directory already exists" . PHP_EOL;
}

// Set proper permissions
if (chmod($uploadsDir, 0755)) {
    echo "✓ Permissions set to 755" . PHP_EOL;
} else {
    echo "⚠ Could not set permissions (this might be normal)" . PHP_EOL;
}

// Test write access
$testFile = $uploadsDir . 'test_write_' . time() . '.txt';
if (file_put_contents($testFile, 'Upload directory test - ' . date('Y-m-d H:i:s'))) {
    echo "✓ Write test successful" . PHP_EOL;
    unlink($testFile);
} else {
    echo "✗ Write test failed" . PHP_EOL;
}

// Create .htaccess file for security
$htaccessContent = '# Prevent execution of PHP files in uploads directory
# This is a critical security measure
<FilesMatch "\.(php|php3|php4|php5|phtml|pl|py|jsp|asp|sh|cgi)$">
    Require all denied
</FilesMatch>

# Allow all image files
<FilesMatch "\.(jpg|jpeg|png|gif|webp|svg|ico|bmp)$">
    Require all granted
</FilesMatch>

# Prevent directory listing
Options -Indexes

# Prevent access to hidden files
<FilesMatch "^\.">
    Require all denied
</FilesMatch>

# Set proper MIME types for images
<IfModule mod_mime.c>
    AddType image/jpeg jpg jpeg
    AddType image/png png
    AddType image/gif gif
    AddType image/webp webp
</IfModule>

# Enable CORS for images (if needed)
<IfModule mod_headers.c>
    Header set Access-Control-Allow-Origin "*"
</IfModule>
';

$htaccessFile = $uploadsDir . '.htaccess';
if (file_put_contents($htaccessFile, $htaccessContent)) {
    echo "✓ Security .htaccess file created" . PHP_EOL;
} else {
    echo "⚠ Could not create .htaccess file" . PHP_EOL;
}

echo "\n=== Setup Complete ===" . PHP_EOL;
echo "You can now test uploading images!" . PHP_EOL;
?>