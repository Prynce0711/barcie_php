<?php
/**
 * Live Server Timezone & Configuration Debugger
 * Use this to diagnose timezone and configuration differences between localhost and live server
 */

// Set timezone
date_default_timezone_set('Asia/Manila');

// Database connection
require_once __DIR__ . '/database/db_connect.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>BarCIE - Server Configuration Debug</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; }
        .section { background: #252526; padding: 15px; margin: 15px 0; border-left: 3px solid #007acc; }
        .section h2 { color: #4ec9b0; margin-top: 0; }
        .ok { color: #4ec9b0; }
        .warning { color: #dcdcaa; }
        .error { color: #f48771; }
        table { border-collapse: collapse; width: 100%; }
        td, th { padding: 8px; text-align: left; border-bottom: 1px solid #3e3e42; }
        th { color: #569cd6; }
        .code { background: #1e1e1e; padding: 10px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🔍 BarCIE Server Configuration Debug</h1>
    <p>Server: <strong><?php echo $_SERVER['SERVER_NAME'] ?? 'Unknown'; ?></strong> | 
       IP: <strong><?php echo $_SERVER['SERVER_ADDR'] ?? 'Unknown'; ?></strong></p>

    <!-- PHP Timezone Info -->
    <div class="section">
        <h2>⏰ PHP Timezone Configuration</h2>
        <table>
            <tr>
                <th>Setting</th>
                <th>Value</th>
                <th>Status</th>
            </tr>
            <tr>
                <td>Default Timezone</td>
                <td><?php echo date_default_timezone_get(); ?></td>
                <td class="<?php echo date_default_timezone_get() === 'Asia/Manila' ? 'ok' : 'warning'; ?>">
                    <?php echo date_default_timezone_get() === 'Asia/Manila' ? '✓ Correct' : '⚠ Should be Asia/Manila'; ?>
                </td>
            </tr>
            <tr>
                <td>Current Date/Time (PHP)</td>
                <td><?php echo date('Y-m-d H:i:s'); ?></td>
                <td class="ok">-</td>
            </tr>
            <tr>
                <td>Timestamp (PHP)</td>
                <td><?php echo time(); ?></td>
                <td class="ok">-</td>
            </tr>
            <tr>
                <td>date.timezone (php.ini)</td>
                <td><?php echo ini_get('date.timezone') ?: '<em>Not set</em>'; ?></td>
                <td class="<?php echo ini_get('date.timezone') ? 'ok' : 'warning'; ?>">
                    <?php echo ini_get('date.timezone') ? '✓' : '⚠ Set in code instead'; ?>
                </td>
            </tr>
        </table>
    </div>

    <!-- MySQL Timezone Info -->
    <div class="section">
        <h2>🗄️ MySQL Timezone Configuration</h2>
        <?php
        if ($conn && !$conn->connect_error) {
            $queries = [
                'Time Zone' => "SELECT @@global.time_zone as global_tz, @@session.time_zone as session_tz",
                'Current Time' => "SELECT NOW() as mysql_now, UNIX_TIMESTAMP() as mysql_timestamp",
                'System Time' => "SELECT @@system_time_zone as system_tz"
            ];
            
            foreach ($queries as $label => $query) {
                $result = $conn->query($query);
                if ($result) {
                    echo "<h3>$label:</h3>";
                    echo "<table>";
                    $row = $result->fetch_assoc();
                    foreach ($row as $key => $value) {
                        $status = 'ok';
                        $statusText = '✓';
                        
                        // Check if timezone is correct
                        if (stripos($key, 'tz') !== false && !in_array($value, ['+08:00', 'Asia/Manila', 'SYSTEM'])) {
                            $status = 'warning';
                            $statusText = '⚠ Should be +08:00 or Asia/Manila';
                        }
                        
                        echo "<tr>";
                        echo "<td><strong>" . htmlspecialchars($key) . "</strong></td>";
                        echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                        echo "<td class='$status'>$statusText</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                }
            }
        } else {
            echo "<p class='error'>❌ Database connection failed: " . ($conn->connect_error ?? 'Unknown error') . "</p>";
        }
        ?>
    </div>

    <!-- Database Test Queries -->
    <div class="section">
        <h2>📊 Database Time Comparison</h2>
        <?php
        if ($conn && !$conn->connect_error) {
            $php_time = date('Y-m-d H:i:s');
            $result = $conn->query("SELECT NOW() as db_time, UNIX_TIMESTAMP(NOW()) as db_timestamp");
            
            if ($result && $row = $result->fetch_assoc()) {
                $db_time = $row['db_time'];
                $php_timestamp = time();
                $db_timestamp = (int)$row['db_timestamp'];
                $diff = abs($php_timestamp - $db_timestamp);
                
                echo "<table>";
                echo "<tr><td><strong>PHP Time</strong></td><td>$php_time</td><td>Timestamp: $php_timestamp</td></tr>";
                echo "<tr><td><strong>MySQL Time</strong></td><td>$db_time</td><td>Timestamp: $db_timestamp</td></tr>";
                echo "<tr><td><strong>Difference</strong></td><td colspan='2' class='" . ($diff > 60 ? 'error' : 'ok') . "'>";
                echo "$diff seconds " . ($diff > 60 ? '❌ TOO LARGE!' : '✓ OK');
                echo "</td></tr>";
                echo "</table>";
                
                if ($diff > 60) {
                    echo "<p class='error'><strong>⚠️ WARNING:</strong> Time difference is more than 1 minute! This will cause issues with timestamps.</p>";
                }
            }
        }
        ?>
    </div>

    <!-- Recent Activities Test -->
    <div class="section">
        <h2>🔔 Recent Activities Test</h2>
        <?php
        if ($conn && !$conn->connect_error) {
            // Test recent booking
            $test_query = "SELECT 
                b.id,
                b.receipt_no,
                b.created_at,
                b.payment_date,
                TIMESTAMPDIFF(SECOND, b.payment_date, NOW()) as seconds_ago,
                TIMESTAMPDIFF(MINUTE, b.payment_date, NOW()) as minutes_ago
            FROM bookings b 
            WHERE b.payment_status = 'pending' 
            ORDER BY COALESCE(b.payment_date, b.created_at) DESC 
            LIMIT 3";
            
            $result = $conn->query($test_query);
            
            if ($result && $result->num_rows > 0) {
                echo "<table>";
                echo "<tr><th>Receipt</th><th>Payment Date</th><th>Seconds Ago</th><th>Minutes Ago</th><th>Display</th></tr>";
                while ($row = $result->fetch_assoc()) {
                    $seconds = (int)$row['seconds_ago'];
                    $minutes = (int)$row['minutes_ago'];
                    
                    // Calculate display
                    if ($seconds < 60) {
                        $display = "JUST NOW";
                    } elseif ($minutes < 60) {
                        $display = $minutes . "M AGO";
                    } else {
                        $hours = floor($minutes / 60);
                        $display = $hours . "H AGO";
                    }
                    
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['receipt_no']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['payment_date']) . "</td>";
                    echo "<td>" . $seconds . "s</td>";
                    echo "<td>" . $minutes . "m</td>";
                    echo "<td><strong>" . $display . "</strong></td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p class='warning'>⚠ No pending bookings found for testing</p>";
            }
        }
        ?>
    </div>

    <!-- Environment Info -->
    <div class="section">
        <h2>🌍 Environment Information</h2>
        <table>
            <tr>
                <td><strong>Server Software</strong></td>
                <td><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></td>
            </tr>
            <tr>
                <td><strong>PHP Version</strong></td>
                <td><?php echo PHP_VERSION; ?></td>
            </tr>
            <tr>
                <td><strong>Is Localhost?</strong></td>
                <td><?php 
                    $is_localhost = in_array($_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost', 
                        ['localhost', '127.0.0.1', '::1', 'localhost:80', 'localhost:443']);
                    echo $is_localhost ? 'Yes ✓' : 'No (Live Server)';
                ?></td>
            </tr>
            <tr>
                <td><strong>Document Root</strong></td>
                <td><?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown'; ?></td>
            </tr>
        </table>
    </div>

    <!-- Recommendations -->
    <div class="section">
        <h2>💡 Recommendations</h2>
        <div class="code">
            <strong>If times are incorrect on live server, add this to the top of your php.ini or .htaccess:</strong><br>
            php.ini: <code>date.timezone = "Asia/Manila"</code><br>
            .htaccess: <code>php_value date.timezone "Asia/Manila"</code><br><br>
            
            <strong>For MySQL timezone, run this query once:</strong><br>
            <code>SET GLOBAL time_zone = '+08:00';</code><br><br>
            
            <strong>Or add to my.cnf/my.ini:</strong><br>
            <code>[mysqld]<br>default-time-zone = '+08:00'</code>
        </div>
    </div>

    <hr style="border-color: #3e3e42; margin: 30px 0;">
    <p style="text-align: center; color: #808080;">
        Generated: <?php echo date('Y-m-d H:i:s'); ?> | 
        <a href="dashboard.php" style="color: #569cd6;">← Back to Dashboard</a>
    </p>
</body>
</html>
