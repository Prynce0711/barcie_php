<?php
require_once 'database/db_connect.php';

$r = $conn->query('DESCRIBE bookings');
echo "Bookings table columns:\n\n";
while ($row = $r->fetch_assoc()) {
    echo $row['Field'] . ' (' . $row['Type'] . ')' . PHP_EOL;
}

echo "\n\nPencil Bookings table columns:\n\n";
$r2 = $conn->query('DESCRIBE pencil_bookings');
while ($row = $r2->fetch_assoc()) {
    echo $row['Field'] . ' (' . $row['Type'] . ')' . PHP_EOL;
}
