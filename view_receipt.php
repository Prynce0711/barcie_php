<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Receipt - BarCIE International Center</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; padding: 20px; }
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        
        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .receipt-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .receipt-header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .receipt-header p {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .receipt-body {
            padding: 40px;
        }
        
        .receipt-number {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            color: #1e3c72;
            margin-bottom: 30px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 6px;
        }
        
        .info-section {
            margin-bottom: 30px;
        }
        
        .info-section h3 {
            color: #1e3c72;
            border-bottom: 2px solid #1e3c72;
            padding-bottom: 10px;
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .info-row {
            display: flex;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .info-label {
            flex: 0 0 200px;
            font-weight: 600;
            color: #666;
        }
        
        .info-value {
            flex: 1;
            color: #333;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d4edda; color: #155724; }
        .status-approved { background: #d1ecf1; color: #0c5460; }
        
        .price-summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            margin-top: 20px;
        }
        
        .price-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
        }
        
        .price-row.total {
            border-top: 2px solid #1e3c72;
            margin-top: 10px;
            padding-top: 15px;
            font-size: 20px;
            font-weight: bold;
            color: #1e3c72;
        }
        
        .receipt-footer {
            background: #f8f9fa;
            padding: 30px;
            text-align: center;
            color: #666;
            font-size: 13px;
        }
        
        .action-buttons {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 30px;
            margin: 0 10px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #1e3c72;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2a5298;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>
<?php
require_once __DIR__ . '/database/db_connect.php';

$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$booking_type = isset($_GET['type']) ? $_GET['type'] : 'booking';

if ($booking_id <= 0) {
    die('<div class="receipt-container"><div class="receipt-body"><h2>Invalid Receipt ID</h2></div></div>');
}

// Fetch booking details
if ($booking_type === 'pencil') {
    $stmt = $conn->prepare("
        SELECT pb.*, i.name as room_name, i.room_number, i.capacity, i.price as room_price, i.item_type
        FROM pencil_bookings pb
        LEFT JOIN items i ON pb.room_id = i.id
        WHERE pb.id = ?
    ");
} else {
    $stmt = $conn->prepare("
        SELECT b.*, i.name as room_name, i.room_number, i.capacity, i.price as room_price, i.item_type
        FROM bookings b
        LEFT JOIN items i ON b.room_id = i.id
        WHERE b.id = ?
    ");
}

$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();
$stmt->close();

if (!$booking) {
    die('<div class="receipt-container"><div class="receipt-body"><h2>Booking Not Found</h2></div></div>');
}

// Extract guest details
if ($booking_type === 'pencil') {
    $guest_name = $booking['guest_name'];
    $guest_email = $booking['email'];
    $guest_contact = $booking['contact_number'];
    $receipt_no = $booking['receipt_no'];
} else {
    $guest_name = '';
    $guest_email = '';
    $guest_contact = '';
    
    if (preg_match('/Guest:\s*([^|]+)/', $booking['details'], $m)) $guest_name = trim($m[1]);
    if (preg_match('/Email:\s*([^|]+)/', $booking['details'], $m)) $guest_email = trim($m[1]);
    if (preg_match('/Contact:\s*([^|]+)/', $booking['details'], $m)) $guest_contact = trim($m[1]);
    
    $receipt_no = $booking['receipt_no'] ?? 'BARCIE-' . date('Ymd', strtotime($booking['created_at'])) . '-' . str_pad($booking['id'], 4, '0', STR_PAD_LEFT);
}

$room_display = $booking['room_name'] ?? 'N/A';
if (!empty($booking['room_number'])) {
    $room_display .= ' (Room #' . $booking['room_number'] . ')';
}

$status_class = 'status-pending';
if ($booking['status'] === 'confirmed') $status_class = 'status-confirmed';
elseif ($booking['status'] === 'approved') $status_class = 'status-approved';
?>

<div class="receipt-container">
    <div class="receipt-header">
        <h1>BarCIE International Center</h1>
        <p>La Consolacion University Philippines</p>
        <p>Official Booking Receipt</p>
    </div>
    
    <div class="action-buttons no-print">
        <button class="btn btn-primary" onclick="window.print()">
            <i class="fas fa-print"></i> Print Receipt
        </button>
        <button class="btn btn-secondary" onclick="downloadPDF()">
            <i class="fas fa-download"></i> Download PDF
        </button>
        <a href="bank_qr.php" target="_blank" class="btn" style="background: #28a745; color: white; margin-left: 10px;">
            <i class="fas fa-qrcode"></i> View Payment QR Code
        </a>
    </div>
    
    <div class="receipt-body">
        <div class="receipt-number">
            Receipt No: <?= htmlspecialchars($receipt_no) ?>
        </div>
        
        <div class="info-section">
            <h3>Guest Information</h3>
            <div class="info-row">
                <div class="info-label">Name:</div>
                <div class="info-value"><?= htmlspecialchars($guest_name) ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Email:</div>
                <div class="info-value"><?= htmlspecialchars($guest_email) ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Contact Number:</div>
                <div class="info-value"><?= htmlspecialchars($guest_contact) ?></div>
            </div>
            <?php if (!empty($booking['company'])): ?>
            <div class="info-row">
                <div class="info-label">Company:</div>
                <div class="info-value"><?= htmlspecialchars($booking['company']) ?></div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="info-section">
            <h3>Booking Details</h3>
            <div class="info-row">
                <div class="info-label">Room/Facility:</div>
                <div class="info-value"><?= htmlspecialchars($room_display) ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Check-in:</div>
                <div class="info-value"><?= date('F j, Y g:i A', strtotime($booking['checkin'])) ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Check-out:</div>
                <div class="info-value"><?= date('F j, Y g:i A', strtotime($booking['checkout'])) ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Number of Occupants:</div>
                <div class="info-value"><?= htmlspecialchars($booking['occupants'] ?? 'N/A') ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Booking Status:</div>
                <div class="info-value">
                    <span class="status-badge <?= $status_class ?>">
                        <?= ucfirst(str_replace('_', ' ', $booking['status'])) ?>
                    </span>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Booking Date:</div>
                <div class="info-value"><?= date('F j, Y g:i A', strtotime($booking['created_at'])) ?></div>
            </div>
        </div>
        
        <?php if ($booking_type === 'pencil'): ?>
        <div class="info-section">
            <div class="info-row">
                <div class="info-label">Expiration Date:</div>
                <div class="info-value" style="color: #dc3545; font-weight: 600;">
                    <?= date('F j, Y', strtotime($booking['expires_at'])) ?>
                </div>
            </div>
            <p style="color: #856404; background: #fff3cd; padding: 15px; border-radius: 5px; margin-top: 15px;">
                <strong>Note:</strong> This is a pencil booking (draft reservation). Please confirm and complete payment within 14 days to secure your reservation.
            </p>
        </div>
        <?php endif; ?>
        
        <div class="price-summary">
            <h3 style="margin-bottom: 15px;">Payment Summary</h3>
            <div class="price-row">
                <span>Base Price:</span>
                <span>₱<?= number_format($booking_type === 'pencil' ? ($booking['base_price'] ?? 0) : ($booking['room_price'] ?? 0), 2) ?></span>
            </div>
            <?php if (isset($booking['discount_amount']) && $booking['discount_amount'] > 0): ?>
            <div class="price-row" style="color: #28a745;">
                <span>Discount:</span>
                <span>- ₱<?= number_format($booking['discount_amount'], 2) ?></span>
            </div>
            <?php endif; ?>
            <div class="price-row total">
                <span>Total Amount:</span>
                <span>₱<?= number_format($booking_type === 'pencil' ? ($booking['total_price'] ?? 0) : ($booking['room_price'] ?? 0), 2) ?></span>
            </div>
        </div>
    </div>
    
    <div class="receipt-footer">
        <p><strong>BarCIE International Center</strong></p>
        <p>La Consolacion University Philippines</p>
        <p>Email: pc.clemente11@gmail.com</p>
        <p style="margin-top: 15px; font-size: 12px;">
            This is an official receipt generated on <?= date('F j, Y g:i A') ?>
        </p>
        <p style="font-size: 12px;">Please present this receipt upon check-in.</p>
    </div>
</div>

<script>
function downloadPDF() {
    alert('Please use Print and select "Save as PDF" as your printer option.');
    window.print();
}
</script>

</body>
</html>
