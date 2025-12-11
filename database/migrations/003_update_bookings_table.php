<?php
/**
 * Migration: Ensure bookings table has correct structure
 * Adds missing columns and indexes if they don't exist
 */

require_once __DIR__ . '/../db_connect.php';

echo "Checking bookings table structure...\n";

try {
    // Helper function to check if column exists
    function columnExists($conn, $table, $column) {
        $result = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
        return $result && $result->num_rows > 0;
    }
    
    // Check if table exists
    $table_exists = $conn->query("SHOW TABLES LIKE 'bookings'");
    
    if ($table_exists->num_rows == 0) {
        // Create table if it doesn't exist
        $sql = "CREATE TABLE `bookings` (
            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `receipt_no` VARCHAR(50) NULL,
            `room_id` INT(11) NULL,
            `type` ENUM('reservation', 'pencil', 'facility') NOT NULL DEFAULT 'reservation',
            `details` TEXT NOT NULL,
            `status` VARCHAR(50) DEFAULT 'Pending',
            `discount_status` VARCHAR(50) DEFAULT 'none',
            `proof_of_id` VARCHAR(255) NULL,
            `payment_status` VARCHAR(50) DEFAULT 'none',
            `proof_of_payment` VARCHAR(255) NULL,
            `payment_verified_by` INT(11) NULL,
            `payment_verified_at` DATETIME NULL,
            `payment_date` TIMESTAMP NULL,
            `checkin` DATETIME NULL,
            `checkout` DATETIME NULL,
            `checked_out_at` TIMESTAMP NULL,
            `reminder_sent` TINYINT(1) DEFAULT 0,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_status` (`status`),
            INDEX `idx_room_id` (`room_id`),
            INDEX `idx_checkin` (`checkin`),
            INDEX `idx_checkout` (`checkout`),
            INDEX `idx_discount_status` (`discount_status`),
            INDEX `idx_payment_status` (`payment_status`),
            INDEX `idx_created_at` (`created_at`),
            INDEX `idx_payment_date` (`payment_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if ($conn->query($sql)) {
            echo "✅ Bookings table created successfully\n";
        } else {
            throw new Exception("Failed to create bookings table: " . $conn->error);
        }
    } else {
        echo "✓ Bookings table exists\n";
        
        // Add missing columns
        $columns_to_add = array(
            'reminder_sent' => "ADD COLUMN `reminder_sent` TINYINT(1) DEFAULT 0 AFTER `discount_status`",
            'payment_date' => "ADD COLUMN `payment_date` TIMESTAMP NULL AFTER `payment_verified_at`",
            'checked_out_at' => "ADD COLUMN `checked_out_at` TIMESTAMP NULL AFTER `payment_date`"
        );
        
        foreach ($columns_to_add as $column => $alter_sql) {
            if (!columnExists($conn, 'bookings', $column)) {
                if ($conn->query("ALTER TABLE `bookings` $alter_sql")) {
                    echo "✅ Added column: $column\n";
                } else {
                    echo "⚠️  Warning: Could not add column $column: " . $conn->error . "\n";
                }
            }
        }
        
        // Ensure type enum includes all values
        $conn->query("ALTER TABLE `bookings` MODIFY COLUMN `type` ENUM('reservation', 'pencil', 'facility') NOT NULL DEFAULT 'reservation'");
    }
    
    echo "✅ Bookings table structure is up to date\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Migration completed.\n";
