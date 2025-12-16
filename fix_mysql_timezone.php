<?php
/**
 * One-Click MySQL Timezone Fix
 * Run this once on your live server to set MySQL timezone to Philippine time
 */

// Set PHP timezone
date_default_timezone_set('Asia/Manila');

// Include database connection
require_once __DIR__ . '/database/db_connect.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>MySQL Timezone Fix</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; 
            max-width: 800px; 
            margin: 50px auto; 
            padding: 20px; 
            background: #f5f5f5;
        }
        .container { 
            background: white; 
            padding: 30px; 
            border-radius: 8px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
        }
        h1 { color: #2563eb; margin-top: 0; }
        .success { 
            background: #d1fae5; 
            border-left: 4px solid #10b981; 
            padding: 15px; 
            margin: 20px 0; 
            border-radius: 4px;
        }
        .error { 
            background: #fee2e2; 
            border-left: 4px solid #ef4444; 
            padding: 15px; 
            margin: 20px 0; 
            border-radius: 4px;
        }
        .warning { 
            background: #fef3c7; 
            border-left: 4px solid #f59e0b; 
            padding: 15px; 
            margin: 20px 0; 
            border-radius: 4px;
        }
        .info { 
            background: #dbeafe; 
            border-left: 4px solid #3b82f6; 
            padding: 15px; 
            margin: 20px 0; 
            border-radius: 4px;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 20px 0; 
        }
        th, td { 
            padding: 12px; 
            text-align: left; 
            border-bottom: 1px solid #e5e7eb; 
        }
        th { 
            background: #f9fafb; 
            font-weight: 600; 
        }
        .btn { 
            display: inline-block; 
            padding: 10px 20px; 
            background: #2563eb; 
            color: white; 
            text-decoration: none; 
            border-radius: 4px; 
            margin: 10px 5px 10px 0;
        }
        .btn:hover { background: #1d4ed8; }
        code { 
            background: #f3f4f6; 
            padding: 2px 6px; 
            border-radius: 3px; 
            font-family: monospace; 
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 MySQL Timezone Configuration</h1>
        
<?php
if (!$conn || $conn->connect_error) {
    echo '<div class="error">';
    echo '<strong>❌ Database Connection Failed</strong><br>';
    echo 'Error: ' . htmlspecialchars($conn->connect_error ?? 'Unknown error');
    echo '</div>';
    exit;
}

echo '<div class="info">';
echo '<strong>ℹ️ Current Configuration</strong>';
echo '</div>';

// Check current timezone
$result = $conn->query("SELECT 
    @@global.time_zone as global_tz, 
    @@session.time_zone as session_tz,
    NOW() as current_time,
    UNIX_TIMESTAMP(NOW()) as db_timestamp
");

if ($result) {
    $row = $result->fetch_assoc();
    echo '<table>';
    echo '<tr><th>Setting</th><th>Value</th></tr>';
    echo '<tr><td>Global Timezone</td><td><code>' . htmlspecialchars($row['global_tz']) . '</code></td></tr>';
    echo '<tr><td>Session Timezone</td><td><code>' . htmlspecialchars($row['session_tz']) . '</code></td></tr>';
    echo '<tr><td>Current Time</td><td>' . htmlspecialchars($row['current_time']) . '</td></tr>';
    echo '</table>';
    
    $php_time = time();
    $db_time = (int)$row['db_timestamp'];
    $diff = abs($php_time - $db_time);
    
    echo '<div class="' . ($diff > 60 ? 'warning' : 'success') . '">';
    echo '<strong>⏰ Time Synchronization Check</strong><br>';
    echo 'PHP Time: ' . date('Y-m-d H:i:s', $php_time) . ' (Timestamp: ' . $php_time . ')<br>';
    echo 'MySQL Time: ' . $row['current_time'] . ' (Timestamp: ' . $db_time . ')<br>';
    echo 'Difference: <strong>' . $diff . ' seconds</strong>';
    
    if ($diff > 60) {
        echo '<br><br>⚠️ Time difference is too large! This will cause issues.';
    } else {
        echo '<br><br>✓ Time synchronization is OK';
    }
    echo '</div>';
}

// Apply fix if requested
if (isset($_GET['apply_fix'])) {
    echo '<hr>';
    echo '<h2>Applying Fix...</h2>';
    
    $success = true;
    $messages = [];
    
    // Set global timezone
    if ($conn->query("SET GLOBAL time_zone = '+08:00'")) {
        $messages[] = ['type' => 'success', 'msg' => '✓ Global timezone set to +08:00'];
    } else {
        $messages[] = ['type' => 'error', 'msg' => '❌ Failed to set global timezone: ' . $conn->error];
        $success = false;
    }
    
    // Set session timezone
    if ($conn->query("SET SESSION time_zone = '+08:00'")) {
        $messages[] = ['type' => 'success', 'msg' => '✓ Session timezone set to +08:00'];
    } else {
        $messages[] = ['type' => 'error', 'msg' => '❌ Failed to set session timezone: ' . $conn->error];
        $success = false;
    }
    
    // Display results
    foreach ($messages as $msg) {
        echo '<div class="' . $msg['type'] . '">' . $msg['msg'] . '</div>';
    }
    
    if ($success) {
        echo '<div class="success">';
        echo '<strong>🎉 Success!</strong><br>';
        echo 'MySQL timezone has been configured correctly.<br><br>';
        echo '<strong>Next Steps:</strong><br>';
        echo '1. Test recent activities on dashboard<br>';
        echo '2. Check payment verification "Submitted" column<br>';
        echo '3. Verify all times display correctly<br>';
        echo '</div>';
        
        echo '<a href="debug_live_timezone.php" class="btn">Run Full Diagnostic</a>';
        echo '<a href="dashboard.php" class="btn">Go to Dashboard</a>';
    } else {
        echo '<div class="error">';
        echo '<strong>⚠️ Manual Configuration Required</strong><br>';
        echo 'Some permissions may be insufficient. Please run this SQL manually:<br><br>';
        echo '<code>SET GLOBAL time_zone = \'+08:00\';</code><br>';
        echo '<code>SET SESSION time_zone = \'+08:00\';</code><br><br>';
        echo 'Or add to your my.cnf/my.ini:<br>';
        echo '<code>[mysqld]<br>default-time-zone = \'+08:00\'</code>';
        echo '</div>';
    }
    
} else {
    // Show fix button
    echo '<hr>';
    echo '<h2>Apply Timezone Fix</h2>';
    
    echo '<div class="info">';
    echo '<strong>What this will do:</strong><br>';
    echo '• Set MySQL global timezone to +08:00 (Asia/Manila)<br>';
    echo '• Set MySQL session timezone to +08:00<br>';
    echo '• Fix all timestamp-related issues<br><br>';
    echo '<strong>Note:</strong> This requires MySQL SUPER privilege. If you don\'t have it, you\'ll need to manually configure MySQL.';
    echo '</div>';
    
    echo '<a href="?apply_fix=1" class="btn" onclick="return confirm(\'Apply MySQL timezone fix?\')">🔧 Apply Fix Now</a>';
    echo '<a href="debug_live_timezone.php" class="btn">Run Diagnostic First</a>';
}
?>

        <hr style="margin-top: 40px;">
        <p style="text-align: center; color: #6b7280;">
            <small>
                For more information, see <a href="LIVE_SERVER_FIXES.md">LIVE_SERVER_FIXES.md</a><br>
                <a href="dashboard.php">← Back to Dashboard</a>
            </small>
        </p>
    </div>
</body>
</html>
