<?php
// Test API Endpoints
echo "<h1>Testing BarCIE API Endpoints</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
    .test { background: white; padding: 15px; margin: 10px 0; border-radius: 8px; border-left: 4px solid #2196F3; }
    .success { border-left-color: #4CAF50; }
    .error { border-left-color: #f44336; }
    pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }
</style>";

// Test 1: fetch_items
echo "<div class='test'>";
echo "<h2>Test 1: Fetch Items</h2>";
echo "<p><strong>Endpoint:</strong> api/items.php</p>";

$base = (isset($_SERVER['HTTP_HOST']) ? (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] : 'http://localhost');
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$url1 = $base . $basePath . "/api/items.php";
$response1 = @file_get_contents($url1);

if ($response1 === false) {
    echo "<p class='error'>❌ Failed to connect to endpoint</p>";
} else {
    $data1 = json_decode($response1, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "<p class='success'>✅ JSON response valid</p>";
        echo "<p><strong>Items count:</strong> " . (is_array($data1) ? count($data1) : 'N/A') . "</p>";
        echo "<pre>" . json_encode($data1, JSON_PRETTY_PRINT) . "</pre>";
    } else {
        echo "<p class='error'>❌ Invalid JSON response</p>";
        echo "<pre>" . htmlspecialchars($response1) . "</pre>";
    }
}
echo "</div>";

// Test 2: get_receipt_no
echo "<div class='test'>";
echo "<h2>Test 2: Get Receipt Number</h2>";
echo "<p><strong>Endpoint:</strong> api/receipt.php</p>";

$url2 = $base . $basePath . "/api/receipt.php";
$response2 = @file_get_contents($url2);

if ($response2 === false) {
    echo "<p class='error'>❌ Failed to connect to endpoint</p>";
} else {
    $data2 = json_decode($response2, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "<p class='success'>✅ JSON response valid</p>";
        echo "<pre>" . json_encode($data2, JSON_PRETTY_PRINT) . "</pre>";
    } else {
        echo "<p class='error'>❌ Invalid JSON response</p>";
        echo "<pre>" . htmlspecialchars($response2) . "</pre>";
    }
}
echo "</div>";

// Test 3: fetch_guest_availability
echo "<div class='test'>";
echo "<h2>Test 3: Fetch Guest Availability</h2>";
echo "<p><strong>Endpoint:</strong> api/availability.php</p>";

$url3 = $base . $basePath . "/api/availability.php";
$response3 = @file_get_contents($url3);

if ($response3 === false) {
    echo "<p class='error'>❌ Failed to connect to endpoint</p>";
} else {
    $data3 = json_decode($response3, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "<p class='success'>✅ JSON response valid</p>";
        echo "<p><strong>Events count:</strong> " . (is_array($data3) ? count($data3) : 'N/A') . "</p>";
        echo "<pre>" . json_encode($data3, JSON_PRETTY_PRINT) . "</pre>";
    } else {
        echo "<p class='error'>❌ Invalid JSON response</p>";
        echo "<pre>" . htmlspecialchars($response3) . "</pre>";
    }
}
echo "</div>";

// Test 4: Available Count
echo "<div class='test'>";
echo "<h2>Test 4: Available Count</h2>";
echo "<p><strong>Endpoint:</strong> api/available_count.php</p>";
$url4 = $base . $basePath . "/api/available_count.php";
$response4 = @file_get_contents($url4);
if ($response4 === false) {
    echo "<p class='error'>❌ Failed to connect to endpoint</p>";
} else {
    $data4 = json_decode($response4, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "<p class='success'>✅ JSON response valid</p>";
        echo "<pre>" . json_encode($data4, JSON_PRETTY_PRINT) . "</pre>";
    } else {
        echo "<p class='error'>❌ Invalid JSON response</p>";
        echo "<pre>" . htmlspecialchars($response4) . "</pre>";
    }
}
echo "</div>";

// Test 4: Check database connection
echo "<div class='test'>";
echo "<h2>Test 5: Database Connection</h2>";
try {
    include 'src/database/db_connect.php';
    if ($conn && !$conn->connect_error) {
        echo "<p class='success'>✅ Database connected successfully</p>";
        
        // Check items table
        $result = $conn->query("SELECT COUNT(*) as count FROM items");
        $row = $result->fetch_assoc();
        echo "<p><strong>Items in database:</strong> " . $row['count'] . "</p>";
        
        // Check bookings table
        $result2 = $conn->query("SELECT COUNT(*) as count FROM bookings");
        $row2 = $result2->fetch_assoc();
        echo "<p><strong>Bookings in database:</strong> " . $row2['count'] . "</p>";
        
        $conn->close();
    } else {
        echo "<p class='error'>❌ Database connection failed</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
echo "</div>";

// Test 6: Health
echo "<div class='test'>";
echo "<h2>Test 6: API Health</h2>";
$health = $base . $basePath . "/api/health.php";
$responseH = @file_get_contents($health);
if ($responseH === false) {
    echo "<p class='error'>❌ Failed to connect to health endpoint</p>";
} else {
    $dataH = json_decode($responseH, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "<p class='success'>✅ Health OK</p>";
        echo "<pre>" . json_encode($dataH, JSON_PRETTY_PRINT) . "</pre>";
    } else {
        echo "<p class='error'>❌ Invalid JSON response</p>";
        echo "<pre>" . htmlspecialchars($responseH) . "</pre>";
    }
}
echo "</div>";

// Test 7: Check PHP errors
echo "<div class='test'>";
echo "<h2>Test 7: PHP Error Log</h2>";
$error_log = ini_get('error_log');
echo "<p><strong>Error log location:</strong> " . ($error_log ?: 'Default location') . "</p>";
echo "<p><strong>Display errors:</strong> " . (ini_get('display_errors') ? 'ON' : 'OFF') . "</p>";
echo "<p><strong>Error reporting:</strong> " . error_reporting() . "</p>";
echo "</div>";
?>
