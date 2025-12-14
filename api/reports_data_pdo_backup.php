<?php
/**
 * Reports Data API
 * Provides comprehensive reporting data for bookings, revenue, occupancy, and guests
 */

require_once '../database/config.php';
require_once '../database/db_connect.php';

header('Content-Type: application/json');

// Enable error reporting for debugging
if (defined('DEBUG_MODE') && DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

try {
    // Use the existing MySQLi connection
    if (!isset($conn)) {
        throw new Exception('Database connection not available');
    }
    
    // Get filter parameters
    $startDate = $_GET['start_date'] ?? date('Y-m-01');
    $endDate = $_GET['end_date'] ?? date('Y-m-d');
    $roomType = $_GET['room_type'] ?? '';
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
    
    // Build room type filter
    $roomTypeFilter = '';
    $params = [':start_date' => $startDate, ':end_date' => $endDate];
    
    if (!empty($roomType)) {
        $roomTypeFilter = " AND r.type = :room_type";
        $params[':room_type'] = $roomType;
    }
    
    switch ($reportType) {
        case 'overview':
        case 'all':
            $response['data'] = getOverviewData($pdo, $startDate, $endDate, $roomType, $params);
            break;
            
        case 'booking':
            $response['data'] = getBookingReports($pdo, $startDate, $endDate, $roomType, $params);
            break;
            
        case 'occupancy':
            $response['data'] = getOccupancyReports($pdo, $startDate, $endDate, $roomType, $params);
            break;
            
        case 'revenue':
            $response['data'] = getRevenueReports($pdo, $startDate, $endDate, $roomType, $params);
            break;
            
        case 'guest':
            $response['data'] = getGuestReports($pdo, $startDate, $endDate, $roomType, $params);
            break;
            
        case 'room':
            $response['data'] = getRoomReports($pdo, $startDate, $endDate, $roomType, $params);
            break;
            
        default:
            throw new Exception('Invalid report type');
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

/**
 * Get overview data (summary of all reports)
 */
function getOverviewData($pdo, $startDate, $endDate, $roomType, $params) {
    $roomTypeFilter = !empty($roomType) ? " AND r.type = :room_type" : "";
    
    // Total bookings
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total
        FROM bookings b
        LEFT JOIN rooms r ON b.room_id = r.id
        WHERE b.check_in_date BETWEEN :start_date AND :end_date
        $roomTypeFilter
    ");
    $stmt->execute($params);
    $totalBookings = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total revenue
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(b.total_price), 0) as total
        FROM bookings b
        LEFT JOIN rooms r ON b.room_id = r.id
        WHERE b.check_in_date BETWEEN :start_date AND :end_date
        AND b.status IN ('confirmed', 'checked_in', 'checked_out')
        $roomTypeFilter
    ");
    $stmt->execute($params);
    $totalRevenue = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total guests
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT b.user_email) as total
        FROM bookings b
        LEFT JOIN rooms r ON b.room_id = r.id
        WHERE b.check_in_date BETWEEN :start_date AND :end_date
        $roomTypeFilter
    ");
    $stmt->execute($params);
    $totalGuests = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Occupancy rate
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(DISTINCT DATE(b.check_in_date)) as booked_days,
            DATEDIFF(:end_date, :start_date) + 1 as total_days,
            (SELECT COUNT(*) FROM rooms WHERE 1=1 " . ($roomType ? "AND type = :room_type" : "") . ") as total_rooms
        FROM bookings b
        LEFT JOIN rooms r ON b.room_id = r.id
        WHERE b.check_in_date BETWEEN :start_date AND :end_date
        AND b.status IN ('confirmed', 'checked_in', 'checked_out')
        $roomTypeFilter
    ");
    $stmt->execute($params);
    $occupancyData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $totalRoomDays = $occupancyData['total_days'] * $occupancyData['total_rooms'];
    $occupancyRate = $totalRoomDays > 0 ? ($occupancyData['booked_days'] / $totalRoomDays) * 100 : 0;
    
    return [
        'summary' => [
            'total_bookings' => (int)$totalBookings,
            'total_revenue' => (float)$totalRevenue,
            'total_guests' => (int)$totalGuests,
            'occupancy_rate' => round($occupancyRate, 2)
        ],
        'booking_reports' => getBookingReports($pdo, $startDate, $endDate, $roomType, $params),
        'occupancy_reports' => getOccupancyReports($pdo, $startDate, $endDate, $roomType, $params),
        'revenue_reports' => getRevenueReports($pdo, $startDate, $endDate, $roomType, $params),
        'guest_reports' => getGuestReports($pdo, $startDate, $endDate, $roomType, $params),
        'room_reports' => getRoomReports($pdo, $startDate, $endDate, $roomType, $params)
    ];
}

/**
 * Get booking reports
 */
function getBookingReports($pdo, $startDate, $endDate, $roomType, $params) {
    $roomTypeFilter = !empty($roomType) ? " AND r.type = :room_type" : "";
    
    // Daily bookings
    $stmt = $pdo->prepare("
        SELECT 
            DATE(b.check_in_date) as date,
            COUNT(*) as count
        FROM bookings b
        LEFT JOIN rooms r ON b.room_id = r.id
        WHERE b.check_in_date BETWEEN :start_date AND :end_date
        $roomTypeFilter
        GROUP BY DATE(b.check_in_date)
        ORDER BY date DESC
        LIMIT 10
    ");
    $stmt->execute($params);
    $dailyBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Monthly bookings
    $stmt = $pdo->prepare("
        SELECT 
            DATE_FORMAT(b.check_in_date, '%Y-%m') as month,
            COUNT(*) as count
        FROM bookings b
        LEFT JOIN rooms r ON b.room_id = r.id
        WHERE b.check_in_date BETWEEN :start_date AND :end_date
        $roomTypeFilter
        GROUP BY DATE_FORMAT(b.check_in_date, '%Y-%m')
        ORDER BY month DESC
    ");
    $stmt->execute($params);
    $monthlyBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Booking status breakdown
    $stmt = $pdo->prepare("
        SELECT 
            b.status,
            COUNT(*) as count,
            COALESCE(SUM(b.total_price), 0) as revenue
        FROM bookings b
        LEFT JOIN rooms r ON b.room_id = r.id
        WHERE b.check_in_date BETWEEN :start_date AND :end_date
        $roomTypeFilter
        GROUP BY b.status
    ");
    $stmt->execute($params);
    $statusBreakdown = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Booking sources (assuming booking_source column exists, otherwise use placeholder)
    $stmt = $pdo->prepare("
        SELECT 
            COALESCE(b.booking_source, 'Online') as source,
            COUNT(*) as count
        FROM bookings b
        LEFT JOIN rooms r ON b.room_id = r.id
        WHERE b.check_in_date BETWEEN :start_date AND :end_date
        $roomTypeFilter
        GROUP BY source
    ");
    $stmt->execute($params);
    $bookingSources = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Booking trends (last 30 days)
    $stmt = $pdo->prepare("
        SELECT 
            DATE(b.check_in_date) as date,
            COUNT(*) as count
        FROM bookings b
        LEFT JOIN rooms r ON b.room_id = r.id
        WHERE b.check_in_date BETWEEN DATE_SUB(:end_date, INTERVAL 30 DAY) AND :end_date
        $roomTypeFilter
        GROUP BY DATE(b.check_in_date)
        ORDER BY date ASC
    ");
    $stmt->execute([':end_date' => $endDate] + ($roomType ? [':room_type' => $roomType] : []));
    $bookingTrends = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return [
        'daily_bookings' => $dailyBookings,
        'monthly_bookings' => $monthlyBookings,
        'status_breakdown' => $statusBreakdown,
        'booking_sources' => $bookingSources,
        'booking_trends' => $bookingTrends
    ];
}

/**
 * Get occupancy reports
 */
function getOccupancyReports($pdo, $startDate, $endDate, $roomType, $params) {
    $roomTypeFilter = !empty($roomType) ? " AND r.type = :room_type" : "";
    
    // Daily occupancy
    $stmt = $pdo->prepare("
        SELECT 
            DATE(b.check_in_date) as date,
            COUNT(DISTINCT b.room_id) as occupied_rooms,
            (SELECT COUNT(*) FROM rooms WHERE 1=1 " . ($roomType ? "AND type = :room_type" : "") . ") as total_rooms
        FROM bookings b
        LEFT JOIN rooms r ON b.room_id = r.id
        WHERE b.check_in_date BETWEEN :start_date AND :end_date
        AND b.status IN ('confirmed', 'checked_in', 'checked_out')
        $roomTypeFilter
        GROUP BY DATE(b.check_in_date)
        ORDER BY date ASC
    ");
    $stmt->execute($params);
    $dailyOccupancy = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate occupancy rates
    foreach ($dailyOccupancy as &$day) {
        $day['occupancy_rate'] = $day['total_rooms'] > 0 
            ? round(($day['occupied_rooms'] / $day['total_rooms']) * 100, 2) 
            : 0;
    }
    
    // Peak occupancy
    $peakDay = !empty($dailyOccupancy) 
        ? max($dailyOccupancy, function($a, $b) { return $a['occupancy_rate'] <=> $b['occupancy_rate']; })
        : null;
    
    // Average occupancy rate
    $avgOccupancyRate = !empty($dailyOccupancy)
        ? round(array_sum(array_column($dailyOccupancy, 'occupancy_rate')) / count($dailyOccupancy), 2)
        : 0;
    
    // Current room status
    $stmt = $pdo->prepare("
        SELECT 
            CASE 
                WHEN b.id IS NOT NULL AND b.status IN ('confirmed', 'checked_in') THEN 'Occupied'
                WHEN r.status = 'maintenance' THEN 'Maintenance'
                ELSE 'Available'
            END as status,
            COUNT(*) as count
        FROM rooms r
        LEFT JOIN bookings b ON r.id = b.room_id 
            AND CURDATE() BETWEEN b.check_in_date AND b.check_out_date
            AND b.status IN ('confirmed', 'checked_in')
        WHERE 1=1 " . ($roomType ? "AND r.type = :room_type" : "") . "
        GROUP BY status
    ");
    $stmt->execute($roomType ? [':room_type' => $roomType] : []);
    $roomStatus = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Currently occupied count
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
        'room_status' => $roomStatus,
        'currently_occupied' => $currentlyOccupied
    ];
}

/**
 * Get revenue reports
 */
function getRevenueReports($pdo, $startDate, $endDate, $roomType, $params) {
    $roomTypeFilter = !empty($roomType) ? " AND r.type = :room_type" : "";
    
    // Total revenue
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(b.total_price), 0) as total
        FROM bookings b
        LEFT JOIN rooms r ON b.room_id = r.id
        WHERE b.check_in_date BETWEEN :start_date AND :end_date
        AND b.status IN ('confirmed', 'checked_in', 'checked_out')
        $roomTypeFilter
    ");
    $stmt->execute($params);
    $totalRevenue = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Daily revenue trend
    $stmt = $pdo->prepare("
        SELECT 
            DATE(b.check_in_date) as date,
            COALESCE(SUM(b.total_price), 0) as revenue
        FROM bookings b
        LEFT JOIN rooms r ON b.room_id = r.id
        WHERE b.check_in_date BETWEEN :start_date AND :end_date
        AND b.status IN ('confirmed', 'checked_in', 'checked_out')
        $roomTypeFilter
        GROUP BY DATE(b.check_in_date)
        ORDER BY date ASC
    ");
    $stmt->execute($params);
    $dailyRevenue = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Monthly revenue breakdown
    $stmt = $pdo->prepare("
        SELECT 
            DATE_FORMAT(b.check_in_date, '%Y-%m') as month,
            DATE_FORMAT(b.check_in_date, '%M %Y') as month_name,
            COALESCE(SUM(b.total_price), 0) as revenue,
            COUNT(*) as bookings
        FROM bookings b
        LEFT JOIN rooms r ON b.room_id = r.id
        WHERE b.check_in_date BETWEEN :start_date AND :end_date
        AND b.status IN ('confirmed', 'checked_in', 'checked_out')
        $roomTypeFilter
        GROUP BY DATE_FORMAT(b.check_in_date, '%Y-%m')
        ORDER BY month DESC
    ");
    $stmt->execute($params);
    $monthlyRevenue = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Revenue by room type
    $stmt = $pdo->prepare("
        SELECT 
            r.type,
            COALESCE(SUM(b.total_price), 0) as revenue,
            COUNT(*) as bookings
        FROM bookings b
        LEFT JOIN rooms r ON b.room_id = r.id
        WHERE b.check_in_date BETWEEN :start_date AND :end_date
        AND b.status IN ('confirmed', 'checked_in', 'checked_out')
        $roomTypeFilter
        GROUP BY r.type
        ORDER BY revenue DESC
    ");
    $stmt->execute($params);
    $revenueByRoomType = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate averages
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

/**
 * Get guest reports
 */
function getGuestReports($pdo, $startDate, $endDate, $roomType, $params) {
    $roomTypeFilter = !empty($roomType) ? " AND r.type = :room_type" : "";
    
    // Total unique guests
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT b.user_email) as total
        FROM bookings b
        LEFT JOIN rooms r ON b.room_id = r.id
        WHERE b.check_in_date BETWEEN :start_date AND :end_date
        $roomTypeFilter
    ");
    $stmt->execute($params);
    $totalGuests = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Average stay length
    $stmt = $pdo->prepare("
        SELECT AVG(DATEDIFF(b.check_out_date, b.check_in_date)) as avg_stay
        FROM bookings b
        LEFT JOIN rooms r ON b.room_id = r.id
        WHERE b.check_in_date BETWEEN :start_date AND :end_date
        AND b.status IN ('confirmed', 'checked_in', 'checked_out')
        $roomTypeFilter
    ");
    $stmt->execute($params);
    $avgStay = $stmt->fetch(PDO::FETCH_ASSOC)['avg_stay'];
    
    // Return guests (guests with more than 1 booking)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as return_guests
        FROM (
            SELECT b.user_email
            FROM bookings b
            LEFT JOIN rooms r ON b.room_id = r.id
            WHERE b.check_in_date BETWEEN :start_date AND :end_date
            $roomTypeFilter
            GROUP BY b.user_email
            HAVING COUNT(*) > 1
        ) as returning
    ");
    $stmt->execute($params);
    $returnGuests = $stmt->fetch(PDO::FETCH_ASSOC)['return_guests'];
    
    // Guest arrival trends
    $stmt = $pdo->prepare("
        SELECT 
            DATE(b.check_in_date) as date,
            COUNT(DISTINCT b.user_email) as guests
        FROM bookings b
        LEFT JOIN rooms r ON b.room_id = r.id
        WHERE b.check_in_date BETWEEN :start_date AND :end_date
        $roomTypeFilter
        GROUP BY DATE(b.check_in_date)
        ORDER BY date ASC
    ");
    $stmt->execute($params);
    $guestTrends = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Top guests
    $stmt = $pdo->prepare("
        SELECT 
            b.user_name,
            b.user_email,
            COUNT(*) as total_bookings,
            COALESCE(SUM(b.total_price), 0) as total_spent
        FROM bookings b
        LEFT JOIN rooms r ON b.room_id = r.id
        WHERE b.check_in_date BETWEEN :start_date AND :end_date
        $roomTypeFilter
        GROUP BY b.user_email, b.user_name
        ORDER BY total_bookings DESC, total_spent DESC
        LIMIT 10
    ");
    $stmt->execute($params);
    $topGuests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return [
        'total_guests' => (int)$totalGuests,
        'avg_stay_length' => round((float)$avgStay, 1),
        'return_guests' => (int)$returnGuests,
        'guest_trends' => $guestTrends,
        'top_guests' => $topGuests
    ];
}

/**
 * Get room reports
 */
function getRoomReports($pdo, $startDate, $endDate, $roomType, $params) {
    $roomTypeFilter = !empty($roomType) ? " AND r.type = :room_type" : "";
    
    // Most booked rooms
    $stmt = $pdo->prepare("
        SELECT 
            r.room_number,
            r.type,
            COUNT(*) as bookings,
            COALESCE(SUM(b.total_price), 0) as revenue
        FROM bookings b
        LEFT JOIN rooms r ON b.room_id = r.id
        WHERE b.check_in_date BETWEEN :start_date AND :end_date
        AND b.status IN ('confirmed', 'checked_in', 'checked_out')
        $roomTypeFilter
        GROUP BY r.id, r.room_number, r.type
        ORDER BY bookings DESC
        LIMIT 10
    ");
    $stmt->execute($params);
    $mostBooked = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Least booked rooms
    $stmt = $pdo->prepare("
        SELECT 
            r.room_number,
            r.type,
            COALESCE(COUNT(b.id), 0) as bookings,
            COALESCE(SUM(b.total_price), 0) as revenue
        FROM rooms r
        LEFT JOIN bookings b ON r.id = b.room_id 
            AND b.check_in_date BETWEEN :start_date AND :end_date
            AND b.status IN ('confirmed', 'checked_in', 'checked_out')
        WHERE 1=1 " . ($roomType ? "AND r.type = :room_type" : "") . "
        GROUP BY r.id, r.room_number, r.type
        ORDER BY bookings ASC
        LIMIT 10
    ");
    $stmt->execute($params);
    $leastBooked = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Room type distribution
    $stmt = $pdo->prepare("
        SELECT 
            r.type,
            COUNT(*) as total_rooms,
            SUM(CASE WHEN b.id IS NULL OR b.status NOT IN ('confirmed', 'checked_in') THEN 1 ELSE 0 END) as available,
            SUM(CASE WHEN b.id IS NOT NULL AND b.status IN ('confirmed', 'checked_in') THEN 1 ELSE 0 END) as occupied
        FROM rooms r
        LEFT JOIN bookings b ON r.id = b.room_id 
            AND CURDATE() BETWEEN b.check_in_date AND b.check_out_date
            AND b.status IN ('confirmed', 'checked_in')
        WHERE 1=1 " . ($roomType ? "AND r.type = :room_type" : "") . "
        GROUP BY r.type
    ");
    $stmt->execute($roomType ? [':room_type' => $roomType] : []);
    $roomTypeDistribution = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Room performance (bookings per room)
    $stmt = $pdo->prepare("
        SELECT 
            r.room_number,
            COUNT(b.id) as bookings
        FROM rooms r
        LEFT JOIN bookings b ON r.id = b.room_id 
            AND b.check_in_date BETWEEN :start_date AND :end_date
            AND b.status IN ('confirmed', 'checked_in', 'checked_out')
        WHERE 1=1 " . ($roomType ? "AND r.type = :room_type" : "") . "
        GROUP BY r.id, r.room_number
        ORDER BY r.room_number
    ");
    $stmt->execute($params);
    $roomPerformance = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return [
        'most_booked' => $mostBooked,
        'least_booked' => $leastBooked,
        'room_type_distribution' => $roomTypeDistribution,
        'room_performance' => $roomPerformance
    ];
}
