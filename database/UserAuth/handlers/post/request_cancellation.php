<?php
/* ---------------------------
   CANCEL BOOKING REQUEST
   --------------------------- */
if ($action === 'request_cancellation') {
    header('Content-Type: application/json');

    $booking_id = (int) ($_POST['booking_id'] ?? 0);
    $booking_type = $_POST['booking_type'] ?? 'booking';
    $reason = $conn->real_escape_string($_POST['reason'] ?? '');
    $cancelled_by = $conn->real_escape_string($_POST['cancelled_by'] ?? '');

    if ($booking_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid booking ID']);
        exit;
    }

    try {
        if ($booking_type === 'pencil') {
            $stmt = $conn->prepare("UPDATE pencil_bookings SET status = 'cancelled', admin_notes = CONCAT(COALESCE(admin_notes, ''), '\nCancellation requested: ', ?, ' by ', ?, ' at ', NOW()) WHERE id = ?");
            $stmt->bind_param("ssi", $reason, $cancelled_by, $booking_id);
        } else {
            // Add cancellation columns if they don't exist
            $conn->query("ALTER TABLE bookings ADD COLUMN IF NOT EXISTS cancellation_requested_at TIMESTAMP NULL");
            $conn->query("ALTER TABLE bookings ADD COLUMN IF NOT EXISTS cancellation_reason TEXT NULL");
            $conn->query("ALTER TABLE bookings ADD COLUMN IF NOT EXISTS cancelled_by VARCHAR(100) NULL");

            $stmt = $conn->prepare("UPDATE bookings SET status = 'cancelled', cancellation_requested_at = NOW(), cancellation_reason = ?, cancelled_by = ? WHERE id = ?");
            $stmt->bind_param("ssi", $reason, $cancelled_by, $booking_id);
        }

        if ($stmt->execute()) {
            // Update room status back to available
            if ($booking_type === 'pencil') {
                $room_stmt = $conn->prepare("SELECT room_id FROM pencil_bookings WHERE id = ?");
            } else {
                $room_stmt = $conn->prepare("SELECT room_id FROM bookings WHERE id = ?");
            }
            $room_stmt->bind_param("i", $booking_id);
            $room_stmt->execute();
            $room_result = $room_stmt->get_result();
            if ($room_data = $room_result->fetch_assoc()) {
                $update_room = $conn->prepare("UPDATE items SET room_status = 'available' WHERE id = ?");
                $update_room->bind_param("i", $room_data['room_id']);
                $update_room->execute();
                $update_room->close();
            }
            $room_stmt->close();

            echo json_encode(['success' => true, 'message' => 'Cancellation request submitted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to submit cancellation request']);
        }

        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }

    exit;
}


