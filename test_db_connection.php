<?php
/**
 * Database Connection Test
 * Quick check to verify database connectivity
 */

echo "<h2>🔌 Database Connection Test</h2>";
echo "<style>
    body { font-family: Arial; padding: 20px; background: #f5f5f5; }
    .box { background: white; padding: 20px; border-radius: 8px; margin: 10px 0; }
    .success { border-left: 4px solid #28a745; }
    .error { border-left: 4px solid #dc3545; }
    .info { border-left: 4px solid #007bff; }
</style>";

// Detect environment
$is_localhost = in_array($_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost', 
    ['localhost', '127.0.0.1', '::1', 'localhost:80', 'localhost:443']);

echo "<div class='box info'>";
echo "<strong>Environment:</strong> " . ($is_localhost ? "🏠 LOCALHOST" : "☁️ REMOTE SERVER") . "<br>";
echo "<strong>Server Name:</strong> " . ($_SERVER['SERVER_NAME'] ?? 'N/A') . "<br>";
echo "<strong>HTTP Host:</strong> " . ($_SERVER['HTTP_HOST'] ?? 'N/A') . "<br>";
echo "</div>";

// Try to connect
try {
    require_once __DIR__ . '/database/db_connect.php';
    
    echo "<div class='box success'>";
    echo "<strong>✅ Database Connected!</strong><br>";
    echo "Host: " . $host . "<br>";
    echo "Database: " . $dbname . "<br>";
    echo "User: " . $user . "<br>";
    
    // Test query
    $result = $conn->query("SELECT COUNT(*) as count FROM items");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "Items in database: <strong>" . $row['count'] . "</strong><br>";
    }
    
    // Check for images column
    $check = $conn->query("SHOW COLUMNS FROM items LIKE 'images'");
    if ($check && $check->num_rows > 0) {
        echo "Images column: <strong style='color: green;'>✓ Exists</strong><br>";
    } else {
        echo "Images column: <strong style='color: orange;'>⚠ Not found (run migration)</strong><br>";
    }
    
    echo "</div>";
    
    echo "<div class='box info'>";
    echo "<a href='dashboard.php' style='color: #007bff; text-decoration: none;'>→ Go to Dashboard</a><br>";
    echo "<a href='check_images_setup.php' style='color: #007bff; text-decoration: none;'>→ Check Images Setup</a><br>";
    echo "<a href='database/add_images_column.php' style='color: #007bff; text-decoration: none;'>→ Run Migration</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='box error'>";
    echo "<strong>❌ Connection Failed!</strong><br>";
    echo "Error: " . $e->getMessage() . "<br><br>";
    
    echo "<strong>Troubleshooting:</strong><br>";
    if ($is_localhost) {
        echo "• Make sure XAMPP MySQL is running<br>";
        echo "• Check database 'barcie_db' exists in phpMyAdmin<br>";
        echo "• Default XAMPP password is usually empty<br>";
        echo "• Try: <a href='http://localhost/phpmyadmin'>phpMyAdmin</a><br>";
    } else {
        echo "• Check remote database server is accessible<br>";
        echo "• Verify IP address: 10.20.0.2<br>";
        echo "• Check firewall settings<br>";
        echo "• Verify credentials are correct<br>";
    }
    echo "</div>";
}
?>
