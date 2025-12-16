<?php
require_once '../vendor/autoload.php';
require_once '../database/config.php';
require_once '../database/db_connect.php';

use Dompdf\Dompdf;
use Dompdf\Options;

header('Content-Type: application/json');

try {
    // Get filters from request
    $dateFilter = isset($_GET['date_filter']) ? $_GET['date_filter'] : '';
    $statusFilter = isset($_GET['status_filter']) ? $_GET['status_filter'] : '';
    $typeFilter = isset($_GET['type_filter']) ? $_GET['type_filter'] : '';
    
    // Build query based on filters
    $whereConditions = [];
    $params = [];
    
    if (!empty($dateFilter)) {
        $whereConditions[] = "DATE(created_at) = ?";
        $params[] = $dateFilter;
    }
    
    if (!empty($statusFilter)) {
        $whereConditions[] = "status LIKE ?";
        $params[] = "%{$statusFilter}%";
    }
    
    if (!empty($typeFilter)) {
        $whereConditions[] = "booking_type LIKE ?";
        $params[] = "%{$typeFilter}%";
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    // Fetch bookings data
    $query = "SELECT 
        receipt_number,
        room_name,
        booking_type,
        guest_name,
        guest_email,
        guest_phone,
        check_in_date,
        check_out_date,
        status,
        discount_status,
        created_at,
        total_amount,
        payment_method
    FROM bookings 
    {$whereClause}
    ORDER BY created_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($bookings)) {
        echo json_encode(['success' => false, 'message' => 'No bookings found with current filters']);
        exit;
    }
    
    // Configure DomPDF options
    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isPhpEnabled', true);
    $options->set('defaultFont', 'Arial');
    
    $dompdf = new Dompdf($options);
    
    // Get logo path and convert to base64
    $logoPath = '../assets/images/imageBg/barcie_logo.jpg';
    $logoBase64 = '';
    if (file_exists($logoPath)) {
        $logoData = base64_encode(file_get_contents($logoPath));
        $logoBase64 = 'data:image/jpeg;base64,' . $logoData;
    }
    
    // Generate current date and time
    $currentDateTime = date('F j, Y \a\t g:i A');
    $totalRecords = count($bookings);
    
    // Build filter summary
    $filterSummary = [];
    if (!empty($dateFilter)) $filterSummary[] = "Date: {$dateFilter}";
    if (!empty($statusFilter)) $filterSummary[] = "Status: {$statusFilter}";
    if (!empty($typeFilter)) $filterSummary[] = "Type: {$typeFilter}";
    $filterText = !empty($filterSummary) ? implode(' | ', $filterSummary) : 'All Records';
    
    // Create elegant HTML template
    $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 20mm;
            background-image: url("' . $logoBase64 . '");
            background-repeat: no-repeat;
            background-position: center center;
            background-size: 400px 400px;
            background-opacity: 0.1;
        }
        
        body {
            font-family: "Arial", sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #2c3e50;
            margin: 0;
            padding: 0;
            position: relative;
        }
        
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            opacity: 0.05;
            z-index: -1;
            font-size: 120px;
            font-weight: bold;
            color: #1e3a8a;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px 0;
            border-bottom: 3px solid #1e3a8a;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: 8px;
            margin-bottom: 25px;
        }
        
        .header h1 {
            color: #1e3a8a;
            font-size: 24px;
            font-weight: bold;
            margin: 0 0 8px 0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .header h2 {
            color: #64748b;
            font-size: 16px;
            margin: 0 0 15px 0;
            font-weight: 300;
        }
        
        .header .subtitle {
            font-size: 12px;
            color: #475569;
            font-style: italic;
        }
        
        .report-info {
            background: #f1f5f9;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #3b82f6;
        }
        
        .report-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        
        .report-info strong {
            color: #1e40af;
            font-weight: 600;
        }
        
        .booking-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .booking-table th {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: 600;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .booking-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 9px;
            vertical-align: top;
        }
        
        .booking-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        
        .booking-table tbody tr:hover {
            background-color: #e0e7ff;
        }
        
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 8px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-confirmed { background-color: #dcfce7; color: #166534; }
        .status-pending { background-color: #fef3c7; color: #92400e; }
        .status-cancelled { background-color: #fee2e2; color: #dc2626; }
        .status-completed { background-color: #dbeafe; color: #1e40af; }
        
        .discount-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 8px;
            font-size: 8px;
            font-weight: 500;
        }
        
        .discount-approved { background-color: #d1fae5; color: #065f46; }
        .discount-pending { background-color: #fde68a; color: #78350f; }
        .discount-rejected { background-color: #fecaca; color: #991b1b; }
        .discount-none { background-color: #f1f5f9; color: #64748b; }
        
        .amount {
            font-weight: 600;
            color: #059669;
        }
        
        .receipt-number {
            font-weight: 600;
            color: #1e40af;
            font-family: monospace;
        }
        
        .guest-info {
            line-height: 1.3;
        }
        
        .guest-name {
            font-weight: 600;
            color: #374151;
            margin-bottom: 2px;
        }
        
        .guest-contact {
            color: #6b7280;
            font-size: 8px;
        }
        
        .schedule-info {
            line-height: 1.3;
        }
        
        .footer {
            position: fixed;
            bottom: 15mm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }
        
        .page-number:before {
            content: "Page " counter(page) " of " counter(pages);
        }
        
        .summary-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: white;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #3b82f6;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .stat-number {
            font-size: 18px;
            font-weight: bold;
            color: #1e40af;
        }
        
        .stat-label {
            font-size: 10px;
            color: #64748b;
            text-transform: uppercase;
            margin-top: 2px;
        }
    </style>
</head>
<body>
    <div class="watermark">BARCIE</div>
    
    <div class="header">
        <h1>BarCIE International Center</h1>
        <h2>Booking Management Report</h2>
        <div class="subtitle">Tempora Mutantur, Nos Et Mutamur In Illis</div>
    </div>
    
    <div class="report-info">
        <div class="report-info-grid">
            <div><strong>Generated:</strong> ' . $currentDateTime . '</div>
            <div><strong>Total Records:</strong> ' . $totalRecords . '</div>
            <div><strong>Filters Applied:</strong> ' . $filterText . '</div>
            <div><strong>Report Type:</strong> Bookings Export</div>
        </div>
    </div>';

    // Add summary statistics
    $confirmedCount = count(array_filter($bookings, function($b) { return stripos($b['status'], 'confirmed') !== false; }));
    $pendingCount = count(array_filter($bookings, function($b) { return stripos($b['status'], 'pending') !== false; }));
    $cancelledCount = count(array_filter($bookings, function($b) { return stripos($b['status'], 'cancelled') !== false; }));
    $totalAmount = array_sum(array_column($bookings, 'total_amount'));

    $html .= '<div class="summary-stats">
        <div class="stat-card">
            <div class="stat-number">' . $confirmedCount . '</div>
            <div class="stat-label">Confirmed</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">' . $pendingCount . '</div>
            <div class="stat-label">Pending</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">' . $cancelledCount . '</div>
            <div class="stat-label">Cancelled</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">₱' . number_format($totalAmount, 2) . '</div>
            <div class="stat-label">Total Amount</div>
        </div>
    </div>';

    // Add bookings table
    $html .= '<table class="booking-table">
        <thead>
            <tr>
                <th style="width: 10%">Receipt #</th>
                <th style="width: 15%">Room/Facility</th>
                <th style="width: 10%">Type</th>
                <th style="width: 20%">Guest Information</th>
                <th style="width: 15%">Schedule</th>
                <th style="width: 10%">Status</th>
                <th style="width: 10%">Discount</th>
                <th style="width: 10%">Amount</th>
            </tr>
        </thead>
        <tbody>';

    foreach ($bookings as $booking) {
        // Determine status badge class
        $statusClass = 'status-pending';
        if (stripos($booking['status'], 'confirmed') !== false) $statusClass = 'status-confirmed';
        elseif (stripos($booking['status'], 'cancelled') !== false) $statusClass = 'status-cancelled';
        elseif (stripos($booking['status'], 'completed') !== false) $statusClass = 'status-completed';
        
        // Determine discount badge class
        $discountClass = 'discount-none';
        $discountText = 'None';
        if (!empty($booking['discount_status'])) {
            if (stripos($booking['discount_status'], 'approved') !== false) {
                $discountClass = 'discount-approved';
                $discountText = 'Approved';
            } elseif (stripos($booking['discount_status'], 'pending') !== false) {
                $discountClass = 'discount-pending';
                $discountText = 'Pending';
            } elseif (stripos($booking['discount_status'], 'rejected') !== false) {
                $discountClass = 'discount-rejected';
                $discountText = 'Rejected';
            }
        }
        
        // Format dates
        $checkIn = date('M j, Y', strtotime($booking['check_in_date']));
        $checkOut = date('M j, Y', strtotime($booking['check_out_date']));
        $created = date('M j, Y g:i A', strtotime($booking['created_at']));
        
        $html .= '<tr>
            <td><span class="receipt-number">' . htmlspecialchars($booking['receipt_number']) . '</span></td>
            <td>' . htmlspecialchars($booking['room_name']) . '</td>
            <td>' . htmlspecialchars($booking['booking_type']) . '</td>
            <td class="guest-info">
                <div class="guest-name">' . htmlspecialchars($booking['guest_name']) . '</div>
                <div class="guest-contact">📞 ' . htmlspecialchars($booking['guest_phone']) . '</div>
                <div class="guest-contact">✉ ' . htmlspecialchars($booking['guest_email']) . '</div>
            </td>
            <td class="schedule-info">
                <div><strong>In:</strong> ' . $checkIn . '</div>
                <div><strong>Out:</strong> ' . $checkOut . '</div>
                <div style="font-size: 8px; color: #6b7280; margin-top: 3px;">Created: ' . $created . '</div>
            </td>
            <td><span class="status-badge ' . $statusClass . '">' . htmlspecialchars($booking['status']) . '</span></td>
            <td><span class="discount-badge ' . $discountClass . '">' . $discountText . '</span></td>
            <td class="amount">₱' . number_format($booking['total_amount'], 2) . '</td>
        </tr>';
    }

    $html .= '</tbody>
    </table>
    
    <div class="footer">
        <div>BarCIE International Center - Booking Management System</div>
        <div>Barangay Center for Innovative Education © 2000</div>
        <div class="page-number"></div>
    </div>
    
</body>
</html>';

    // Load HTML to DomPDF
    $dompdf->loadHtml($html);
    
    // Set paper size and orientation
    $dompdf->setPaper('A4', 'portrait');
    
    // Render PDF
    $dompdf->render();
    
    // Generate filename
    $filename = 'BarCIE_Bookings_' . date('Y-m-d_H-i-s');
    if (!empty($dateFilter)) {
        $filename .= '_' . str_replace('-', '', $dateFilter);
    }
    $filename .= '.pdf';
    
    // Output PDF
    $dompdf->stream($filename, array('Attachment' => true));
    
} catch (Exception $e) {
    error_log("PDF Generation Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to generate PDF: ' . $e->getMessage()
    ]);
}
?>