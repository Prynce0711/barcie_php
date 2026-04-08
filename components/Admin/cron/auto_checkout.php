<?php
/**
 * Automated Checkout Cron Job
 * 
 * This script should be run every hour via cron job or Windows Task Scheduler
 * It performs:
 * 1. Sends reminder emails 1 hour before checkout time
 * 2. Automatically checks out guests when checkout time is reached
 * 
 * Setup for Windows Task Scheduler:
 * Program: C:\xampp\php\php.exe
 * Arguments: -f "C:\xampp\htdocs\barcie_php\cron\auto_checkout.php"
 * Schedule: Every 1 hour
 */

// Prevent direct browser access
if (php_sapi_name() !== 'cli' && !defined('ALLOW_CRON_WEB_ACCESS')) {
    // Allow web access only if explicitly enabled for testing
    if (!isset($_GET['test']) || $_GET['test'] !== 'run_cron_now') {
        die('This script can only be run from command line or with proper test parameter.');
    }
}

// Set timezone
date_default_timezone_set('Asia/Manila');

// Include required files
require_once __DIR__ . '/../database/db_connect.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load mail configuration
$mail_config = require __DIR__ . '/../database/mail_config.php';

// Log function
function logCron($message) {
    $logFile = __DIR__ . '/../logs/auto_checkout.log';
    $logDir = dirname($logFile);
    
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $message\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

/**
 * Send email using PHPMailer
 */
function sendEmail($to, $toName, $subject, $body) {
    global $mail_config;
    
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = $mail_config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $mail_config['username'];
        $mail->Password = $mail_config['password'];
        $mail->SMTPSecure = $mail_config['secure'];
        $mail->Port = $mail_config['port'];
        
        // Recipients
        $mail->setFrom($mail_config['from_email'], $mail_config['from_name']);
        $mail->addAddress($to, $toName);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body);
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        logCron("Email sending failed: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Send checkout reminder email
 */
function sendCheckoutReminder($booking, $conn) {
    // Extract guest email from booking details
    $guest_email = '';
    $guest_name = 'Guest';
    
    if (preg_match('/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/', $booking['details'], $matches)) {
        $guest_email = $matches[1];
    }
    
    if (preg_match('/Guest:\s*([^|]+)/', $booking['details'], $matches)) {
        $guest_name = trim($matches[1]);
    }
    
    if (empty($guest_email)) {
        logCron("No email found for booking #{$booking['id']}");
        return false;
    }
    
    // Get room/facility name
    $room_name = $booking['room_name'] ?? 'Your Room';
    if (!empty($booking['room_number'])) {
        $room_name .= " #" . $booking['room_number'];
    }
    
    $checkout_time = date('F j, Y \a\t g:i A', strtotime($booking['checkout']));
    $receipt_no = "BARCIE-" . date('Ymd', strtotime($booking['created_at'])) . "-" . str_pad($booking['id'], 4, '0', STR_PAD_LEFT);
    
    $subject = "Checkout Reminder - BarCIE International Center";
    
    $body = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #2a5298 0%, #1e3a6d 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
            .info-box { background: white; padding: 20px; border-left: 4px solid #ffc107; margin: 20px 0; border-radius: 5px; }
            .button { display: inline-block; padding: 12px 30px; background: #2a5298; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { text-align: center; color: #666; font-size: 12px; margin-top: 20px; }
            .icon { font-size: 50px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <div class='icon'>⏰</div>
                <h1>Checkout Reminder</h1>
                <p>Your checkout time is approaching</p>
            </div>
            <div class='content'>
                <p>Dear <strong>{$guest_name}</strong>,</p>
                
                <p>This is a friendly reminder that your checkout time is <strong>1 hour away</strong>.</p>
                
                <div class='info-box'>
                    <h3 style='color: #2a5298; margin-top: 0;'>Booking Details</h3>
                    <p><strong>Receipt #:</strong> {$receipt_no}</p>
                    <p><strong>Room/Facility:</strong> {$room_name}</p>
                    <p><strong>Checkout Time:</strong> {$checkout_time}</p>
                </div>
                
                <h3>Before You Leave:</h3>
                <ul>
                    <li>Please ensure all personal belongings are collected</li>
                    <li>Return any room keys or access cards to the front desk</li>
                    <li>Check if you have any outstanding charges</li>
                    <li>We hope you enjoyed your stay!</li>
                </ul>
                
                <p>If you need a late checkout or have any questions, please contact our front desk immediately.</p>
                
                <div style='text-align: center;'>
                    <p style='color: #2a5298; font-weight: bold;'>Thank you for choosing BarCIE International Center!</p>
                </div>
            </div>
            <div class='footer'>
                <p>BarCIE International Center<br>
                This is an automated message. Please do not reply to this email.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $result = sendEmail($guest_email, $guest_name, $subject, $body);
    
    if ($result) {
        // Mark reminder as sent
        $stmt = $conn->prepare("UPDATE bookings SET reminder_sent = 1 WHERE id = ?");
        $stmt->bind_param("i", $booking['id']);
        $stmt->execute();
        $stmt->close();
        
        logCron("Checkout reminder sent to {$guest_email} for booking #{$booking['id']}");
    }
    
    return $result;
}

/**
 * Send auto-checkout notification email
 */
function sendAutoCheckoutNotification($booking, $conn) {
    // Extract guest email from booking details
    $guest_email = '';
    $guest_name = 'Guest';
    
    if (preg_match('/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/', $booking['details'], $matches)) {
        $guest_email = $matches[1];
    }
    
    if (preg_match('/Guest:\s*([^|]+)/', $booking['details'], $matches)) {
        $guest_name = trim($matches[1]);
    }
    
    if (empty($guest_email)) {
        return false;
    }
    
    // Get room/facility name
    $room_name = $booking['room_name'] ?? 'Your Room';
    if (!empty($booking['room_number'])) {
        $room_name .= " #" . $booking['room_number'];
    }
    
    $checkout_time = date('F j, Y \a\t g:i A', strtotime($booking['checkout']));
    $receipt_no = "BARCIE-" . date('Ymd', strtotime($booking['created_at'])) . "-" . str_pad($booking['id'], 4, '0', STR_PAD_LEFT);
    
    $subject = "Checkout Completed - Thank You for Your Stay";
    
    $body = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
            .info-box { background: white; padding: 20px; border-left: 4px solid #28a745; margin: 20px 0; border-radius: 5px; }
            .footer { text-align: center; color: #666; font-size: 12px; margin-top: 20px; }
            .icon { font-size: 50px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <div class='icon'>✅</div>
                <h1>Checkout Completed</h1>
                <p>Thank you for staying with us!</p>
            </div>
            <div class='content'>
                <p>Dear <strong>{$guest_name}</strong>,</p>
                
                <p>Your checkout has been completed successfully. We hope you enjoyed your stay at BarCIE International Center!</p>
                
                <div class='info-box'>
                    <h3 style='color: #28a745; margin-top: 0;'>Checkout Summary</h3>
                    <p><strong>Receipt #:</strong> {$receipt_no}</p>
                    <p><strong>Room/Facility:</strong> {$room_name}</p>
                    <p><strong>Checkout Time:</strong> {$checkout_time}</p>
                    <p><strong>Status:</strong> <span style='color: #28a745; font-weight: bold;'>Checked Out</span></p>
                </div>
                
                <h3>We'd Love Your Feedback!</h3>
                <p>Your opinion matters to us. Please take a moment to share your experience and help us improve our services.</p>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <p style='font-size: 18px; color: #2a5298;'><strong>We hope to see you again soon!</strong></p>
                </div>
                
                <p>If you have any questions or concerns about your stay, please don't hesitate to contact us.</p>
            </div>
            <div class='footer'>
                <p>BarCIE International Center<br>
                This is an automated message. Please do not reply to this email.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    sendEmail($guest_email, $guest_name, $subject, $body);
}

// Start cron job execution
logCron("=== Starting auto-checkout cron job ===");

$current_time = date('Y-m-d H:i:s');
$reminder_time = date('Y-m-d H:i:s', strtotime('+1 hour'));

// 1. Send checkout reminders (1 hour before checkout)
$reminder_query = "
    SELECT b.*, i.name as room_name, i.room_number 
    FROM bookings b 
    LEFT JOIN items i ON b.room_id = i.id 
    WHERE b.status = 'checked_in' 
    AND b.checkout <= ? 
    AND b.checkout > ?
    AND (b.reminder_sent IS NULL OR b.reminder_sent = 0)
";

$stmt = $conn->prepare($reminder_query);
$stmt->bind_param("ss", $reminder_time, $current_time);
$stmt->execute();
$reminders = $stmt->get_result();

$reminder_count = 0;
while ($booking = $reminders->fetch_assoc()) {
    if (sendCheckoutReminder($booking, $conn)) {
        $reminder_count++;
    }
}

$stmt->close();

if ($reminder_count > 0) {
    logCron("Sent {$reminder_count} checkout reminder(s)");
} else {
    logCron("No checkout reminders to send");
}

// 2. Auto-checkout bookings past checkout time
$checkout_query = "
    SELECT b.*, i.name as room_name, i.room_number 
    FROM bookings b 
    LEFT JOIN items i ON b.room_id = i.id 
    WHERE b.status = 'checked_in' 
    AND b.checkout <= ?
";

$stmt = $conn->prepare($checkout_query);
$stmt->bind_param("s", $current_time);
$stmt->execute();
$checkouts = $stmt->get_result();

$checkout_count = 0;
while ($booking = $checkouts->fetch_assoc()) {
    // Update status to checked_out
    $update_stmt = $conn->prepare("UPDATE bookings SET status = 'checked_out', updated_at = NOW() WHERE id = ?");
    $update_stmt->bind_param("i", $booking['id']);
    
    if ($update_stmt->execute()) {
        $checkout_count++;
        logCron("Auto-checkout completed for booking #{$booking['id']}");
        
        // Send checkout notification email
        sendAutoCheckoutNotification($booking, $conn);
    }
    
    $update_stmt->close();
}

$stmt->close();

if ($checkout_count > 0) {
    logCron("Completed {$checkout_count} auto-checkout(s)");
} else {
    logCron("No bookings to auto-checkout");
}

logCron("=== Auto-checkout cron job completed ===\n");

// Close database connection
$conn->close();

// Output for command line
if (php_sapi_name() === 'cli') {
    echo "Auto-checkout cron job completed.\n";
    echo "Reminders sent: {$reminder_count}\n";
    echo "Auto-checkouts: {$checkout_count}\n";
}

// For web testing
if (isset($_GET['test'])) {
    echo json_encode([
        'success' => true,
        'reminders_sent' => $reminder_count,
        'auto_checkouts' => $checkout_count,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
