<?php
/* ---------------------------
   ADMIN: Get booking details for payment verification
   --------------------------- */
if ($action === 'get_booking_details') {
    if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Access denied. Admin login required.']);
        exit;
    }

    $bookingId = (int) ($_POST['booking_id'] ?? 0);

    if ($bookingId <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Invalid booking ID.']);
        exit;
    }

    // Get booking details with room information
    $stmt = $conn->prepare("SELECT b.*, i.name as room_name, i.room_number, i.price as room_price 
                            FROM bookings b 
                            LEFT JOIN items i ON b.room_id = i.id 
                            WHERE b.id = ?");
    $stmt->bind_param("i", $bookingId);
    $stmt->execute();
    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();
    $stmt->close();

    if (!$booking) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Booking not found.']);
        exit;
    }

    // Extract guest info from details
    $guest_name = 'Guest';
    $guest_phone = '';
    $guest_email = '';

    if (preg_match('/Guest:\s*([^|]+)/', $booking['details'], $matches)) {
        $guest_name = trim($matches[1]);
    }
    if (preg_match('/Contact:\s*([^|]+)/', $booking['details'], $matches)) {
        $guest_phone = trim($matches[1]);
    }
    if (preg_match('/Email:\s*([^|]+)/', $booking['details'], $matches)) {
        $guest_email = trim($matches[1]);
    }

    // Build response
    $response = [
        'success' => true,
        'booking' => [
            'id' => $booking['id'],
            'receipt_no' => $booking['receipt_no'],
            'guest_name' => $guest_name,
            'guest_email' => $guest_email,
            'guest_phone' => $guest_phone,
            'guest_age' => $booking['guest_age'],
            'room_name' => $booking['room_name'] . ($booking['room_number'] ? ' #' . $booking['room_number'] : ''),
            'checkin' => $booking['checkin'] ? date('M j, Y g:i A', strtotime($booking['checkin'])) : 'N/A',
            'checkout' => $booking['checkout'] ? date('M j, Y g:i A', strtotime($booking['checkout'])) : 'N/A',
            'amount' => $booking['amount'],
            'room_price' => $booking['room_price'],
            'add_ons' => $booking['add_ons'] ?: null,
            'proof_of_id' => $booking['proof_of_id'] ?: null,
            'proof_of_payment' => $booking['proof_of_payment'] ?: null,
            'status' => $booking['status'],
            'payment_status' => $booking['payment_status'],
            'created_at' => date('M j, Y g:i A', strtotime($booking['created_at']))
        ]
    ];

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}


