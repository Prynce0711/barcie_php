<?php
require_once '../database/config.php';
require_once '../database/db_connect.php';
require_once '../database/user_auth.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$receipt = $_GET['receipt'] ?? '';
$email = $_GET['email'] ?? '';
$type = $_GET['type'] ?? 'booking'; // 'booking' or 'pencil'
$confirm = $_GET['confirm'] ?? '';

if (empty($receipt) || empty($email)) {
    die('Invalid request. Receipt number and email are required.');
}

// Verify the booking exists and belongs to this email
$table = ($type === 'pencil') ? 'pencil_bookings' : 'bookings';

if ($type === 'pencil') {
    // For pencil_bookings: has 'email' column and 'receipt_no' column
    $query = "SELECT * FROM {$table} WHERE receipt_no = ? AND email = ?";
} else {
    // For bookings: email is stored in 'details' field, use 'receipt_no' column
    $query = "SELECT * FROM {$table} WHERE receipt_no = ? AND details LIKE ?";
}

$stmt = $pdo->prepare($query);
if ($type === 'pencil') {
    $stmt->execute([$receipt, $email]);
} else {
    // For regular bookings, search for email in details field
    $stmt->execute([$receipt, "%Email: $email%"]);
}
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    die('Booking not found or email does not match.');
}

// Extract guest name from details field or use column for display
$guest_name = 'Guest';
if ($type === 'pencil' && !empty($booking['guest_name'])) {
    $guest_name = $booking['guest_name'];
} elseif (!empty($booking['details']) && preg_match('/Guest:\s*([^|]+)/', $booking['details'], $matches)) {
    $guest_name = trim($matches[1]);
}

// Check if booking is already cancelled
if (stripos($booking['status'], 'cancelled') !== false) {
    die('This booking has already been cancelled.');
}

// Check if it's too late to cancel (less than 48 hours before check-in)
$checkin_field = 'checkin'; // Both tables use 'checkin' column
$checkin_time = strtotime($booking[$checkin_field]);
$now = time();
$hours_until_checkin = ($checkin_time - $now) / 3600;

if ($hours_until_checkin < 48 && $confirm !== 'yes') {
    $cancellation_allowed = false;
    $warning_message = 'Your check-in is in less than 48 hours. Cancellations within 48 hours may not be eligible for refund.';
} else {
    $cancellation_allowed = true;
    $warning_message = '';
}

// If confirm=yes, process the cancellation
if ($confirm === 'yes') {
    try {
        // Update booking status to cancelled
        $update_query = "UPDATE {$table} SET status = 'cancelled', updated_at = NOW() WHERE receipt_no = ?";
        $update_stmt = $pdo->prepare($update_query);
        $update_stmt->execute([$receipt]);
        
        // If it's a regular booking, update room status back to available
        if ($type !== 'pencil' && !empty($booking['room_id'])) {
            $room_update = "UPDATE items SET room_status = 'available' WHERE id = ?";
            $room_stmt = $pdo->prepare($room_update);
            $room_stmt->execute([$booking['room_id']]);
        }
        
        // Send cancellation confirmation email
        if (!empty($email)) {
            $subject = "Booking Cancellation Confirmation - BarCIE International Center";
            $booking_type = ($type === 'pencil') ? 'Pencil Booking' : 'Booking';
            
            // Extract guest name from details field or use column
            $guest_name = 'Guest';
            if ($type === 'pencil' && !empty($booking['guest_name'])) {
                $guest_name = $booking['guest_name'];
            } elseif (!empty($booking['details']) && preg_match('/Guest:\s*([^|]+)/', $booking['details'], $matches)) {
                $guest_name = trim($matches[1]);
            }
            
            $emailContent = '
                <div style="text-align: center; margin-bottom: 30px;">
                    <div style="display: inline-block; padding: 12px 28px; background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; border-radius: 50px; font-size: 15px; font-weight: 700; box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);">
                        ❌ BOOKING CANCELLED
                    </div>
                </div>
                
                <h2 style="margin: 0 0 15px 0; color: #212529; font-size: 26px; font-weight: 700; text-align: center;">Cancellation Confirmed</h2>
                <p style="margin: 0 0 25px 0; color: #6c757d; font-size: 14px; text-align: center;">
                    Receipt #<strong style="color: #dc3545;">' . htmlspecialchars($receipt) . '</strong>
                </p>
                
                <p style="margin: 0 0 20px 0; color: #495057; font-size: 16px; line-height: 1.6;">
                    Dear <strong style="color: #1e3c72;">' . htmlspecialchars($guest_name) . '</strong>,
                </p>
                <p style="margin: 0 0 30px 0; color: #495057; font-size: 15px; line-height: 1.7;">
                    Your ' . $booking_type . ' has been successfully cancelled as per your request.
                </p>
                
                <div style="background-color: #f8d7da; border-left: 5px solid #dc3545; padding: 20px 25px; margin-bottom: 25px; border-radius: 8px;">
                    <h4 style="margin: 0 0 12px 0; color: #721c24; font-size: 16px; font-weight: 700;">
                        📋 Cancelled Booking Details
                    </h4>
                    <ul style="margin: 0; padding-left: 20px; color: #721c24; font-size: 14px; line-height: 1.8;">
                        <li><strong>Receipt Number:</strong> ' . htmlspecialchars($receipt) . '</li>
                        <li><strong>Guest Name:</strong> ' . htmlspecialchars($guest_name) . '</li>
                        <li><strong>Cancellation Date:</strong> ' . date('F j, Y g:i A') . '</li>
                    </ul>
                </div>
                
                <div style="background-color: #d1ecf1; border-left: 5px solid #17a2b8; padding: 20px 25px; margin-bottom: 25px; border-radius: 8px;">
                    <p style="margin: 0 0 10px 0; color: #0c5460; font-size: 15px; font-weight: 600;">
                        💡 What Happens Next?
                    </p>
                    <p style="margin: 0; color: #0c5460; font-size: 14px; line-height: 1.6;">
                        If you made a payment, our team will process your refund according to our cancellation policy. 
                        For questions about refunds, please contact us with your receipt number.
                    </p>
                </div>
                
                <p style="margin: 0 0 15px 0; color: #495057; font-size: 15px; line-height: 1.7; text-align: center;">
                    We hope to serve you in the future. If you need to make a new reservation, please visit our website.
                </p>';
            
            $emailBody = create_email_template('Booking Cancellation', $emailContent, 'This is an automated confirmation message.');
            send_smtp_mail($email, $subject, $emailBody);
        }
        
        $success = true;
    } catch (Exception $e) {
        $success = false;
        $error_message = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancel Booking - BarCIE International Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .cancel-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 90%;
            padding: 40px;
        }
        .icon-warning {
            font-size: 64px;
            color: #ffc107;
            animation: pulse 2s infinite;
        }
        .icon-success {
            font-size: 64px;
            color: #28a745;
        }
        .icon-error {
            font-size: 64px;
            color: #dc3545;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        .btn-cancel {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-cancel:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(220, 53, 69, 0.4);
        }
        .booking-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="cancel-card">
        <?php if (isset($success) && $success): ?>
            <!-- Success State -->
            <div class="text-center">
                <i class="fas fa-check-circle icon-success mb-3"></i>
                <h2 class="mb-3">Cancellation Successful</h2>
                <p class="text-muted mb-4">Your booking has been cancelled successfully.</p>
                <div class="alert alert-success">
                    <strong>Receipt #:</strong> <?= htmlspecialchars($receipt) ?>
                </div>
                <p class="mb-4">A confirmation email has been sent to <strong><?= htmlspecialchars($email) ?></strong></p>
                <a href="../../Guest.php" class="btn btn-primary">
                    <i class="fas fa-home me-2"></i>Return to Home
                </a>
            </div>
        <?php elseif (isset($success) && !$success): ?>
            <!-- Error State -->
            <div class="text-center">
                <i class="fas fa-times-circle icon-error mb-3"></i>
                <h2 class="mb-3">Cancellation Failed</h2>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error_message ?? 'An error occurred while processing your cancellation.') ?>
                </div>
                <a href="../../Guest.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Go Back
                </a>
            </div>
        <?php else: ?>
            <!-- Confirmation State -->
            <div class="text-center">
                <i class="fas fa-exclamation-triangle icon-warning mb-3"></i>
                <h2 class="mb-3">Cancel Booking</h2>
                <p class="text-muted mb-4">Are you sure you want to cancel this booking?</p>
            </div>
            
            <div class="booking-info">
                <h5 class="mb-3"><i class="fas fa-info-circle text-primary me-2"></i>Booking Details</h5>
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Receipt Number:</td>
                        <td class="fw-bold"><?= htmlspecialchars($receipt) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Guest Name:</td>
                        <td class="fw-bold"><?= htmlspecialchars($guest_name) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Email:</td>
                        <td class="fw-bold"><?= htmlspecialchars($email) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status:</td>
                        <td><span class="badge bg-warning"><?= htmlspecialchars($booking['status']) ?></span></td>
                    </tr>
                </table>
            </div>
            
            <?php if (!$cancellation_allowed): ?>
                <div class="alert alert-warning mb-4">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?= $warning_message ?>
                </div>
            <?php endif; ?>
            
            <div class="alert alert-info mb-4">
                <strong><i class="fas fa-info-circle me-2"></i>Cancellation Policy:</strong><br>
                • Cancellations made 48+ hours before check-in: Full refund<br>
                • Cancellations made within 48 hours: No refund<br>
                • This action cannot be undone
            </div>
            
            <div class="d-grid gap-2">
                <a href="?receipt=<?= urlencode($receipt) ?>&email=<?= urlencode($email) ?>&type=<?= urlencode($type) ?>&confirm=yes" 
                   class="btn btn-danger btn-cancel">
                    <i class="fas fa-times-circle me-2"></i>Yes, Cancel This Booking
                </a>
                <a href="../../Guest.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>No, Keep My Booking
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
