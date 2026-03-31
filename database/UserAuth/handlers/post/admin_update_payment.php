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

/* ---------------------------
   ADMIN: update payment status (verify/reject)
   --------------------------- */
if ($action === 'admin_update_payment') {
    $bookingId = isset($_POST['booking_id']) ? (int) $_POST['booking_id'] : 0;
    $paymentAction = trim((string) ($_POST['payment_action'] ?? ''));

    if ($bookingId <= 0 || ($paymentAction !== 'verify' && $paymentAction !== 'reject')) {
        handleResponse('Invalid payment update request.', false);
    }

    $newPaymentStatus = $paymentAction === 'verify' ? 'verified' : 'rejected';
    $newBookingStatus = $paymentAction === 'verify' ? 'approved' : 'pending';
    $verifierId = isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null;

    $autoRejectedRows = [];
    $verifiedBooking = null;
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
            throw new Exception('Failed to prepare target booking lookup.');
        }
        $targetStmt->bind_param('i', $bookingId);
        $targetStmt->execute();
        $targetRes = $targetStmt->get_result();
        $verifiedBooking = $targetRes ? $targetRes->fetch_assoc() : null;
        $targetStmt->close();

        if (!$verifiedBooking) {
            throw new Exception('Booking not found.');
        }

        if (
            $paymentAction === 'verify'
            && !empty($verifiedBooking['checkin'])
            && !empty($verifiedBooking['checkout'])
        ) {
            $checkin = (string) $verifiedBooking['checkin'];
            $checkout = (string) $verifiedBooking['checkout'];
            $roomId = (int) ($verifiedBooking['room_id'] ?? 0);

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
                $newPaymentStatus = 'rejected';
                $newBookingStatus = 'need to change room';
            }
        }

        // Update booking payment and status fields.
        $stmt = $conn->prepare(
            'UPDATE bookings
             SET payment_status = ?,
                 status = ?,
                 payment_verified_by = ?,
                 payment_verified_at = NOW(),
                 updated_at = NOW()
             WHERE id = ?'
        );
        if (!$stmt) {
            throw new Exception('Failed to prepare booking update statement.');
        }

        // Allow null verifier id when session does not carry admin id.
        if ($verifierId === null) {
            $nullInt = null;
            $stmt->bind_param('ssii', $newPaymentStatus, $newBookingStatus, $nullInt, $bookingId);
        } else {
            $stmt->bind_param('ssii', $newPaymentStatus, $newBookingStatus, $verifierId, $bookingId);
        }

        if (!$stmt->execute()) {
            throw new Exception('Failed to update booking payment status: ' . $stmt->error);
        }

        if ($stmt->affected_rows < 1) {
            throw new Exception('Booking not found or no changes were applied.');
        }
        $stmt->close();

        if (
            $paymentAction === 'verify'
            && !$blockedByExistingApproval
            && !empty($verifiedBooking['checkin'])
            && !empty($verifiedBooking['checkout'])
        ) {
            $checkin = (string) $verifiedBooking['checkin'];
            $checkout = (string) $verifiedBooking['checkout'];
            $roomId = (int) ($verifiedBooking['room_id'] ?? 0);

            // Mark other overlapping same-room bookings that are still awaiting decision.
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
                        b.payment_status IN ('pending', 'none')
                        OR b.payment_status IS NULL
                        OR TRIM(b.payment_status) = ''
                        OR b.payment_status = '0'
                    )
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
                         payment_status = 'rejected',
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

        // Keep room/facility status in sync after payment decision.
        $roomStmt = $conn->prepare('SELECT room_id FROM bookings WHERE id = ? LIMIT 1');
        if (!$roomStmt) {
            throw new Exception('Failed to prepare room lookup statement.');
        }
        $roomStmt->bind_param('i', $bookingId);
        $roomStmt->execute();
        $roomRes = $roomStmt->get_result();
        $roomId = null;
        if ($roomRes && $roomRes->num_rows > 0) {
            $row = $roomRes->fetch_assoc();
            $roomId = isset($row['room_id']) ? (int) $row['room_id'] : null;
        }
        $roomStmt->close();

        if ($roomId && $roomId > 0) {
            if ($paymentAction === 'verify' && !$blockedByExistingApproval) {
                $itemStmt = $conn->prepare("UPDATE items SET room_status = 'occupied' WHERE id = ?");
            } else {
                $itemStmt = $conn->prepare("UPDATE items SET room_status = 'available' WHERE id = ?");
            }

            if ($itemStmt) {
                $itemStmt->bind_param('i', $roomId);
                $itemStmt->execute();
                $itemStmt->close();
            }
        }

        $conn->commit();

        // Notify the target guest for this admin action.
        if ($paymentAction === 'verify' && $blockedByExistingApproval && $blockingApprovedBooking) {
            if (function_exists('booking_send_conflict_auto_reject_email')) {
                booking_send_conflict_auto_reject_email($conn, $blockingApprovedBooking, $verifiedBooking);
            }
        } elseif ($paymentAction === 'verify') {
            if (function_exists('booking_send_admin_action_email')) {
                booking_send_admin_action_email('approve', $verifiedBooking);
            } else {
                $details = (string) ($verifiedBooking['details'] ?? '');
                $guestEmail = booking_extract_detail_value($details, 'Email');
                $guestName = booking_extract_detail_value($details, 'Guest');

                if ($guestEmail !== '') {
                    $roomDisplay = (string) ($verifiedBooking['room_name'] ?? 'Room');
                    if (!empty($verifiedBooking['room_number'])) {
                        $roomDisplay .= ' #' . (string) $verifiedBooking['room_number'];
                    }

                    $tpl = build_admin_booking_update_email('approve', [
                        'guest_name' => $guestName,
                        'room_name' => $roomDisplay,
                        'checkin' => (string) ($verifiedBooking['checkin'] ?? ''),
                        'checkout' => (string) ($verifiedBooking['checkout'] ?? ''),
                    ]);
                    if ($tpl) {
                        $body = create_email_template($tpl['title'], $tpl['content'], $tpl['footer']);
                        $sent = send_smtp_mail($guestEmail, (string) $tpl['subject'], $body);
                        error_log('Payment verify action email for booking #' . (int) ($verifiedBooking['id'] ?? 0) . ': ' . ($sent ? 'sent' : 'failed'));
                    }
                }
            }
        } elseif ($paymentAction === 'reject') {
            $details = (string) ($verifiedBooking['details'] ?? '');
            $guestEmail = booking_extract_detail_value($details, 'Email');
            $guestName = booking_extract_detail_value($details, 'Guest');

            if ($guestEmail !== '') {
                $tpl = build_admin_payment_update_email('reject', [
                    'guest_name' => $guestName,
                    'receipt_no' => (string) ($verifiedBooking['receipt_no'] ?? ''),
                ]);
                if ($tpl) {
                    $body = create_email_template($tpl['title'], $tpl['content'], $tpl['footer']);
                    $sent = send_smtp_mail($guestEmail, (string) $tpl['subject'], $body);
                    error_log('Payment reject action email for booking #' . (int) ($verifiedBooking['id'] ?? 0) . ': ' . ($sent ? 'sent' : 'failed'));
                }
            }
        }

        // Send conflict emails after commit so guest notifications only happen on durable updates.
        if ($paymentAction === 'verify' && !empty($autoRejectedRows)) {
            foreach ($autoRejectedRows as $dup) {
                if (function_exists('booking_send_conflict_auto_reject_email')) {
                    booking_send_conflict_auto_reject_email($conn, $verifiedBooking, $dup);
                    continue;
                }

                $baseUrl = booking_base_url();
                $approvedRoomName = (string) ($verifiedBooking['room_name'] ?? 'Room');
                $dupDetails = (string) ($dup['details'] ?? '');
                $guestEmail = booking_extract_detail_value($dupDetails, 'Email');
                $guestName = booking_extract_detail_value($dupDetails, 'Guest');
                $receiptNo = (string) ($dup['receipt_no'] ?? '');

                if ($guestEmail === '') {
                    continue;
                }

                $suggestions = [];
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
                if ($suggestStmt) {
                    $dupRoomId = (int) ($dup['room_id'] ?? 0);
                    $dupRoomName = (string) ($dup['room_name'] ?? '');
                    $dupId = (int) ($dup['id'] ?? 0);
                    $dupCheckout = (string) ($dup['checkout'] ?? '');
                    $dupCheckin = (string) ($dup['checkin'] ?? '');
                    $suggestStmt->bind_param('isiss', $dupRoomId, $dupRoomName, $dupId, $dupCheckout, $dupCheckin);
                    $suggestStmt->execute();
                    $suggestRes = $suggestStmt->get_result();
                    while ($suggestRes && ($suggestRow = $suggestRes->fetch_assoc())) {
                        $suggestions[] = $suggestRow;
                    }
                    $suggestStmt->close();
                }

                $changeRoomUrl = $baseUrl
                    . '/components/guest/Booking/ChangeRoom.php?booking_id=' . urlencode((string) ($dup['id'] ?? '0'))
                    . '&receipt=' . urlencode($receiptNo)
                    . '&email=' . urlencode($guestEmail);

                $tpl = build_conflict_auto_reject_email([
                    'guest_name' => $guestName,
                    'receipt_no' => $receiptNo,
                    'room_name' => $approvedRoomName,
                    'room_number' => (string) ($dup['room_number'] ?? ''),
                    'checkin' => (string) ($dup['checkin'] ?? ''),
                    'checkout' => (string) ($dup['checkout'] ?? ''),
                    'suggested_rooms' => $suggestions,
                    'change_room_url' => $changeRoomUrl,
                ]);

                $body = create_email_template($tpl['title'], $tpl['content'], $tpl['footer']);
                $sent = send_smtp_mail($guestEmail, (string) $tpl['subject'], $body);
                error_log('Auto-reject conflict email for booking #' . (int) ($dup['id'] ?? 0) . ': ' . ($sent ? 'sent' : 'failed'));
            }
        }

        if ($paymentAction === 'verify' && $blockedByExistingApproval) {
            $message = 'Payment verify blocked because another booking on the same date is already approved. This booking was marked as need to change room and the guest was notified.';
        } else {
            $message = $paymentAction === 'verify'
                ? 'Payment verified successfully.' . (!empty($autoRejectedRows) ? ' Conflicting overlapping bookings were marked as need to change room and notified.' : '')
                : 'Payment rejected successfully.';
        }

        handleResponse($message, true);
    } catch (Throwable $e) {
        try {
            $conn->rollback();
        } catch (Throwable $rollbackError) {
            // Ignore rollback failures and report original error.
        }

        error_log('admin_update_payment error: ' . $e->getMessage());
        handleResponse('Failed to update payment status. ' . $e->getMessage(), false);
    }
}
