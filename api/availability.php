<?php
require __DIR__ . '/bootstrap.php';

try {
    if (!table_exists($conn, 'bookings') || !table_exists($conn, 'items')) {
        json_error('Required tables missing', 500);
    }

    $query = "SELECT b.details, b.checkin, b.checkout, b.status, b.room_id, i.name as room_name, i.room_number
              FROM bookings b
              LEFT JOIN items i ON b.room_id = i.id
              WHERE b.status IN ('confirmed', 'approved', 'pending', 'checked_in')
              AND (b.checkin >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) OR b.checkin IS NULL)
              ORDER BY b.checkin ASC";
    $result = $conn->query($query);
    if (!$result) { json_error('Query failed: ' . $conn->error, 500); }

    $events = [];
    while ($row = $result->fetch_assoc()) {
        $room_facility = 'Room/Facility';
        if (!empty($row['room_name'])) {
            $room_facility = $row['room_name'];
            if (!empty($row['room_number'])) { $room_facility .= " (Room #" . $row['room_number'] . ")"; }
        } elseif (!empty($row['details'])) {
            $details = $row['details'];
            if (preg_match('/Item:\s*([^|]+)/i', $details, $m) && !empty($m[1])) {
                $room_facility = trim($m[1]);
            } elseif (preg_match('/Room:\s*([^|]+)/i', $details, $m) && !empty($m[1])) {
                $room_facility = trim($m[1]);
            } elseif (preg_match('/Facility:\s*([^|]+)/i', $details, $m) && !empty($m[1])) {
                $room_facility = trim($m[1]);
            }
        }

        $start = $row['checkin'] ?: date('Y-m-d');
        $end = $row['checkout'] ?: date('Y-m-d', strtotime($start . ' +1 day'));
        $calendar_end = date('Y-m-d', strtotime($end . ' +1 day'));
        $duration_days = (strtotime($end) - strtotime($start)) / 86400;

        $color = '#dc3545';
        $status_text = 'Occupied';
        if ($row['status'] === 'pending') { $color = '#ffc107'; $status_text = 'Pending'; }
        elseif ($row['status'] === 'checked_in') { $color = '#17a2b8'; $status_text = 'Occupied'; }

        $events[] = [
            'title' => $room_facility . ' - ' . $status_text . ($duration_days > 1 ? " ({$duration_days} days)" : ' (1 day)'),
            'start' => $start,
            'end' => $calendar_end,
            'backgroundColor' => $color,
            'borderColor' => $color,
            'textColor' => '#ffffff',
            'allDay' => true,
            'extendedProps' => [
                'facility' => $room_facility,
                'status' => strtolower($status_text),
                'booking_status' => $row['status'],
                'checkin_date' => $start,
                'checkout_date' => $end,
                'duration_days' => $duration_days,
            ],
        ];
    }

    while (ob_get_level()) { ob_end_clean(); }
    echo json_encode($events);
    exit;
} catch (Throwable $e) {
    json_error('Failed to fetch availability data', 500, ['message' => $e->getMessage(), 'events' => []]);
}
