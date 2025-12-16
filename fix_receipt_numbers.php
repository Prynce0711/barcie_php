<?php
require_once 'database/db_connect.php';

// Find bookings with 1970 dates in receipt_no
$result = $conn->query("SELECT id, receipt_no, created_at, type FROM bookings WHERE receipt_no LIKE '%19700101%' OR receipt_no IS NULL OR receipt_no = ''");

echo "Found " . $result->num_rows . " bookings with invalid receipt numbers\n\n";

$updated = 0;
while ($row = $result->fetch_assoc()) {
    $id = $row['id'];
    $oldReceipt = $row['receipt_no'];
    $createdAt = $row['created_at'];
    $type = $row['type'];
    
    // Generate new receipt number based on created_at
    if ($createdAt) {
        $date = date('Ymd', strtotime($createdAt));
    } else {
        $date = date('Ymd'); // Use today if no created_at
    }
    
    $prefix = 'BARCIE';
    $newReceipt = $prefix . '-' . $date . '-' . str_pad($id, 4, '0', STR_PAD_LEFT);
    
    // Update the receipt number
    $stmt = $conn->prepare("UPDATE bookings SET receipt_no = ? WHERE id = ?");
    $stmt->bind_param('si', $newReceipt, $id);
    
    if ($stmt->execute()) {
        echo "✓ Updated booking ID $id: $oldReceipt → $newReceipt\n";
        $updated++;
    } else {
        echo "✗ Failed to update booking ID $id: " . $stmt->error . "\n";
    }
    $stmt->close();
}

echo "\nTotal updated: $updated bookings\n";
