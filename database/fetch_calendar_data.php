<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

try {
    $query = "SELECT b.*, i.name as room_name, i.item_type 
              FROM bookings b 
              LEFT JOIN items i ON b.item_id = i.id 
              WHERE b.status NOT IN ('cancelled', 'rejected')";
              
    $result = $mysqli->query($query);
    
    if (!$result) {
        throw new Exception("Database query failed");
    }
    
    $events = [];
    while($row = $result->fetch_assoc()) {
        // Set color based on booking status
        $color = '#ffa500'; // Default orange for pending
        switch($row['status']) {
            case 'confirmed':
                $color = '#28a745'; // Green
                break;
            case 'checked_in':
                $color = '#007bff'; // Blue
                break;
            case 'checked_out':
                $color = '#6c757d'; // Grey
                break;
        }
        
        // Calculate duration in days
        $checkin = strtotime($row['checkin']);
        $checkout = strtotime($row['checkout']);
        $duration = ceil(($checkout - $checkin) / (60 * 60 * 24));
        
        $events[] = [
            'title' => $row['item_type'] === 'room' ? 'Room Booked' : 'Facility Reserved',
            'start' => $row['checkin'],
            'end' => $row['checkout'],
            'color' => $color,
            'extendedProps' => [
                'facility' => $row['room_name'],
                'booking_status' => $row['status'],
                'duration_days' => $duration,
                'checkin_date' => $row['checkin'],
                'checkout_date' => $row['checkout']
            ]
        ];
    }
    
    echo json_encode($events);
    
} catch(Exception $e) {
    error_log("Calendar Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Unable to fetch calendar data',
        'details' => $e->getMessage()
    ]);
}