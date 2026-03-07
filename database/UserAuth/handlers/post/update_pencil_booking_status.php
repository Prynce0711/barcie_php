<?php
/* ---------------------------
   UPDATE PENCIL BOOKING STATUS
   --------------------------- */
if ($action === 'update_pencil_booking_status') {
    // Only Front Desk (admin), managers and super_admin can update pencil booking status - staff CANNOT
    require_once __DIR__ . '/role_check.php';
    if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        echo json_encode(['success' => false, 'message' => 'Access denied. Admin login required.']);
        exit;
    }
    $role = $_SESSION['admin_role'] ?? 'staff';
    if (!in_array($role, ['admin', 'manager', 'super_admin'], true)) {
        echo json_encode(['success' => false, 'message' => 'You do not have permission to modify pencil bookings']);
        exit;
    }

    header('Content-Type: application/json');

    $booking_id = (int) ($_POST['booking_id'] ?? 0);
    $new_status = $_POST['status'] ?? '';

    if ($booking_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid booking ID']);
        exit;
    }

    $allowed_statuses = ['pending', 'approved', 'confirmed', 'cancelled', 'rejected', 'expired'];
    if (!in_array($new_status, $allowed_statuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        exit;
    }

    try {
        // Get booking details first
        $stmt = $conn->prepare("SELECT * FROM pencil_bookings WHERE id = ?");
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $booking = $result->fetch_assoc();
        $stmt->close();

        if (!$booking) {
            echo json_encode(['success' => false, 'message' => 'Pencil booking not found']);
            exit;
        }

        // Get admin ID from session
        $admin_id = isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null;

        // Update the status
        if ($new_status === 'confirmed') {
            // When confirmed, set confirmed_at timestamp
            $update_stmt = $conn->prepare("UPDATE pencil_bookings SET status = ?, confirmed_at = NOW() WHERE id = ?");
            $update_stmt->bind_param("si", $new_status, $booking_id);
        } elseif ($new_status === 'approved' && $admin_id) {
            // When approved, set approved_by and approved_at
            $update_stmt = $conn->prepare("UPDATE pencil_bookings SET status = ?, approved_by = ?, approved_at = NOW() WHERE id = ?");
            $update_stmt->bind_param("sii", $new_status, $admin_id, $booking_id);
        } else {
            $update_stmt = $conn->prepare("UPDATE pencil_bookings SET status = ? WHERE id = ?");
            $update_stmt->bind_param("si", $new_status, $booking_id);
        }

        if ($update_stmt->execute()) {
            // Send confirmation email if status is confirmed
            if ($new_status === 'confirmed' && !empty($booking['email'])) {
                $subject = "Pencil Booking Confirmed - BarCIE International Center";
                $emailContent = '
                    <h2 style="margin: 0 0 20px 0; color: #28a745; font-size: 24px; font-weight: 600;">✅ Your Pencil Booking is Confirmed!</h2>
                    <p style="margin: 0 0 20px 0; color: #495057; font-size: 16px; line-height: 1.6;">
                        Dear <strong>' . htmlspecialchars($booking['guest_name']) . '</strong>,
                    </p>
                    <p style="margin: 0 0 25px 0; color: #495057; font-size: 15px; line-height: 1.6;">
                        Great news! Your pencil booking has been <strong>CONFIRMED</strong>. Your reservation is now secured.
                    </p>
                    <div style="background-color: #d4edda; border-left: 4px solid #28a745; padding: 15px 20px; margin-bottom: 25px; border-radius: 4px;">
                        <p style="margin: 0; color: #155724; font-size: 15px; font-weight: 600;">
                            ✓ Reservation Confirmed
                        </p>
                        <p style="margin: 5px 0 0 0; color: #155724; font-size: 14px;">
                            Your booking number: <strong>' . htmlspecialchars($booking['receipt_no']) . '</strong>
                        </p>
                    </div>
                    <p style="margin: 0; color: #495057; font-size: 15px; line-height: 1.6;">
                        We look forward to welcoming you at BarCIE International Center!
                    </p>';

                $emailBody = create_email_template('Booking Confirmed', $emailContent);
                send_smtp_mail($booking['email'], $subject, $emailBody);
            }

            // Send rejection email if status is rejected
            if ($new_status === 'rejected' && !empty($booking['email'])) {
                $subject = "Pencil Booking Update - BarCIE International Center";
                $emailContent = '
                    <h2 style="margin: 0 0 20px 0; color: #dc3545; font-size: 24px; font-weight: 600;">Pencil Booking Update</h2>
                    <p style="margin: 0 0 20px 0; color: #495057; font-size: 16px; line-height: 1.6;">
                        Dear <strong>' . htmlspecialchars($booking['guest_name']) . '</strong>,
                    </p>
                    <p style="margin: 0 0 25px 0; color: #495057; font-size: 15px; line-height: 1.6;">
                        We regret to inform you that your pencil booking (Receipt: <strong>' . htmlspecialchars($booking['receipt_no']) . '</strong>) could not be confirmed at this time.
                    </p>
                    <p style="margin: 0; color: #495057; font-size: 15px; line-height: 1.6;">
                        Please contact us for alternative dates or rooms. We apologize for any inconvenience.
                    </p>';

                $emailBody = create_email_template('Booking Update', $emailContent);
                send_smtp_mail($booking['email'], $subject, $emailBody);
            }

            echo json_encode(['success' => true, 'message' => 'Pencil booking status updated to ' . $new_status]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update status: ' . $update_stmt->error]);
        }

        $update_stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }

    exit;
}


