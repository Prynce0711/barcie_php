<?php
require_once 'database/db_connect.php';

$result = $conn->query("SELECT id, details FROM bookings WHERE id IN (9,16,22) LIMIT 3");
while ($row = $result->fetch_assoc()) {
    echo "Booking ID: " . $row['id'] . "\n";
    echo "Details: " . $row['details'] . "\n";
    if ($row['details']) {
        $json = json_decode($row['details'], true);
        echo "Parsed keys: " . implode(", ", array_keys($json ?: [])) . "\n";
    }
    echo "\n---\n\n";
}
