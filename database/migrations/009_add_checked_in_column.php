<?php
/**
 * Migration: Add checked_in_at column
 * Adds checked_in_at to bookings table to record actual check-in time
 */

require_once __DIR__ . '/../db_connect.php';

echo "Starting migration: Add checked_in_at column...\n";

try {
    $result = $conn->query("SHOW COLUMNS FROM bookings LIKE 'checked_in_at'");
    if ($result->num_rows == 0) {
        echo "Adding checked_in_at column to bookings table...\n";
        $conn->query("ALTER TABLE bookings ADD COLUMN checked_in_at DATETIME NULL AFTER checkin");
    } else {
        echo "checked_in_at column already exists in bookings table.\n";
    }

    echo "✓ Migration completed successfully!\n";
} catch (Exception $e) {
    echo "✗ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
