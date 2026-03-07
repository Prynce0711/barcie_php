<?php
/* ---------------------------
   GET BOOKING DETAILS (ADMIN ONLY)
   --------------------------- */
if ($action === 'get_booking_details') {
    header('Content-Type: application/json');

    // Admin access check
    if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        echo json_encode(['success' => false, 'error' => 'Admin access required']);
        exit;
    }

    $booking_id = (int) ($_POST['booking_id'] ?? 0);

    if ($booking_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid booking ID']);
        exit;
    }

    try {
        $stmt = $conn->prepare("
            SELECT b.*, i.name as room_name, i.item_type, i.room_number, i.capacity, i.price
            FROM bookings b 
            LEFT JOIN items i ON b.room_id = i.id 
            WHERE b.id = ?
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
            echo json_encode(['success' => false, 'error' => 'Booking not found']);
        }

        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }

    exit;
}


