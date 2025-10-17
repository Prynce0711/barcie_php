<?php
// Simple debug page for live server
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>BarCIE Debug - Live Server</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .test { background: white; padding: 15px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .success { border-left: 4px solid #4CAF50; }
        .error { border-left: 4px solid #f44336; }
        .warning { border-left: 4px solid #ff9800; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; border-radius: 4px; }
        h1 { color: #333; }
        h2 { color: #555; font-size: 18px; margin-top: 0; }
        .status { display: inline-block; padding: 4px 12px; border-radius: 4px; color: white; font-weight: bold; }
        .status.ok { background: #4CAF50; }
        .status.fail { background: #f44336; }
    </style>
</head>
<body>
    <h1>🔍 BarCIE Live Server Debug</h1>
    <p><strong>Current Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
    <p><strong>Server:</strong> <?php echo $_SERVER['HTTP_HOST'] ?? 'Unknown'; ?></p>

    <!-- Test 1: PHP Version -->
    <div class="test <?php echo version_compare(PHP_VERSION, '7.4.0', '>=') ? 'success' : 'warning'; ?>">
        <h2>✓ PHP Version</h2>
        <p><strong>Version:</strong> <?php echo phpversion(); ?></p>
        <p><strong>Status:</strong> 
            <span class="status <?php echo version_compare(PHP_VERSION, '7.4.0', '>=') ? 'ok' : 'fail'; ?>">
                <?php echo version_compare(PHP_VERSION, '7.4.0', '>=') ? 'OK' : 'TOO OLD'; ?>
            </span>
        </p>
    </div>

    <!-- Test 2: Database Connection -->
    <div class="test <?php
        try {
            include 'database/db_connect.php';
            echo ($conn && !$conn->connect_error) ? 'success' : 'error';
        } catch (Exception $e) {
            echo 'error';
        }
    ?>">
        <h2>🗄️ Database Connection</h2>
        <?php
        try {
            include 'database/db_connect.php';
            if ($conn && !$conn->connect_error) {
                echo '<p><span class="status ok">CONNECTED</span></p>';
                echo '<p><strong>Database:</strong> ' . ($conn->query("SELECT DATABASE()")->fetch_row()[0] ?? 'Unknown') . '</p>';
            } else {
                echo '<p><span class="status fail">FAILED</span></p>';
                echo '<p><strong>Error:</strong> ' . ($conn->connect_error ?? 'Unknown error') . '</p>';
            }
        } catch (Exception $e) {
            echo '<p><span class="status fail">EXCEPTION</span></p>';
            echo '<p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
        ?>
    </div>

    <!-- Test 3: Tables Check -->
    <div class="test <?php
        try {
            if (isset($conn) && !$conn->connect_error) {
                $tables = ['items', 'bookings', 'feedback'];
                $all_exist = true;
                foreach ($tables as $table) {
                    $check = $conn->query("SHOW TABLES LIKE '$table'");
                    if (!$check || $check->num_rows == 0) {
                        $all_exist = false;
                        break;
                    }
                }
                echo $all_exist ? 'success' : 'error';
            } else {
                echo 'error';
            }
        } catch (Exception $e) {
            echo 'error';
        }
    ?>">
        <h2>📋 Database Tables</h2>
        <?php
        if (isset($conn) && !$conn->connect_error) {
            $tables = ['items', 'bookings', 'feedback'];
            foreach ($tables as $table) {
                $check = $conn->query("SHOW TABLES LIKE '$table'");
                $exists = $check && $check->num_rows > 0;
                
                echo '<p><strong>' . ucfirst($table) . ':</strong> ';
                echo '<span class="status ' . ($exists ? 'ok' : 'fail') . '">';
                echo $exists ? 'EXISTS' : 'MISSING';
                echo '</span>';
                
                if ($exists) {
                    $count = $conn->query("SELECT COUNT(*) as cnt FROM $table")->fetch_assoc()['cnt'];
                    echo ' (' . $count . ' records)';
                }
                echo '</p>';
            }
        } else {
            echo '<p><span class="status fail">Cannot check - database not connected</span></p>';
        }
        ?>
    </div>

    <!-- Test 4: API Endpoints -->
    <div class="test">
        <h2>🌐 API Endpoints</h2>
        <p>Testing API endpoints...</p>
        
        <div id="api-tests">
            <p>⏳ Loading...</p>
        </div>
    </div>

    <script>
        // Test API endpoints via JavaScript
        async function testAPIs() {
            const container = document.getElementById('api-tests');
            container.innerHTML = '';
            
            const endpoints = [
                { name: 'Debug Connection', url: 'database/user_auth.php?action=debug_connection' },
                { name: 'Fetch Items', url: 'database/user_auth.php?action=fetch_items' },
                { name: 'Fetch Availability', url: 'database/user_auth.php?action=fetch_guest_availability' },
                { name: 'Get Receipt Number', url: 'database/user_auth.php?action=get_receipt_no' }
            ];
            
            for (const endpoint of endpoints) {
                const div = document.createElement('div');
                div.style.marginBottom = '10px';
                
                try {
                    const response = await fetch(endpoint.url);
                    const data = await response.json();
                    
                    const status = response.ok ? 'ok' : 'fail';
                    div.innerHTML = `
                        <strong>${endpoint.name}:</strong> 
                        <span class="status ${status}">${response.status} ${response.statusText}</span>
                        <br><small>Response: ${JSON.stringify(data).substring(0, 100)}...</small>
                    `;
                } catch (error) {
                    div.innerHTML = `
                        <strong>${endpoint.name}:</strong> 
                        <span class="status fail">ERROR</span>
                        <br><small>Error: ${error.message}</small>
                    `;
                }
                
                container.appendChild(div);
            }
        }
        
        testAPIs();
    </script>

    <!-- Test 5: Files Check -->
    <div class="test">
        <h2>📁 Critical Files</h2>
        <?php
        $files = [
            'database/db_connect.php' => 'Database Config',
            'database/user_auth.php' => 'API Handler',
            'assets/js/guest-bootstrap.js' => 'Guest JS',
            'Guest.php' => 'Guest Portal'
        ];
        
        foreach ($files as $file => $desc) {
            $exists = file_exists($file);
            echo '<p><strong>' . $desc . ':</strong> ';
            echo '<span class="status ' . ($exists ? 'ok' : 'fail') . '">';
            echo $exists ? 'FOUND' : 'MISSING';
            echo '</span>';
            echo ' <small>' . $file . '</small>';
            echo '</p>';
        }
        ?>
    </div>

    <!-- Test 6: PHP Extensions -->
    <div class="test">
        <h2>🔌 PHP Extensions</h2>
        <?php
        $extensions = ['mysqli', 'json', 'mbstring', 'curl'];
        foreach ($extensions as $ext) {
            $loaded = extension_loaded($ext);
            echo '<p><strong>' . $ext . ':</strong> ';
            echo '<span class="status ' . ($loaded ? 'ok' : 'fail') . '">';
            echo $loaded ? 'LOADED' : 'MISSING';
            echo '</span></p>';
        }
        ?>
    </div>

    <?php
    if (isset($conn) && !$conn->connect_error) {
        $conn->close();
    }
    ?>
</body>
</html>
