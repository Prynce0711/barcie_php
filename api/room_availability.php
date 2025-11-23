<?php
require __DIR__ . '/bootstrap.php';

header('Content-Type: application/json');

try {
    $room_id = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 0;
    
    if ($room_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid room ID']);
        exit;
    }
    
    // Get occupied dates for this room from both regular bookings and pencil bookings
    $occupied_dates = [];
    
    // Check regular bookings
    $stmt = $conn->prepare("
        SELECT checkin, checkout 
        FROM bookings 
        WHERE room_id = ? 
        AND status IN ('confirmed', 'approved', 'pending', 'checked_in')
        AND checkout >= CURDATE()
    ");
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $start = strtotime($row['checkin']);
        $end = strtotime($row['checkout']);
        
        // Add each date in the range, EXCLUDING checkout date (checkout day is available for next booking)
        for ($date = $start; $date < $end; $date += 86400) {
            $occupied_dates[] = date('Y-m-d', $date);
        }
    }
    $stmt->close();
    
    // Check pencil bookings
    $stmt2 = $conn->prepare("
        SELECT checkin, checkout 
        FROM pencil_bookings 
        WHERE room_id = ? 
        AND status IN ('approved', 'pending', 'confirmed')
        AND checkout >= CURDATE()
        AND expires_at >= NOW()
    ");
    $stmt2->bind_param("i", $room_id);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    
    while ($row = $result2->fetch_assoc()) {
        $start = strtotime($row['checkin']);
        $end = strtotime($row['checkout']);
        
        // Add each date in the range, EXCLUDING checkout date (checkout day is available for next booking)
        for ($date = $start; $date < $end; $date += 86400) {
            $occupied_dates[] = date('Y-m-d', $date);
        }
    }
    $stmt2->close();
    
    // Remove duplicates
    $occupied_dates = array_unique($occupied_dates);
    
    echo json_encode([
        'success' => true,
        'room_id' => $room_id,
        'occupied_dates' => array_values($occupied_dates)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}

$conn->close();
exit;
