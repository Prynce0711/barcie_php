<?php
/**
 * Export Reports to PDF
 * Generates PDF reports using DomPDF
 */

require_once '../vendor/autoload.php';
require_once '../database/config.php';
require_once '../database/db_connect.php';

use Dompdf\Dompdf;
use Dompdf\Options;

try {
    $pdo = getDBConnection();
    
    // Get filter parameters
    $startDate = $_GET['start_date'] ?? date('Y-m-01');
    $endDate = $_GET['end_date'] ?? date('Y-m-d');
    $roomType = $_GET['room_type'] ?? '';
    $reportType = $_GET['report_type'] ?? 'overview';
    
    // Fetch report data
    $params = [':start_date' => $startDate, ':end_date' => $endDate];
    if (!empty($roomType)) {
        $params[':room_type'] = $roomType;
    }
    
    // Build the HTML content
    $html = generateReportHTML($pdo, $reportType, $startDate, $endDate, $roomType, $params);
    
    // Configure DomPDF
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    $options->set('defaultFont', 'Arial');
    
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    
    // Output the PDF
    $filename = 'report_' . $reportType . '_' . date('Y-m-d_His') . '.pdf';
    $dompdf->stream($filename, ['Attachment' => true]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo "Error generating PDF: " . $e->getMessage();
}

function generateReportHTML($pdo, $reportType, $startDate, $endDate, $roomType, $params) {
    $roomTypeFilter = !empty($roomType) ? " AND i.item_type = :room_type" : "";
    $roomTypeText = !empty($roomType) ? " - {$roomType} Rooms" : " - All Room Types";
    
    ob_start();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>BarCIE Hotel - <?php echo ucfirst($reportType); ?> Report</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                font-size: 12px;
                color: #333;
                line-height: 1.6;
            }
            .header {
                text-align: center;
                margin-bottom: 30px;
                padding-bottom: 20px;
                border-bottom: 3px solid #0066cc;
            }
            .header h1 {
                color: #0066cc;
                margin: 0 0 10px 0;
                font-size: 24px;
            }
            .header .subtitle {
                color: #666;
                font-size: 14px;
            }
            .info-box {
                background: #f8f9fa;
                padding: 15px;
                margin-bottom: 20px;
                border-left: 4px solid #0066cc;
            }
            .info-box h3 {
                margin: 0 0 10px 0;
                color: #0066cc;
                font-size: 16px;
            }
            .summary-grid {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 15px;
                margin-bottom: 30px;
            }
            .summary-card {
                background: #f8f9fa;
                padding: 15px;
                border-radius: 5px;
                text-align: center;
                border: 1px solid #ddd;
            }
            .summary-card h4 {
                margin: 0 0 10px 0;
                color: #666;
                font-size: 11px;
                text-transform: uppercase;
            }
            .summary-card .value {
                font-size: 20px;
                font-weight: bold;
                color: #0066cc;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }
            table th {
                background: #0066cc;
                color: white;
                padding: 10px;
                text-align: left;
                font-weight: bold;
            }
            table td {
                padding: 8px;
                border-bottom: 1px solid #ddd;
            }
            table tr:nth-child(even) {
                background: #f8f9fa;
            }
            .section-title {
                color: #0066cc;
                font-size: 18px;
                margin: 30px 0 15px 0;
                padding-bottom: 10px;
                border-bottom: 2px solid #0066cc;
            }
            .footer {
                margin-top: 40px;
                padding-top: 20px;
                border-top: 1px solid #ddd;
                text-align: center;
                color: #666;
                font-size: 10px;
            }
            .no-data {
                text-align: center;
                color: #999;
                padding: 20px;
                font-style: italic;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>BarCIE Hotel Management System</h1>
            <div class="subtitle"><?php echo ucfirst($reportType); ?> Report</div>
        </div>
        
        <div class="info-box">
            <h3>Report Information</h3>
            <p>
                <strong>Report Type:</strong> <?php echo ucfirst($reportType); ?> Report<br>
                <strong>Date Range:</strong> <?php echo date('F d, Y', strtotime($startDate)); ?> - <?php echo date('F d, Y', strtotime($endDate)); ?><br>
                <strong>Room Type:</strong> <?php echo $roomType ?: 'All Room Types'; ?><br>
                <strong>Generated:</strong> <?php echo date('F d, Y - h:i A'); ?>
            </p>
        </div>
        
        <?php
        // Generate summary cards
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total_bookings
            FROM bookings b
            LEFT JOIN items i ON b.room_id = i.id
            WHERE b.checkin BETWEEN :start_date AND :end_date
            $roomTypeFilter
        ");
        $stmt->execute($params);
        $totalBookings = $stmt->fetch(PDO::FETCH_ASSOC)['total_bookings'];
        
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(b.amount), 0) as total_revenue
            FROM bookings b
            LEFT JOIN items i ON b.room_id = i.id
            WHERE b.checkin BETWEEN :start_date AND :end_date
            AND b.status IN ('approved', 'confirmed', 'checked_in', 'checked_out')
            $roomTypeFilter
        ");
        $stmt->execute($params);
        $totalRevenue = $stmt->fetch(PDO::FETCH_ASSOC)['total_revenue'];
        
        $stmt = $pdo->prepare("
            SELECT COUNT(DISTINCT SUBSTRING_INDEX(b.details, '|', 1)) as total_guests
            FROM bookings b
            LEFT JOIN items i ON b.room_id = i.id
            WHERE b.checkin BETWEEN :start_date AND :end_date
            $roomTypeFilter
        ");
        $stmt->execute($params);
        $totalGuests = $stmt->fetch(PDO::FETCH_ASSOC)['total_guests'];
        
        $stmt = $pdo->prepare("
            SELECT AVG(DATEDIFF(b.checkout, b.checkin)) as avg_stay
            FROM bookings b
            LEFT JOIN items i ON b.room_id = i.id
            WHERE b.checkin BETWEEN :start_date AND :end_date
            AND b.status IN ('approved', 'confirmed', 'checked_in', 'checked_out')
            $roomTypeFilter
        ");
        $stmt->execute($params);
        $avgStay = $stmt->fetch(PDO::FETCH_ASSOC)['avg_stay'];
        ?>
        
        <div class="summary-grid">
            <div class="summary-card">
                <h4>Total Bookings</h4>
                <div class="value"><?php echo number_format($totalBookings); ?></div>
            </div>
            <div class="summary-card">
                <h4>Total Revenue</h4>
                <div class="value">₱<?php echo number_format($totalRevenue, 2); ?></div>
            </div>
            <div class="summary-card">
                <h4>Total Guests</h4>
                <div class="value"><?php echo number_format($totalGuests); ?></div>
            </div>
            <div class="summary-card">
                <h4>Avg Stay</h4>
                <div class="value"><?php echo number_format($avgStay, 1); ?> days</div>
            </div>
        </div>
        
        <?php
        // Generate specific report sections based on report type
        switch ($reportType) {
            case 'booking':
                echo generateBookingReportSection($pdo, $params, $roomTypeFilter);
                break;
            case 'occupancy':
                echo generateOccupancyReportSection($pdo, $params, $roomTypeFilter, $roomType);
                break;
            case 'revenue':
                echo generateRevenueReportSection($pdo, $params, $roomTypeFilter);
                break;
            case 'guest':
                echo generateGuestReportSection($pdo, $params, $roomTypeFilter);
                break;
            case 'room':
                echo generateRoomReportSection($pdo, $params, $roomTypeFilter, $roomType);
                break;
            case 'overview':
            default:
                echo generateBookingReportSection($pdo, $params, $roomTypeFilter);
                echo generateRevenueReportSection($pdo, $params, $roomTypeFilter);
                echo generateOccupancyReportSection($pdo, $params, $roomTypeFilter, $roomType);
                break;
        }
        ?>
        
        <div class="footer">
            <p>BarCIE Hotel Management System | Generated on <?php echo date('F d, Y - h:i A'); ?></p>
            <p>This is a computer-generated report. No signature required.</p>
        </div>
    </body>
    </html>
    <?php
    return ob_get_clean();
}

function generateBookingReportSection($pdo, $params, $roomTypeFilter) {
    ob_start();
    ?>
    <h2 class="section-title">Booking Reports</h2>
    
    <h3 style="font-size: 14px; margin: 20px 0 10px 0;">Booking Status Breakdown</h3>
    <table>
        <thead>
            <tr>
                <th>Status</th>
                <th>Count</th>
                <th>Percentage</th>
                <th>Revenue</th>
            </tr>
        </thead>
        <tbody>
            <?php
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
            ");
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $total = array_sum(array_column($results, 'count'));
            
            if (count($results) > 0) {
                foreach ($results as $row) {
                    $percentage = $total > 0 ? ($row['count'] / $total) * 100 : 0;
                    echo "<tr>";
                    echo "<td>" . ucfirst(str_replace('_', ' ', $row['status'])) . "</td>";
                    echo "<td>" . number_format($row['count']) . "</td>";
                    echo "<td>" . number_format($percentage, 1) . "%</td>";
                    echo "<td>₱" . number_format($row['revenue'], 2) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4' class='no-data'>No bookings found</td></tr>";
            }
            ?>
        </tbody>
    </table>
    
    <h3 style="font-size: 14px; margin: 20px 0 10px 0;">Monthly Bookings Summary</h3>
    <table>
        <thead>
            <tr>
                <th>Month</th>
                <th>Total Bookings</th>
                <th>Revenue</th>
            </tr>
        </thead>
        <tbody>
            <?php
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
            
            if (count($results) > 0) {
                foreach ($results as $row) {
                    echo "<tr>";
                    echo "<td>" . $row['month'] . "</td>";
                    echo "<td>" . number_format($row['count']) . "</td>";
                    echo "<td>₱" . number_format($row['revenue'], 2) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='3' class='no-data'>No data available</td></tr>";
            }
            ?>
        </tbody>
    </table>
    <?php
    return ob_get_clean();
}

function generateRevenueReportSection($pdo, $params, $roomTypeFilter) {
    ob_start();
    ?>
    <h2 class="section-title">Revenue Reports</h2>
    
    <h3 style="font-size: 14px; margin: 20px 0 10px 0;">Revenue by Room Type</h3>
    <table>
        <thead>
            <tr>
                <th>Room Type</th>
                <th>Bookings</th>
                <th>Revenue</th>
                <th>Avg per Booking</th>
            </tr>
        </thead>
        <tbody>
            <?php
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
            
            if (count($results) > 0) {
                foreach ($results as $row) {
                    $avg = $row['bookings'] > 0 ? $row['revenue'] / $row['bookings'] : 0;
                    echo "<tr>";
                    echo "<td>" . $row['type'] . "</td>";
                    echo "<td>" . number_format($row['bookings']) . "</td>";
                    echo "<td>₱" . number_format($row['revenue'], 2) . "</td>";
                    echo "<td>₱" . number_format($avg, 2) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4' class='no-data'>No revenue data available</td></tr>";
            }
            ?>
        </tbody>
    </table>
    <?php
    return ob_get_clean();
}

function generateOccupancyReportSection($pdo, $params, $roomTypeFilter, $roomType) {
    ob_start();
    ?>
    <h2 class="section-title">Occupancy Reports</h2>
    
    <h3 style="font-size: 14px; margin: 20px 0 10px 0;">Room Status Distribution</h3>
    <table>
        <thead>
            <tr>
                <th>Status</th>
                <th>Count</th>
                <th>Percentage</th>
            </tr>
        </thead>
        <tbody>
            <?php
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
            
            if (count($results) > 0) {
                foreach ($results as $row) {
                    $percentage = $total > 0 ? ($row['count'] / $total) * 100 : 0;
                    echo "<tr>";
                    echo "<td>" . $row['status'] . "</td>";
                    echo "<td>" . number_format($row['count']) . "</td>";
                    echo "<td>" . number_format($percentage, 1) . "%</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='3' class='no-data'>No data available</td></tr>";
            }
            ?>
        </tbody>
    </table>
    <?php
    return ob_get_clean();
}

function generateGuestReportSection($pdo, $params, $roomTypeFilter) {
    ob_start();
    ?>
    <h2 class="section-title">Guest Reports</h2>
    
    <h3 style="font-size: 14px; margin: 20px 0 10px 0;">Top Guests by Bookings</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Guest Name</th>
                <th>Email</th>
                <th>Total Bookings</th>
                <th>Total Spent</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stmt = $pdo->prepare("
                SELECT 
                    SUBSTRING_INDEX(SUBSTRING_INDEX(b.details, 'Guest:', -1), '|', 1) as user_name,
                    b.receipt_no as user_email,
                    COUNT(*) as total_bookings,
                    COALESCE(SUM(b.amount), 0) as total_spent
                FROM bookings b
                LEFT JOIN items i ON b.room_id = i.id
                WHERE b.checkin BETWEEN :start_date AND :end_date
                $roomTypeFilter
                GROUP BY user_name, b.receipt_no
                ORDER BY total_bookings DESC, total_spent DESC
                LIMIT 10
            ");
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($results) > 0) {
                $counter = 1;
                foreach ($results as $row) {
                    echo "<tr>";
                    echo "<td>" . $counter++ . "</td>";
                    echo "<td>" . htmlspecialchars($row['user_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['user_email']) . "</td>";
                    echo "<td>" . number_format($row['total_bookings']) . "</td>";
                    echo "<td>₱" . number_format($row['total_spent'], 2) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5' class='no-data'>No guest data available</td></tr>";
            }
            ?>
        </tbody>
    </table>
    <?php
    return ob_get_clean();
}

function generateRoomReportSection($pdo, $params, $roomTypeFilter, $roomType) {
    ob_start();
    ?>
    <h2 class="section-title">Room Reports</h2>
    
    <h3 style="font-size: 14px; margin: 20px 0 10px 0;">Most Booked Rooms</h3>
    <table>
        <thead>
            <tr>
                <th>Room Number</th>
                <th>Room Type</th>
                <th>Total Bookings</th>
                <th>Revenue</th>
            </tr>
        </thead>
        <tbody>
            <?php
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
            
            if (count($results) > 0) {
                foreach ($results as $row) {
                    echo "<tr>";
                    echo "<td>" . $row['room_number'] . "</td>";
                    echo "<td>" . $row['type'] . "</td>";
                    echo "<td>" . number_format($row['bookings']) . "</td>";
                    echo "<td>₱" . number_format($row['revenue'], 2) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4' class='no-data'>No room data available</td></tr>";
            }
            ?>
        </tbody>
    </table>
    
    <h3 style="font-size: 14px; margin: 20px 0 10px 0;">Room/Facility Type Distribution</h3>
    <table>
        <thead>
            <tr>
                <th>Type</th>
                <th>Total Items</th>
                <th>Available</th>
                <th>Occupied</th>
            </tr>
        </thead>
        <tbody>
            <?php
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
            
            if (count($results) > 0) {
                foreach ($results as $row) {
                    echo "<tr>";
                    echo "<td>" . ucfirst($row['type']) . "</td>";
                    echo "<td>" . number_format($row['total_items']) . "</td>";
                    echo "<td>" . number_format($row['available']) . "</td>";
                    echo "<td>" . number_format($row['occupied']) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4' class='no-data'>No room type data available</td></tr>";
            }
            ?>
        </tbody>
    </table>
    <?php
    return ob_get_clean();
}
