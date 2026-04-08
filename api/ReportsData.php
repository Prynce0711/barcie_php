<?php
/**
 * Reports Data API - Fixed for BarCIE Database Structure
 * Uses: items.name (room names: PENTHOUSE, SUITE, TRIPLE, TWIN, SINGLE)
 *       bookings.amount (instead of total_price)
 *       bookings.details (JSON with guest info)
 */

// Set timezone first to ensure consistent time display
date_default_timezone_set('Asia/Manila');

require_once '../database/db_connect.php';

header('Content-Type: application/json');

// Enable error logging for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

try {
    if (!isset($conn)) {
        throw new Exception('Database connection not available');
    }
    
    // Test database connection
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }
    
    // Get filter parameters
    $startDate = $_GET['start_date'] ?? date('Y-m-01');
    $endDate = $_GET['end_date'] ?? date('Y-m-d');
    $roomType = $_GET['room_type'] ?? ''; // Now uses room names: PENTHOUSE, SUITE, etc.
    $reportType = $_GET['report_type'] ?? 'overview';
    
    $response = [
        'success' => true,
        'filters' => [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'room_type' => $roomType
        ],
        'data' => []
    ];
    
    switch ($reportType) {
        case 'overview':
        case 'all':
            $response['data'] = getOverviewData($conn, $startDate, $endDate, $roomType);
            break;
            
        case 'booking':
            $response['data'] = getBookingReports($conn, $startDate, $endDate, $roomType);
            break;
            
        case 'occupancy':
            $response['data'] = getOccupancyReports($conn, $startDate, $endDate, $roomType);
            break;
            
        case 'revenue':
            $response['data'] = getRevenueReports($conn, $startDate, $endDate, $roomType);
            break;
            
        case 'guest':
            $response['data'] = getGuestReports($conn, $startDate, $endDate, $roomType);
            break;
            
        case 'room':
            $response['data'] = getRoomReports($conn, $startDate, $endDate, $roomType);
            break;
            
        default:
            throw new Exception('Invalid report type');
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}

function getOverviewData($conn, $startDate, $endDate, $roomType) {
    try {
        $roomTypeFilter = !empty($roomType) ? " AND i.name = ?" : "";
        
        // Total bookings
        $sql = "SELECT COUNT(*) as total FROM bookings b
                LEFT JOIN items i ON b.room_id = i.id
                WHERE b.checkin BETWEEN ? AND ?$roomTypeFilter";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        if (!empty($roomType)) {
            $stmt->bind_param('sss', $startDate, $endDate, $roomType);
        } else {
            $stmt->bind_param('ss', $startDate, $endDate);
        }
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        $totalBookings = $stmt->get_result()->fetch_assoc()['total'];
    
    // Total revenue (including approved bookings with verified payments)
    $sql = "SELECT COALESCE(SUM(b.amount), 0) as total FROM bookings b
            LEFT JOIN items i ON b.room_id = i.id
            WHERE b.checkin BETWEEN ? AND ?
            AND b.status IN ('approved', 'confirmed', 'checked_in', 'checked_out')
            AND b.payment_status = 'verified'$roomTypeFilter";
    $stmt = $conn->prepare($sql);
    if (!empty($roomType)) {
        $stmt->bind_param('sss', $startDate, $endDate, $roomType);
    } else {
        $stmt->bind_param('ss', $startDate, $endDate);
    }
    $stmt->execute();
    $totalRevenue = $stmt->get_result()->fetch_assoc()['total'];
    
    // Total guests (count bookings as proxy)
    $sql = "SELECT COUNT(*) as total FROM bookings b
            LEFT JOIN items i ON b.room_id = i.id
            WHERE b.checkin BETWEEN ? AND ?$roomTypeFilter";
    $stmt = $conn->prepare($sql);
    if (!empty($roomType)) {
        $stmt->bind_param('sss', $startDate, $endDate, $roomType);
    } else {
        $stmt->bind_param('ss', $startDate, $endDate);
    }
    $stmt->execute();
    $totalGuests = $stmt->get_result()->fetch_assoc()['total'];
    
    // Occupancy rate
    $sql = "SELECT COUNT(DISTINCT DATE(b.checkin)) as booked_days FROM bookings b
            LEFT JOIN items i ON b.room_id = i.id
            WHERE b.checkin BETWEEN ? AND ?
            AND b.status IN ('approved', 'confirmed', 'checked_in', 'checked_out')$roomTypeFilter";
    $stmt = $conn->prepare($sql);
    if (!empty($roomType)) {
        $stmt->bind_param('sss', $startDate, $endDate, $roomType);
    } else {
        $stmt->bind_param('ss', $startDate, $endDate);
    }
    $stmt->execute();
    $bookedDays = $stmt->get_result()->fetch_assoc()['booked_days'];
    
    $daysDiff = max((strtotime($endDate) - strtotime($startDate)) / (60 * 60 * 24), 1);
    $occupancyRate = ($bookedDays / $daysDiff) * 100;
    
    return [
        'summary' => [
            'total_bookings' => (int)$totalBookings,
            'total_revenue' => (float)$totalRevenue,
            'total_guests' => (int)$totalGuests,
            'occupancy_rate' => round($occupancyRate, 2)
        ],
        'booking_reports' => getBookingReports($conn, $startDate, $endDate, $roomType),
        'occupancy_reports' => getOccupancyReports($conn, $startDate, $endDate, $roomType),
        'revenue_reports' => getRevenueReports($conn, $startDate, $endDate, $roomType),
        'guest_reports' => getGuestReports($conn, $startDate, $endDate, $roomType),
        'room_reports' => getRoomReports($conn, $startDate, $endDate, $roomType)
    ];
    
    } catch (Exception $e) {
        error_log("getOverviewData error: " . $e->getMessage());
        return [
            'summary' => [
                'total_bookings' => 0,
                'total_revenue' => 0,
                'total_guests' => 0,
                'occupancy_rate' => 0
            ],
            'error' => $e->getMessage()
        ];
    }
}

function getBookingReports($conn, $startDate, $endDate, $roomType) {
    $roomTypeFilter = !empty($roomType) ? " AND i.name = ?" : "";
    
    // Daily bookings
    $sql = "SELECT DATE(b.checkin) as date, COUNT(*) as count FROM bookings b
            LEFT JOIN items i ON b.room_id = i.id
            WHERE b.checkin BETWEEN ? AND ?$roomTypeFilter
            GROUP BY DATE(b.checkin) ORDER BY date DESC LIMIT 10";
    $stmt = $conn->prepare($sql);
    if (!empty($roomType)) {
        $stmt->bind_param('sss', $startDate, $endDate, $roomType);
    } else {
        $stmt->bind_param('ss', $startDate, $endDate);
    }
    $stmt->execute();
    $dailyBookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Monthly bookings
    $sql = "SELECT DATE_FORMAT(b.checkin, '%Y-%m') as month, COUNT(*) as count FROM bookings b
            LEFT JOIN items i ON b.room_id = i.id
            WHERE b.checkin BETWEEN ? AND ?$roomTypeFilter
            GROUP BY DATE_FORMAT(b.checkin, '%Y-%m') ORDER BY month DESC";
    $stmt = $conn->prepare($sql);
    if (!empty($roomType)) {
        $stmt->bind_param('sss', $startDate, $endDate, $roomType);
    } else {
        $stmt->bind_param('ss', $startDate, $endDate);
    }
    $stmt->execute();
    $monthlyBookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Booking status breakdown
    $sql = "SELECT b.status, COUNT(*) as count, COALESCE(SUM(b.amount), 0) as revenue FROM bookings b
            LEFT JOIN items i ON b.room_id = i.id
            WHERE b.checkin BETWEEN ? AND ?$roomTypeFilter
            GROUP BY b.status";
    $stmt = $conn->prepare($sql);
    if (!empty($roomType)) {
        $stmt->bind_param('sss', $startDate, $endDate, $roomType);
    } else {
        $stmt->bind_param('ss', $startDate, $endDate);
    }
    $stmt->execute();
    $statusBreakdown = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Booking sources
    $bookingSources = [
        ['source' => 'Online', 'count' => count($dailyBookings)]
    ];
    
    // Booking trends
    $sql = "SELECT DATE(b.checkin) as date, COUNT(*) as count FROM bookings b
            LEFT JOIN items i ON b.room_id = i.id
            WHERE b.checkin BETWEEN DATE_SUB(?, INTERVAL 30 DAY) AND ?$roomTypeFilter
            GROUP BY DATE(b.checkin) ORDER BY date ASC";
    $stmt = $conn->prepare($sql);
    if (!empty($roomType)) {
        $stmt->bind_param('sss', $endDate, $endDate, $roomType);
    } else {
        $stmt->bind_param('ss', $endDate, $endDate);
    }
    $stmt->execute();
    $bookingTrends = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    return [
        'daily_bookings' => $dailyBookings,
        'monthly_bookings' => $monthlyBookings,
        'status_breakdown' => $statusBreakdown,
        'booking_sources' => $bookingSources,
        'booking_trends' => $bookingTrends
    ];
}

function getOccupancyReports($conn, $startDate, $endDate, $roomType) {
    $roomTypeFilter = !empty($roomType) ? " AND i.name = ?" : "";
    
    // Daily occupancy
    $sql = "SELECT DATE(b.checkin) as date, COUNT(DISTINCT b.room_id) as occupied_items,
            (SELECT COUNT(*) FROM items WHERE item_type IN ('room', 'facility')) as total_items
            FROM bookings b
            LEFT JOIN items i ON b.room_id = i.id
            WHERE b.checkin BETWEEN ? AND ?
            AND b.status IN ('approved', 'confirmed', 'checked_in', 'checked_out')$roomTypeFilter
            GROUP BY DATE(b.checkin) ORDER BY date ASC";
    $stmt = $conn->prepare($sql);
    if (!empty($roomType)) {
        $stmt->bind_param('sss', $startDate, $endDate, $roomType);
    } else {
        $stmt->bind_param('ss', $startDate, $endDate);
    }
    $stmt->execute();
    $dailyOccupancy = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Calculate occupancy rates
    foreach ($dailyOccupancy as &$day) {
        $day['occupancy_rate'] = $day['total_items'] > 0 
            ? round(($day['occupied_items'] / $day['total_items']) * 100, 2) 
            : 0;
    }
    
    $avgOccupancyRate = !empty($dailyOccupancy)
        ? round(array_sum(array_column($dailyOccupancy, 'occupancy_rate')) / count($dailyOccupancy), 2)
        : 0;
    
    $peakDay = !empty($dailyOccupancy) ? $dailyOccupancy[0] : null;
    if (!empty($dailyOccupancy)) {
        foreach ($dailyOccupancy as $day) {
            if ($day['occupancy_rate'] > $peakDay['occupancy_rate']) {
                $peakDay = $day;
            }
        }
    }
    
    // Current room/facility status
    $sql = "SELECT 
            CASE 
                WHEN b.id IS NOT NULL AND b.status IN ('approved', 'confirmed', 'checked_in') THEN 'Occupied'
                ELSE 'Available'
            END as status,
            COUNT(*) as count
            FROM items i
            LEFT JOIN bookings b ON i.id = b.room_id 
                AND CURDATE() BETWEEN b.checkin AND b.checkout
                AND b.status IN ('approved', 'confirmed', 'checked_in')
            WHERE i.item_type IN ('room', 'facility')" . ($roomType ? " AND i.name = ?" : "") . "
            GROUP BY status";
    $stmt = $conn->prepare($sql);
    if (!empty($roomType)) {
        $stmt->bind_param('s', $roomType);
    }
    $stmt->execute();
    $roomStatus = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    $currentlyOccupied = 0;
    foreach ($roomStatus as $status) {
        if ($status['status'] === 'Occupied') {
            $currentlyOccupied = $status['count'];
            break;
        }
    }
    
    return [
        'daily_occupancy' => $dailyOccupancy,
        'avg_occupancy_rate' => $avgOccupancyRate,
        'peak_occupancy' => $peakDay,
        'item_status' => $roomStatus,
        'currently_occupied' => $currentlyOccupied
    ];
}

function getRevenueReports($conn, $startDate, $endDate, $roomType) {
    $roomTypeFilter = !empty($roomType) ? " AND i.name = ?" : "";
    
    // Total revenue (only verified payments)
    $sql = "SELECT COALESCE(SUM(b.amount), 0) as total FROM bookings b
            LEFT JOIN items i ON b.room_id = i.id
            WHERE b.checkin BETWEEN ? AND ?
            AND b.status IN ('approved', 'confirmed', 'checked_in', 'checked_out')
            AND b.payment_status = 'verified'$roomTypeFilter";
    $stmt = $conn->prepare($sql);
    if (!empty($roomType)) {
        $stmt->bind_param('sss', $startDate, $endDate, $roomType);
    } else {
        $stmt->bind_param('ss', $startDate, $endDate);
    }
    $stmt->execute();
    $totalRevenue = $stmt->get_result()->fetch_assoc()['total'];
    
    // Daily revenue trend (only verified payments)
    $sql = "SELECT DATE(b.checkin) as date, COALESCE(SUM(b.amount), 0) as revenue FROM bookings b
            LEFT JOIN items i ON b.room_id = i.id
            WHERE b.checkin BETWEEN ? AND ?
            AND b.status IN ('approved', 'confirmed', 'checked_in', 'checked_out')
            AND b.payment_status = 'verified'$roomTypeFilter
            GROUP BY DATE(b.checkin) ORDER BY date ASC";
    $stmt = $conn->prepare($sql);
    if (!empty($roomType)) {
        $stmt->bind_param('sss', $startDate, $endDate, $roomType);
    } else {
        $stmt->bind_param('ss', $startDate, $endDate);
    }
    $stmt->execute();
    $dailyRevenue = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Monthly revenue breakdown (only verified payments)
    $sql = "SELECT DATE_FORMAT(b.checkin, '%Y-%m') as month,
            DATE_FORMAT(b.checkin, '%M %Y') as month_name,
            COALESCE(SUM(b.amount), 0) as revenue, COUNT(*) as bookings
            FROM bookings b
            LEFT JOIN items i ON b.room_id = i.id
            WHERE b.checkin BETWEEN ? AND ?
            AND b.status IN ('approved', 'confirmed', 'checked_in', 'checked_out')
            AND b.payment_status = 'verified'$roomTypeFilter
            GROUP BY DATE_FORMAT(b.checkin, '%Y-%m') ORDER BY month DESC";
    $stmt = $conn->prepare($sql);
    if (!empty($roomType)) {
        $stmt->bind_param('sss', $startDate, $endDate, $roomType);
    } else {
        $stmt->bind_param('ss', $startDate, $endDate);
    }
    $stmt->execute();
    $monthlyRevenue = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Revenue by room type (using room names)
    $sql = "SELECT i.name as type, COALESCE(SUM(b.amount), 0) as revenue, COUNT(*) as bookings
            FROM bookings b
            LEFT JOIN items i ON b.room_id = i.id
            WHERE b.checkin BETWEEN ? AND ?
            AND b.status IN ('confirmed', 'approved', 'checked_in', 'checked_out')$roomTypeFilter
            GROUP BY i.name ORDER BY revenue DESC";
    $stmt = $conn->prepare($sql);
    if (!empty($roomType)) {
        $stmt->bind_param('sss', $startDate, $endDate, $roomType);
    } else {
        $stmt->bind_param('ss', $startDate, $endDate);
    }
    $stmt->execute();
    $revenueByRoomType = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    $daysDiff = max((strtotime($endDate) - strtotime($startDate)) / (60 * 60 * 24), 1);
    $monthsDiff = max($daysDiff / 30, 1);
    
    $dailyAvg = $totalRevenue / $daysDiff;
    $monthlyAvg = $totalRevenue / $monthsDiff;
    
    return [
        'total_revenue' => (float)$totalRevenue,
        'daily_average' => round($dailyAvg, 2),
        'monthly_average' => round($monthlyAvg, 2),
        'daily_revenue' => $dailyRevenue,
        'monthly_revenue' => $monthlyRevenue,
        'revenue_by_room_type' => $revenueByRoomType
    ];
}

function getGuestReports($conn, $startDate, $endDate, $roomType) {
    $roomTypeFilter = !empty($roomType) ? " AND i.name = ?" : "";
    
    $sql = "SELECT COUNT(*) as total FROM bookings b
            LEFT JOIN items i ON b.room_id = i.id
            WHERE b.checkin BETWEEN ? AND ?$roomTypeFilter";
    $stmt = $conn->prepare($sql);
    if (!empty($roomType)) {
        $stmt->bind_param('sss', $startDate, $endDate, $roomType);
    } else {
        $stmt->bind_param('ss', $startDate, $endDate);
    }
    $stmt->execute();
    $totalGuests = $stmt->get_result()->fetch_assoc()['total'];
    
    // Average stay length
    $sql = "SELECT AVG(DATEDIFF(b.checkout, b.checkin)) as avg_stay FROM bookings b
            LEFT JOIN items i ON b.room_id = i.id
            WHERE b.checkin BETWEEN ? AND ?
            AND b.status IN ('confirmed', 'approved', 'checked_in', 'checked_out')$roomTypeFilter";
    $stmt = $conn->prepare($sql);
    if (!empty($roomType)) {
        $stmt->bind_param('sss', $startDate, $endDate, $roomType);
    } else {
        $stmt->bind_param('ss', $startDate, $endDate);
    }
    $stmt->execute();
    $avgStay = $stmt->get_result()->fetch_assoc()['avg_stay'] ?? 0;
    
    // Guest arrival trends
    $sql = "SELECT DATE(b.checkin) as date, COUNT(*) as guests
            FROM bookings b
            LEFT JOIN items i ON b.room_id = i.id
            WHERE b.checkin BETWEEN ? AND ?$roomTypeFilter
            GROUP BY DATE(b.checkin) ORDER BY date ASC";
    $stmt = $conn->prepare($sql);
    if (!empty($roomType)) {
        $stmt->bind_param('sss', $startDate, $endDate, $roomType);
    } else {
        $stmt->bind_param('ss', $startDate, $endDate);
    }
    $stmt->execute();
    $guestTrends = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    return [
        'total_guests' => (int)$totalGuests,
        'avg_stay_length' => round((float)$avgStay, 1),
        'return_guests' => 0,
        'guest_trends' => $guestTrends,
        'top_guests' => []
    ];
}

function getRoomReports($conn, $startDate, $endDate, $roomType) {
    $roomTypeFilter = !empty($roomType) ? " AND i.name = ?" : "";
    
    // Most booked rooms
    $sql = "SELECT i.name as room_number, i.name as type, COUNT(*) as bookings,
            COALESCE(SUM(b.amount), 0) as revenue
            FROM bookings b
            LEFT JOIN items i ON b.room_id = i.id
            WHERE b.checkin BETWEEN ? AND ?
            AND b.status IN ('confirmed', 'approved', 'checked_in', 'checked_out')$roomTypeFilter
            GROUP BY i.id, i.name
            ORDER BY bookings DESC LIMIT 10";
    $stmt = $conn->prepare($sql);
    if (!empty($roomType)) {
        $stmt->bind_param('sss', $startDate, $endDate, $roomType);
    } else {
        $stmt->bind_param('ss', $startDate, $endDate);
    }
    $stmt->execute();
    $mostBooked = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Least booked rooms/facilities
    $sql = "SELECT i.name as room_number, i.item_type as type, COALESCE(COUNT(b.id), 0) as bookings,
            COALESCE(SUM(b.amount), 0) as revenue
            FROM items i
            LEFT JOIN bookings b ON i.id = b.room_id 
                AND b.checkin BETWEEN ? AND ?
                AND b.status IN ('approved', 'confirmed', 'checked_in', 'checked_out')
            WHERE i.item_type IN ('room', 'facility')" . ($roomType ? " AND i.name = ?" : "") . "
            GROUP BY i.id, i.name, i.item_type
            ORDER BY bookings ASC LIMIT 10";
    $stmt = $conn->prepare($sql);
    if (!empty($roomType)) {
        $stmt->bind_param('sss', $startDate, $endDate, $roomType);
    } else {
        $stmt->bind_param('ss', $startDate, $endDate);
    }
    $stmt->execute();
    $leastBooked = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Room/Facility type distribution
    $sql = "SELECT i.item_type as type,
            COUNT(*) as total_items,
            SUM(CASE WHEN b.id IS NULL OR b.status NOT IN ('approved', 'confirmed', 'checked_in', '0') THEN 1 ELSE 0 END) as available,
            SUM(CASE WHEN b.id IS NOT NULL AND b.status IN ('approved', 'confirmed', 'checked_in') THEN 1 ELSE 0 END) as occupied
            FROM items i
            LEFT JOIN bookings b ON i.id = b.room_id 
                AND CURDATE() BETWEEN b.checkin AND b.checkout
                AND b.status IN ('approved', 'confirmed', 'checked_in')
            WHERE i.item_type IN ('room', 'facility')" . ($roomType ? " AND i.name = ?" : "") . "
            GROUP BY i.item_type";
    $stmt = $conn->prepare($sql);
    if (!empty($roomType)) {
        $stmt->bind_param('s', $roomType);
    }
    $stmt->execute();
    $roomTypeDistribution = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Room/Facility performance
    $sql = "SELECT i.name as room_number, i.item_type, COUNT(b.id) as bookings
            FROM items i
            LEFT JOIN bookings b ON i.id = b.room_id 
                AND b.checkin BETWEEN ? AND ?
                AND b.status IN ('approved', 'confirmed', 'checked_in', 'checked_out')
            WHERE i.item_type IN ('room', 'facility')" . ($roomType ? " AND i.name = ?" : "") . "
            GROUP BY i.id, i.name, i.item_type
            ORDER BY i.name";
    $stmt = $conn->prepare($sql);
    if (!empty($roomType)) {
        $stmt->bind_param('sss', $startDate, $endDate, $roomType);
    } else {
        $stmt->bind_param('ss', $startDate, $endDate);
    }
    $stmt->execute();
    $roomPerformance = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    return [
        'most_booked' => $mostBooked,
        'least_booked' => $leastBooked,
        'room_type_distribution' => $roomTypeDistribution,
        'room_performance' => $roomPerformance
    ];
}
