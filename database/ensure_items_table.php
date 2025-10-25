<?php
/**
 * Database Migration: Ensure items table has all required columns
 * Run this script to update the items table structure
 */

require_once __DIR__ . '/../src/database/db_connect.php';

echo "Starting database migration for items table...\n";

try {
    // Check if room_status column exists
    $result = $conn->query("SHOW COLUMNS FROM items LIKE 'room_status'");
    
    if ($result->num_rows == 0) {
        echo "Adding room_status column...\n";
        $conn->query("ALTER TABLE items ADD COLUMN room_status ENUM('available', 'occupied', 'maintenance', 'clean') DEFAULT 'available' AFTER image");
        echo "✓ room_status column added successfully\n";
    } else {
        echo "✓ room_status column already exists\n";
    }
    
    // Update any NULL room_status to 'available'
    $conn->query("UPDATE items SET room_status = 'available' WHERE room_status IS NULL");
    echo "✓ Updated NULL room_status values to 'available'\n";
    
    // Check if created_at column exists
    $result = $conn->query("SHOW COLUMNS FROM items LIKE 'created_at'");
    
    if ($result->num_rows == 0) {
        echo "Adding created_at column...\n";
        $conn->query("ALTER TABLE items ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
        echo "✓ created_at column added successfully\n";
    } else {
        echo "✓ created_at column already exists\n";
    }
    
    // Verify the table structure
    echo "\nCurrent items table structure:\n";
    $result = $conn->query("DESCRIBE items");
    
    while ($row = $result->fetch_assoc()) {
        echo "  - {$row['Field']}: {$row['Type']}" . 
             ($row['Null'] == 'NO' ? ' NOT NULL' : '') . 
             ($row['Default'] !== null ? " DEFAULT '{$row['Default']}'" : '') . "\n";
    }
    
    // Count total items
    $count_result = $conn->query("SELECT COUNT(*) as count FROM items");
    $count = $count_result->fetch_assoc()['count'];
    echo "\nTotal items in database: $count\n";
    
    echo "\n✅ Migration completed successfully!\n";
    
} catch (Exception $e) {
    echo "\n❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}

$conn->close();
?>
