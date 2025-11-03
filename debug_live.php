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

    <!-- Test 7: SMTP/Email Configuration -->
    <div class="test <?php
        $env_file = __DIR__ . '/.env';
        $vendor_exists = file_exists(__DIR__ . '/vendor/autoload.php');
        echo ($vendor_exists && file_exists($env_file)) ? 'success' : 'warning';
    ?>">
        <h2>📧 Email Configuration</h2>
        <?php
        $env_file = __DIR__ . '/.env';
        $vendor_exists = file_exists(__DIR__ . '/vendor/autoload.php');
        
        echo '<p><strong>Vendor/Autoload:</strong> ';
        echo '<span class="status ' . ($vendor_exists ? 'ok' : 'fail') . '">';
        echo $vendor_exists ? 'FOUND' : 'MISSING';
        echo '</span></p>';
        
        echo '<p><strong>.env File:</strong> ';
        echo '<span class="status ' . (file_exists($env_file) ? 'ok' : 'fail') . '">';
        echo file_exists($env_file) ? 'EXISTS' : 'MISSING';
        echo '</span></p>';
        
        if ($vendor_exists && file_exists($env_file)) {
            require __DIR__ . '/vendor/autoload.php';
            
            try {
                $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
                $dotenv->safeLoad();
                
                $smtp_host = getenv('SMTP_HOST') ?: ($_ENV['SMTP_HOST'] ?? 'NOT SET');
                $smtp_user = getenv('SMTP_USERNAME') ?: ($_ENV['SMTP_USERNAME'] ?? 'NOT SET');
                $smtp_port = getenv('SMTP_PORT') ?: ($_ENV['SMTP_PORT'] ?? 'NOT SET');
                $from_email = getenv('FROM_EMAIL') ?: ($_ENV['FROM_EMAIL'] ?? 'NOT SET');
                
                echo '<p><strong>SMTP Host:</strong> ' . htmlspecialchars($smtp_host) . '</p>';
                echo '<p><strong>SMTP Username:</strong> ' . htmlspecialchars($smtp_user) . '</p>';
                // Display masked mail configuration and recent PHPMailer debug log (safe for admins)
                echo '<div style="margin-top:12px;">';
                $mail_config_path = __DIR__ . '/database/mail_config.php';
                if (file_exists($mail_config_path)) {
                    try {
                        $mc = @include $mail_config_path;
                        if (is_array($mc)) {
                            $masked_mc = $mc;
                            if (!empty($masked_mc['password'])) {
                                $masked_mc['password'] = str_repeat('*', 8) . ' (masked)';
                            }
                            if (!empty($masked_mc['username'])) {
                                // mask part of username for privacy
                                $u = $masked_mc['username'];
                                $masked_mc['username'] = strlen($u) > 4 ? substr($u,0,2) . '...' . substr($u,-2) : $u;
                            }
                            echo '<h3 style="margin:6px 0 4px 0;">🔒 Mail Config (masked)</h3>';
                            echo '<pre style="background:#f5f5f5;padding:8px;border-radius:4px;max-width:100%;overflow:auto;">' . htmlspecialchars(json_encode($masked_mc, JSON_PRETTY_PRINT)) . '</pre>';
                        }
                    } catch (Exception $e) {
                        // ignore
                    }
                } else {
                    echo '<p><small>Mail config not found at <code>' . htmlspecialchars($mail_config_path) . '</code></small></p>';
                }

                // Show tail of email debug log (masked)
                $debugLog = __DIR__ . '/logs/email_debug.log';
                echo '<h3 style="margin:8px 0 4px 0;">📝 PHPMailer Debug Log (tail)</h3>';
                if (file_exists($debugLog)) {
                    $content = @file_get_contents($debugLog);
                    if ($content === false) $content = '';
                    // mask any literal password value from config if present
                    if (!empty($mc['password'])) {
                        $content = str_replace($mc['password'], str_repeat('*', 8), $content);
                    }
                    // show only last ~2000 chars to keep page small
                    $len = strlen($content);
                    $tail = $len > 2000 ? substr($content, -2000) : $content;
                    echo '<pre style="background:#111;color:#0f0;padding:10px;border-radius:4px;max-height:360px;overflow:auto;white-space:pre-wrap;">' . htmlspecialchars($tail) . '</pre>';
                } else {
                    echo '<p><small>No debug log found at <code>' . htmlspecialchars($debugLog) . '</code></small></p>';
                }

                echo '</div>';

                // Automated suggestions based on common SMTP reply patterns
                try {
                    $analysis = [];
                    $lower = strtolower($tail ?? '');

                    if (preg_match('/535|could not authenticate|authentication failed|username and password not accepted|5\.7\.8|5\.7\.1|535-5\.7\.8/', $lower)) {
                        $analysis[] = "Authentication failed: verify SMTP username and password. For Gmail, enable 2-Step Verification and create an App Password or configure OAuth2. Update SMTP_PASSWORD in your .env with the App Password.";
                    }

                    if (preg_match('/connect\(\) failed|could not connect to smtp host|connection timed out|connection refused|failed to connect/i', $lower)) {
                        $analysis[] = "Connection failed: check SMTP_HOST and SMTP_PORT, ensure the remote SMTP server is reachable from this server and no firewall blocks the port. For Gmail use host smtp.gmail.com with port 587 (TLS) or 465 (SSL).";
                    }

                    if (preg_match('/tls|ssl|handshake|certificate|unable to get local issuer certificate/i', $lower)) {
                        $analysis[] = "TLS/SSL issue: try switching secure modes (tls on port 587, ssl on 465). For testing you can allow self-signed certs in SMTPOptions, but don't use that in production.";
                    }

                    if (preg_match('/5\.5\.1|5\.7\.0|authentication required|not authorized|permission denied/i', $lower)) {
                        $analysis[] = "Server response indicates authorization/policy block: check account settings (security, app access) or use a transactional SMTP provider (SendGrid, Mailgun) for reliable delivery.";
                    }

                    if (preg_match('/quota|rate limit|too many requests/i', $lower)) {
                        $analysis[] = "Rate limit or quota problem: check account sending limits or move to a provider with higher quotas for production traffic.";
                    }

                    if (preg_match('/5?21|lost connection|broken pipe/i', $lower)) {
                        $analysis[] = "Network-level problem: ensure the server has stable network connectivity to the SMTP host and DNS resolves correctly.";
                    }

                    // If nothing matched, add a generic hint
                    if (empty($analysis)) {
                        $analysis[] = "No obvious pattern detected in the log tail. You can paste the displayed tail here for help or download the full log for deeper inspection.";
                    }

                    echo '<h3 style="margin:8px 0 4px 0;">💡 Suggested next steps</h3>';
                    echo '<ul style="background:#fff;padding:10px;border-radius:4px;">';
                    foreach ($analysis as $a) {
                        echo '<li style="margin:6px 0;">' . htmlspecialchars($a) . '</li>';
                    }
                    echo '</ul>';
                } catch (Exception $e) {
                    // ignore analysis errors
                }
                echo '<p><strong>SMTP Port:</strong> ' . htmlspecialchars($smtp_port) . '</p>';
                echo '<p><strong>From Email:</strong> ' . htmlspecialchars($from_email) . '</p>';
                
                $smtp_password = getenv('SMTP_PASSWORD') ?: ($_ENV['SMTP_PASSWORD'] ?? '');
                echo '<p><strong>SMTP Password:</strong> ';
                echo '<span class="status ' . (!empty($smtp_password) ? 'ok' : 'fail') . '">';
                echo !empty($smtp_password) ? 'SET (' . str_repeat('*', 10) . ')' : 'NOT SET';
                echo '</span></p>';
                
                // Check PHPMailer
                $phpmailer_exists = class_exists('\PHPMailer\PHPMailer\PHPMailer');
                echo '<p><strong>PHPMailer:</strong> ';
                echo '<span class="status ' . ($phpmailer_exists ? 'ok' : 'fail') . '">';
                echo $phpmailer_exists ? 'LOADED' : 'NOT FOUND';
                echo '</span></p>';
                
            } catch (Exception $e) {
                echo '<p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
            }
        }
        ?>
    </div>

    <!-- Test 8: Send Test Email -->
    <div class="test">
        <h2>✉️ Send Test Email</h2>
        <form id="emailTestForm" style="margin-top: 10px;">
            <div style="margin-bottom: 10px;">
                <label for="test_email" style="display: block; margin-bottom: 5px; font-weight: bold;">
                    Recipient Email:
                </label>
                <input 
                    type="email" 
                    id="test_email" 
                    name="email" 
                    required 
                    placeholder="your.email@example.com"
                    style="width: 100%; max-width: 400px; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"
                >
            </div>
            <button 
                type="submit" 
                style="background: #4CAF50; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: bold;"
            >
                📧 Send Test Email
            </button>
        </form>
        <div id="emailResult" style="margin-top: 15px;"></div>
    </div>

    <script>
        document.getElementById('emailTestForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = document.getElementById('test_email').value;
            const resultDiv = document.getElementById('emailResult');
            const button = e.target.querySelector('button');
            
            button.disabled = true;
            button.textContent = '⏳ Sending...';
            resultDiv.innerHTML = '<p>Sending email...</p>';
            
            try {
                const response = await fetch('api/test_email_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ email: email })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    resultDiv.innerHTML = `
                        <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; border-left: 4px solid #28a745;">
                            <strong>✅ Success!</strong> ${data.message}
                            <br><small>Check your inbox (and spam folder)</small>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; border-left: 4px solid #f44336;">
                            <strong>❌ Failed!</strong> ${data.message || 'Unknown error'}
                        </div>
                    `;
                }
            } catch (error) {
                resultDiv.innerHTML = `
                    <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; border-left: 4px solid #f44336;">
                        <strong>❌ Error!</strong> ${error.message}
                    </div>
                `;
            } finally {
                button.disabled = false;
                button.textContent = '📧 Send Test Email';
            }
        });
    </script>

    <?php
    if (isset($conn) && !$conn->connect_error) {
        $conn->close();
    }
    ?>
</body>
</html>
