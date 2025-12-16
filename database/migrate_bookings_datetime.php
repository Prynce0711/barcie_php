<?php
/**
 * Migration: Convert bookings checkin/checkout from DATE to DATETIME
 * This allows storing check-in and check-out times, not just dates
 */

require_once __DIR__ . '/db_connect.php';

echo "Starting migration: Convert bookings checkin/checkout to DATETIME\n";
echo "=================================================================\n\n";

try {
    // Check current column types
    $result = $conn->query("SHOW COLUMNS FROM bookings WHERE Field IN ('checkin', 'checkout')");
    echo "Current column types:\n";
    while ($row = $result->fetch_assoc()) {
        echo "  {$row['Field']}: {$row['Type']}\n";
    }
    echo "\n";

    // Convert checkin from DATE to DATETIME
    echo "Converting 'checkin' column from DATE to DATETIME...\n";
    $sql1 = "ALTER TABLE bookings MODIFY COLUMN checkin DATETIME NULL";
    if ($conn->query($sql1)) {
        echo "  ✓ Successfully converted 'checkin' to DATETIME\n";
    } else {
        throw new Exception("Failed to convert 'checkin': " . $conn->error);
    }

    // Convert checkout from DATE to DATETIME
    echo "Converting 'checkout' column from DATE to DATETIME...\n";
    $sql2 = "ALTER TABLE bookings MODIFY COLUMN checkout DATETIME NULL";
    if ($conn->query($sql2)) {
        echo "  ✓ Successfully converted 'checkout' to DATETIME\n";
    } else {
        throw new Exception("Failed to convert 'checkout': " . $conn->error);
    }

    echo "\n";
    
    // Verify the changes
    $result = $conn->query("SHOW COLUMNS FROM bookings WHERE Field IN ('checkin', 'checkout')");
    echo "New column types:\n";
    while ($row = $result->fetch_assoc()) {
        echo "  {$row['Field']}: {$row['Type']}\n";
    }
    
    echo "\n✅ Migration completed successfully!\n";
    echo "\nNote: Existing records will keep their dates but have time set to 00:00:00.\n";
    echo "New bookings should include time when inserting data.\n";

} catch (Exception $e) {
    echo "\n❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}

$conn->close();
?>
