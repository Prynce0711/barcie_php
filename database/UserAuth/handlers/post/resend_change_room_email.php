<?php

/**
 * Handler: Resend Change Room Email
 *
 * Resends the "Need to Change Room" email to a guest whose booking
 * is in "need to change room" status.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../../../components/Email/smtp_mailer.php';
require_once __DIR__ . '/../../../../components/Email/email_template.php';
require_once __DIR__ . '/../../../../components/Email/template_builders.php';

if (!function_exists('resend_extract_detail_value')) {
    function resend_extract_detail_value(string $details, string $label): string
    {
        $pattern = '/\b' . preg_quote($label, '/') . ':\s*([^|]+)/i';
        if (preg_match($pattern, $details, $m)) {
            return trim((string) ($m[1] ?? ''));
        }
        return '';
    }
}

if (!function_exists('resend_base_url')) {
    function resend_base_url(): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/database/user_auth.php';
        $basePath = rtrim((string) preg_replace('#/database/.*$#', '', $scriptName), '/');
        return $scheme . '://' . $host . $basePath;
    }
}

if (!function_exists('resend_collect_room_suggestions')) {
    function resend_collect_room_suggestions($conn, array $booking): array
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

if ($action === 'resend_change_room_email') {
    $bookingId = isset($_POST['booking_id']) ? (int) $_POST['booking_id'] : 0;

    if ($bookingId <= 0) {
        handleResponse('Invalid booking ID.', false, '../index.php?view=dashboard#bookings-section');
    }

    try {
        // Fetch booking details
        $stmt = $conn->prepare(
            'SELECT b.id, b.receipt_no, b.room_id, b.checkin, b.checkout, b.details, b.status,
                    i.name AS room_name, i.room_number
             FROM bookings b
             LEFT JOIN items i ON b.room_id = i.id
             WHERE b.id = ?
             LIMIT 1'
        );

        if (!$stmt) {
            throw new Exception('Failed to prepare booking lookup.');
        }

        $stmt->bind_param('i', $bookingId);
        $stmt->execute();
        $result = $stmt->get_result();
        $booking = $result ? $result->fetch_assoc() : null;
        $stmt->close();

        if (!$booking) {
            throw new Exception('Booking not found.');
        }

        // Verify booking is in "need to change room" status
        $status = strtolower(trim((string) ($booking['status'] ?? '')));
        if ($status !== 'need to change room') {
            throw new Exception('Booking is not in "need to change room" status. Current status: ' . ($booking['status'] ?? 'unknown'));
        }

        // Extract guest email from details
        $details = (string) ($booking['details'] ?? '');
        $guestEmail = resend_extract_detail_value($details, 'Email');

        if ($guestEmail === '' || !filter_var($guestEmail, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Guest email not found or invalid in booking details.');
        }

        // Build change room URL
        $guestName = resend_extract_detail_value($details, 'Guest');
        $receiptNo = (string) ($booking['receipt_no'] ?? '');
        $suggestions = resend_collect_room_suggestions($conn, $booking);

        $changeRoomUrl = resend_base_url()
            . '/components/guest/Booking/ChangeRoom.php?booking_id=' . urlencode((string) $bookingId)
            . '&receipt=' . urlencode($receiptNo)
            . '&email=' . urlencode($guestEmail);

        // Build and send email
        $tpl = build_conflict_auto_reject_email([
            'guest_name' => $guestName,
            'receipt_no' => $receiptNo,
            'room_name' => (string) ($booking['room_name'] ?? 'Room'),
            'room_number' => (string) ($booking['room_number'] ?? ''),
            'checkin' => (string) ($booking['checkin'] ?? ''),
            'checkout' => (string) ($booking['checkout'] ?? ''),
            'suggested_rooms' => $suggestions,
            'change_room_url' => $changeRoomUrl,
        ]);

        $body = create_email_template($tpl['title'], $tpl['content'], $tpl['footer']);
        $sent = send_smtp_mail($guestEmail, (string) $tpl['subject'], $body);

        if ($sent) {
            error_log('Resent change room email for booking #' . $bookingId . ' to ' . $guestEmail);
            handleResponse('Change room email resent successfully to ' . $guestEmail, true, '../index.php?view=dashboard#bookings-section');
        } else {
            throw new Exception('Failed to send email. Check email configuration.');
        }
    } catch (Throwable $e) {
        error_log('resend_change_room_email error: ' . $e->getMessage());
        handleResponse('Failed to resend email: ' . $e->getMessage(), false, '../index.php?view=dashboard#bookings-section');
    }
}
