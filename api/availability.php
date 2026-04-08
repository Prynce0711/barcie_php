<?php
require __DIR__ . '/Bootstrap.php';

// Prevent caching
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

try {
    if (!table_exists($conn, 'bookings') || !table_exists($conn, 'items')) {
        json_error('Required tables missing', 500);
    }

    // Support filtering by item_id for room-specific calendars
    $item_id_filter = '';
    $item_id = null;
    if (isset($_GET['item_id']) && is_numeric($_GET['item_id'])) {
        $item_id = intval($_GET['item_id']);
        $item_id_filter = " AND b.room_id = $item_id";
    }

    // Optional filter by item_type (room|facility) to optimize server-side filtering
    $item_type_filter = '';
    if (isset($_GET['item_type'])) {
        $t = strtolower(trim($_GET['item_type']));
        if (in_array($t, ['room', 'facility'])) {
            // safe to include since whitelisted
            $item_type_filter = " AND i.item_type = '" . $conn->real_escape_string($t) . "'";
        }
    }

    $events = [];

    // FETCH REGULAR BOOKINGS
    $query = "SELECT b.details, b.checkin, b.checkout, b.status, b.room_id, i.name as room_name, i.room_number, i.item_type
              FROM bookings b
              LEFT JOIN items i ON b.room_id = i.id
              WHERE b.status IN ('confirmed', 'approved', 'pending', 'checked_in')
              AND (b.checkin >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) OR b.checkin IS NULL)
              $item_id_filter
              $item_type_filter
              ORDER BY b.checkin ASC";
    $result = $conn->query($query);
    if (!$result) { json_error('Query failed: ' . $conn->error, 500); }

    while ($row = $result->fetch_assoc()) {
        $room_facility = 'Room/Facility';
        if (!empty($row['room_name'])) {
            $room_facility = $row['room_name'];
            if (!empty($row['room_number'])) { $room_facility .= " (#" . $row['room_number'] . ")"; }
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
        // If checkout is null/empty, default to same day (1 day event)
        $end = $row['checkout'] ?: $start;
        // Calendar end is always +1 day for FullCalendar's exclusive end date
        $calendar_end = date('Y-m-d', strtotime($end . ' +1 day'));
        $duration_days = ceil((strtotime($end) - strtotime($start)) / 86400);
        // Ensure at least 1 day
        if ($duration_days < 1) $duration_days = 1;

        $color = '#dc3545';
        $status_text = 'Booked';
        if ($row['status'] === 'pending') { $color = '#ffc107'; $status_text = 'Pending'; }
        elseif ($row['status'] === 'checked_in') { $color = '#17a2b8'; $status_text = 'Occupied'; }

        // Format title with date range if multi-day
        $title = $room_facility . ' - ' . $status_text;
        if ($duration_days > 1) {
            $title .= ' (' . date('j', strtotime($start)) . '-' . date('j', strtotime($end)) . ')';
        }

        $events[] = [
            'title' => $title,
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
                'booking_type' => 'regular',
                'checkin_date' => $start,
                'checkout_date' => $end,
                'duration_days' => $duration_days,
                'item_id' => $row['room_id'],
                'room_id' => $row['room_id'],
            ],
        ];
    }

    // FETCH PENCIL BOOKINGS (if table exists)
    if (table_exists($conn, 'pencil_bookings')) {
        $pencil_filter = str_replace('b.room_id', 'pb.room_id', $item_id_filter);
        $pencil_type_filter = str_replace('i.item_type', 'pi.item_type', $item_type_filter);
        
        $pencil_query = "SELECT pb.checkin, pb.checkout, pb.status, pb.room_id, pb.guest_name, 
                         pi.name as room_name, pi.room_number, pi.item_type
                  FROM pencil_bookings pb
                  LEFT JOIN items pi ON pb.room_id = pi.id
                  WHERE pb.status IN ('approved', 'pending', 'confirmed')
                  AND pb.token_expires_at >= NOW()
                  AND (pb.checkin >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) OR pb.checkin IS NULL)
                  $pencil_filter
                  $pencil_type_filter
                  ORDER BY pb.checkin ASC";
        $pencil_result = $conn->query($pencil_query);
        
        if ($pencil_result) {
            while ($prow = $pencil_result->fetch_assoc()) {
                $room_facility = 'Room/Facility';
                if (!empty($prow['room_name'])) {
                    $room_facility = $prow['room_name'];
                    if (!empty($prow['room_number'])) { $room_facility .= " (#" . $prow['room_number'] . ")"; }
                }

                $start = $prow['checkin'] ?: date('Y-m-d');
                // If checkout is null/empty, default to same day (1 day event)
                $end = $prow['checkout'] ?: $start;
                // Calendar end is always +1 day for FullCalendar's exclusive end date
                $calendar_end = date('Y-m-d', strtotime($end . ' +1 day'));
                $duration_days = ceil((strtotime($end) - strtotime($start)) / 86400);
                // Ensure at least 1 day
                if ($duration_days < 1) $duration_days = 1;

                // Format title with date range if multi-day
                $title = $room_facility . ' - Pencil';
                if ($duration_days > 1) {
                    $title .= ' (' . date('j', strtotime($start)) . '-' . date('j', strtotime($end)) . ')';
                }
                
                // Pencil bookings shown in orange/amber color
                $events[] = [
                    'title' => $title,
                    'start' => $start,
                    'end' => $calendar_end,
                    'backgroundColor' => '#fd7e14',
                    'borderColor' => '#fd7e14',
                    'textColor' => '#ffffff',
                    'allDay' => true,
                    'extendedProps' => [
                        'facility' => $room_facility,
                        'status' => 'pencil',
                        'booking_status' => $prow['status'],
                        'booking_type' => 'pencil',
                        'guest_name' => $prow['guest_name'],
                        'checkin_date' => $start,
                        'checkout_date' => $end,
                        'duration_days' => $duration_days,
                        'item_id' => $prow['room_id'],
                        'room_id' => $prow['room_id'],
                    ],
                ];
            }
        }
    }

    while (ob_get_level()) { ob_end_clean(); }
    echo json_encode($events);
    exit;
} catch (Throwable $e) {
    json_error('Failed to fetch availability data', 500, ['message' => $e->getMessage(), 'events' => []]);
}
