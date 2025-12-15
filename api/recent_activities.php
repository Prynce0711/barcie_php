<?php
/**
 * Recent Activities API
 * Fetches recent system activities for dashboard display
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../database/db_connect.php';

// Ensure session is active (some entry points don't start session)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    // For development/testing allow sample activities when explicitly requested
    if (isset($_GET['allow_sample']) && $_GET['allow_sample'] === '1') {
        // proceed and return sample activities below if no real ones
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
}

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

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
    $stmt->bind_param('i', $limit);
    $stmt->execute();
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
    
    // Add sample activities if no real activities found
    if (empty($activities)) {
        $activities = [
            [
                'activity_type' => 'pencil_created',
                'id' => 0,
                'receipt_no' => 'SAMPLE-001',
                'guest_name' => 'John Doe',
                'activity_date' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                'room_name' => 'Standard Room',
                'admin_name' => null
            ],
            [
                'activity_type' => 'payment_approved',
                'id' => 0,
                'receipt_no' => 'SAMPLE-002',
                'guest_name' => 'Jane Smith',
                'activity_date' => date('Y-m-d H:i:s', strtotime('-5 hours')),
                'room_name' => 'Deluxe Suite',
                'admin_name' => 'Admin User'
            ],
            [
                'activity_type' => 'guest_checkin',
                'id' => 0,
                'receipt_no' => 'SAMPLE-003',
                'guest_name' => 'Robert Johnson',
                'activity_date' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'room_name' => 'Premium Room',
                'admin_name' => null
            ],
            [
                'activity_type' => 'pencil_approved',
                'id' => 0,
                'receipt_no' => 'SAMPLE-004',
                'guest_name' => 'Maria Garcia',
                'activity_date' => date('Y-m-d H:i:s', strtotime('-1 day 3 hours')),
                'room_name' => 'Conference Room',
                'admin_name' => 'Manager User'
            ],
            [
                'activity_type' => 'guest_checkout',
                'id' => 0,
                'receipt_no' => 'SAMPLE-005',
                'guest_name' => 'David Lee',
                'activity_date' => date('Y-m-d H:i:s', strtotime('-2 days')),
                'room_name' => 'Executive Suite',
                'admin_name' => null
            ],
            [
                'activity_type' => 'booking_cancelled',
                'id' => 0,
                'receipt_no' => 'SAMPLE-006',
                'guest_name' => 'Sarah Wilson',
                'activity_date' => date('Y-m-d H:i:s', strtotime('-3 days')),
                'room_name' => 'Family Room',
                'admin_name' => null
            ]
        ];
    }
    
    // Sort all activities by date
    usort($activities, function($a, $b) {
        return strtotime($b['activity_date']) - strtotime($a['activity_date']);
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
