<?php
/**
 * Export Reports to Excel (CSV Format)
 * Generates Excel-compatible CSV reports
 */

require_once '../database/config.php';
require_once '../database/db_connect.php';

try {
    $pdo = getDBConnection();
    
    // Get filter parameters
    $startDate = $_GET['start_date'] ?? date('Y-m-01');
    $endDate = $_GET['end_date'] ?? date('Y-m-d');
    $roomType = $_GET['room_type'] ?? '';
    $reportType = $_GET['report_type'] ?? 'overview';
    
    // Set headers for CSV download
    $filename = 'report_' . $reportType . '_' . date('Y-m-d_His') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Add BOM for Excel UTF-8 support
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Build params
    $params = [':start_date' => $startDate, ':end_date' => $endDate];
    if (!empty($roomType)) {
        $params[':room_type'] = $roomType;
    }
    
    // Write report header
    fputcsv($output, ['BarCIE Hotel Management System']);
    fputcsv($output, [ucfirst($reportType) . ' Report']);
    fputcsv($output, ['Date Range: ' . date('M d, Y', strtotime($startDate)) . ' - ' . date('M d, Y', strtotime($endDate))]);
    fputcsv($output, ['Room Type: ' . ($roomType ?: 'All Room Types')]);
    fputcsv($output, ['Generated: ' . date('M d, Y - h:i A')]);
    fputcsv($output, []); // Empty row
    
    // Generate specific report based on type
    switch ($reportType) {
        case 'booking':
            generateBookingExcelReport($pdo, $output, $params, $roomType);
            break;
        case 'occupancy':
            generateOccupancyExcelReport($pdo, $output, $params, $roomType);
            break;
        case 'revenue':
            generateRevenueExcelReport($pdo, $output, $params, $roomType);
            break;
        case 'guest':
            generateGuestExcelReport($pdo, $output, $params, $roomType);
            break;
        case 'room':
            generateRoomExcelReport($pdo, $output, $params, $roomType);
            break;
        case 'overview':
        default:
            generateOverviewExcelReport($pdo, $output, $params, $roomType);
            break;
    }
    
    fclose($output);
    exit;
    
} catch (Exception $e) {
    http_response_code(500);
    echo "Error generating Excel report: " . $e->getMessage();
}

function generateOverviewExcelReport($pdo, $output, $params, $roomType) {
    $roomTypeFilter = !empty($roomType) ? " AND i.item_type = :room_type" : "";
    
    // Summary section
    fputcsv($output, ['=== SUMMARY ===']);
    fputcsv($output, []);
    
    // Get summary data
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_bookings,
            COUNT(DISTINCT SUBSTRING_INDEX(b.details, '|', 1)) as total_guests,
            COALESCE(SUM(CASE WHEN b.status IN ('approved', 'confirmed', 'checked_in', 'checked_out') THEN b.amount ELSE 0 END), 0) as total_revenue,
            AVG(DATEDIFF(b.checkout, b.checkin)) as avg_stay
        FROM bookings b
        LEFT JOIN items i ON b.room_id = i.id
        WHERE b.checkin BETWEEN :start_date AND :end_date
        $roomTypeFilter
    ");
    $stmt->execute($params);
    $summary = $stmt->fetch(PDO::FETCH_ASSOC);
    
    fputcsv($output, ['Metric', 'Value']);
    fputcsv($output, ['Total Bookings', $summary['total_bookings']]);
    fputcsv($output, ['Total Revenue', '₱' . number_format($summary['total_revenue'], 2)]);
    fputcsv($output, ['Total Guests', $summary['total_guests']]);
    fputcsv($output, ['Average Stay', number_format($summary['avg_stay'], 1) . ' days']);
    fputcsv($output, []);
    
    // Add all report sections
    generateBookingExcelReport($pdo, $output, $params, $roomType);
    generateRevenueExcelReport($pdo, $output, $params, $roomType);
    generateOccupancyExcelReport($pdo, $output, $params, $roomType);
    generateGuestExcelReport($pdo, $output, $params, $roomType);
    generateRoomExcelReport($pdo, $output, $params, $roomType);
}

function generateBookingExcelReport($pdo, $output, $params, $roomType) {
    $roomTypeFilter = !empty($roomType) ? " AND i.item_type = :room_type" : "";
    
    fputcsv($output, ['=== BOOKING REPORTS ===']);
    fputcsv($output, []);
    
    // Booking status breakdown
    fputcsv($output, ['Booking Status Breakdown']);
    fputcsv($output, ['Status', 'Count', 'Percentage', 'Revenue']);
    
    $stmt = $pdo->prepare("
        SELECT 
            b.status,
            COUNT(*) as count,
            COALESCE(SUM(b.amount), 0) as revenue
        FROM bookings b
        LEFT JOIN items i ON b.room_id = i.id
        WHERE b.checkin BETWEEN :start_date AND :end_date
        $roomTypeFilter
        GROUP BY b.status
        ORDER BY count DESC
    ");
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total = array_sum(array_column($results, 'count'));
    foreach ($results as $row) {
        $percentage = $total > 0 ? number_format(($row['count'] / $total) * 100, 1) : '0';
        fputcsv($output, [
            ucfirst(str_replace('_', ' ', $row['status'])),
            $row['count'],
            $percentage . '%',
            '₱' . number_format($row['revenue'], 2)
        ]);
    }
    fputcsv($output, []);
    
    // Monthly bookings
    fputcsv($output, ['Monthly Bookings Summary']);
    fputcsv($output, ['Month', 'Total Bookings', 'Revenue']);
    
    $stmt = $pdo->prepare("
        SELECT 
            DATE_FORMAT(b.checkin, '%M %Y') as month,
            COUNT(*) as count,
            COALESCE(SUM(b.amount), 0) as revenue
        FROM bookings b
        LEFT JOIN items i ON b.room_id = i.id
        WHERE b.checkin BETWEEN :start_date AND :end_date
        AND b.status IN ('approved', 'confirmed', 'checked_in', 'checked_out')
        $roomTypeFilter
        GROUP BY DATE_FORMAT(b.checkin, '%Y-%m')
        ORDER BY DATE_FORMAT(b.checkin, '%Y-%m') DESC
    ");
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($results as $row) {
        fputcsv($output, [
            $row['month'],
            $row['count'],
            '₱' . number_format($row['revenue'], 2)
        ]);
    }
    fputcsv($output, []);
    
    // Daily bookings (last 10 days)
    fputcsv($output, ['Daily Bookings (Recent)']);
    fputcsv($output, ['Date', 'Bookings']);
    
    $stmt = $pdo->prepare("
        SELECT 
            DATE_FORMAT(b.checkin, '%M %d, %Y') as date,
            COUNT(*) as count
        FROM bookings b
        LEFT JOIN items i ON b.room_id = i.id
        WHERE b.checkin BETWEEN :start_date AND :end_date
        $roomTypeFilter
        GROUP BY DATE(b.checkin)
        ORDER BY DATE(b.checkin) DESC
        LIMIT 10
    ");
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($results as $row) {
        fputcsv($output, [$row['date'], $row['count']]);
    }
    fputcsv($output, []);
}

function generateRevenueExcelReport($pdo, $output, $params, $roomType) {
    $roomTypeFilter = !empty($roomType) ? " AND i.item_type = :room_type" : "";
    
    fputcsv($output, ['=== REVENUE REPORTS ===']);
    fputcsv($output, []);
    
    // Revenue by room type
    fputcsv($output, ['Revenue by Room Type']);
    fputcsv($output, ['Room Type', 'Bookings', 'Revenue', 'Avg per Booking']);
    
    $stmt = $pdo->prepare("
        SELECT 
            i.item_type as type,
            COUNT(*) as bookings,
            COALESCE(SUM(b.amount), 0) as revenue
        FROM bookings b
        LEFT JOIN items i ON b.room_id = i.id
        WHERE b.checkin BETWEEN :start_date AND :end_date
        AND b.status IN ('approved', 'confirmed', 'checked_in', 'checked_out')
        $roomTypeFilter
        GROUP BY i.item_type
        ORDER BY revenue DESC
    ");
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($results as $row) {
        $avg = $row['bookings'] > 0 ? $row['revenue'] / $row['bookings'] : 0;
        fputcsv($output, [
            $row['type'],
            $row['bookings'],
            '₱' . number_format($row['revenue'], 2),
            '₱' . number_format($avg, 2)
        ]);
    }
    fputcsv($output, []);
    
    // Monthly revenue
    fputcsv($output, ['Monthly Revenue Breakdown']);
    fputcsv($output, ['Month', 'Revenue', 'Bookings']);
    
    $stmt = $pdo->prepare("
        SELECT 
            DATE_FORMAT(b.checkin, '%M %Y') as month,
            COALESCE(SUM(b.amount), 0) as revenue,
            COUNT(*) as bookings
        FROM bookings b
        LEFT JOIN items i ON b.room_id = i.id
        WHERE b.checkin BETWEEN :start_date AND :end_date
        AND b.status IN ('approved', 'confirmed', 'checked_in', 'checked_out')
        $roomTypeFilter
        GROUP BY DATE_FORMAT(b.checkin, '%Y-%m')
        ORDER BY DATE_FORMAT(b.checkin, '%Y-%m') DESC
    ");
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($results as $row) {
        fputcsv($output, [
            $row['month'],
            '₱' . number_format($row['revenue'], 2),
            $row['bookings']
        ]);
    }
    fputcsv($output, []);
}

function generateOccupancyExcelReport($pdo, $output, $params, $roomType) {
    $roomTypeFilter = !empty($roomType) ? " AND i.item_type = :room_type" : "";
    
    fputcsv($output, ['=== OCCUPANCY REPORTS ===']);
    fputcsv($output, []);
    
    // Room status distribution
    fputcsv($output, ['Room Status Distribution']);
    fputcsv($output, ['Status', 'Count', 'Percentage']);
    
    $stmt = $pdo->prepare("
        SELECT 
            CASE 
                WHEN b.id IS NOT NULL AND b.status IN ('approved', 'confirmed', 'checked_in') THEN 'Occupied'
                ELSE 'Available'
            END as status,
            COUNT(*) as count
        FROM items i
        LEFT JOIN bookings b ON i.id = b.room_id 
            AND CURDATE() BETWEEN b.checkin AND b.checkout
            AND b.status IN ('approved', 'confirmed', 'checked_in')
        WHERE i.item_type IN ('room', 'facility') " . ($roomType ? "AND i.item_type = :room_type" : "") . "
        GROUP BY status
    ");
    $stmt->execute($roomType ? [':room_type' => $roomType] : []);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total = array_sum(array_column($results, 'count'));
    foreach ($results as $row) {
        $percentage = $total > 0 ? number_format(($row['count'] / $total) * 100, 1) : '0';
        fputcsv($output, [
            $row['status'],
            $row['count'],
            $percentage . '%'
        ]);
    }
    fputcsv($output, []);
    
    // Daily occupancy trends (last 30 days)
    fputcsv($output, ['Daily Occupancy Trends']);
    fputcsv($output, ['Date', 'Occupied Items', 'Total Items', 'Occupancy Rate']);
    
    $stmt = $pdo->prepare("
        SELECT 
            DATE_FORMAT(b.checkin, '%M %d, %Y') as date,
            COUNT(DISTINCT b.room_id) as occupied_items,
            (SELECT COUNT(*) FROM items WHERE item_type IN ('room', 'facility') " . ($roomType ? "AND item_type = :room_type" : "") . ") as total_items
        FROM bookings b
        LEFT JOIN items i ON b.room_id = i.id
        WHERE b.checkin BETWEEN DATE_SUB(:end_date, INTERVAL 30 DAY) AND :end_date
        AND b.status IN ('approved', 'confirmed', 'checked_in', 'checked_out')
        $roomTypeFilter
        GROUP BY DATE(b.checkin)
        ORDER BY DATE(b.checkin) DESC
    ");
    $stmt->execute([':end_date' => $params[':end_date']] + ($roomType ? [':room_type' => $roomType] : []));
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($results as $row) {
        $occupancyRate = $row['total_items'] > 0 
            ? number_format(($row['occupied_items'] / $row['total_items']) * 100, 1) 
            : '0';
        fputcsv($output, [
            $row['date'],
            $row['occupied_items'],
            $row['total_items'],
            $occupancyRate . '%'
        ]);
    }
    fputcsv($output, []);
}

function generateGuestExcelReport($pdo, $output, $params, $roomType) {
    $roomTypeFilter = !empty($roomType) ? " AND i.item_type = :room_type" : "";
    
    fputcsv($output, ['=== GUEST REPORTS ===']);
    fputcsv($output, []);
    
    // Top guests
    fputcsv($output, ['Top Guests by Bookings']);
    fputcsv($output, ['Rank', 'Guest Name', 'Email', 'Total Bookings', 'Total Spent']);
    
    $stmt = $pdo->prepare("
        SELECT 
            SUBSTRING_INDEX(SUBSTRING_INDEX(b.details, 'Guest:', -1), '|', 1) as user_name,
            b.receipt_no,
            COUNT(*) as total_bookings,
            COALESCE(SUM(b.amount), 0) as total_spent
        FROM bookings b
        LEFT JOIN items i ON b.room_id = i.id
        WHERE b.checkin BETWEEN :start_date AND :end_date
        $roomTypeFilter
        GROUP BY user_name, b.receipt_no
        ORDER BY total_bookings DESC, total_spent DESC
        LIMIT 20
    ");
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $rank = 1;
    foreach ($results as $row) {
        fputcsv($output, [
            $rank++,
            $row['user_name'],
            $row['user_email'],
            $row['total_bookings'],
            '₱' . number_format($row['total_spent'], 2)
        ]);
    }
    fputcsv($output, []);
}

function generateRoomExcelReport($pdo, $output, $params, $roomType) {
    $roomTypeFilter = !empty($roomType) ? " AND i.item_type = :room_type" : "";
    
    fputcsv($output, ['=== ROOM REPORTS ===']);
    fputcsv($output, []);
    
    // Most booked rooms
    fputcsv($output, ['Most Booked Rooms']);
    fputcsv($output, ['Room Number', 'Room Type', 'Total Bookings', 'Revenue']);
    
    $stmt = $pdo->prepare("
        SELECT 
            i.room_number,
            i.item_type as type,
            COUNT(*) as bookings,
            COALESCE(SUM(b.amount), 0) as revenue
        FROM bookings b
        LEFT JOIN items i ON b.room_id = i.id
        WHERE b.checkin BETWEEN :start_date AND :end_date
        AND b.status IN ('approved', 'confirmed', 'checked_in', 'checked_out')
        $roomTypeFilter
        GROUP BY i.id, i.room_number, i.item_type
        ORDER BY bookings DESC
        LIMIT 10
    ");
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($results as $row) {
        fputcsv($output, [
            $row['room_number'],
            $row['type'],
            $row['bookings'],
            '₱' . number_format($row['revenue'], 2)
        ]);
    }
    fputcsv($output, []);
    
    // Least booked rooms
    fputcsv($output, ['Least Booked Rooms']);
    fputcsv($output, ['Room Number', 'Room Type', 'Total Bookings', 'Revenue']);
    
    $stmt = $pdo->prepare("
        SELECT 
            i.room_number,
            i.item_type as type,
            COALESCE(COUNT(b.id), 0) as bookings,
            COALESCE(SUM(b.amount), 0) as revenue
        FROM items i
        LEFT JOIN bookings b ON i.id = b.room_id 
            AND b.checkin BETWEEN :start_date AND :end_date
            AND b.status IN ('approved', 'confirmed', 'checked_in', 'checked_out')
        WHERE i.item_type IN ('room', 'facility') " . ($roomType ? "AND i.item_type = :room_type" : "") . "
        GROUP BY i.id, i.room_number, i.item_type
        ORDER BY bookings ASC
        LIMIT 10
    ");
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($results as $row) {
        fputcsv($output, [
            $row['room_number'],
            $row['type'],
            $row['bookings'],
            '₱' . number_format($row['revenue'], 2)
        ]);
    }
    fputcsv($output, []);
    
    // Room/Facility type distribution
    fputcsv($output, ['Room/Facility Type Distribution']);
    fputcsv($output, ['Type', 'Total Items', 'Available', 'Occupied']);
    
    $stmt = $pdo->prepare("
        SELECT 
            i.item_type as type,
            COUNT(*) as total_items,
            SUM(CASE WHEN b.id IS NULL OR b.status NOT IN ('approved', 'confirmed', 'checked_in') THEN 1 ELSE 0 END) as available,
            SUM(CASE WHEN b.id IS NOT NULL AND b.status IN ('approved', 'confirmed', 'checked_in') THEN 1 ELSE 0 END) as occupied
        FROM items i
        LEFT JOIN bookings b ON i.id = b.room_id 
            AND CURDATE() BETWEEN b.checkin AND b.checkout
            AND b.status IN ('approved', 'confirmed', 'checked_in')
        WHERE i.item_type IN ('room', 'facility') " . ($roomType ? "AND i.item_type = :room_type" : "") . "
        GROUP BY i.item_type
    ");
    $stmt->execute($roomType ? [':room_type' => $roomType] : []);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($results as $row) {
        fputcsv($output, [
            ucfirst($row['type']),
            $row['total_items'],
            $row['available'],
            $row['occupied']
        ]);
    }
    fputcsv($output, []);
}
