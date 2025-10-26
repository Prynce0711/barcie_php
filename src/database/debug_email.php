<?php
/**
 * Email Debug Tool
 * This file helps diagnose email configuration issues
 * Access: http://localhost/barcie_php/src/database/debug_email.php
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Email Configuration Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        .section { margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 4px; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        .info { color: #17a2b8; }
        pre { background: #263238; color: #aed581; padding: 15px; border-radius: 4px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        table td { padding: 8px; border: 1px solid #ddd; }
        table td:first-child { font-weight: bold; background: #e9ecef; width: 30%; }
        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; margin: 10px 5px 0 0; }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📧 Email Configuration Debug</h1>
        
        <?php
        // Check if vendor/autoload exists
        $vendor_path = __DIR__ . '/../../vendor/autoload.php';
        $vendor_exists = file_exists($vendor_path);
        ?>
        
        <div class="section">
            <h2>1. PHPMailer Installation Check</h2>
            <table>
                <tr>
                    <td>Vendor Autoload Path</td>
                    <td><code><?php echo $vendor_path; ?></code></td>
                </tr>
                <tr>
                    <td>Vendor Folder Exists</td>
                    <td><?php echo $vendor_exists ? '<span class="success">✓ YES</span>' : '<span class="error">✗ NO - Run: composer install</span>'; ?></td>
                </tr>
                <?php if ($vendor_exists): ?>
                <tr>
                    <td>PHPMailer Class Available</td>
                    <td>
                        <?php
                        require_once $vendor_path;
                        $phpmailer_exists = class_exists('\PHPMailer\PHPMailer\PHPMailer');
                        echo $phpmailer_exists ? '<span class="success">✓ YES</span>' : '<span class="error">✗ NO</span>';
                        ?>
                    </td>
                </tr>
                <?php endif; ?>
            </table>
        </div>

        <div class="section">
            <h2>2. Mail Configuration File</h2>
            <?php
            $config_path = __DIR__ . '/mail_config.php';
            $config_exists = file_exists($config_path);
            ?>
            <table>
                <tr>
                    <td>Config File Path</td>
                    <td><code><?php echo $config_path; ?></code></td>
                </tr>
                <tr>
                    <td>Config File Exists</td>
                    <td><?php echo $config_exists ? '<span class="success">✓ YES</span>' : '<span class="error">✗ NO</span>'; ?></td>
                </tr>
                <?php if ($config_exists): ?>
                <tr>
                    <td colspan="2">
                        <h3>Configuration Values:</h3>
                        <?php
                        try {
                            $config = require $config_path;
                            echo '<table>';
                            echo '<tr><td>SMTP Host</td><td><code>' . htmlspecialchars($config['host'] ?? 'NOT SET') . '</code></td></tr>';
                            echo '<tr><td>SMTP Username</td><td><code>' . (empty($config['username']) ? '<span class="error">EMPTY - NEEDS TO BE SET!</span>' : htmlspecialchars($config['username'])) . '</code></td></tr>';
                            echo '<tr><td>SMTP Password</td><td><code>' . (empty($config['password']) ? '<span class="error">EMPTY - NEEDS TO BE SET!</span>' : '••••••••••••••••') . '</code></td></tr>';
                            echo '<tr><td>SMTP Secure</td><td><code>' . htmlspecialchars($config['secure'] ?? 'NOT SET') . '</code></td></tr>';
                            echo '<tr><td>SMTP Port</td><td><code>' . htmlspecialchars($config['port'] ?? 'NOT SET') . '</code></td></tr>';
                            echo '<tr><td>From Email</td><td><code>' . htmlspecialchars($config['from_email'] ?? 'NOT SET') . '</code></td></tr>';
                            echo '<tr><td>From Name</td><td><code>' . htmlspecialchars($config['from_name'] ?? 'NOT SET') . '</code></td></tr>';
                            echo '</table>';
                            
                            // Check if credentials are empty
                            if (empty($config['username']) || empty($config['password'])) {
                                echo '<div style="margin-top: 15px; padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">';
                                echo '<strong>⚠️ WARNING:</strong> SMTP username or password is empty!<br>';
                                echo 'Emails will fail to send. Please update <code>mail_config.php</code> with your Gmail credentials.';
                                echo '</div>';
                            }
                        } catch (Exception $e) {
                            echo '<span class="error">Error loading config: ' . htmlspecialchars($e->getMessage()) . '</span>';
                        }
                        ?>
                    </td>
                </tr>
                <?php endif; ?>
            </table>
        </div>

        <div class="section">
            <h2>3. PHP Environment</h2>
            <table>
                <tr>
                    <td>PHP Version</td>
                    <td><code><?php echo phpversion(); ?></code></td>
                </tr>
                <tr>
                    <td>OpenSSL Extension</td>
                    <td><?php echo extension_loaded('openssl') ? '<span class="success">✓ Loaded</span>' : '<span class="error">✗ Not Loaded</span>'; ?></td>
                </tr>
                <tr>
                    <td>PHP mail() Function</td>
                    <td><?php echo function_exists('mail') ? '<span class="success">✓ Available</span>' : '<span class="error">✗ Not Available</span>'; ?></td>
                </tr>
                <tr>
                    <td>Error Log Location</td>
                    <td><code><?php echo ini_get('error_log') ?: 'Not set (check C:\xampp\php\logs\php_error_log)'; ?></code></td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h2>4. Quick Setup Guide</h2>
            <div style="background: #e7f3ff; border-left: 4px solid #2196F3; padding: 15px; border-radius: 4px;">
                <h3>📝 Steps to Fix Email Issues:</h3>
                <ol>
                    <li><strong>Create Gmail App Password:</strong>
                        <ul>
                            <li>Go to <a href="https://myaccount.google.com/security" target="_blank">Google Account Security</a></li>
                            <li>Enable 2-Step Verification if not already enabled</li>
                            <li>Search for "App passwords" or visit <a href="https://myaccount.google.com/apppasswords" target="_blank">App Passwords</a></li>
                            <li>Select "Mail" and generate a password</li>
                            <li>Copy the 16-character password (e.g., <code>abcd efgh ijkl mnop</code>)</li>
                        </ul>
                    </li>
                    <li><strong>Update mail_config.php:</strong>
                        <pre style="margin: 10px 0;">return [
    'host' => 'smtp.gmail.com',
    'username' => 'your-email@gmail.com',    // Your Gmail address
    'password' => 'abcdefghijklmnop',        // Your 16-char App Password (no spaces)
    'secure' => 'tls',
    'port' => 587,
    'from_email' => 'barcieinternationalcenter@gmail.com',
    'from_name' => 'Barcie International Center'
];</pre>
                    </li>
                    <li><strong>Test the configuration</strong> using the test email tool below</li>
                </ol>
            </div>
        </div>

        <div class="section">
            <h2>5. Test Tools</h2>
            <a href="test_email.php" class="btn">📨 Send Test Email</a>
            <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn" style="background: #28a745;">🔄 Refresh Debug Info</a>
            <a href="../../Guest.php" class="btn" style="background: #6c757d;">← Back to Guest Page</a>
        </div>

        <div class="section">
            <h2>6. Recent Error Logs</h2>
            <?php
            $error_log = ini_get('error_log');
            if (!$error_log) {
                $error_log = 'C:\xampp\php\logs\php_error_log';
            }
            
            if (file_exists($error_log)) {
                echo '<p class="info">Reading last 50 lines from: <code>' . htmlspecialchars($error_log) . '</code></p>';
                $lines = file($error_log);
                $recent_lines = array_slice($lines, -50);
                
                // Filter for email-related logs
                $email_logs = array_filter($recent_lines, function($line) {
                    return stripos($line, 'email') !== false || 
                           stripos($line, 'phpmailer') !== false ||
                           stripos($line, 'smtp') !== false ||
                           stripos($line, 'BOOKING EMAIL') !== false;
                });
                
                if (count($email_logs) > 0) {
                    echo '<h3>Email-Related Logs:</h3>';
                    echo '<pre>' . htmlspecialchars(implode('', $email_logs)) . '</pre>';
                } else {
                    echo '<p class="warning">No email-related logs found in recent entries. Logs will appear here after you try to send an email.</p>';
                }
                
                echo '<p><small>To view full log, check: <code>' . htmlspecialchars($error_log) . '</code></small></p>';
            } else {
                echo '<p class="warning">Error log file not found at: <code>' . htmlspecialchars($error_log) . '</code></p>';
            }
            ?>
        </div>
    </div>
</body>
</html>
