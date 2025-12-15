<?php
/**
 * Auto Check-In/Check-Out Cron Job
 * 
 * This script automatically:
 * 1. Checks in guests at 2:00 PM on their check-in date
 * 2. Checks out guests at 12:00 PM on their check-out date
 * 
 * Setup for Windows Task Scheduler:
 * Program: C:\xampp\php\php.exe
 * Arguments: -f "C:\xampp\htdocs\barcie_php\cron\auto_checkin_checkout.php"
 * Schedule: Every 30 minutes (or hourly)
 */

// Prevent direct browser access
if (php_sapi_name() !== 'cli' && !defined('ALLOW_CRON_WEB_ACCESS')) {
    if (!isset($_GET['test']) || $_GET['test'] !== 'run_cron_now') {
        die('This script can only be run from command line or with proper test parameter.');
    }
}

// Set timezone
date_default_timezone_set('Asia/Manila');

// Include required files
require_once __DIR__ . '/../database/db_connect.php';

// Log function
function logCron($message) {
    $logFile = __DIR__ . '/../logs/auto_checkin_checkout.log';
    $logDir = dirname($logFile);
    
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $message\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
    
    // Also output for CLI
    echo $logEntry;
}

logCron("=== Auto Check-In/Check-Out Cron Job Started ===");

try {
    $checkin_count = 0;
    $checkout_count = 0;
    $current_datetime = date('Y-m-d H:i:s');
    $current_date = date('Y-m-d');
    $current_time = date('H:i:s');
    
    // Auto Check-In: Process bookings with check-in date today at or after 2:00 PM (14:00)
    // Status should be 'approved' or 'confirmed'
    if ($current_time >= '14:00:00') {
        logCron("Processing auto check-ins (current time: $current_time)...");
        
        $checkin_query = "SELECT b.id, b.receipt_no, b.details, i.name as room_name, i.id as room_id
                         FROM bookings b
                         LEFT JOIN items i ON b.room_id = i.id
                         WHERE DATE(b.checkin) = ?
                         AND b.status IN ('approved', 'confirmed')
                         AND TIME(b.checkin) <= ?";
        
        $stmt = $conn->prepare($checkin_query);
        $check_time = '14:00:00';
        $stmt->bind_param('ss', $current_date, $check_time);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($booking = $result->fetch_assoc()) {
            // Update booking status to checked_in
            $update = $conn->prepare("UPDATE bookings SET status = 'checked_in' WHERE id = ?");
            $update->bind_param('i', $booking['id']);
            
            if ($update->execute()) {
                // Update room status to occupied
                if ($booking['room_id']) {
                    $room_update = $conn->prepare("UPDATE items SET room_status = 'occupied' WHERE id = ?");
                    $room_update->bind_param('i', $booking['room_id']);
                    $room_update->execute();
                    $room_update->close();
                }
                
                $checkin_count++;
                logCron("✓ Auto checked-in: Receipt #{$booking['receipt_no']} - {$booking['room_name']}");
            } else {
                logCron("✗ Failed to check-in: Receipt #{$booking['receipt_no']}");
            }
            
            $update->close();
        }
        
        $stmt->close();
    } else {
        logCron("Skipping auto check-ins (current time: $current_time, check-in starts at 14:00)");
    }
    
    // Auto Check-Out: Process bookings with check-out date today at or after 12:00 PM (12:00)
    // Status should be 'checked_in'
    if ($current_time >= '12:00:00') {
        logCron("Processing auto check-outs (current time: $current_time)...");
        
        $checkout_query = "SELECT b.id, b.receipt_no, b.details, i.name as room_name, i.id as room_id
                          FROM bookings b
                          LEFT JOIN items i ON b.room_id = i.id
                          WHERE DATE(b.checkout) = ?
                          AND b.status = 'checked_in'
                          AND TIME(b.checkout) <= ?";
        
        $stmt = $conn->prepare($checkout_query);
        $checkout_time = '12:00:00';
        $stmt->bind_param('ss', $current_date, $checkout_time);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($booking = $result->fetch_assoc()) {
            // Update booking status to checked_out
            $update = $conn->prepare("UPDATE bookings SET status = 'checked_out', checked_out_at = NOW() WHERE id = ?");
            $update->bind_param('i', $booking['id']);
            
            if ($update->execute()) {
                // Update room status to available (assuming cleaning is done)
                if ($booking['room_id']) {
                    $room_update = $conn->prepare("UPDATE items SET room_status = 'available' WHERE id = ?");
                    $room_update->bind_param('i', $booking['room_id']);
                    $room_update->execute();
                    $room_update->close();
                }
                
                $checkout_count++;
                logCron("✓ Auto checked-out: Receipt #{$booking['receipt_no']} - {$booking['room_name']}");
            } else {
                logCron("✗ Failed to check-out: Receipt #{$booking['receipt_no']}");
            }
            
            $update->close();
        }
        
        $stmt->close();
    } else {
        logCron("Skipping auto check-outs (current time: $current_time, check-out starts at 12:00)");
    }
    
    logCron("=== Cron Job Completed ===");
    logCron("Summary: $checkin_count check-ins, $checkout_count check-outs processed");
    
    // Return JSON if called via web (for testing)
    if (php_sapi_name() !== 'cli') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'timestamp' => $current_datetime,
            'checkins' => $checkin_count,
            'checkouts' => $checkout_count
        ]);
    }
    
} catch (Exception $e) {
    logCron("✗ ERROR: " . $e->getMessage());
    
    if (php_sapi_name() !== 'cli') {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
    
    exit(1);
}
