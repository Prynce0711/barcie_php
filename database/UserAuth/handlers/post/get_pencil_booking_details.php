<?php
/* ---------------------------
   GET PENCIL BOOKING DETAILS
   --------------------------- */
if ($action === 'get_pencil_booking_details') {
    header('Content-Type: application/json');

    $booking_id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

    if ($booking_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid booking ID']);
        exit;
    }

    try {
        $stmt = $conn->prepare("
            SELECT pb.*, i.name as room_name, i.item_type, i.room_number, i.capacity, i.price,
                   DATEDIFF(pb.token_expires_at, NOW()) as days_remaining
            FROM pencil_bookings pb
            LEFT JOIN items i ON pb.room_id = i.id 
            WHERE pb.id = ?
        ");
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($booking = $result->fetch_assoc()) {
            echo json_encode([
                'success' => true,
                'booking' => $booking
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Pencil booking not found']);
        }

        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }

    exit;
}


