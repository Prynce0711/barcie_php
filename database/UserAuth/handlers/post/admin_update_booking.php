<?php
/* ---------------------------
   ADMIN: update booking status
   --------------------------- */
if ($action === 'admin_update_booking') {
    if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        // Return JSON for AJAX clients; for normal requests redirect with session message
        handleResponse('Access denied. Admin login required.', false, '../dashboard.php');
    }

    $bookingId = (int) ($_POST['booking_id'] ?? 0);
    $adminAction = $_POST['admin_action'] ?? '';

    $statusMap = [
        "approve" => "confirmed",
        "reject" => "rejected",
        "checkin" => "checked_in",
        "checkout" => "checked_out",
        "cancel" => "cancelled"
    ];

    if (!array_key_exists($adminAction, $statusMap)) {
        $_SESSION['msg'] = "Unknown admin action.";
        redirect('../dashboard.php');
    }

    $newStatus = $statusMap[$adminAction];

    // Get booking details first
    $booking_stmt = $conn->prepare("SELECT room_id, status, details FROM bookings WHERE id = ?");
    $booking_stmt->bind_param("i", $bookingId);
    $booking_stmt->execute();
    $booking_result = $booking_stmt->get_result();
    $booking_data = $booking_result->fetch_assoc();
    $booking_stmt->close();

    // Get admin ID from session
    $admin_id = isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null;

    // Update booking status and set timestamps where appropriate
    if ($adminAction === 'approve' && $admin_id) {
        $stmt = $conn->prepare("UPDATE bookings SET status = ?, approved_by = ?, approved_at = NOW(), updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("sii", $newStatus, $admin_id, $bookingId);
    } elseif ($adminAction === 'checkout') {
        // when checking out, record checked_out_at
        $stmt = $conn->prepare("UPDATE bookings SET status = ?, checked_out_at = NOW(), updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("si", $newStatus, $bookingId);
    } elseif ($adminAction === 'checkin') {
        // when checking in, record checked_in_at
        $stmt = $conn->prepare("UPDATE bookings SET status = ?, checked_in_at = NOW(), updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("si", $newStatus, $bookingId);
    } else {
        $stmt = $conn->prepare("UPDATE bookings SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("si", $newStatus, $bookingId);
    }
    $success = $stmt->execute();
    $stmt->close();

    if ($success && $booking_data) {
        // Extract guest email and name from details
        $details = $booking_data['details'];
        $guest_email = '';
        $guest_name = 'Guest';
        $room_name = '';
        $checkin = '';
        $checkout = '';

        if (preg_match('/Email:\s*([^|]+)/', $details, $matches)) {
            $guest_email = trim($matches[1]);
        }
        if (preg_match('/Guest:\s*([^|]+)/', $details, $matches)) {
            $guest_name = trim($matches[1]);
        }
        if (preg_match('/(?:Room|Facility):\s*([^|]+)/', $details, $matches)) {
            $room_name = trim($matches[1]);
        }
        if (preg_match('/Check-in:\s*([^|]+)/', $details, $matches)) {
            $checkin = trim($matches[1]);
        }
        if (preg_match('/Check-out:\s*([^|]+)/', $details, $matches)) {
            $checkout = trim($matches[1]);
        }

        // Send email notification to guest for every status change
        if (!empty($guest_email)) {
            error_log("========================================");
            error_log("ADMIN UPDATE EMAIL - Booking ID: $bookingId");
            error_log("Action: $adminAction → Status: $newStatus");
            error_log("Guest: $guest_name");
            error_log("Email: $guest_email");
            error_log("========================================");

            $emailTemplate = build_admin_booking_update_email($adminAction, [
                'guest_name' => $guest_name,
                'room_name' => $room_name,
                'checkin' => $checkin,
                'checkout' => $checkout,
            ]);

            if ($emailTemplate) {
                error_log("ADMIN UPDATE EMAIL - Sending email...");
                error_log("Subject: " . $emailTemplate['subject']);
                $emailBody = create_email_template($emailTemplate['title'], $emailTemplate['content'], $emailTemplate['footer']);
                $email_sent = send_smtp_mail($guest_email, $emailTemplate['subject'], $emailBody);
                error_log("ADMIN UPDATE EMAIL - Result: " . ($email_sent ? "SUCCESS" : "FAILED"));
                error_log("========================================");
            } else {
                error_log("ADMIN UPDATE EMAIL - Skipped: No email template for action '$adminAction'");
                error_log("========================================");
            }
        } else {
            error_log("ADMIN UPDATE EMAIL - Skipped: No email address found in booking details");
            error_log("Booking ID: $bookingId");
            error_log("========================================");
        }

        // Update room status based on booking status
        if ($booking_data['room_id']) {
            $room_id = $booking_data['room_id'];
            $room_status = 'available'; // default

            switch ($adminAction) {
                case 'approve':
                    $room_status = 'reserved';
                    break;
                case 'checkin':
                    $room_status = 'occupied';
                    break;
                case 'checkout':
                    $room_status = 'dirty'; // needs cleaning after checkout
                    break;
                case 'reject':
                case 'cancel':
                    // Check if there are other active bookings for this room
                    $check_stmt = $conn->prepare("SELECT COUNT(*) as active_bookings FROM bookings WHERE room_id = ? AND status IN ('confirmed', 'approved', 'pending', 'checked_in') AND id != ?");
                    $check_stmt->bind_param("ii", $room_id, $bookingId);
                    $check_stmt->execute();
                    $check_result = $check_stmt->get_result();
                    $check_data = $check_result->fetch_assoc();
                    $check_stmt->close();

                    if ($check_data['active_bookings'] == 0) {
                        $room_status = 'available';
                    } else {
                        $room_status = 'reserved'; // keep as reserved if other bookings exist
                    }
                    break;
            }

            // Update room status
            $room_update = $conn->prepare("UPDATE items SET room_status = ? WHERE id = ?");
            $room_update->bind_param("si", $room_status, $room_id);
            $room_update->execute();
            $room_update->close();
        }
    }

    // Check if this is an AJAX request
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

    if ($isAjax) {
        // Return JSON response for AJAX requests
        header('Content-Type: application/json');
        if ($success) {
            // Prepare refreshed room list and room events to allow frontend to update without full reload
            $roomList = [];
            $items_q = "SELECT id, name, room_number, item_type FROM items ORDER BY name ASC";
            $items_r = $conn->query($items_q);
            if ($items_r && $items_r->num_rows > 0) {
                while ($it = $items_r->fetch_assoc()) {
                    $roomList[] = [
                        'id' => (int) $it['id'],
                        'name' => $it['name'],
                        'room_number' => $it['room_number'],
                        'item_type' => $it['item_type']
                    ];
                }
            }

            // Build room events (limited range to past 1 year -> next 1 year)
            $roomEvents = [];
            $bookings_q = "SELECT b.*, i.name as item_name, i.item_type, i.room_number FROM bookings b LEFT JOIN items i ON b.room_id = i.id WHERE b.status IN ('approved', 'confirmed', 'checked_in', 'checked_out', 'pending') AND b.checkin >= DATE_SUB(CURDATE(), INTERVAL 365 DAY) AND b.checkin <= DATE_ADD(CURDATE(), INTERVAL 365 DAY) ORDER BY b.checkin ASC";
            $bookings_r = $conn->query($bookings_q);
            if ($bookings_r && $bookings_r->num_rows > 0) {
                while ($bk = $bookings_r->fetch_assoc()) {
                    $item_name = $bk['item_name'] ? $bk['item_name'] : 'Unassigned Room/Facility';
                    $room_number = $bk['room_number'] ? '#' . $bk['room_number'] : '';
                    $item_type = $bk['item_type'] ?: 'room';
                    $guest = 'Guest';
                    $status = $bk['status'];
                    $display_title = $item_name . ' ' . $room_number . ' - ' . $guest;
                    $color = '#28a745';
                    if ($status == 'checked_in')
                        $color = '#0d6efd';
                    if ($status == 'checked_out')
                        $color = '#6f42c1';
                    if ($status == 'pending')
                        $color = '#fd7e14';

                    $roomEvents[] = [
                        'id' => 'booking-' . $bk['id'],
                        'title' => $display_title,
                        'start' => $bk['checkin'],
                        'end' => date('Y-m-d', strtotime($bk['checkout'] . ' +1 day')),
                        'backgroundColor' => $color,
                        'borderColor' => $color,
                        'textColor' => '#ffffff',
                        'extendedProps' => [
                            'itemName' => $item_name,
                            'roomNumber' => $bk['room_number'] ?: '',
                            'itemType' => $item_type,
                            'guest' => $guest,
                            'status' => $status,
                            'checkin' => $bk['checkin'],
                            'checkout' => $bk['checkout'],
                            'roomId' => $bk['room_id'] ? (int) $bk['room_id'] : null
                        ]
                    ];
                }
            }

            echo json_encode([
                'success' => true,
                'message' => "Booking #$bookingId updated to $newStatus successfully.",
                'status' => $newStatus,
                'roomList' => $roomList,
                'roomEvents' => $roomEvents
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => "Error updating booking #$bookingId."
            ]);
        }
        exit;
    } else {
        // Traditional redirect for non-AJAX requests
        $_SESSION['msg'] = $success ? "Booking #$bookingId updated to $newStatus." : "Error updating booking.";
        redirect('../dashboard.php');
    }
}


