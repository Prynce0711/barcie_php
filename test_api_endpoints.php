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
echo "<p><strong>Endpoint:</strong> database/user_auth.php?action=fetch_items</p>";

$url1 = "http://localhost/barcie_php/database/user_auth.php?action=fetch_items";
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
echo "<p><strong>Endpoint:</strong> database/user_auth.php?action=get_receipt_no</p>";

$url2 = "http://localhost/barcie_php/database/user_auth.php?action=get_receipt_no";
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
echo "<p><strong>Endpoint:</strong> database/user_auth.php?action=fetch_guest_availability</p>";

$url3 = "http://localhost/barcie_php/database/user_auth.php?action=fetch_guest_availability";
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

// Test 4: Check database connection
echo "<div class='test'>";
echo "<h2>Test 4: Database Connection</h2>";
try {
    include 'database/db_connect.php';
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

// Test 5: Check PHP errors
echo "<div class='test'>";
echo "<h2>Test 5: PHP Error Log</h2>";
$error_log = ini_get('error_log');
echo "<p><strong>Error log location:</strong> " . ($error_log ?: 'Default location') . "</p>";
echo "<p><strong>Display errors:</strong> " . (ini_get('display_errors') ? 'ON' : 'OFF') . "</p>";
echo "<p><strong>Error reporting:</strong> " . error_reporting() . "</p>";
echo "</div>";
?>
