<?php
/**
 * Recent Activities API
 * Fetches recent system activities for dashboard display
 */

// Set timezone first to ensure consistent time calculations
date_default_timezone_set('Asia/Manila');

header('Content-Type: application/json');
require_once __DIR__ . '/../database/db_connect.php';
require_once __DIR__ . '/../components/Login/remember_me.php';

// Ensure session is active (some entry points don't start session)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id']) && isset($conn) && $conn instanceof mysqli) {
    remember_me_restore_session($conn);
}

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized', 'debug' => 'No admin session found']);
    exit;
}

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Check database connection
if (!isset($conn) || $conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Database connection failed',
        'message' => isset($conn) ? $conn->connect_error : 'Connection object not found'
    ]);
    exit;
}

try {
    $activities = [];
    
    // Get recent pencil booking creations
    $pencil_query = "SELECT 
        'pencil_created' as activity_type,
        pb.id,
        pb.receipt_no,
        pb.guest_name,
        pb.created_at as activity_date,
        i.name as room_name,
        NULL as admin_name
    FROM pencil_bookings pb
    LEFT JOIN items i ON pb.room_id = i.id
    WHERE pb.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ORDER BY pb.created_at DESC
    LIMIT ?";
    
    $stmt = $conn->prepare($pencil_query);
    if (!$stmt) {
        throw new Exception("Failed to prepare pencil_query: " . $conn->error);
    }
    $stmt->bind_param('i', $limit);
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute pencil_query: " . $stmt->error);
    }
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }
    $stmt->close();
    
    // Get pencil booking approvals/status changes
    $pencil_status_query = "SELECT 
        CASE 
            WHEN pb.status = 'approved' THEN 'pencil_approved'
            WHEN pb.status = 'converted' THEN 'pencil_converted'
            WHEN pb.status = 'cancelled' THEN 'pencil_cancelled'
            ELSE 'pencil_updated'
        END as activity_type,
        pb.id,
        pb.receipt_no,
        pb.guest_name,
        pb.updated_at as activity_date,
        i.name as room_name,
        a.full_name as admin_name,
        pb.status
    FROM pencil_bookings pb
    LEFT JOIN items i ON pb.room_id = i.id
    LEFT JOIN admins a ON pb.approved_by = a.id
    WHERE pb.status IN ('approved', 'converted', 'cancelled')
    AND pb.updated_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ORDER BY pb.updated_at DESC
    LIMIT ?";
    
    $stmt = $conn->prepare($pencil_status_query);
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }
    $stmt->close();
    
    // Get booking reservations (payment submitted)
    $booking_reserved_query = "SELECT 
        'booking_reserved' as activity_type,
        b.id,
        b.receipt_no,
        SUBSTRING_INDEX(SUBSTRING_INDEX(b.details, 'Guest: ', -1), ' |', 1) as guest_name,
        b.payment_date as activity_date,
        i.name as room_name,
        NULL as admin_name
    FROM bookings b
    LEFT JOIN items i ON b.room_id = i.id
    WHERE b.payment_status = 'pending'
    AND b.payment_date IS NOT NULL
    AND b.payment_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ORDER BY b.payment_date DESC
    LIMIT ?";
    
    $stmt = $conn->prepare($booking_reserved_query);
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }
    $stmt->close();
    
    // Get payment approvals
    $payment_approved_query = "SELECT 
        'payment_approved' as activity_type,
        b.id,
        b.receipt_no,
        SUBSTRING_INDEX(SUBSTRING_INDEX(b.details, 'Guest: ', -1), ' |', 1) as guest_name,
        b.payment_verified_at as activity_date,
        i.name as room_name,
        a.full_name as admin_name
    FROM bookings b
    LEFT JOIN items i ON b.room_id = i.id
    LEFT JOIN admins a ON b.payment_verified_by = a.id
    WHERE b.payment_status = 'verified'
    AND b.payment_verified_at IS NOT NULL
    AND b.payment_verified_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ORDER BY b.payment_verified_at DESC
    LIMIT ?";
    
    $stmt = $conn->prepare($payment_approved_query);
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }
    $stmt->close();

    // Get booking approvals (admin approved)
    $booking_approved_query = "SELECT
        'booking_approved' as activity_type,
        b.id,
        b.receipt_no,
        SUBSTRING_INDEX(SUBSTRING_INDEX(b.details, 'Guest: ', -1), ' |', 1) as guest_name,
        b.approved_at as activity_date,
        i.name as room_name,
        a.full_name as admin_name
    FROM bookings b
    LEFT JOIN items i ON b.room_id = i.id
    LEFT JOIN admins a ON b.approved_by = a.id
    WHERE b.approved_at IS NOT NULL
    AND b.approved_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ORDER BY b.approved_at DESC
    LIMIT ?";

    $stmt = $conn->prepare($booking_approved_query);
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }
    $stmt->close();
    
    // Get check-ins
    $checkin_query = "SELECT 
        'guest_checkin' as activity_type,
        b.id,
        b.receipt_no,
        SUBSTRING_INDEX(SUBSTRING_INDEX(b.details, 'Guest: ', -1), ' |', 1) as guest_name,
        b.checkin as activity_date,
        i.name as room_name,
        NULL as admin_name
    FROM bookings b
    LEFT JOIN items i ON b.room_id = i.id
    WHERE b.status = 'checked_in'
    AND b.checkin >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ORDER BY b.checkin DESC
    LIMIT ?";
    
    $stmt = $conn->prepare($checkin_query);
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }
    $stmt->close();
    
    // Get check-outs
    $checkout_query = "SELECT 
        'guest_checkout' as activity_type,
        b.id,
        b.receipt_no,
        SUBSTRING_INDEX(SUBSTRING_INDEX(b.details, 'Guest: ', -1), ' |', 1) as guest_name,
        b.checked_out_at as activity_date,
        i.name as room_name,
        NULL as admin_name
    FROM bookings b
    LEFT JOIN items i ON b.room_id = i.id
    WHERE b.status = 'checked_out'
    AND b.checked_out_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ORDER BY b.checked_out_at DESC
    LIMIT ?";
    
    $stmt = $conn->prepare($checkout_query);
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }
    $stmt->close();
    
    // Get cancelled bookings
    $cancelled_query = "SELECT 
        'booking_cancelled' as activity_type,
        b.id,
        b.receipt_no,
        SUBSTRING_INDEX(SUBSTRING_INDEX(b.details, 'Guest: ', -1), ' |', 1) as guest_name,
        b.updated_at as activity_date,
        i.name as room_name,
        NULL as admin_name
    FROM bookings b
    LEFT JOIN items i ON b.room_id = i.id
    WHERE b.status = 'cancelled'
    AND b.updated_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ORDER BY b.updated_at DESC
    LIMIT ?";
    
    $stmt = $conn->prepare($cancelled_query);
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }
    $stmt->close();
    
    // Get feedback submissions
    $feedback_query = "SELECT 
        'feedback_submitted' as activity_type,
        f.id,
        NULL as receipt_no,
        COALESCE(f.feedback_name, f.google_name, 'Anonymous') as guest_name,
        f.created_at as activity_date,
        NULL as room_name,
        NULL as admin_name,
        f.rating as rating
    FROM feedback f
    WHERE f.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ORDER BY f.created_at DESC
    LIMIT ?";
    
    $stmt = $conn->prepare($feedback_query);
    if (!$stmt) {
        throw new Exception("Failed to prepare feedback_query: " . $conn->error);
    }
    $stmt->bind_param('i', $limit);
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute feedback_query: " . $stmt->error);
    }
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }
    $stmt->close();
    
    // Sort all activities by date (handle null/invalid dates)
    usort($activities, function($a, $b) {
        $timeA = strtotime($a['activity_date'] ?? '1970-01-01');
        $timeB = strtotime($b['activity_date'] ?? '1970-01-01');
        if ($timeA === false) $timeA = 0;
        if ($timeB === false) $timeB = 0;
        return $timeB - $timeA;
    });
    
    // Limit to requested number
    $activities = array_slice($activities, 0, $limit);
    
    echo json_encode([
        'success' => true,
        'activities' => $activities
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to fetch activities',
        'message' => $e->getMessage()
    ]);
}
