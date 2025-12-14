<?php
require_once 'database/db_connect.php';

$startDate = '2025-11-01';
$endDate = '2025-12-31';

echo "=== TESTING REVENUE QUERIES ===\n\n";

// Test 1: Total revenue with approved status
$sql = "SELECT COALESCE(SUM(b.amount), 0) as total FROM bookings b
        LEFT JOIN items i ON b.room_id = i.id
        WHERE b.checkin BETWEEN ? AND ?
        AND b.status IN ('confirmed', 'approved', 'checked_in', 'checked_out', '0')";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $startDate, $endDate);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
echo "Total Revenue (with approved): ₱" . number_format($result['total'], 2) . "\n\n";

// Test 2: Revenue by room
$sql = "SELECT i.name as room_name, COALESCE(SUM(b.amount), 0) as revenue, COUNT(*) as bookings
        FROM bookings b
        LEFT JOIN items i ON b.room_id = i.id
        WHERE b.checkin BETWEEN ? AND ?
        AND b.status IN ('confirmed', 'approved', 'checked_in', 'checked_out', '0')
        GROUP BY i.name ORDER BY revenue DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $startDate, $endDate);
$stmt->execute();
$result = $stmt->get_result();

echo "=== REVENUE BY ROOM ===\n";
while ($row = $result->fetch_assoc()) {
    echo $row['room_name'] . ": ₱" . number_format($row['revenue'], 2) . " (" . $row['bookings'] . " bookings)\n";
}

// Test 3: Monthly revenue
echo "\n=== MONTHLY REVENUE ===\n";
$sql = "SELECT DATE_FORMAT(b.checkin, '%Y-%m') as month,
        DATE_FORMAT(b.checkin, '%M %Y') as month_name,
        COALESCE(SUM(b.amount), 0) as revenue, COUNT(*) as bookings
        FROM bookings b
        WHERE b.checkin BETWEEN ? AND ?
        AND b.status IN ('confirmed', 'approved', 'checked_in', 'checked_out', '0')
        GROUP BY DATE_FORMAT(b.checkin, '%Y-%m') ORDER BY month DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $startDate, $endDate);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    echo $row['month_name'] . ": ₱" . number_format($row['revenue'], 2) . " (" . $row['bookings'] . " bookings)\n";
}
