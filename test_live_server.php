<?php
/**
 * Live Server Test Script
 * Use this to verify everything works correctly on your live server
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Live Server Test - BarCIE</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #4CAF50; padding-bottom: 10px; }
        h2 { color: #555; margin-top: 30px; }
        .test { margin: 15px 0; padding: 15px; border-left: 4px solid #ddd; background: #f9f9f9; }
        .success { border-left-color: #4CAF50; background: #f1f8f4; }
        .error { border-left-color: #f44336; background: #fef1f0; }
        .warning { border-left-color: #ff9800; background: #fff8f0; }
        .icon { font-size: 20px; margin-right: 10px; }
        .details { margin-top: 10px; padding: 10px; background: white; border-radius: 4px; font-size: 14px; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 BarCIE Live Server Test</h1>
        <p>Server: <strong><?php echo $_SERVER['HTTP_HOST']; ?></strong></p>
        <p>Time: <strong><?php echo date('Y-m-d H:i:s'); ?></strong></p>

<?php
// Test 1: Environment Detection
echo '<h2>1. Environment Detection</h2>';
$is_localhost = in_array($_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost', 
    ['localhost', '127.0.0.1', '::1', 'localhost:80', 'localhost:443']);

if ($is_localhost) {
    echo '<div class="test warning"><span class="icon">⚠️</span><strong>Running on LOCALHOST</strong></div>';
} else {
    echo '<div class="test success"><span class="icon">✅</span><strong>Running on LIVE SERVER</strong></div>';
}

// Test 2: .env File
echo '<h2>2. Configuration File Check</h2>';
if (file_exists(__DIR__ . '/.env')) {
    echo '<div class="test success"><span class="icon">✅</span>.env file exists</div>';
    
    // Check if it has DB configuration
    $env_content = file_get_contents(__DIR__ . '/.env');
    if (strpos($env_content, 'DB_HOST') !== false) {
        echo '<div class="test success"><span class="icon">✅</span>DB configuration found in .env</div>';
    } else {
        echo '<div class="test error"><span class="icon">❌</span>DB configuration missing in .env</div>';
    }
} else {
    echo '<div class="test warning"><span class="icon">⚠️</span>.env file not found (using defaults)</div>';
}

// Test 3: Database Connection
echo '<h2>3. Database Connection Test</h2>';
require_once 'database/db_connect.php';

if (isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {
    echo '<div class="test success"><span class="icon">✅</span>Database connected successfully!</div>';
    echo '<div class="details">';
    echo '<strong>Host:</strong> ' . $host . '<br>';
    echo '<strong>Database:</strong> ' . $dbname . '<br>';
    echo '<strong>Server Info:</strong> ' . $conn->server_info . '<br>';
    echo '</div>';
    
    // Test 4: Database Tables
    echo '<h2>4. Database Tables Check</h2>';
    $tables_to_check = ['admins', 'bookings', 'items', 'feedback', 'pencil_bookings', 'news_updates'];
    $all_tables_exist = true;
    
    foreach ($tables_to_check as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result && $result->num_rows > 0) {
            $count_result = $conn->query("SELECT COUNT(*) as count FROM $table");
            $count = $count_result->fetch_assoc()['count'];
            echo '<div class="test success"><span class="icon">✅</span>Table <strong>' . $table . '</strong>: ' . $count . ' records</div>';
        } else {
            echo '<div class="test error"><span class="icon">❌</span>Table <strong>' . $table . '</strong>: NOT FOUND</div>';
            $all_tables_exist = false;
        }
    }
    
    // Test 5: Sample Data Query
    if ($all_tables_exist) {
        echo '<h2>5. Sample Data Test</h2>';
        
        $rooms = $conn->query("SELECT COUNT(*) as count FROM items WHERE item_type = 'room'");
        $room_count = $rooms->fetch_assoc()['count'];
        echo '<div class="test success"><span class="icon">✅</span>Rooms/Facilities: ' . $room_count . '</div>';
        
        $bookings = $conn->query("SELECT COUNT(*) as count FROM bookings");
        $booking_count = $bookings->fetch_assoc()['count'];
        echo '<div class="test success"><span class="icon">✅</span>Bookings: ' . $booking_count . '</div>';
        
        $admins = $conn->query("SELECT COUNT(*) as count FROM admins");
        $admin_count = $admins->fetch_assoc()['count'];
        echo '<div class="test success"><span class="icon">✅</span>Admin Users: ' . $admin_count . '</div>';
    }
    
} else {
    echo '<div class="test error"><span class="icon">❌</span>Database connection FAILED!</div>';
    if ($conn->connect_error) {
        echo '<div class="details"><strong>Error:</strong> ' . $conn->connect_error . '</div>';
    }
}

// Test 6: File Permissions
echo '<h2>6. Directory Permissions Check</h2>';
$dirs_to_check = ['uploads', 'logs', 'api', 'database', 'components'];
foreach ($dirs_to_check as $dir) {
    if (is_dir(__DIR__ . '/' . $dir)) {
        if (is_writable(__DIR__ . '/' . $dir)) {
            echo '<div class="test success"><span class="icon">✅</span>' . $dir . '/ is writable</div>';
        } else {
            echo '<div class="test warning"><span class="icon">⚠️</span>' . $dir . '/ is NOT writable (may cause issues)</div>';
        }
    } else {
        echo '<div class="test error"><span class="icon">❌</span>' . $dir . '/ directory NOT FOUND</div>';
    }
}

// Test 7: PHP Extensions
echo '<h2>7. PHP Extensions Check</h2>';
$required_extensions = ['mysqli', 'pdo', 'pdo_mysql', 'mbstring', 'json', 'curl'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo '<div class="test success"><span class="icon">✅</span>' . $ext . ' is loaded</div>';
    } else {
        echo '<div class="test error"><span class="icon">❌</span>' . $ext . ' is NOT loaded</div>';
    }
}

// Test 8: API Endpoints
echo '<h2>8. API Endpoints Test</h2>';
$api_files = ['items.php', 'availability.php', 'health.php', 'receipt.php'];
foreach ($api_files as $file) {
    if (file_exists(__DIR__ . '/api/' . $file)) {
        echo '<div class="test success"><span class="icon">✅</span>api/' . $file . ' exists</div>';
    } else {
        echo '<div class="test error"><span class="icon">❌</span>api/' . $file . ' NOT FOUND</div>';
    }
}

// Test 9: Server Information
echo '<h2>9. Server Information</h2>';
echo '<div class="details">';
echo '<strong>PHP Version:</strong> ' . PHP_VERSION . '<br>';
echo '<strong>Server Software:</strong> ' . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . '<br>';
echo '<strong>Document Root:</strong> ' . $_SERVER['DOCUMENT_ROOT'] . '<br>';
echo '<strong>Script Path:</strong> ' . __FILE__ . '<br>';
echo '<strong>Max Upload Size:</strong> ' . ini_get('upload_max_filesize') . '<br>';
echo '<strong>Post Max Size:</strong> ' . ini_get('post_max_size') . '<br>';
echo '<strong>Memory Limit:</strong> ' . ini_get('memory_limit') . '<br>';
echo '</div>';

?>
        <h2>✅ Test Complete</h2>
        <p><strong>Next Steps:</strong></p>
        <ul>
            <li>If all tests pass, your live server is ready!</li>
            <li>Test the main application: <a href="index.php">Go to Homepage</a></li>
            <li>Test admin dashboard: <a href="dashboard.php">Go to Dashboard</a></li>
            <li>Delete this test file after verification for security</li>
        </ul>
    </div>
</body>
</html>
