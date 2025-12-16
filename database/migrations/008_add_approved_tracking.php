<?php
/**
 * Migration: Add approved tracking fields
 * Adds approved_by and approved_at columns to bookings and pencil_bookings tables
 */

require_once __DIR__ . '/../db_connect.php';

echo "Starting migration: Add approved tracking fields...\n";

try {
    // Check if columns exist in bookings table
    $result = $conn->query("SHOW COLUMNS FROM bookings LIKE 'approved_by'");
    if ($result->num_rows == 0) {
        echo "Adding approved_by column to bookings table...\n";
        $conn->query("ALTER TABLE bookings ADD COLUMN approved_by INT(11) NULL AFTER payment_verified_by");
    } else {
        echo "approved_by column already exists in bookings table.\n";
    }
    
    $result = $conn->query("SHOW COLUMNS FROM bookings LIKE 'approved_at'");
    if ($result->num_rows == 0) {
        echo "Adding approved_at column to bookings table...\n";
        $conn->query("ALTER TABLE bookings ADD COLUMN approved_at DATETIME NULL AFTER approved_by");
    } else {
        echo "approved_at column already exists in bookings table.\n";
    }
    
    // Check if columns exist in pencil_bookings table
    $result = $conn->query("SHOW COLUMNS FROM pencil_bookings LIKE 'approved_by'");
    if ($result->num_rows == 0) {
        echo "Adding approved_by column to pencil_bookings table...\n";
        $conn->query("ALTER TABLE pencil_bookings ADD COLUMN approved_by INT(11) NULL AFTER status");
    } else {
        echo "approved_by column already exists in pencil_bookings table.\n";
    }
    
    $result = $conn->query("SHOW COLUMNS FROM pencil_bookings LIKE 'approved_at'");
    if ($result->num_rows == 0) {
        echo "Adding approved_at column to pencil_bookings table...\n";
        $conn->query("ALTER TABLE pencil_bookings ADD COLUMN approved_at DATETIME NULL AFTER approved_by");
    } else {
        echo "approved_at column already exists in pencil_bookings table.\n";
    }
    
    // Add indexes for better performance
    echo "Adding indexes...\n";
    $conn->query("CREATE INDEX IF NOT EXISTS idx_approved_by ON bookings(approved_by)");
    $conn->query("CREATE INDEX IF NOT EXISTS idx_approved_at ON bookings(approved_at)");
    $conn->query("CREATE INDEX IF NOT EXISTS idx_approved_by_pencil ON pencil_bookings(approved_by)");
    $conn->query("CREATE INDEX IF NOT EXISTS idx_approved_at_pencil ON pencil_bookings(approved_at)");
    
    echo "✓ Migration completed successfully!\n";
    
} catch (Exception $e) {
    echo "✗ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
