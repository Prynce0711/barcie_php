<?php

declare(strict_types=1);

if (!function_exists('booking_extract_detail_value')) {
    function booking_extract_detail_value(string $details, string $label): string
    {
        $pattern = '/\b' . preg_quote($label, '/') . ':\s*([^|]+)/i';
        if (preg_match($pattern, $details, $m)) {
            return trim((string) ($m[1] ?? ''));
        }
        return '';
    }
}

if (!function_exists('booking_base_url')) {
    function booking_base_url(): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/database/user_auth.php';
        $basePath = rtrim((string) preg_replace('#/database/.*$#', '', $scriptName), '/');
        return $scheme . '://' . $host . $basePath;
    }
}

if (!function_exists('booking_collect_room_suggestions')) {
    function booking_collect_room_suggestions($conn, array $booking): array
    {
        $suggestions = [];

        $roomId = (int) ($booking['room_id'] ?? 0);
        $roomName = (string) ($booking['room_name'] ?? '');
        $bookingId = (int) ($booking['id'] ?? 0);
        $checkin = (string) ($booking['checkin'] ?? '');
        $checkout = (string) ($booking['checkout'] ?? '');

        if ($roomId <= 0 || $roomName === '' || $checkin === '' || $checkout === '') {
            return $suggestions;
        }

        $suggestStmt = $conn->prepare(
            "SELECT i.id, i.name, i.room_number, i.capacity, i.price
             FROM items i
             WHERE i.id <> ?
               AND i.name = ?
               AND i.item_type IN ('room','facility')
               AND NOT EXISTS (
                   SELECT 1
                   FROM bookings b2
                   WHERE b2.room_id = i.id
                     AND b2.id <> ?
                     AND DATE(b2.checkin) <= DATE(?)
                     AND DATE(b2.checkout) >= DATE(?)
                     AND (
                         b2.payment_status = 'verified'
                         OR b2.status IN ('pending', 'approved', 'confirmed', 'checked_in')
                     )
               )
             ORDER BY i.room_number ASC, i.id ASC
             LIMIT 5"
        );

        if (!$suggestStmt) {
            return $suggestions;
        }

        $suggestStmt->bind_param('isiss', $roomId, $roomName, $bookingId, $checkout, $checkin);
        $suggestStmt->execute();
        $suggestRes = $suggestStmt->get_result();
        while ($suggestRes && ($suggestRow = $suggestRes->fetch_assoc())) {
            $suggestions[] = $suggestRow;
        }
        $suggestStmt->close();

        return $suggestions;
    }
}

if (!function_exists('booking_send_conflict_auto_reject_email')) {
    function booking_send_conflict_auto_reject_email($conn, array $approvedBooking, array $duplicateBooking): void
    {
        $dupDetails = (string) ($duplicateBooking['details'] ?? '');
        $guestEmail = booking_extract_detail_value($dupDetails, 'Email');
        if ($guestEmail === '') {
            return;
        }

        $guestName = booking_extract_detail_value($dupDetails, 'Guest');
        $receiptNo = (string) ($duplicateBooking['receipt_no'] ?? '');
        $suggestions = booking_collect_room_suggestions($conn, $duplicateBooking);

        $changeRoomUrl = booking_base_url()
            . '/components/guest/Booking/ChangeRoom.php?booking_id=' . urlencode((string) ($duplicateBooking['id'] ?? '0'))
            . '&receipt=' . urlencode($receiptNo)
            . '&email=' . urlencode($guestEmail);

        $tpl = build_conflict_auto_reject_email([
            'guest_name' => $guestName,
            'receipt_no' => $receiptNo,
            'room_name' => (string) ($approvedBooking['room_name'] ?? ($duplicateBooking['room_name'] ?? 'Room')),
            'room_number' => (string) ($duplicateBooking['room_number'] ?? ''),
            'checkin' => (string) ($duplicateBooking['checkin'] ?? ''),
            'checkout' => (string) ($duplicateBooking['checkout'] ?? ''),
            'suggested_rooms' => $suggestions,
            'change_room_url' => $changeRoomUrl,
        ]);

        $body = create_email_template($tpl['title'], $tpl['content'], $tpl['footer']);
        $sent = send_smtp_mail($guestEmail, (string) $tpl['subject'], $body);
        error_log('Auto-reject conflict email for booking #' . (int) ($duplicateBooking['id'] ?? 0) . ': ' . ($sent ? 'sent' : 'failed'));
    }
}

if (!function_exists('booking_send_admin_action_email')) {
    function booking_send_admin_action_email(string $adminAction, array $booking): void
    {
        $details = (string) ($booking['details'] ?? '');
        $guestEmail = booking_extract_detail_value($details, 'Email');
        if ($guestEmail === '') {
            return;
        }

        $guestName = booking_extract_detail_value($details, 'Guest');
        $roomDisplay = (string) ($booking['room_name'] ?? 'Room');
        if (!empty($booking['room_number'])) {
            $roomDisplay .= ' #' . (string) $booking['room_number'];
        }

        $template = build_admin_booking_update_email($adminAction, [
            'guest_name' => $guestName,
            'room_name' => $roomDisplay,
            'checkin' => (string) ($booking['checkin'] ?? ''),
            'checkout' => (string) ($booking['checkout'] ?? ''),
        ]);

        if (!$template) {
            return;
        }

        $body = create_email_template($template['title'], $template['content'], $template['footer']);
        $sent = send_smtp_mail($guestEmail, (string) $template['subject'], $body);
        error_log('Admin action email (' . $adminAction . ') for booking #' . (int) ($booking['id'] ?? 0) . ': ' . ($sent ? 'sent' : 'failed'));
    }
}

/* ---------------------------
   ADMIN: update booking status
   --------------------------- */
if ($action === 'admin_update_booking') {
    $bookingId = isset($_POST['booking_id']) ? (int) $_POST['booking_id'] : 0;
    $newStatus = trim((string) ($_POST['new_status'] ?? ''));
    $adminAction = trim((string) ($_POST['admin_action'] ?? ''));

    $actionToStatus = [
        'approve' => 'approved',
        'reject' => 'rejected',
        'checkin' => 'checked_in',
        'checkout' => 'checked_out',
        'cancel' => 'cancelled',
    ];

    $statusToAction = [
        'approved' => 'approve',
        'rejected' => 'reject',
        'checked_in' => 'checkin',
        'checked_out' => 'checkout',
        'cancelled' => 'cancel',
    ];

    if ($newStatus === '' && $adminAction !== '' && isset($actionToStatus[$adminAction])) {
        $newStatus = $actionToStatus[$adminAction];
    }

    if ($adminAction === '' && isset($statusToAction[$newStatus])) {
        $adminAction = $statusToAction[$newStatus];
    }

    $allowedStatuses = ['pending', 'approved', 'confirmed', 'checked_in', 'checked_out', 'cancelled', 'rejected', 'need to change room'];
    if ($bookingId <= 0 || $newStatus === '' || !in_array($newStatus, $allowedStatuses, true)) {
        handleResponse('Invalid booking update request.', false, '../dashboard.php#bookings-section');
    }

    $autoRejectedRows = [];
    $targetBooking = null;
    $blockingApprovedBooking = null;
    $blockedByExistingApproval = false;

    try {
        $conn->begin_transaction();

        $targetStmt = $conn->prepare(
            'SELECT b.id, b.receipt_no, b.room_id, b.checkin, b.checkout, b.details, b.status, b.payment_status,
                    i.name AS room_name, i.room_number
             FROM bookings b
             LEFT JOIN items i ON b.room_id = i.id
             WHERE b.id = ?
             LIMIT 1'
        );
        if (!$targetStmt) {
            throw new Exception('Failed to prepare booking lookup.');
        }

        $targetStmt->bind_param('i', $bookingId);
        $targetStmt->execute();
        $targetRes = $targetStmt->get_result();
        $targetBooking = $targetRes ? $targetRes->fetch_assoc() : null;
        $targetStmt->close();

        if (!$targetBooking) {
            throw new Exception('Booking not found.');
        }

        if (
            $newStatus === 'approved'
            && !empty($targetBooking['checkin'])
            && !empty($targetBooking['checkout'])
        ) {
            $checkin = (string) $targetBooking['checkin'];
            $checkout = (string) $targetBooking['checkout'];
            $roomId = (int) ($targetBooking['room_id'] ?? 0);

            $blockingStmt = $conn->prepare(
                "SELECT b.id, b.receipt_no, b.details, b.checkin, b.checkout, b.room_id,
                        i.name AS room_name, i.room_number
                 FROM bookings b
                 LEFT JOIN items i ON b.room_id = i.id
                 WHERE b.id <> ?
                   AND b.room_id = ?
                   AND DATE(b.checkin) <= DATE(?)
                   AND DATE(b.checkout) >= DATE(?)
                   AND LOWER(TRIM(COALESCE(b.status, ''))) = 'approved'
                 ORDER BY b.id ASC
                 LIMIT 1"
            );
            if (!$blockingStmt) {
                throw new Exception('Failed to prepare approved-overlap lookup.');
            }

            $blockingStmt->bind_param('iiss', $bookingId, $roomId, $checkout, $checkin);
            $blockingStmt->execute();
            $blockingRes = $blockingStmt->get_result();
            $blockingApprovedBooking = $blockingRes ? $blockingRes->fetch_assoc() : null;
            $blockingStmt->close();

            if ($blockingApprovedBooking) {
                $blockedByExistingApproval = true;
                $newStatus = 'need to change room';
                // Prevent sending an "approved" action email for a blocked approval.
                $adminAction = '';
            }
        }

        if ($blockedByExistingApproval) {
            $updateStmt = $conn->prepare(
                "UPDATE bookings
                 SET status = ?,
                     payment_status = CASE
                        WHEN payment_status IS NULL
                             OR TRIM(payment_status) = ''
                             OR payment_status = '0'
                             OR payment_status IN ('none', 'pending', 'verified')
                        THEN 'rejected'
                        ELSE payment_status
                     END,
                     updated_at = NOW()
                 WHERE id = ?"
            );
        } else {
            $updateStmt = $conn->prepare('UPDATE bookings SET status = ?, updated_at = NOW() WHERE id = ?');
        }
        if (!$updateStmt) {
            throw new Exception('Failed to prepare booking update.');
        }

        $updateStmt->bind_param('si', $newStatus, $bookingId);
        if (!$updateStmt->execute()) {
            throw new Exception('Failed to update booking status: ' . $updateStmt->error);
        }
        $updateStmt->close();

        // Auto-mark other overlapping same-room bookings when this booking is approved.
        if (
            $newStatus === 'approved'
            && !empty($targetBooking['checkin'])
            && !empty($targetBooking['checkout'])
        ) {
            $checkin = (string) $targetBooking['checkin'];
            $checkout = (string) $targetBooking['checkout'];
            $roomId = (int) ($targetBooking['room_id'] ?? 0);

            $overlapStmt = $conn->prepare(
                "SELECT b.id, b.receipt_no, b.details, b.checkin, b.checkout, b.room_id,
                        i.name AS room_name, i.room_number
                 FROM bookings b
                 LEFT JOIN items i ON b.room_id = i.id
                 WHERE b.id <> ?
                   AND b.room_id = ?
                   AND DATE(b.checkin) <= DATE(?)
                   AND DATE(b.checkout) >= DATE(?)
                    AND (
                        b.status IS NULL
                        OR TRIM(b.status) = ''
                        OR b.status = '0'
                        OR LOWER(b.status) IN ('pending', 'confirmed')
                    )"
            );
            if (!$overlapStmt) {
                throw new Exception('Failed to prepare overlap lookup.');
            }

            $overlapStmt->bind_param('iiss', $bookingId, $roomId, $checkout, $checkin);
            $overlapStmt->execute();
            $overlapRes = $overlapStmt->get_result();

            $overlapping = [];
            while ($overlapRes && ($row = $overlapRes->fetch_assoc())) {
                $overlapping[] = $row;
            }
            $overlapStmt->close();

            if (!empty($overlapping)) {
                $rejectStmt = $conn->prepare(
                    "UPDATE bookings
                     SET status = 'need to change room',
                         payment_status = CASE
                             WHEN payment_status IN ('none', 'pending', 'verified') THEN 'rejected'
                             ELSE payment_status
                         END,
                         updated_at = NOW()
                     WHERE id = ?"
                );
                if (!$rejectStmt) {
                    throw new Exception('Failed to prepare auto-reject update statement.');
                }

                foreach ($overlapping as $dup) {
                    $dupId = (int) $dup['id'];
                    $rejectStmt->bind_param('i', $dupId);
                    if (!$rejectStmt->execute()) {
                        throw new Exception('Failed to auto-reject overlapping booking #' . $dupId . ': ' . $rejectStmt->error);
                    }
                    $autoRejectedRows[] = $dup;
                }
                $rejectStmt->close();
            }
        }

        $conn->commit();

        if ($blockedByExistingApproval && $blockingApprovedBooking) {
            booking_send_conflict_auto_reject_email($conn, $blockingApprovedBooking, $targetBooking);
        }

        if ($adminAction !== '') {
            booking_send_admin_action_email($adminAction, $targetBooking);
        }

        if ($newStatus === 'approved' && !empty($autoRejectedRows)) {
            foreach ($autoRejectedRows as $dupBooking) {
                booking_send_conflict_auto_reject_email($conn, $targetBooking, $dupBooking);
            }
        }

        if ($blockedByExistingApproval) {
            $message = 'Booking overlaps an already approved schedule. It was marked as need to change room and the guest was notified by email.';
        } else {
            $message = 'Booking status updated successfully.';
        }
        if (!$blockedByExistingApproval && $newStatus === 'approved' && !empty($autoRejectedRows)) {
            $message .= ' Conflicting same-date bookings were marked as need to change room and guests were notified by email.';
        }

        handleResponse($message, true, '../dashboard.php#bookings-section');
    } catch (Throwable $e) {
        try {
            $conn->rollback();
        } catch (Throwable $rollbackError) {
            // keep original error
        }

        error_log('admin_update_booking error: ' . $e->getMessage());
        handleResponse('Failed to update booking status. ' . $e->getMessage(), false, '../dashboard.php#bookings-section');
    }
}
