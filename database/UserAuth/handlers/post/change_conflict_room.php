<?php

declare(strict_types=1);

if (!function_exists('change_room_extract_detail_value')) {
    function change_room_extract_detail_value(string $details, string $label): string
    {
        $pattern = '/\b' . preg_quote($label, '/') . ':\s*([^|]+)/i';
        if (preg_match($pattern, $details, $m)) {
            return trim((string) ($m[1] ?? ''));
        }
        return '';
    }
}

if ($action === 'change_conflict_room') {
    $bookingId = isset($_POST['booking_id']) ? (int) $_POST['booking_id'] : 0;
    $selectedRoomId = isset($_POST['selected_room_id']) ? (int) $_POST['selected_room_id'] : 0;
    $guestEmail = trim((string) ($_POST['email'] ?? ''));
    $receiptNo = trim((string) ($_POST['receipt'] ?? ''));

    $redirectUrl = '../Components/Guest/Booking/ChangeRoom.php?booking_id=' . urlencode((string) $bookingId)
        . '&receipt=' . urlencode($receiptNo)
        . '&email=' . urlencode($guestEmail);

    if ($bookingId <= 0 || $selectedRoomId <= 0) {
        handleResponse('Please choose a room number before continuing.', false, $redirectUrl);
    }

    try {
        $conn->begin_transaction();

        $bookingStmt = $conn->prepare(
            'SELECT b.id, b.receipt_no, b.room_id, b.details, b.checkin, b.checkout,
                    i.name AS current_room_name, i.room_number AS current_room_number
             FROM bookings b
             LEFT JOIN items i ON b.room_id = i.id
             WHERE b.id = ?
             LIMIT 1'
        );
        if (!$bookingStmt) {
            throw new Exception('Unable to prepare booking lookup.');
        }

        $bookingStmt->bind_param('i', $bookingId);
        $bookingStmt->execute();
        $bookingRes = $bookingStmt->get_result();
        $booking = $bookingRes ? $bookingRes->fetch_assoc() : null;
        $bookingStmt->close();

        if (!$booking) {
            throw new Exception('Booking record was not found.');
        }

        $details = (string) ($booking['details'] ?? '');
        $storedEmail = change_room_extract_detail_value($details, 'Email');
        $storedReceipt = (string) ($booking['receipt_no'] ?? '');

        if ($guestEmail === '' || strcasecmp($storedEmail, $guestEmail) !== 0) {
            throw new Exception('This change-room link does not match the booking email.');
        }

        if ($receiptNo !== '' && strcasecmp($storedReceipt, $receiptNo) !== 0) {
            throw new Exception('Receipt number mismatch for this booking.');
        }

        $currentRoomId = (int) ($booking['room_id'] ?? 0);
        if ($currentRoomId === $selectedRoomId) {
            throw new Exception('Please select a different room number.');
        }

        $targetRoomStmt = $conn->prepare(
            'SELECT id, name, room_number, item_type
             FROM items
             WHERE id = ?
             LIMIT 1'
        );
        if (!$targetRoomStmt) {
            throw new Exception('Unable to prepare room lookup.');
        }
        $targetRoomStmt->bind_param('i', $selectedRoomId);
        $targetRoomStmt->execute();
        $targetRoomRes = $targetRoomStmt->get_result();
        $targetRoom = $targetRoomRes ? $targetRoomRes->fetch_assoc() : null;
        $targetRoomStmt->close();

        if (!$targetRoom) {
            throw new Exception('Selected room was not found.');
        }

        $currentRoomName = trim((string) ($booking['current_room_name'] ?? ''));
        $newRoomName = trim((string) ($targetRoom['name'] ?? ''));
        if ($currentRoomName !== '' && strcasecmp($currentRoomName, $newRoomName) !== 0) {
            throw new Exception('Only the same room type with a different room number can be selected.');
        }

        $checkin = (string) ($booking['checkin'] ?? '');
        $checkout = (string) ($booking['checkout'] ?? '');

        if ($checkin === '' || $checkout === '') {
            throw new Exception('Booking dates are missing. Please contact support.');
        }

        $overlapStmt = $conn->prepare(
            "SELECT id
             FROM bookings
             WHERE id <> ?
               AND room_id = ?
               AND status IN ('confirmed', 'approved', 'pending', 'checked_in')
               AND checkin < ?
               AND checkout > ?
             LIMIT 1"
        );
        if (!$overlapStmt) {
            throw new Exception('Unable to prepare availability check.');
        }

        $overlapStmt->bind_param('iiss', $bookingId, $selectedRoomId, $checkout, $checkin);
        $overlapStmt->execute();
        $overlapRes = $overlapStmt->get_result();
        $hasConflict = $overlapRes && $overlapRes->num_rows > 0;
        $overlapStmt->close();

        if ($hasConflict) {
            throw new Exception('That room number is no longer available for your selected schedule.');
        }

        $newRoomDisplay = $newRoomName;
        if (!empty($targetRoom['room_number'])) {
            $newRoomDisplay .= ' #' . (string) $targetRoom['room_number'];
        }

        $updatedDetails = preg_replace(
            '/\b(Room|Facility):\s*[^|]+/i',
            ucfirst((string) ($targetRoom['item_type'] ?? 'room')) . ': ' . $newRoomDisplay,
            $details,
            1
        );

        if (!is_string($updatedDetails) || $updatedDetails === '') {
            $updatedDetails = $details;
        }

        $note = ' | Change Room: reassigned by guest on ' . date('Y-m-d H:i:s');
        if (strpos($updatedDetails, 'Change Room:') === false) {
            $updatedDetails .= $note;
        }

        $updateStmt = $conn->prepare(
            'UPDATE bookings
             SET room_id = ?,
                 details = ?,
                 status = ?,
                 payment_status = ?,
                 payment_verified_by = NULL,
                 payment_verified_at = NULL,
                 updated_at = NOW()
             WHERE id = ?'
        );
        if (!$updateStmt) {
            throw new Exception('Unable to prepare booking update.');
        }

        $pendingStatus = 'pending';
        $updateStmt->bind_param('isssi', $selectedRoomId, $updatedDetails, $pendingStatus, $pendingStatus, $bookingId);
        if (!$updateStmt->execute()) {
            throw new Exception('Failed to update booking room: ' . $updateStmt->error);
        }
        $updateStmt->close();

        $conn->commit();

        handleResponse('Room number updated. Your booking has been moved back to pending verification.', true, $redirectUrl);
    } catch (Throwable $e) {
        try {
            $conn->rollback();
        } catch (Throwable $rollbackError) {
            // Keep original error context.
        }

        error_log('change_conflict_room error: ' . $e->getMessage());
        handleResponse('Unable to change room: ' . $e->getMessage(), false, $redirectUrl);
    }
}
