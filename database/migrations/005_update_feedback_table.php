<?php
/**
 * Migration: Ensure feedback table has correct structure
 * Adds missing columns and indexes if they don't exist
 */

require_once __DIR__ . '/../db_connect.php';

echo "Checking feedback table structure...\n";

try {
    // Helper function to check if column exists
    function columnExists($conn, $table, $column) {
        $result = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
        return $result && $result->num_rows > 0;
    }
    
    // Check if table exists
    $table_exists = $conn->query("SHOW TABLES LIKE 'feedback'");
    
    if ($table_exists->num_rows == 0) {
        // Create table if it doesn't exist
        $sql = "CREATE TABLE `feedback` (
            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `room_id` INT(11) NULL,
            `rating` INT(11) NOT NULL DEFAULT 5,
            `message` TEXT NULL,
            `feedback_name` VARCHAR(255) NULL,
            `feedback_email` VARCHAR(255) NULL,
            `is_anonymous` TINYINT(1) DEFAULT 0,
            `approval_status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
            `admin_response` TEXT NULL,
            `responded_by` INT(11) NULL,
            `responded_at` DATETIME NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_room_id` (`room_id`),
            INDEX `idx_rating` (`rating`),
            INDEX `idx_approval_status` (`approval_status`),
            INDEX `idx_created_at` (`created_at`),
            CONSTRAINT `chk_rating` CHECK (`rating` >= 1 AND `rating` <= 5)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if ($conn->query($sql)) {
            echo "✅ Feedback table created successfully\n";
        } else {
            throw new Exception("Failed to create feedback table: " . $conn->error);
        }
    } else {
        echo "✓ Feedback table exists\n";
        
        // Add missing columns
        $columns_to_add = array(
            'admin_response' => "ADD COLUMN `admin_response` TEXT NULL AFTER `approval_status`",
            'responded_by' => "ADD COLUMN `responded_by` INT(11) NULL AFTER `admin_response`",
            'responded_at' => "ADD COLUMN `responded_at` DATETIME NULL AFTER `responded_by`"
        );
        
        foreach ($columns_to_add as $column => $alter_sql) {
            if (!columnExists($conn, 'feedback', $column)) {
                if ($conn->query("ALTER TABLE `feedback` $alter_sql")) {
                    echo "✅ Added column: $column\n";
                } else {
                    echo "⚠️  Warning: Could not add column $column: " . $conn->error . "\n";
                }
            }
        }
    }
    
    echo "✅ Feedback table structure is up to date\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Migration completed.\n";
