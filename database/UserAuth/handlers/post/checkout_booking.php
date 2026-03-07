<?php
/* ---------------------------
   CHECKOUT AND MARK ROOM AVAILABLE
   --------------------------- */
if ($action === 'checkout_booking') {
    header('Content-Type: application/json');

    $booking_id = (int) ($_POST['booking_id'] ?? 0);

    if ($booking_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid booking ID']);
        exit;
    }

    try {
        // Add checked_out_at column if it doesn't exist
        $conn->query("ALTER TABLE bookings ADD COLUMN IF NOT EXISTS checked_out_at TIMESTAMP NULL");

        // Update booking status
        $stmt = $conn->prepare("UPDATE bookings SET status = 'checked_out', checked_out_at = NOW() WHERE id = ?");
        $stmt->bind_param("i", $booking_id);

        if ($stmt->execute()) {
            // Get room_id and mark as available
            $room_stmt = $conn->prepare("SELECT room_id FROM bookings WHERE id = ?");
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

            echo json_encode(['success' => true, 'message' => 'Room checked out and marked as available']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to checkout']);
        }

        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
