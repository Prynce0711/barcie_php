<?php
/**
 * Email Test API Endpoint
 * Sends a test email to verify email configuration
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Load dependencies
require_once __DIR__ . '/../vendor/autoload.php';

// Load .env
if (class_exists(\Dotenv\Dotenv::class)) {
    try {
        $dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->safeLoad();
    } catch (Exception $e) {
        error_log("Failed to load .env: " . $e->getMessage());
    }
}

// Get email from request
$input = json_decode(file_get_contents('php://input'), true);
$to = $input['email'] ?? '';

if (empty($to) || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

// Load mail config
$config_path = __DIR__ . '/../database/mail_config.php';
if (!file_exists($config_path)) {
    echo json_encode(['success' => false, 'message' => 'Mail configuration not found']);
    exit;
}

$config = require $config_path;

// Send test email using PHPMailer
try {
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    
    // Server settings
    $mail->isSMTP();
    $mail->Host = $config['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $config['username'];
    $mail->Password = $config['password'];
    $mail->SMTPSecure = $config['secure'];
    $mail->Port = $config['port'];
    
    // SSL options
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    
    // Recipients
    $mail->setFrom($config['from_email'], $config['from_name']);
    $mail->addAddress($to);
    
    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Test Email - BarCIE System';
    $mail->Body = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
    </head>
    <body style="font-family: Arial, sans-serif; padding: 0; margin: 0; background-color: #f4f4f4;">
        <table role="presentation" style="width: 100%; border-collapse: collapse;">
            <tr>
                <td align="center" style="padding: 40px 0;">
                    <table role="presentation" style="width: 600px; max-width: 100%; border-collapse: collapse; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                        <tr>
                            <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; border-radius: 8px 8px 0 0;">
                                <h1 style="margin: 0; color: #ffffff; font-size: 28px;">✅ Email Test Successful!</h1>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 40px 30px;">
                                <h2 style="margin: 0 0 20px 0; color: #2d3748;">Your email system is working perfectly!</h2>
                                <p style="margin: 0 0 15px 0; color: #4a5568; line-height: 1.6;">
                                    This is a test email from your <strong>BarCIE International Center</strong> booking system.
                                </p>
                                <div style="background-color: #f7fafc; border-left: 4px solid #48bb78; padding: 15px; margin: 20px 0; border-radius: 4px;">
                                    <p style="margin: 0; color: #2d5016; font-weight: 600;">✅ Email Configuration Status</p>
                                    <ul style="margin: 10px 0 0 0; padding-left: 20px; color: #4a5568;">
                                        <li>SMTP Connection: Working</li>
                                        <li>Email Delivery: Successful</li>
                                        <li>Templates: Rendering correctly</li>
                                        <li>Booking Notifications: Ready</li>
                                    </ul>
                                </div>
                                <p style="margin: 20px 0 0 0; color: #4a5568; line-height: 1.6;">
                                    <strong>Test Details:</strong><br>
                                    Date: ' . date('F j, Y g:i A') . '<br>
                                    Recipient: ' . htmlspecialchars($to) . '<br>
                                    System: BarCIE Booking System
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td style="background-color: #f8f9fa; padding: 20px 30px; text-align: center; border-radius: 0 0 8px 8px;">
                                <p style="margin: 0; color: #6c757d; font-size: 13px;">
                                    BarCIE International Center<br>
                                    La Consolacion University Philippines
                                </p>
                                <p style="margin: 10px 0 0 0; color: #adb5bd; font-size: 12px;">
                                    © ' . date('Y') . ' BarCIE. All rights reserved.
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>';
    
    $mail->AltBody = 'Email Test Successful! Your BarCIE booking system email configuration is working correctly. Date: ' . date('F j, Y g:i A');
    
    $mail->send();
    
    echo json_encode([
        'success' => true,
        'message' => 'Test email sent successfully to ' . $to . '!',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (\PHPMailer\PHPMailer\Exception $e) {
    error_log('PHPMailer error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'PHPMailer error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log('Email error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
