<?php
/**
 * Pencil Booking Conversion Page
 * This page loads pencil booking data and pre-fills the reservation form
 */

session_start();

// Include database connection
require_once __DIR__ . '/database/db_connect.php';

// Get and validate token
$token = $_GET['token'] ?? '';

if (empty($token)) {
    die('Invalid or missing token. Please check the link in your email.');
}

// Fetch pencil booking data using the token
$stmt = $conn->prepare("SELECT * FROM pencil_bookings WHERE conversion_token = ? AND token_expires_at > NOW() AND status = 'pending' LIMIT 1");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    die('This conversion link has expired or is invalid. Please contact us for assistance.');
}

$pencil_booking = $result->fetch_assoc();
$stmt->close();

// Get room details
$room_stmt = $conn->prepare("SELECT id, name, item_type, price, capacity FROM items WHERE id = ?");
$room_stmt->bind_param("i", $pencil_booking['room_id']);
$room_stmt->execute();
$room_result = $room_stmt->get_result();
$room_data = $room_result->fetch_assoc();
$room_stmt->close();

if (!$room_data) {
    die('Room/facility not found. Please contact us for assistance.');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Reservation - BarCIE International Center</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .conversion-container {
            max-width: 800px;
            margin: 50px auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .header-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px;
            text-align: center;
            color: white;
        }

        .header-section h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }

        .header-section p {
            margin: 10px 0 0 0;
            opacity: 0.95;
        }

        .content-section {
            padding: 40px;
        }

        .info-card {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin-bottom: 25px;
            border-radius: 8px;
        }

        .info-row {
            display: flex;
            margin-bottom: 12px;
        }

        .info-label {
            font-weight: 600;
            color: #495057;
            min-width: 180px;
        }

        .info-value {
            color: #212529;
        }

        .alert-info {
            background-color: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
        }

        .btn-complete {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 15px 40px;
            font-size: 18px;
            font-weight: 600;
            border-radius: 8px;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .btn-complete:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
            color: white;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 15px;
            background-color: #ffc107;
            color: #000;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        .expiry-warning {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="conversion-container">
        <div class="header-section">
            <h1><i class="fas fa-check-circle me-2"></i>Complete Your Reservation</h1>
            <p>Convert your draft booking to a full reservation</p>
        </div>

        <div class="content-section">
            <!-- Expiry Warning -->
            <?php
            $days_remaining = ceil((strtotime($pencil_booking['token_expires_at']) - time()) / (60 * 60 * 24));
            if ($days_remaining <= 7) {
                echo '<div class="expiry-warning">';
                echo '<i class="fas fa-exclamation-triangle me-2"></i>';
                echo '<strong>Hurry!</strong> Your draft reservation expires in <strong>' . $days_remaining . ' day' . ($days_remaining != 1 ? 's' : '') . '</strong>.';
                echo '</div>';
            }
            ?>

            <!-- Booking Information -->
            <div class="info-card">
                <h5 class="mb-3">
                    <i class="fas fa-file-alt me-2" style="color: #667eea;"></i>
                    Draft Reservation Details
                </h5>

                <div class="info-row">
                    <span class="info-label">Booking Number:</span>
                    <span
                        class="info-value"><strong><?php echo htmlspecialchars($pencil_booking['receipt_no']); ?></strong></span>
                </div>

                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-value"><span class="status-badge">Draft - Awaiting Confirmation</span></span>
                </div>

                <div class="info-row">
                    <span class="info-label">Guest Name:</span>
                    <span class="info-value"><?php echo htmlspecialchars($pencil_booking['guest_name']); ?></span>
                </div>

                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value"><?php echo htmlspecialchars($pencil_booking['email']); ?></span>
                </div>

                <div class="info-row">
                    <span class="info-label">Contact:</span>
                    <span class="info-value"><?php echo htmlspecialchars($pencil_booking['contact_number']); ?></span>
                </div>

                <div class="info-row">
                    <span class="info-label">Room/Facility:</span>
                    <span class="info-value"><strong><?php echo htmlspecialchars($room_data['name']); ?></strong></span>
                </div>

                <div class="info-row">
                    <span class="info-label">Check-in:</span>
                    <span
                        class="info-value"><?php echo date('F j, Y g:i A', strtotime($pencil_booking['checkin'])); ?></span>
                </div>

                <div class="info-row">
                    <span class="info-label">Check-out:</span>
                    <span
                        class="info-value"><?php echo date('F j, Y g:i A', strtotime($pencil_booking['checkout'])); ?></span>
                </div>

                <div class="info-row">
                    <span class="info-label">Number of Occupants:</span>
                    <span class="info-value"><?php echo htmlspecialchars($pencil_booking['occupants']); ?>
                        person<?php echo $pencil_booking['occupants'] != 1 ? 's' : ''; ?></span>
                </div>

                <div class="info-row">
                    <span class="info-label">Estimated Price:</span>
                    <span
                        class="info-value"><strong>₱<?php echo number_format($pencil_booking['total_price'], 2); ?></strong></span>
                </div>

                <?php if (!empty($pencil_booking['company'])): ?>
                    <div class="info-row">
                        <span class="info-label">Company:</span>
                        <span class="info-value"><?php echo htmlspecialchars($pencil_booking['company']); ?></span>
                    </div>
                <?php endif; ?>

                <div class="info-row">
                    <span class="info-label">Expires On:</span>
                    <span class="info-value" style="color: #dc3545; font-weight: 600;">
                        <?php echo date('F j, Y g:i A', strtotime($pencil_booking['token_expires_at'])); ?>
                    </span>
                </div>
            </div>

            <!-- Next Steps -->
            <div class="alert alert-info mb-4">
                <h6><i class="fas fa-info-circle me-2"></i>Next Steps:</h6>
                <ol class="mb-0 ps-3">
                    <li>Click the button below to proceed to the full reservation form</li>
                    <li>Review and confirm your booking details</li>
                    <li>Complete the payment process</li>
                    <li>Receive your final booking confirmation</li>
                </ol>
            </div>

            <!-- Action Buttons -->
            <div class="text-center">
                <form id="conversionForm" method="POST" action="Guest.php#booking">
                    <input type="hidden" name="convert_from_pencil" value="1">
                    <input type="hidden" name="pencil_id" value="<?php echo $pencil_booking['id']; ?>">
                    <input type="hidden" name="room_id" value="<?php echo $pencil_booking['room_id']; ?>">
                    <input type="hidden" name="guest_name"
                        value="<?php echo htmlspecialchars($pencil_booking['guest_name']); ?>">
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($pencil_booking['email']); ?>">
                    <input type="hidden" name="contact_number"
                        value="<?php echo htmlspecialchars($pencil_booking['contact_number']); ?>">
                    <input type="hidden" name="checkin" value="<?php echo $pencil_booking['checkin']; ?>">
                    <input type="hidden" name="checkout" value="<?php echo $pencil_booking['checkout']; ?>">
                    <input type="hidden" name="occupants" value="<?php echo $pencil_booking['occupants']; ?>">
                    <input type="hidden" name="company"
                        value="<?php echo htmlspecialchars($pencil_booking['company'] ?? ''); ?>">
                    <input type="hidden" name="company_contact"
                        value="<?php echo htmlspecialchars($pencil_booking['company_contact'] ?? ''); ?>">

                    <button type="submit" class="btn btn-complete">
                        <i class="fas fa-arrow-right me-2"></i>
                        Proceed to Full Reservation
                    </button>
                </form>

                <p class="mt-3 text-muted">
                    <small>Your information will be pre-filled in the reservation form</small>
                </p>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Add smooth scroll animation when redirecting
        document.getElementById('conversionForm').addEventListener('submit', function () {
            // Store a flag so Guest.php knows to scroll to booking section and pre-fill
            sessionStorage.setItem('convertingFromPencil', 'true');
        });
    </script>
</body>

</html>
<?php $conn->close(); ?>