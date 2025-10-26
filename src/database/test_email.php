<?php
/**
 * Email Test Tool
 * This file sends a test email to verify SMTP configuration
 * Access: http://localhost/barcie_php/src/database/test_email.php
 */

// Start session for messages
session_start();

// Handle form submission
$test_result = null;
$error_details = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_test'])) {
    $recipient_email = trim($_POST['recipient_email'] ?? '');
    
    if (empty($recipient_email)) {
        $test_result = ['success' => false, 'message' => 'Please enter a recipient email address'];
    } elseif (!filter_var($recipient_email, FILTER_VALIDATE_EMAIL)) {
        $test_result = ['success' => false, 'message' => 'Please enter a valid email address'];
    } else {
        // Include the email function
        $vendor_path = __DIR__ . '/../../vendor/autoload.php';
        if (!file_exists($vendor_path)) {
            $test_result = ['success' => false, 'message' => 'PHPMailer not installed. Run: composer install'];
        } else {
            require_once $vendor_path;
            
            // Load mail config
            $config_path = __DIR__ . '/mail_config.php';
            if (!file_exists($config_path)) {
                $test_result = ['success' => false, 'message' => 'Mail configuration file not found'];
            } else {
                $config = require $config_path;
                
                // Check if credentials are set
                if (empty($config['username']) || empty($config['password'])) {
                    $test_result = ['success' => false, 'message' => 'SMTP credentials not configured in mail_config.php'];
                } else {
                    // Try to send test email
                    try {
                        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
                        
                        // Capture debug output
                        ob_start();
                        
                        // SMTP Configuration
                        $mail->SMTPDebug = 2;
                        $mail->Debugoutput = function($str, $level) {
                            echo $str . "\n";
                        };
                        
                        $mail->isSMTP();
                        $mail->Host = $config['host'];
                        $mail->SMTPAuth = true;
                        $mail->Username = $config['username'];
                        $mail->Password = $config['password'];
                        $mail->SMTPSecure = $config['secure'];
                        $mail->Port = $config['port'];
                        
                        // SSL/TLS options
                        $mail->SMTPOptions = array(
                            'ssl' => array(
                                'verify_peer' => false,
                                'verify_peer_name' => false,
                                'allow_self_signed' => true
                            )
                        );
                        
                        // Email content
                        $mail->setFrom($config['from_email'], $config['from_name']);
                        $mail->addAddress($recipient_email);
                        $mail->Subject = 'BarCIE Email Test - ' . date('Y-m-d H:i:s');
                        $mail->isHTML(true);
                        
                        $mail->Body = '
                        <!DOCTYPE html>
                        <html>
                        <head>
                            <style>
                                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                                .container { max-width: 600px; margin: 0 auto; padding: 20px; background: #f9f9f9; }
                                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
                                .content { background: white; padding: 30px; border-radius: 0 0 8px 8px; }
                                .success-badge { display: inline-block; background: #28a745; color: white; padding: 10px 20px; border-radius: 20px; font-weight: bold; margin: 20px 0; }
                                .info-box { background: #e7f3ff; border-left: 4px solid #2196F3; padding: 15px; margin: 20px 0; border-radius: 4px; }
                                table { width: 100%; border-collapse: collapse; margin: 15px 0; }
                                table td { padding: 8px; border-bottom: 1px solid #eee; }
                                table td:first-child { font-weight: bold; color: #666; width: 40%; }
                            </style>
                        </head>
                        <body>
                            <div class="container">
                                <div class="header">
                                    <h1>✅ Email Test Successful!</h1>
                                </div>
                                <div class="content">
                                    <div class="success-badge">🎉 Your Email Configuration is Working!</div>
                                    
                                    <p>Congratulations! If you\'re reading this email, your SMTP configuration is set up correctly and emails are being sent successfully from your BarCIE booking system.</p>
                                    
                                    <div class="info-box">
                                        <strong>📋 Test Details:</strong>
                                        <table>
                                            <tr>
                                                <td>Test Date:</td>
                                                <td>' . date('F j, Y') . '</td>
                                            </tr>
                                            <tr>
                                                <td>Test Time:</td>
                                                <td>' . date('g:i A') . '</td>
                                            </tr>
                                            <tr>
                                                <td>SMTP Server:</td>
                                                <td>' . htmlspecialchars($config['host']) . '</td>
                                            </tr>
                                            <tr>
                                                <td>Port:</td>
                                                <td>' . htmlspecialchars($config['port']) . '</td>
                                            </tr>
                                            <tr>
                                                <td>Security:</td>
                                                <td>' . strtoupper(htmlspecialchars($config['secure'])) . '</td>
                                            </tr>
                                            <tr>
                                                <td>From:</td>
                                                <td>' . htmlspecialchars($config['from_email']) . '</td>
                                            </tr>
                                        </table>
                                    </div>
                                    
                                    <h3>✨ What This Means:</h3>
                                    <ul>
                                        <li>✓ PHPMailer is installed and working</li>
                                        <li>✓ SMTP credentials are correct</li>
                                        <li>✓ Email server connection is successful</li>
                                        <li>✓ Booking confirmation emails will be sent to guests</li>
                                        <li>✓ Admin notification emails will be delivered</li>
                                    </ul>
                                    
                                    <h3>🚀 Next Steps:</h3>
                                    <p>Your email system is ready! Guest booking confirmations and admin notifications will now be sent automatically when bookings are made.</p>
                                    
                                    <hr style="border: none; border-top: 1px solid #eee; margin: 30px 0;">
                                    <p style="text-align: center; color: #888; font-size: 12px;">
                                        This is a test email from BarCIE International Center Booking System<br>
                                        Generated on ' . date('Y-m-d H:i:s') . '
                                    </p>
                                </div>
                            </div>
                        </body>
                        </html>';
                        
                        $mail->AltBody = 'Email Test - If you can read this, your email configuration is working correctly!';
                        
                        // Send email
                        $send_result = $mail->send();
                        
                        // Get debug output
                        $debug_output = ob_get_clean();
                        
                        if ($send_result) {
                            $test_result = [
                                'success' => true, 
                                'message' => 'Test email sent successfully to ' . htmlspecialchars($recipient_email) . '!',
                                'debug' => $debug_output
                            ];
                        } else {
                            $test_result = [
                                'success' => false, 
                                'message' => 'Failed to send test email',
                                'debug' => $debug_output
                            ];
                        }
                        
                    } catch (\PHPMailer\PHPMailer\Exception $e) {
                        $debug_output = ob_get_clean();
                        $test_result = [
                            'success' => false, 
                            'message' => 'PHPMailer Error: ' . $e->getMessage(),
                            'debug' => $debug_output
                        ];
                        $error_details = $e->getTraceAsString();
                    } catch (Exception $e) {
                        $debug_output = ob_get_clean();
                        $test_result = [
                            'success' => false, 
                            'message' => 'Error: ' . $e->getMessage(),
                            'debug' => $debug_output
                        ];
                        $error_details = $e->getTraceAsString();
                    }
                }
            }
        }
    }
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Send Test Email</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        .form-group { margin: 20px 0; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        input[type="email"] { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px; box-sizing: border-box; }
        .btn { display: inline-block; padding: 12px 24px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; border: none; cursor: pointer; font-size: 16px; margin: 10px 5px 0 0; }
        .btn:hover { background: #0056b3; }
        .btn-secondary { background: #6c757d; }
        .btn-secondary:hover { background: #5a6268; }
        .alert { padding: 15px; margin: 20px 0; border-radius: 4px; }
        .alert-success { background: #d4edda; border-left: 4px solid #28a745; color: #155724; }
        .alert-error { background: #f8d7da; border-left: 4px solid #dc3545; color: #721c24; }
        .debug-output { background: #263238; color: #aed581; padding: 15px; border-radius: 4px; overflow-x: auto; font-family: 'Courier New', monospace; font-size: 12px; margin-top: 15px; white-space: pre-wrap; word-wrap: break-word; max-height: 400px; overflow-y: auto; }
        .info-box { background: #e7f3ff; border-left: 4px solid #2196F3; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .help-text { color: #666; font-size: 14px; margin-top: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📨 Send Test Email</h1>
        
        <?php if ($test_result): ?>
            <div class="alert <?php echo $test_result['success'] ? 'alert-success' : 'alert-error'; ?>">
                <strong><?php echo $test_result['success'] ? '✅ Success!' : '❌ Error!'; ?></strong><br>
                <?php echo htmlspecialchars($test_result['message']); ?>
            </div>
            
            <?php if (isset($test_result['debug']) && !empty($test_result['debug'])): ?>
                <details open>
                    <summary style="cursor: pointer; font-weight: bold; margin: 15px 0; padding: 10px; background: #f8f9fa; border-radius: 4px;">
                        🔍 SMTP Debug Output (Click to collapse)
                    </summary>
                    <div class="debug-output"><?php echo htmlspecialchars($test_result['debug']); ?></div>
                </details>
            <?php endif; ?>
            
            <?php if ($error_details): ?>
                <details>
                    <summary style="cursor: pointer; font-weight: bold; margin: 15px 0; padding: 10px; background: #f8f9fa; border-radius: 4px;">
                        🐛 Error Trace (Click to expand)
                    </summary>
                    <div class="debug-output"><?php echo htmlspecialchars($error_details); ?></div>
                </details>
            <?php endif; ?>
        <?php endif; ?>

        <div class="info-box">
            <strong>ℹ️ About This Test:</strong>
            <p>This tool sends a test email using your configured SMTP settings. It will show you detailed debug information to help troubleshoot any email delivery issues.</p>
        </div>

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <div class="form-group">
                <label for="recipient_email">Recipient Email Address:</label>
                <input 
                    type="email" 
                    id="recipient_email" 
                    name="recipient_email" 
                    placeholder="your-email@example.com"
                    value="<?php echo htmlspecialchars($_POST['recipient_email'] ?? ''); ?>"
                    required
                >
                <p class="help-text">Enter your email address to receive the test email</p>
            </div>
            
            <button type="submit" name="send_test" class="btn">📧 Send Test Email</button>
            <a href="debug_email.php" class="btn btn-secondary">🔍 View Debug Info</a>
            <a href="../../Guest.php" class="btn btn-secondary">← Back to Guest Page</a>
        </form>

        <div style="margin-top: 30px; padding: 20px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
            <h3 style="margin-top: 0;">⚠️ Common Issues & Solutions:</h3>
            <ul>
                <li><strong>SMTP Error: Authentication failed</strong>
                    <ul>
                        <li>Make sure you're using an App Password, not your regular Gmail password</li>
                        <li>Verify 2-Step Verification is enabled on your Google Account</li>
                        <li>Double-check the username (full email) and password in mail_config.php</li>
                    </ul>
                </li>
                <li><strong>Connection timeout</strong>
                    <ul>
                        <li>Check your firewall settings</li>
                        <li>Verify port 587 is not blocked</li>
                        <li>Try using port 465 with SSL instead of TLS</li>
                    </ul>
                </li>
                <li><strong>Email not received</strong>
                    <ul>
                        <li>Check your spam/junk folder</li>
                        <li>Wait a few minutes (email delivery can be delayed)</li>
                        <li>Verify the recipient email address is correct</li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</body>
</html>
