<?php
/* ---------------------------
   ADMIN: update payment verification status
   --------------------------- */
if ($action === 'admin_update_payment') {
    // Require login and roles: Front Desk (admin) and above can verify payments
    require_once __DIR__ . '/../../../role_check.php';
    if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        handleResponse('Access denied. Admin login required.', false, '../dashboard.php');
    }
    // Enforce role permission for payment verification
    page_require_roles(['admin', 'manager', 'super_admin'], '../dashboard.php', 'You do not have permission to verify payments');

    $bookingId = (int) ($_POST['booking_id'] ?? 0);
    $paymentAction = $_POST['payment_action'] ?? ''; // 'verify' or 'reject'

    if (!in_array($paymentAction, ['verify', 'reject'])) {
        $_SESSION['msg'] = "Unknown payment action.";
        redirect('../dashboard.php');
    }

    $newPaymentStatus = $paymentAction === 'verify' ? 'verified' : 'rejected';

    // Get booking details first including room_id
    $booking_stmt = $conn->prepare("SELECT details, proof_of_payment, payment_status, room_id FROM bookings WHERE id = ?");
    $booking_stmt->bind_param("i", $bookingId);
    $booking_stmt->execute();
    $booking_result = $booking_stmt->get_result();
    $booking_data = $booking_result->fetch_assoc();
    $booking_stmt->close();

    // Update payment status and set audit trail (who verified and when)
    $admin_id = isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : 0;
    // Update payment status and touch updated_at so activity feed sees the event
    // Also update booking status to 'approved' when payment is verified
    if ($paymentAction === 'verify') {
        $stmt = $conn->prepare("UPDATE bookings SET payment_status = ?, payment_verified_by = ?, payment_verified_at = NOW(), status = 'approved', updated_at = NOW() WHERE id = ?");
    } else {
        // When rejecting, also update booking status to 'rejected'
        $stmt = $conn->prepare("UPDATE bookings SET payment_status = ?, payment_verified_by = ?, payment_verified_at = NOW(), status = 'rejected', updated_at = NOW() WHERE id = ?");
    }
    $stmt->bind_param("sii", $newPaymentStatus, $admin_id, $bookingId);
    $success = $stmt->execute();
    $stmt->close();

    // If payment is verified, mark room as occupied
    if ($success && $paymentAction === 'verify' && !empty($booking_data['room_id'])) {
        $room_id = (int) $booking_data['room_id'];
        $update_room = $conn->prepare("UPDATE items SET room_status = 'occupied' WHERE id = ?");
        $update_room->bind_param("i", $room_id);
        $update_room->execute();
        $update_room->close();
        error_log("Room/Facility ID $room_id set to occupied after payment verification for booking ID $bookingId");
    }

    // If payment is rejected, ensure room stays available
    if ($success && $paymentAction === 'reject' && !empty($booking_data['room_id'])) {
        $room_id = (int) $booking_data['room_id'];

        // Check if there are any other active bookings for this room
        $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM bookings WHERE room_id = ? AND id != ? AND status IN ('pending', 'approved', 'confirmed', 'checked_in')");
        $check_stmt->bind_param("ii", $room_id, $bookingId);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $check_data = $check_result->fetch_assoc();
        $check_stmt->close();

        // Only set to available if no other active bookings exist
        if ($check_data['count'] == 0) {
            $update_room = $conn->prepare("UPDATE items SET room_status = 'available' WHERE id = ?");
            $update_room->bind_param("i", $room_id);
            $update_room->execute();
            $update_room->close();
            error_log("Room/Facility ID $room_id set to available after payment rejection for booking ID $bookingId");
        }
    }

    if ($success && $booking_data) {
        // Extract guest info from details
        $details = $booking_data['details'];
        $guest_email = '';
        $guest_name = 'Guest';
        $receipt_no = '';

        if (preg_match('/Email:\s*([^|]+)/', $details, $matches)) {
            $guest_email = trim($matches[1]);
        }
        if (preg_match('/Guest:\s*([^|]+)/', $details, $matches)) {
            $guest_name = trim($matches[1]);
        }
        if (preg_match('/Receipt:\s*([^|]+)/', $details, $matches)) {
            $receipt_no = trim($matches[1]);
        }

        // Send email notification about payment decision
        if (!empty($guest_email)) {
            error_log("========================================");
            error_log("PAYMENT UPDATE EMAIL - Booking ID: $bookingId");
            error_log("Action: $paymentAction");
            error_log("Guest: $guest_name");
            error_log("Email: $guest_email");
            error_log("========================================");

            $emailTemplate = build_admin_payment_update_email($paymentAction, [
                'guest_name' => $guest_name,
                'receipt_no' => $receipt_no,
            ]);

            if ($emailTemplate) {
                $emailBody = create_email_template($emailTemplate['title'], $emailTemplate['content'], $emailTemplate['footer']);
                $email_sent = send_smtp_mail($guest_email, $emailTemplate['subject'], $emailBody);
                error_log("PAYMENT UPDATE EMAIL - Result: " . ($email_sent ? "SUCCESS" : "FAILED"));
            }
        }
    }

    // AJAX response
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    if ($isAjax) {
        header('Content-Type: application/json');
        if ($success) {
            // Build verifier info
            $verifier_id = $admin_id;
            $verifier_username = isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : '';
            // Use server time for verified_at
            $verified_at = date('Y-m-d H:i:s');

            echo json_encode([
                'success' => true,
                'message' => "Payment " . ($paymentAction === 'verify' ? 'verified' : 'rejected') . " successfully.",
                'payment_status' => $newPaymentStatus,
                'verifier_id' => $verifier_id,
                'verifier_username' => $verifier_username,
                'verified_at' => $verified_at
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error updating payment status.']);
        }
        exit;
    } else {
        $_SESSION['msg'] = $success ? "Payment " . ($paymentAction === 'verify' ? 'verified' : 'rejected') . " successfully." : "Error updating payment.";
        redirect('../dashboard.php');
    }
}


