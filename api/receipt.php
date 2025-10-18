<?php
require __DIR__ . '/bootstrap.php';

try {
    if (!table_exists($conn, 'bookings')) {
        json_error('Bookings table does not exist', 500);
    }

    // Ensure receipt_no column exists as VARCHAR(50)
    $check = $conn->query("SHOW COLUMNS FROM bookings LIKE 'receipt_no'");
    if ($check && $check->num_rows === 0) {
        $conn->query("ALTER TABLE bookings ADD COLUMN receipt_no VARCHAR(50) NULL AFTER id");
    } elseif ($check) {
        $info = $check->fetch_assoc();
        if (strpos(strtolower($info['Type']), 'int') !== false) {
            $conn->query("ALTER TABLE bookings DROP COLUMN receipt_no");
            $conn->query("ALTER TABLE bookings ADD COLUMN receipt_no VARCHAR(50) NULL AFTER id");
        }
    }

    $currentDate = date('Ymd');
    $pattern = "BARCIE-{$currentDate}-%";
    $stmt = $conn->prepare("SELECT receipt_no FROM bookings WHERE receipt_no LIKE ? ORDER BY receipt_no DESC LIMIT 1");
    if (!$stmt) { json_error('Prepare failed: ' . $conn->error, 500); }
    $stmt->bind_param('s', $pattern);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $parts = explode('-', $row['receipt_no']);
        $last = isset($parts[2]) ? (int)$parts[2] : 0;
        $next = $last + 1;
    } else {
        $next = 1;
    }
    $stmt->close();

    $formatted = str_pad($next, 4, '0', STR_PAD_LEFT);
    $receipt = "BARCIE-{$currentDate}-{$formatted}";
    json_ok(['receipt_no' => $receipt, 'next_number' => $next, 'date' => $currentDate]);
} catch (Throwable $e) {
    json_error('Failed to generate receipt number', 500, ['message' => $e->getMessage()]);
}
