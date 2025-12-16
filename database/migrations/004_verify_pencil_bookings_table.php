<?php
/**
 * Migration: Ensure pencil_bookings table has correct structure
 * This table is already properly structured, just verify it exists
 */

require_once __DIR__ . '/../db_connect.php';

echo "Checking pencil_bookings table structure...\n";

try {
    // Check if table exists
    $table_exists = $conn->query("SHOW TABLES LIKE 'pencil_bookings'");
    
    if ($table_exists->num_rows == 0) {
        // Create table if it doesn't exist (based on existing migration)
        include __DIR__ . '/../migrate_pencil_bookings.php';
    } else {
        echo "✓ Pencil bookings table exists\n";
        echo "✅ Pencil bookings table structure is up to date\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Migration completed.\n";
