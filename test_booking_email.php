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

// Simulate a booking confirmation email
$subject = "Booking Confirmation - BarCIE International Center";
$emailBody = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px;'>
        <h2 style='color: #2d7be5;'>Booking Confirmation</h2>
        <p>Dear Test Guest,</p>
        <p>Your booking has been received with the following details:</p>
        <div style='background: #f5f5f5; padding: 15px; border-radius: 5px;'>
            <p><strong>Receipt Number:</strong> BARCIE-TEST-12345</p>
            <p><strong>Room/Facility:</strong> Test Room</p>
            <p><strong>Check-in:</strong> 2025-10-20</p>
            <p><strong>Check-out:</strong> 2025-10-22</p>
            <p><strong>Number of Occupants:</strong> 2</p>
            <p><strong>Status:</strong> Pending approval</p>
        </div>
        <p>We will review your booking and notify you once it's approved.</p>
        <p><em>This is an automated message. Please do not reply to this email.</em></p>
    </div>";

$result = send_smtp_mail($test_email, $subject, $emailBody);

if ($result) {
    echo "\n\n🎉 SUCCESS! Check your email inbox at: $test_email\n";
    echo "\nThis means booking confirmation emails WILL work when guests make bookings.\n";
} else {
    echo "\n\n❌ FAILED! There's an issue with the email function.\n";
}

echo "\n\n";
echo "Next Steps:\n";
echo "1. If this test worked, make a real booking at: http://localhost/barcie_php/Guest.php\n";
echo "2. Use your real email address in the booking form\n";
echo "3. You should receive the booking confirmation email immediately\n";

echo "</pre>";
?>
