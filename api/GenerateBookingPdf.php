<?php
require_once '../vendor/autoload.php';
require_once '../database/config.php';
require_once '../database/db_connect.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Don't set JSON header - we're returning PDF
// header('Content-Type: application/json');

try {
    // Get booking data from request
    $bookingId = isset($_GET['booking_id']) ? $_GET['booking_id'] : '';
    $receiptNumber = isset($_GET['receipt_number']) ? $_GET['receipt_number'] : '';
    $type = isset($_GET['type']) ? $_GET['type'] : 'reservation'; // reservation or pencil_booking

    if (empty($bookingId) && empty($receiptNumber)) {
        throw new Exception('Booking ID or receipt number is required');
    }

    // Determine the correct table based on type
    $table = ($type === 'pencil_booking') ? 'pencil_bookings' : 'bookings';

    // Build query
    $whereClause = '';
    $param = '';
    if (!empty($bookingId)) {
        $whereClause = 'b.id = ?';
        $param = $bookingId;
    } else {
        $whereClause = 'b.receipt_number = ?';
        $param = $receiptNumber;
    }

    // Fetch booking details
    if ($type === 'pencil_booking') {
        $query = "SELECT 
            b.id,
            b.receipt_no as receipt_number,
            b.guest_name,
            b.email as guest_email,
            b.contact_number as guest_phone,
            b.checkin as check_in_date,
            b.checkout as check_out_date,
            b.occupants,
            b.company,
            b.company_contact,
            b.status,
            'N/A' as discount_status,
            b.created_at,
            b.total_price as total_amount,
            'N/A' as payment_method,
            i.name as room_name,
            i.item_type,
            i.room_number,
            i.capacity,
            i.price
        FROM {$table} b 
        LEFT JOIN items i ON b.room_id = i.id
        WHERE {$whereClause}";
    } else {
        $query = "SELECT 
            b.id,
            b.receipt_no as receipt_number,
            SUBSTRING_INDEX(SUBSTRING_INDEX(b.details, 'Guest: ', -1), ' |', 1) as guest_name,
            SUBSTRING_INDEX(SUBSTRING_INDEX(b.details, 'Email: ', -1), ' |', 1) as guest_email,
            SUBSTRING_INDEX(SUBSTRING_INDEX(b.details, 'Phone: ', -1), ' |', 1) as guest_phone,
            b.checkin as check_in_date,
            b.checkout as check_out_date,
            SUBSTRING_INDEX(SUBSTRING_INDEX(b.details, 'Occupants: ', -1), ' |', 1) as occupants,
            SUBSTRING_INDEX(SUBSTRING_INDEX(b.details, 'Company: ', -1), ' |', 1) as company,
            SUBSTRING_INDEX(SUBSTRING_INDEX(b.details, 'Company Contact: ', -1), ' |', 1) as company_contact,
            b.status,
            b.discount_status,
            b.created_at,
            b.amount as total_amount,
            SUBSTRING_INDEX(SUBSTRING_INDEX(b.details, 'Payment Method: ', -1), ' |', 1) as payment_method,
            i.name as room_name,
            i.item_type,
            i.room_number,
            i.capacity,
            i.price
        FROM {$table} b 
        LEFT JOIN items i ON b.room_id = i.id
        WHERE {$whereClause}";
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute([$param]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        throw new Exception('Booking not found');
    }

    // Configure DomPDF options
    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isPhpEnabled', true);
    $options->set('defaultFont', 'Arial');

    $dompdf = new Dompdf($options);

    // Get logo path and convert to base64 - use absolute path
    $logoPath = __DIR__ . '/../public/images/imageBg/barcie_logo.jpg';
    $logoBase64 = '';
    if (file_exists($logoPath)) {
        $logoData = base64_encode(file_get_contents($logoPath));
        $logoBase64 = 'data:image/jpeg;base64,' . $logoData;
        error_log("PDF Logo loaded: " . strlen($logoData) . " bytes from " . $logoPath);
    } else {
        error_log("PDF Logo NOT FOUND at: " . $logoPath);
    }

    // Generate current date and time
    $currentDateTime = date('F j, Y \a\t g:i A');

    // Format dates with validation
    $checkInFormatted = $booking['check_in_date'] ? date('F j, Y \a\t g:i A', strtotime($booking['check_in_date'])) : 'Not set';
    $checkOutFormatted = $booking['check_out_date'] ? date('F j, Y \a\t g:i A', strtotime($booking['check_out_date'])) : 'Not set';
    $createdFormatted = $booking['created_at'] ? date('F j, Y \a\t g:i A', strtotime($booking['created_at'])) : 'Not available';

    error_log('PDF Date Debug - check_in_date: ' . $booking['check_in_date'] . ', check_out_date: ' . $booking['check_out_date'] . ', created_at: ' . $booking['created_at']);

    // Calculate duration
    $checkInDate = new DateTime($booking['check_in_date']);
    $checkOutDate = new DateTime($booking['check_out_date']);
    $duration = $checkInDate->diff($checkOutDate);
    $durationText = $duration->days . ' day(s)';
    if ($duration->h > 0 || $duration->i > 0) {
        $durationText .= ', ' . $duration->h . ' hour(s)';
        if ($duration->i > 0)
            $durationText .= ', ' . $duration->i . ' minute(s)';
    }

    // Determine booking type and status
    $bookingTypeText = ($type === 'pencil_booking') ? 'Draft Reservation (Pencil Booking)' : 'Confirmed Reservation';
    $statusBadgeClass = 'status-pending';
    $statusIcon = 'fas fa-clock';

    if (stripos($booking['status'], 'confirmed') !== false) {
        $statusBadgeClass = 'status-confirmed';
        $statusIcon = 'fas fa-check-circle';
    } elseif (stripos($booking['status'], 'cancelled') !== false) {
        $statusBadgeClass = 'status-cancelled';
        $statusIcon = 'fas fa-times-circle';
    } elseif (stripos($booking['status'], 'completed') !== false) {
        $statusBadgeClass = 'status-completed';
        $statusIcon = 'fas fa-flag-checkered';
    }

    // Room/facility details
    $roomDetails = $booking['room_name'];
    if ($booking['room_number'])
        $roomDetails .= ' (Room #' . $booking['room_number'] . ')';
    $roomDetails .= ' - ' . $booking['capacity'] . ' persons capacity';

    // Payment method display
    $paymentMethod = ucfirst($booking['payment_method'] ?? 'Not specified');

    // Create elegant HTML template
    $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 15mm;
        }
        
        body {
            font-family: "Arial", sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #2c3e50;
            margin: 0;
            padding: 0;
            position: relative;
        }
        
        .watermark-logo {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.15;
            z-index: 0;
            width: 500px;
            height: auto;
        }
        
        .page-content {
            position: relative;
            z-index: 1;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px 0;
            border-bottom: 3px solid #1e3a8a;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: 10px;
        }
        
        .header h1 {
            color: #1e3a8a;
            font-size: 28px;
            font-weight: bold;
            margin: 0 0 8px 0;
            text-transform: uppercase;
            letter-spacing: 3px;
        }
        
        .header h2 {
            color: #64748b;
            font-size: 18px;
            margin: 0 0 15px 0;
            font-weight: 300;
        }
        
        .header .subtitle {
            font-size: 14px;
            color: #475569;
            font-style: italic;
            margin: 0;
        }
        
        .booking-type-badge {
            display: inline-block;
            padding: 8px 20px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            border-radius: 25px;
            font-weight: 600;
            font-size: 14px;
            margin-top: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .pencil-badge {
            background: linear-gradient(135deg, #f59e0b, #d97706) !important;
        }
        
        .document-info {
            background: #f1f5f9;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            border-left: 4px solid #3b82f6;
            font-size: 11px;
        }
        
        .document-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        
        .document-info strong {
            color: #1e40af;
            font-weight: 600;
        }
        
        .booking-details {
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .receipt-number {
            font-size: 24px;
            font-weight: bold;
            color: #1e40af;
            font-family: monospace;
        }
        
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-confirmed { background-color: #dcfce7; color: #166534; }
        .status-pending { background-color: #fef3c7; color: #92400e; }
        .status-cancelled { background-color: #fee2e2; color: #dc2626; }
        .status-completed { background-color: #dbeafe; color: #1e40af; }
        
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }
        
        .detail-item {
            margin-bottom: 15px;
        }
        
        .detail-label {
            font-weight: 600;
            color: #374151;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
            display: block;
        }
        
        .detail-value {
            color: #1f2937;
            font-size: 13px;
            font-weight: 500;
        }
        
        .highlight-value {
            color: #1e40af;
            font-weight: 600;
            font-size: 14px;
        }
        
        .room-section {
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            border: 2px solid #3b82f6;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .room-title {
            color: #1e40af;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        
        .room-title i {
            margin-right: 8px;
            font-size: 18px;
        }
        
        .schedule-section {
            background: linear-gradient(135deg, #f0fdf4, #dcfce7);
            border: 2px solid #10b981;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .schedule-title {
            color: #059669;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        
        .schedule-title i {
            margin-right: 8px;
            font-size: 18px;
        }
        
        .date-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            padding: 10px;
            background: white;
            border-radius: 8px;
            border-left: 4px solid #10b981;
        }
        
        .date-item i {
            margin-right: 10px;
            color: #059669;
            width: 20px;
        }
        
        .guest-section {
            background: linear-gradient(135deg, #fefce8, #fef3c7);
            border: 2px solid #f59e0b;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .guest-title {
            color: #d97706;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        
        .guest-title i {
            margin-right: 8px;
            font-size: 18px;
        }
        
        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .contact-item i {
            margin-right: 10px;
            color: #d97706;
            width: 20px;
        }
        
        .amount-section {
            background: linear-gradient(135deg, #ecfdf5, #d1fae5);
            border: 2px solid #10b981;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .amount-title {
            color: #059669;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .amount-value {
            color: #047857;
            font-size: 28px;
            font-weight: bold;
            font-family: monospace;
        }
        
        .footer {
            position: fixed;
            bottom: 10mm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
        }
        
        .terms-section {
            background: #fef7cd;
            border: 1px solid #fbbf24;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            font-size: 11px;
        }
        
        .terms-title {
            font-weight: bold;
            color: #92400e;
            margin-bottom: 8px;
        }
        
        .terms-list {
            color: #78350f;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    ' . ($logoBase64 ? '<img src="' . $logoBase64 . '" class="watermark-logo" alt="BarCIE Logo Watermark">' : '') . '
    
    <div class="page-content">
    <div class="header">
        <h1>BarCIE International Center</h1>
        <h2>Booking Confirmation Receipt</h2>
        <p class="subtitle">Tempora Mutantur, Nos Et Mutamur In Illis</p>
        <div class="booking-type-badge' . ($type === 'pencil_booking' ? ' pencil-badge' : '') . '">' . $bookingTypeText . '</div>
    </div>
    
    <div class="document-info">
        <div class="document-info-grid">
            <div><strong>Generated:</strong> ' . $currentDateTime . '</div>
            <div><strong>Receipt #:</strong> ' . htmlspecialchars($booking['receipt_number']) . '</div>
            <div><strong>Document Type:</strong> ' . $bookingTypeText . '</div>
            <div><strong>Created:</strong> ' . $createdFormatted . '</div>
        </div>
    </div>
    
    <div class="booking-details">
        <div class="booking-header">
            <div class="receipt-number">' . htmlspecialchars($booking['receipt_number']) . '</div>
            <div class="status-badge ' . $statusBadgeClass . '">
                <i class="' . $statusIcon . '"></i> ' . htmlspecialchars($booking['status']) . '
            </div>
        </div>
        
        <div class="room-section">
            <div class="room-title">
                <i class="fas fa-bed"></i> Accommodation Details
            </div>
            <div class="detail-item">
                <span class="detail-label">Room/Facility</span>
                <span class="detail-value highlight-value">' . htmlspecialchars($roomDetails) . '</span>
            </div>
            <div class="details-grid">
                <div class="detail-item">
                    <span class="detail-label">Type</span>
                    <span class="detail-value">' . ucfirst(htmlspecialchars($booking['item_type'])) . '</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Occupants</span>
                    <span class="detail-value">' . htmlspecialchars($booking['occupants']) . ' person(s)</span>
                </div>
            </div>
        </div>
        
        <div class="schedule-section">
            <div class="schedule-title">
                <i class="fas fa-calendar-alt"></i> Schedule Information
            </div>
            <div class="date-item">
                <i class="fas fa-sign-in-alt"></i>
                <div>
                    <strong>Check-in:</strong> ' . $checkInFormatted . '
                </div>
            </div>
            <div class="date-item">
                <i class="fas fa-sign-out-alt"></i>
                <div>
                    <strong>Check-out:</strong> ' . $checkOutFormatted . '
                </div>
            </div>
            <div class="date-item">
                <i class="fas fa-clock"></i>
                <div>
                    <strong>Duration:</strong> ' . $durationText . '
                </div>
            </div>
        </div>
        
        <div class="guest-section">
            <div class="guest-title">
                <i class="fas fa-user"></i> Guest Information
            </div>
            <div class="details-grid">
                <div>
                    <div class="detail-item">
                        <span class="detail-label">Guest Name</span>
                        <span class="detail-value highlight-value">' . htmlspecialchars($booking['guest_name']) . '</span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <span>' . htmlspecialchars($booking['guest_phone']) . '</span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <span>' . htmlspecialchars($booking['guest_email']) . '</span>
                    </div>
                </div>
                <div>';

    if (!empty($booking['company'])) {
        $html .= '
                    <div class="detail-item">
                        <span class="detail-label">Company</span>
                        <span class="detail-value">' . htmlspecialchars($booking['company']) . '</span>
                    </div>';
    }

    if (!empty($booking['company_contact'])) {
        $html .= '
                    <div class="detail-item">
                        <span class="detail-label">Company Contact</span>
                        <span class="detail-value">' . htmlspecialchars($booking['company_contact']) . '</span>
                    </div>';
    }

    $html .= '
                    <div class="detail-item">
                        <span class="detail-label">Payment Method</span>
                        <span class="detail-value">' . $paymentMethod . '</span>
                    </div>
                </div>
            </div>
        </div>';

    if (!empty($booking['total_amount']) && $booking['total_amount'] > 0) {
        $html .= '
        <div class="amount-section">
            <div class="amount-title">Total Amount</div>
            <div class="amount-value">₱' . number_format($booking['total_amount'], 2) . '</div>
        </div>';
    }

    if ($type === 'pencil_booking') {
        $html .= '
        <div class="terms-section">
            <div class="terms-title">📋 Pencil Booking Terms & Conditions</div>
            <div class="terms-list">
                • This is a <strong>draft reservation</strong> and requires confirmation within 2 weeks<br>
                • Payment must be completed to confirm your reservation<br>
                • Reserved slot may be released if not confirmed within the timeframe<br>
                • Please contact BarCIE for confirmation and payment arrangements
            </div>
        </div>';
    } else {
        $html .= '
        <div class="terms-section">
            <div class="terms-title">📋 Booking Terms & Conditions</div>
            <div class="terms-list">
                • Please arrive on time for your scheduled check-in<br>
                • Cancellations must be made at least 24 hours in advance<br>
                • Additional charges may apply for extra services<br>
                • Please present this confirmation upon arrival
            </div>
        </div>';
    }

    $html .= '
    </div>
    
    <div class="footer">
        <div>BarCIE International Center - Barangay Center for Innovative Education © 2000</div>
        <div>For inquiries, please contact us at your convenience</div>
        <div>Generated on ' . $currentDateTime . '</div>
    </div>
    
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
    $filename = 'BarCIE_' . ($type === 'pencil_booking' ? 'Pencil_Booking' : 'Booking_Confirmation') . '_' . $booking['receipt_number'] . '_' . date('Y-m-d') . '.pdf';

    // Output PDF
    $dompdf->stream($filename, array('Attachment' => true));

} catch (Exception $e) {
    error_log("Booking PDF Generation Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to generate PDF: ' . $e->getMessage()
    ]);
}
?>