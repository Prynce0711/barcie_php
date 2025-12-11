<?php
/**
 * Migration: Ensure items table has correct structure
 * Adds missing columns and indexes if they don't exist
 */

require_once __DIR__ . '/../db_connect.php';

echo "Checking items table structure...\n";

try {
    // Helper function to check if column exists
    function columnExists($conn, $table, $column) {
        $result = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
        return $result && $result->num_rows > 0;
    }
    
    // Helper function to check if index exists
    function indexExists($conn, $table, $index) {
        $result = $conn->query("SHOW INDEX FROM `$table` WHERE Key_name = '$index'");
        return $result && $result->num_rows > 0;
    }
    
    // Check if table exists
    $table_exists = $conn->query("SHOW TABLES LIKE 'items'");
    
    if ($table_exists->num_rows == 0) {
        // Create table if it doesn't exist
        $sql = "CREATE TABLE `items` (
            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(255) NOT NULL,
            `item_type` ENUM('room', 'facility', 'amenity', 'service') NOT NULL DEFAULT 'room',
            `room_number` VARCHAR(50) NULL,
            `description` TEXT NULL,
            `capacity` INT(11) NULL DEFAULT 0,
            `price` DECIMAL(10,2) NULL DEFAULT 0.00,
            `average_rating` DECIMAL(3,2) NULL DEFAULT 0.00,
            `total_reviews` INT(11) NULL DEFAULT 0,
            `image` VARCHAR(255) NULL,
            `images` TEXT NULL COMMENT 'JSON array of image paths',
            `addons` LONGTEXT NULL COMMENT 'JSON array of add-ons',
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `room_status` ENUM('available', 'reserved', 'occupied', 'clean', 'dirty', 'maintenance', 'out_of_order') DEFAULT 'available',
            INDEX `idx_name` (`name`),
            INDEX `idx_item_type` (`item_type`),
            INDEX `idx_room_status` (`room_status`),
            INDEX `idx_average_rating` (`average_rating`),
            INDEX `idx_total_reviews` (`total_reviews`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if ($conn->query($sql)) {
            echo "✅ Items table created successfully\n";
        } else {
            throw new Exception("Failed to create items table: " . $conn->error);
        }
    } else {
        echo "✓ Items table exists\n";
        
        // Add missing columns
        $columns_to_check = [
            'images' => "ADD COLUMN `images` TEXT NULL COMMENT 'JSON array of image paths' AFTER `image`",
            'addons' => "ADD COLUMN `addons` LONGTEXT NULL COMMENT 'JSON array of add-ons' AFTER `images`",
            'room_status' => "ADD COLUMN `room_status` ENUM('available', 'reserved', 'occupied', 'clean', 'dirty', 'maintenance', 'out_of_order') DEFAULT 'available' AFTER `created_at`",
            'average_rating' => "ADD COLUMN `average_rating` DECIMAL(3,2) NULL DEFAULT 0.00 AFTER `price`",
            'total_reviews' => "ADD COLUMN `total_reviews` INT(11) NULL DEFAULT 0 AFTER `average_rating`"
        ];
        
        foreach ($columns_to_check as $column => $alter_sql) {
            if (!columnExists($conn, 'items', $column)) {
                if ($conn->query("ALTER TABLE `items` $alter_sql")) {
                    echo "✅ Added column: $column\n";
                } else {
                    echo "⚠️  Warning: Could not add column $column: " . $conn->error . "\n";
                }
            }
        }
        
        // Ensure item_type enum includes all values
        $conn->query("ALTER TABLE `items` MODIFY COLUMN `item_type` ENUM('room', 'facility', 'amenity', 'service') NOT NULL DEFAULT 'room'");
        
        // Add indexes if they don't exist
        $indexes_to_check = [
            'idx_name' => 'name',
            'idx_item_type' => 'item_type',
            'idx_room_status' => 'room_status',
            'idx_average_rating' => 'average_rating',
            'idx_total_reviews' => 'total_reviews'
        ];
        
        foreach ($indexes_to_check as $index_name => $column) {
            if (!indexExists($conn, 'items', $index_name)) {
                if ($conn->query("ALTER TABLE `items` ADD INDEX `$index_name` (`$column`)")) {
                    echo "✅ Added index: $index_name\n";
                }
            }
        }
    }
    
    echo "✅ Items table structure is up to date\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Migration completed.\n";
