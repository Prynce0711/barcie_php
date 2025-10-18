<?php
require __DIR__ . '/bootstrap.php';

try {
    if (!table_exists($conn, 'items') || !table_exists($conn, 'bookings')) {
        json_error('Required tables missing', 500);
    }

    $query = "SELECT COUNT(*) as available_count FROM items i 
              WHERE (i.room_status = 'available' OR i.room_status = 'clean')
              AND i.id NOT IN (
                  SELECT DISTINCT b.room_id 
                  FROM bookings b 
                  WHERE b.room_id IS NOT NULL 
                  AND b.status IN ('confirmed', 'approved', 'pending', 'checked_in')
                  AND (
                      (b.checkin <= CURDATE() AND b.checkout >= CURDATE()) OR
                      (b.checkin = CURDATE())
                  )
              )";
    $res = $conn->query($query);
    if (!$res) { json_error('Query failed: ' . $conn->error, 500); }
    $row = $res->fetch_assoc();
    $count = (int)($row['available_count'] ?? 0);
    json_ok(['available_count' => $count, 'query_time' => date('Y-m-d H:i:s')]);
} catch (Throwable $e) {
    json_error('Failed to get available count', 500, ['message' => $e->getMessage(), 'available_count' => 0]);
}
