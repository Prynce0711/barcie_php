<?php
header('Content-Type: application/json');
require_once '../database/db_connect.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
  echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
  exit;
}

$booking_id = intval($_GET['id']);

try {
  $query = "SELECT b.*, i.name as room_name, i.room_number
            FROM bookings b
            LEFT JOIN items i ON b.room_id = i.id
            WHERE b.id = ?";
  
  $stmt = $conn->prepare($query);
  $stmt->bind_param('i', $booking_id);
  $stmt->execute();
  $result = $stmt->get_result();
  
  if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Booking not found']);
    exit;
  }
  
  $booking = $result->fetch_assoc();
  
  // Extract guest info from details if not in separate columns
  if (empty($booking['guest_name']) && !empty($booking['details'])) {
    if (preg_match('/Guest:\s*([^|]+)/', $booking['details'], $matches)) {
      $booking['guest_name'] = trim($matches[1]);
    }
  }
  
  if (empty($booking['guest_contact']) && !empty($booking['details'])) {
    // Try to extract phone number
    if (preg_match('/(\d{10,11})/', $booking['details'], $matches)) {
      $booking['guest_contact'] = $matches[1];
    }
    // Or email
    if (preg_match('/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/', $booking['details'], $matches)) {
      if (empty($booking['guest_contact'])) {
        $booking['guest_contact'] = $matches[1];
      } else {
        $booking['guest_contact'] .= ' | ' . $matches[1];
      }
    }
  }
  
  // Add room number to room name if available
  if (!empty($booking['room_number'])) {
    $booking['room_name'] .= ' #' . $booking['room_number'];
  }
  
  echo json_encode(['success' => true, 'booking' => $booking]);
  
} catch (Exception $e) {
  echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
?>
