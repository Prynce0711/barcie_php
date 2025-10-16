<?php
// Test booking email flow
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Booking Email Debug Test</h2>";
echo "<pre>";

// Include required files
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/database/db_connect.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Helper function to create professional email template
function create_email_template($title, $content, $footerText = '') {
    $currentYear = date('Y');
    
    // Get base64 encoded logo
    $logo_path = __DIR__ . '/assets/images/imageBg/barcie_logo.jpg';
    $logo_data = '';
    if (file_exists($logo_path)) {
        $logo_base64 = base64_encode(file_get_contents($logo_path));
        $logo_data = 'data:image/jpeg;base64,' . $logo_base64;
    }
    
    return '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . htmlspecialchars($title) . '</title>
    </head>
    <body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, \'Helvetica Neue\', Arial, sans-serif; background-color: #f4f4f4;">
        <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #f4f4f4;" cellpadding="0" cellspacing="0">
            <tr>
                <td align="center" style="padding: 40px 0;">
                    <!-- Main Container -->
                    <table role="presentation" style="width: 600px; max-width: 100%; border-collapse: collapse; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);" cellpadding="0" cellspacing="0">
                        
                        <!-- Header -->
                        <tr>
                            <td style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); padding: 30px; text-align: center; border-radius: 8px 8px 0 0;">
                                ' . ($logo_data ? '<img src="' . $logo_data . '" alt="BarCIE Logo" style="width: 80px; height: 80px; margin-bottom: 15px; border-radius: 50%; border: 3px solid #ffffff; box-shadow: 0 2px 8px rgba(0,0,0,0.2);" />' : '') . '
                                <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 600; letter-spacing: -0.5px;">BarCIE International Center</h1>
                                <p style="margin: 10px 0 0 0; color: #f0f0f0; font-size: 14px;">La Consolacion University Philippines</p>
                            </td>
                        </tr>
                        
                        <!-- Content -->
                        <tr>
                            <td style="padding: 40px 30px;">
                                ' . $content . '
                            </td>
                        </tr>
                        
                        <!-- Footer -->
                        <tr>
                            <td style="background-color: #f8f9fa; padding: 30px; text-align: center; border-radius: 0 0 8px 8px; border-top: 1px solid #e9ecef;">
                                ' . ($footerText ? '<p style="margin: 0 0 15px 0; color: #6c757d; font-size: 13px;">' . $footerText . '</p>' : '') . '
                                <p style="margin: 0 0 5px 0; color: #6c757d; font-size: 13px;">
                                    <strong>BarCIE International Center</strong><br>
                                    La Consolacion University Philippines<br>
                                    Email: pc.clemente11@gmail.com
                                </p>
                                <p style="margin: 15px 0 0 0; color: #adb5bd; font-size: 12px;">
                                    © ' . $currentYear . ' BarCIE International Center. All rights reserved.
                                </p>
                            </td>
                        </tr>
                        
                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>';
}

// Function to send email (copied from user_auth.php)
function send_smtp_mail($to, $subject, $body) {
    echo "\n=== ATTEMPTING TO SEND EMAIL ===\n";
    echo "To: $to\n";
    echo "Subject: $subject\n\n";
    
    $config_path = __DIR__ . '/database/mail_config.php';
    
    if (!file_exists($config_path)) {
        echo "ERROR: Config file not found at $config_path\n";
        return false;
    }
    
    $config = require $config_path;
    
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->SMTPDebug = 2;
        $mail->Debugoutput = function($str, $level) {
            echo "SMTP: " . $str . "\n";
        };
        
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        $mail->SMTPSecure = $config['secure'];
        $mail->Port = $config['port'];
        
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addAddress($to);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->isHTML(true);
        
        $result = $mail->send();
        
        if ($result) {
            echo "\n✓✓✓ EMAIL SENT SUCCESSFULLY ✓✓✓\n";
        }
        
        return $result;
        
    } catch (Exception $e) {
        echo "\n✗✗✗ EMAIL FAILED ✗✗✗\n";
        echo "Error: " . $e->getMessage() . "\n";
        echo "Trace: " . $e->getTraceAsString() . "\n";
        return false;
    }
}

// Get email from URL parameter
if (!isset($_GET['email']) || empty($_GET['email'])) {
    echo "\n================================================\n";
    echo "To test booking confirmation email, visit:\n";
    echo "http://localhost/barcie_php/test_booking_email.php?email=YOUR_EMAIL@gmail.com\n";
    echo "================================================\n";
    exit;
}

$test_email = $_GET['email'];

echo "Testing booking confirmation email...\n";
echo "Recipient: $test_email\n";
echo "----------------------------------------\n\n";

// Simulate a booking confirmation email with professional template
$subject = "Booking Confirmation - BarCIE International Center";

$emailContent = '
    <h2 style="margin: 0 0 20px 0; color: #212529; font-size: 24px; font-weight: 600;">Booking Confirmation</h2>
    <p style="margin: 0 0 20px 0; color: #495057; font-size: 16px; line-height: 1.6;">
        Dear <strong>Test Guest</strong>,
    </p>
    <p style="margin: 0 0 25px 0; color: #495057; font-size: 15px; line-height: 1.6;">
        Thank you for your booking! We have received your reservation request with the following details:
    </p>
    
    <!-- Booking Details Card -->
    <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #f8f9fa; border-radius: 6px; margin-bottom: 25px;" cellpadding="0" cellspacing="0">
        <tr>
            <td style="padding: 25px;">
                <table role="presentation" style="width: 100%; border-collapse: collapse;" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="padding: 8px 0; color: #6c757d; font-size: 14px; width: 40%;">Receipt Number:</td>
                        <td style="padding: 8px 0; color: #212529; font-size: 14px; font-weight: 600;">BARCIE-TEST-12345</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #6c757d; font-size: 14px;">Room/Facility:</td>
                        <td style="padding: 8px 0; color: #212529; font-size: 14px; font-weight: 600;">Deluxe Suite</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #6c757d; font-size: 14px;">Check-in Date:</td>
                        <td style="padding: 8px 0; color: #212529; font-size: 14px; font-weight: 600;">' . date('F j, Y', strtotime('+5 days')) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #6c757d; font-size: 14px;">Check-out Date:</td>
                        <td style="padding: 8px 0; color: #212529; font-size: 14px; font-weight: 600;">' . date('F j, Y', strtotime('+7 days')) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #6c757d; font-size: 14px;">Number of Occupants:</td>
                        <td style="padding: 8px 0; color: #212529; font-size: 14px; font-weight: 600;">2</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #6c757d; font-size: 14px;">Booking Status:</td>
                        <td style="padding: 8px 0;">
                            <span style="display: inline-block; padding: 4px 12px; background-color: #ffc107; color: #000; font-size: 13px; font-weight: 600; border-radius: 4px;">Pending Approval</span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    
    <!-- Next Steps -->
    <div style="background-color: #e7f3ff; border-left: 4px solid #2196F3; padding: 15px 20px; margin-bottom: 25px; border-radius: 4px;">
        <p style="margin: 0; color: #1976D2; font-size: 14px; line-height: 1.6;">
            <strong>📋 What happens next?</strong><br>
            Our team will review your booking request and notify you via email once it has been approved. Please keep this receipt number for your records.
        </p>
    </div>
    
    <p style="margin: 0 0 15px 0; color: #495057; font-size: 15px; line-height: 1.6;">
        If you have any questions or need to make changes to your booking, please contact us with your receipt number.
    </p>
    <p style="margin: 0; color: #495057; font-size: 15px; line-height: 1.6;">
        Thank you for choosing BarCIE International Center!
    </p>';

$emailBody = create_email_template($subject, $emailContent, 'This is an automated message. Please do not reply directly to this email.');

$result = send_smtp_mail($test_email, $subject, $emailBody);

if ($result) {
    echo "\n\n🎉 SUCCESS! Check your email inbox at: $test_email\n";
    echo "\nThis means booking confirmation emails WILL work when guests make bookings.\n";
    echo "The email will have a professional design with:\n";
    echo "- Purple gradient header with BarCIE branding\n";
    echo "- Clean, modern layout\n";
    echo "- Booking details in a styled card\n";
    echo "- Status badges and helpful information\n";
    echo "- Professional footer with contact information\n";
} else {
    echo "\n\n❌ FAILED! There's an issue with the email function.\n";
}

echo "\n\n";
echo "Next Steps:\n";
echo "1. If this test worked, make a real booking at: http://localhost/barcie_php/Guest.php\n";
echo "2. Use your real email address in the booking form\n";
echo "3. You should receive a beautifully formatted booking confirmation email\n";
echo "4. All status change emails (approved, rejected, check-in, etc.) will also look professional!\n";

echo "</pre>";
?>
