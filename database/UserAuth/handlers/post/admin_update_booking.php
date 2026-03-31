<?php

declare(strict_types=1);

/* ---------------------------
   ADMIN: update booking status
   --------------------------- */
if ($action === 'admin_update_booking') {
    $bookingId = isset($_POST['booking_id']) ? (int) $_POST['booking_id'] : 0;
    $newStatus = trim((string) ($_POST['new_status'] ?? ''));

    if ($bookingId <= 0 || $newStatus === '') {
        handleResponse('Invalid booking update request.', false);
    }

    $stmt = $conn->prepare('UPDATE bookings SET status = ?, updated_at = NOW() WHERE id = ?');
    if (!$stmt) {
        handleResponse('Failed to prepare booking update.', false);
    }

    $stmt->bind_param('si', $newStatus, $bookingId);
    $ok = $stmt->execute();
    $stmt->close();

    if (!$ok) {
        handleResponse('Failed to update booking status.', false);
    }

    handleResponse('Booking status updated successfully.', true);
}
