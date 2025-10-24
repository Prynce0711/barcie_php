<?php
/**
 * Booking Admin Debug Script
 * Purpose: Diagnose booking system issues on live server
 * Usage: Access via browser - https://your-domain.com/debug_bookings.php
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Start output buffering to capture all output
ob_start();

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Booking Admin Debug - BarCIE</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            color: #333;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { 
            background: white; 
            padding: 30px; 
            border-radius: 15px; 
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .header h1 { color: #667eea; margin-bottom: 10px; }
        .header p { color: #666; }
        .section { 
            background: white; 
            padding: 25px; 
            margin-bottom: 20px; 
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .section h2 { 
            color: #764ba2; 
            margin-bottom: 15px; 
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        .status-ok { 
            color: #28a745; 
            font-weight: bold;
            padding: 5px 10px;
            background: #d4edda;
            border-radius: 5px;
            display: inline-block;
        }
        .status-error { 
            color: #dc3545; 
            font-weight: bold;
            padding: 5px 10px;
            background: #f8d7da;
            border-radius: 5px;
            display: inline-block;
        }
        .status-warning { 
            color: #ffc107; 
            font-weight: bold;
            padding: 5px 10px;
            background: #fff3cd;
            border-radius: 5px;
            display: inline-block;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 15px;
        }
        th, td { 
            padding: 12px; 
            text-align: left; 
            border-bottom: 1px solid #ddd;
        }
        th { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            color: white;
            font-weight: 600;
        }
        tr:hover { background: #f8f9fa; }
        .info-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); 
            gap: 15px;
            margin-top: 15px;
        }
        .info-item { 
            background: #f8f9fa; 
            padding: 15px; 
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        .info-label { 
            font-weight: bold; 
            color: #764ba2; 
            margin-bottom: 5px;
        }
        .info-value { 
            color: #555; 
            font-size: 14px;
        }
        code { 
            background: #f4f4f4; 
            padding: 2px 6px; 
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        .query-box { 
            background: #2d3748; 
            color: #68d391; 
            padding: 15px; 
            border-radius: 8px;
            overflow-x: auto;
            margin: 10px 0;
            font-family: 'Courier New', monospace;
            font-size: 13px;
        }
        .badge { 
            display: inline-block; 
            padding: 4px 10px; 
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-success { background: #28a745; color: white; }
        .badge-danger { background: #dc3545; color: white; }
        .badge-warning { background: #ffc107; color: #333; }
        .badge-info { background: #17a2b8; color: white; }
        .badge-secondary { background: #6c757d; color: white; }
    </style>
</head>
<body>
    <div class='container'>";

echo "<div class='header'>
    <h1>🔍 Booking Admin Debug Tool</h1>
    <p>Comprehensive diagnostics for BarCIE booking system</p>
    <p><small>Generated: " . date('Y-m-d H:i:s') . " | Server: " . $_SERVER['SERVER_NAME'] . "</small></p>
</div>";

// ============================================================================
// 1. ENVIRONMENT CHECK
// ============================================================================
echo "<div class='section'>
    <h2>📋 Environment Information</h2>
    <div class='info-grid'>";

$env_checks = [
    'PHP Version' => phpversion(),
    'Server Software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'Document Root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
    'Current Script' => __FILE__,
    'Session Status' => session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive',
    'Error Reporting' => error_reporting(),
    'Display Errors' => ini_get('display_errors') ? 'On' : 'Off',
    'Memory Limit' => ini_get('memory_limit'),
    'Max Execution Time' => ini_get('max_execution_time') . 's',
    'Upload Max Filesize' => ini_get('upload_max_filesize'),
];

foreach ($env_checks as $label => $value) {
    echo "<div class='info-item'>
        <div class='info-label'>{$label}</div>
        <div class='info-value'>" . htmlspecialchars($value) . "</div>
    </div>";
}

echo "</div></div>";

// ============================================================================
// 2. DATABASE CONNECTION TEST
// ============================================================================
echo "<div class='section'>
    <h2>🗄️ Database Connection</h2>";

try {
    require_once __DIR__ . '/src/database/db_connect.php';
    
    if ($conn && $conn->ping()) {
        echo "<p><span class='status-ok'>✓ Connected Successfully</span></p>";
        
        // Database info
        $db_info = [
            'Host' => $conn->host_info,
            'Database' => $conn->query("SELECT DATABASE()")->fetch_row()[0],
            'Server Version' => $conn->server_info,
            'Character Set' => $conn->character_set_name(),
            'Protocol Version' => $conn->protocol_version,
        ];
        
        echo "<div class='info-grid'>";
        foreach ($db_info as $label => $value) {
            echo "<div class='info-item'>
                <div class='info-label'>{$label}</div>
                <div class='info-value'>" . htmlspecialchars($value) . "</div>
            </div>";
        }
        echo "</div>";
    }
} catch (Exception $e) {
    echo "<p><span class='status-error'>✗ Connection Failed</span></p>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</div>";

// ============================================================================
// 3. DATABASE TABLES CHECK
// ============================================================================
echo "<div class='section'>
    <h2>📊 Database Tables Status</h2>";

$required_tables = ['bookings', 'items', 'users', 'admins', 'feedback', 'chat_messages', 'chat_conversations'];

echo "<table>
    <tr>
        <th>Table Name</th>
        <th>Status</th>
        <th>Row Count</th>
        <th>Columns</th>
    </tr>";

foreach ($required_tables as $table) {
    $exists = false;
    $count = 0;
    $columns = [];
    
    try {
        $result = $conn->query("SHOW TABLES LIKE '{$table}'");
        $exists = $result && $result->num_rows > 0;
        
        if ($exists) {
            $count_result = $conn->query("SELECT COUNT(*) as cnt FROM `{$table}`");
            $count = $count_result->fetch_assoc()['cnt'];
            
            $col_result = $conn->query("SHOW COLUMNS FROM `{$table}`");
            while ($col = $col_result->fetch_assoc()) {
                $columns[] = $col['Field'];
            }
        }
    } catch (Exception $e) {
        $exists = false;
    }
    
    $status = $exists ? "<span class='status-ok'>✓ Exists</span>" : "<span class='status-error'>✗ Missing</span>";
    $col_list = $exists ? implode(', ', array_slice($columns, 0, 5)) . (count($columns) > 5 ? '...' : '') : 'N/A';
    
    echo "<tr>
        <td><code>{$table}</code></td>
        <td>{$status}</td>
        <td>" . ($exists ? number_format($count) : 'N/A') . "</td>
        <td><small>{$col_list}</small></td>
    </tr>";
}

echo "</table></div>";

// ============================================================================
// 4. BOOKINGS TABLE STRUCTURE
// ============================================================================
echo "<div class='section'>
    <h2>🏗️ Bookings Table Structure</h2>";

try {
    $structure = $conn->query("DESCRIBE bookings");
    
    echo "<table>
        <tr>
            <th>Field</th>
            <th>Type</th>
            <th>Null</th>
            <th>Key</th>
            <th>Default</th>
            <th>Extra</th>
        </tr>";
    
    while ($row = $structure->fetch_assoc()) {
        echo "<tr>
            <td><strong>{$row['Field']}</strong></td>
            <td><code>{$row['Type']}</code></td>
            <td>" . ($row['Null'] === 'YES' ? 'Yes' : 'No') . "</td>
            <td>" . ($row['Key'] ? "<span class='badge badge-info'>{$row['Key']}</span>" : '-') . "</td>
            <td>" . ($row['Default'] ?? 'NULL') . "</td>
            <td><small>{$row['Extra']}</small></td>
        </tr>";
    }
    
    echo "</table>";
} catch (Exception $e) {
    echo "<p><span class='status-error'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</span></p>";
}

echo "</div>";

// ============================================================================
// 5. RECENT BOOKINGS
// ============================================================================
echo "<div class='section'>
    <h2>📅 Recent Bookings (Last 10)</h2>";

try {
    $query = "SELECT b.*, i.name as room_name, i.room_number, u.username as guest_username
              FROM bookings b
              LEFT JOIN items i ON b.room_id = i.id
              LEFT JOIN users u ON b.user_id = u.id
              ORDER BY b.created_at DESC
              LIMIT 10";
    
    echo "<div class='query-box'>SQL: " . htmlspecialchars($query) . "</div>";
    
    $bookings = $conn->query($query);
    
    if ($bookings && $bookings->num_rows > 0) {
        echo "<p><span class='status-ok'>✓ Found {$bookings->num_rows} bookings</span></p>";
        
        echo "<table>
            <tr>
                <th>ID</th>
                <th>Receipt #</th>
                <th>Guest</th>
                <th>Room/Facility</th>
                <th>Type</th>
                <th>Status</th>
                <th>Discount Status</th>
                <th>Check-in</th>
                <th>Created</th>
            </tr>";
        
        while ($booking = $bookings->fetch_assoc()) {
            $receipt = 'BARCIE-' . date('Ymd', strtotime($booking['created_at'])) . '-' . str_pad($booking['id'], 4, '0', STR_PAD_LEFT);
            
            $status_badges = [
                'pending' => 'warning',
                'approved' => 'success',
                'confirmed' => 'info',
                'checked_in' => 'info',
                'checked_out' => 'secondary',
                'cancelled' => 'danger',
                'rejected' => 'danger'
            ];
            $badge = $status_badges[$booking['status']] ?? 'secondary';
            
            // Extract guest name
            $guest = $booking['guest_username'] ?? 'Unknown';
            if (preg_match('/Guest:\s*([^|]+)/', $booking['details'] ?? '', $matches)) {
                $guest = trim($matches[1]);
            }
            
            $room = $booking['room_name'] ?? 'Unassigned';
            if ($booking['room_number']) {
                $room .= ' #' . $booking['room_number'];
            }
            
            echo "<tr>
                <td>{$booking['id']}</td>
                <td><code>{$receipt}</code></td>
                <td>" . htmlspecialchars($guest) . "</td>
                <td>" . htmlspecialchars($room) . "</td>
                <td><span class='badge badge-" . ($booking['type'] === 'reservation' ? 'info' : 'warning') . "'>{$booking['type']}</span></td>
                <td><span class='badge badge-{$badge}'>{$booking['status']}</span></td>
                <td><span class='badge badge-secondary'>" . ($booking['discount_status'] ?? 'none') . "</span></td>
                <td>" . ($booking['checkin'] ? date('M j, Y', strtotime($booking['checkin'])) : 'N/A') . "</td>
                <td>" . date('M j, Y', strtotime($booking['created_at'])) . "</td>
            </tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p><span class='status-warning'>⚠ No bookings found</span></p>";
    }
} catch (Exception $e) {
    echo "<p><span class='status-error'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</span></p>";
}

echo "</div>";

// ============================================================================
// 6. BOOKING STATISTICS
// ============================================================================
echo "<div class='section'>
    <h2>📈 Booking Statistics</h2>";

try {
    $stats_queries = [
        'Total Bookings' => "SELECT COUNT(*) as count FROM bookings",
        'Pending Bookings' => "SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'",
        'Approved Bookings' => "SELECT COUNT(*) as count FROM bookings WHERE status = 'approved'",
        'Checked In' => "SELECT COUNT(*) as count FROM bookings WHERE status = 'checked_in'",
        'Checked Out' => "SELECT COUNT(*) as count FROM bookings WHERE status = 'checked_out'",
        'Cancelled/Rejected' => "SELECT COUNT(*) as count FROM bookings WHERE status IN ('cancelled', 'rejected')",
        'Reservations' => "SELECT COUNT(*) as count FROM bookings WHERE type = 'reservation'",
        'Pencil Bookings' => "SELECT COUNT(*) as count FROM bookings WHERE type = 'pencil'",
        'With Discount Requests' => "SELECT COUNT(*) as count FROM bookings WHERE discount_status IS NOT NULL AND discount_status != 'none'",
        'Pending Discounts' => "SELECT COUNT(*) as count FROM bookings WHERE discount_status = 'pending'",
    ];
    
    echo "<div class='info-grid'>";
    
    foreach ($stats_queries as $label => $query) {
        $result = $conn->query($query);
        $count = $result ? $result->fetch_assoc()['count'] : 0;
        
        echo "<div class='info-item'>
            <div class='info-label'>{$label}</div>
            <div class='info-value' style='font-size: 24px; font-weight: bold; color: #667eea;'>{$count}</div>
        </div>";
    }
    
    echo "</div>";
} catch (Exception $e) {
    echo "<p><span class='status-error'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</span></p>";
}

echo "</div>";

// ============================================================================
// 7. ITEMS (ROOMS & FACILITIES)
// ============================================================================
echo "<div class='section'>
    <h2>🏨 Rooms & Facilities</h2>";

try {
    $items = $conn->query("SELECT * FROM items ORDER BY item_type, name");
    
    if ($items && $items->num_rows > 0) {
        echo "<p><span class='status-ok'>✓ Found {$items->num_rows} items</span></p>";
        
        echo "<table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Type</th>
                <th>Room #</th>
                <th>Capacity</th>
                <th>Price</th>
                <th>Image</th>
            </tr>";
        
        while ($item = $items->fetch_assoc()) {
            $type_badge = $item['item_type'] === 'room' ? 'info' : 'success';
            $image_status = ($item['image'] && file_exists($item['image'])) ? '✓' : '✗';
            
            echo "<tr>
                <td>{$item['id']}</td>
                <td>" . htmlspecialchars($item['name']) . "</td>
                <td><span class='badge badge-{$type_badge}'>{$item['item_type']}</span></td>
                <td>" . ($item['room_number'] ?? '-') . "</td>
                <td>{$item['capacity']}</td>
                <td>₱" . number_format($item['price']) . "</td>
                <td>{$image_status}</td>
            </tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p><span class='status-warning'>⚠ No items found</span></p>";
    }
} catch (Exception $e) {
    echo "<p><span class='status-error'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</span></p>";
}

echo "</div>";

// ============================================================================
// 8. SESSION & AUTHENTICATION
// ============================================================================
echo "<div class='section'>
    <h2>🔐 Session & Authentication</h2>";

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

echo "<div class='info-grid'>";

$session_items = [
    'Session ID' => session_id() ?: 'Not started',
    'Admin Logged In' => isset($_SESSION['admin_id']) ? 'Yes (ID: ' . $_SESSION['admin_id'] . ')' : 'No',
    'Admin Username' => $_SESSION['admin_username'] ?? 'N/A',
    'User Logged In' => isset($_SESSION['user_id']) ? 'Yes (ID: ' . $_SESSION['user_id'] . ')' : 'No',
    'User Username' => $_SESSION['username'] ?? 'N/A',
];

foreach ($session_items as $label => $value) {
    echo "<div class='info-item'>
        <div class='info-label'>{$label}</div>
        <div class='info-value'>" . htmlspecialchars($value) . "</div>
    </div>";
}

echo "</div>";

// Show all session variables
if (!empty($_SESSION)) {
    echo "<h3 style='margin-top: 20px; color: #764ba2;'>All Session Variables:</h3>";
    echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto;'>";
    print_r($_SESSION);
    echo "</pre>";
} else {
    echo "<p><span class='status-warning'>⚠ No session variables set</span></p>";
}

echo "</div>";

// ============================================================================
// 9. FILE PERMISSIONS
// ============================================================================
echo "<div class='section'>
    <h2>📁 File Permissions</h2>";

$check_paths = [
    'Database Config' => __DIR__ . '/src/database/db_connect.php',
    'Data Processing' => __DIR__ . '/src/components/dashboard/data_processing.php',
    'Uploads Directory' => __DIR__ . '/uploads',
    'Dashboard' => __DIR__ . '/dashboard.php',
];

echo "<table>
    <tr>
        <th>File/Directory</th>
        <th>Exists</th>
        <th>Readable</th>
        <th>Writable</th>
        <th>Permissions</th>
    </tr>";

foreach ($check_paths as $label => $path) {
    $exists = file_exists($path);
    $readable = $exists ? is_readable($path) : false;
    $writable = $exists ? is_writable($path) : false;
    $perms = $exists ? substr(sprintf('%o', fileperms($path)), -4) : 'N/A';
    
    echo "<tr>
        <td><strong>{$label}</strong><br><small><code>{$path}</code></small></td>
        <td>" . ($exists ? "✓" : "✗") . "</td>
        <td>" . ($readable ? "✓" : "✗") . "</td>
        <td>" . ($writable ? "✓" : "✗") . "</td>
        <td><code>{$perms}</code></td>
    </tr>";
}

echo "</table></div>";

// ============================================================================
// 10. PHP EXTENSIONS
// ============================================================================
echo "<div class='section'>
    <h2>🔌 Required PHP Extensions</h2>";

$required_extensions = ['mysqli', 'session', 'json', 'mbstring', 'fileinfo', 'gd'];

echo "<div class='info-grid'>";

foreach ($required_extensions as $ext) {
    $loaded = extension_loaded($ext);
    $status = $loaded ? "<span class='status-ok'>✓ Loaded</span>" : "<span class='status-error'>✗ Missing</span>";
    
    echo "<div class='info-item'>
        <div class='info-label'>{$ext}</div>
        <div class='info-value'>{$status}</div>
    </div>";
}

echo "</div></div>";

// ============================================================================
// FOOTER
// ============================================================================
echo "<div class='section' style='text-align: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;'>
    <p><strong>Debug Complete</strong></p>
    <p><small>BarCIE Hotel Management System | Generated at " . date('Y-m-d H:i:s') . "</small></p>
    <p><small>For security, delete this file after debugging: <code>debug_bookings.php</code></small></p>
</div>";

echo "</div></body></html>";

// Flush output
ob_end_flush();
?>
