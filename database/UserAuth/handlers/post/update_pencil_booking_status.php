<?php
/* ---------------------------
   UPDATE PENCIL BOOKING STATUS
   --------------------------- */
if ($action === 'update_pencil_booking_status') {
    // Only Front Desk (admin), managers and super_admin can update pencil booking status - staff CANNOT
    require_once __DIR__ . '/../../../role_check.php';
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
                $template = build_pencil_status_email('confirmed', [
                    'guest_name' => $booking['guest_name'],
                    'receipt_no' => $booking['receipt_no'],
                ]);
                if ($template) {
                    $emailBody = create_email_template($template['title'], $template['content'], $template['footer']);
                    send_smtp_mail($booking['email'], $template['subject'], $emailBody);
                }
            }

            // Send rejection email if status is rejected
            if ($new_status === 'rejected' && !empty($booking['email'])) {
                $template = build_pencil_status_email('rejected', [
                    'guest_name' => $booking['guest_name'],
                    'receipt_no' => $booking['receipt_no'],
                ]);
                if ($template) {
                    $emailBody = create_email_template($template['title'], $template['content'], $template['footer']);
                    send_smtp_mail($booking['email'], $template['subject'], $emailBody);
                }
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


