<?php
require_once 'database/db_connect.php';

echo "=== ITEMS TABLE STRUCTURE ===\n";
$result = $conn->query('DESCRIBE items');
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}

echo "\n=== ROOM NAMES (FIRST 10) ===\n";
$result = $conn->query('SELECT name, item_type FROM items WHERE item_type = "room" LIMIT 10');
while ($row = $result->fetch_assoc()) {
    echo $row['name'] . " (" . $row['item_type'] . ")\n";
}

echo "\n=== BOOKINGS TABLE STRUCTURE ===\n";
$result = $conn->query('DESCRIBE bookings');
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}

echo "\n=== SAMPLE BOOKINGS (FIRST 10) ===\n";
$result = $conn->query('SELECT id, room_id, checkin, checkout, status, amount, created_at FROM bookings LIMIT 10');
while ($row = $result->fetch_assoc()) {
    echo "ID: " . $row['id'] . " | Room: " . $row['room_id'] . " | Check-in: " . $row['checkin'] . " | Status: " . $row['status'] . " | Amount: " . ($row['amount'] ?? 'NULL') . "\n";
}

echo "\n=== BOOKINGS WITH AMOUNTS (REVENUE CHECK) ===\n";
$result = $conn->query("SELECT status, COUNT(*) as count, SUM(amount) as total_amount, MIN(checkin) as earliest, MAX(checkin) as latest FROM bookings GROUP BY status");
while ($row = $result->fetch_assoc()) {
    echo "Status: " . $row['status'] . " | Count: " . $row['count'] . " | Total Amount: " . ($row['total_amount'] ?? 'NULL') . " | Date Range: " . $row['earliest'] . " to " . $row['latest'] . "\n";
}

echo "\n=== TOTAL COUNTS ===\n";
$result = $conn->query('SELECT COUNT(*) as count FROM items WHERE item_type = "room"');
$count = $result->fetch_assoc()['count'];
echo "Total rooms: " . $count . "\n";

$result = $conn->query('SELECT COUNT(*) as count FROM bookings');
$count = $result->fetch_assoc()['count'];
echo "Total bookings: " . $count . "\n";
