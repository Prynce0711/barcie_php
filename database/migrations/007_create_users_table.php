<?php
/**
 * Migration: Create users table if it doesn't exist
 * For future guest user account system
 */

require_once __DIR__ . '/../db_connect.php';

echo "Checking users table...\n";

try {
    // Check if table exists
    $table_exists = $conn->query("SHOW TABLES LIKE 'users'");
    
    if ($table_exists->num_rows == 0) {
        // Create table for future use
        $sql = "CREATE TABLE `users` (
            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `username` VARCHAR(100) UNIQUE NOT NULL,
            `email` VARCHAR(255) UNIQUE NOT NULL,
            `password` VARCHAR(255) NOT NULL,
            `full_name` VARCHAR(255) NULL,
            `phone` VARCHAR(50) NULL,
            `address` TEXT NULL,
            `is_active` BOOLEAN DEFAULT TRUE,
            `email_verified` BOOLEAN DEFAULT FALSE,
            `verification_token` VARCHAR(255) NULL,
            `reset_token` VARCHAR(255) NULL,
            `reset_token_expires` DATETIME NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `last_login` TIMESTAMP NULL,
            INDEX `idx_email` (`email`),
            INDEX `idx_username` (`username`),
            INDEX `idx_is_active` (`is_active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if ($conn->query($sql)) {
            echo "✅ Users table created successfully\n";
        } else {
            throw new Exception("Failed to create users table: " . $conn->error);
        }
    } else {
        echo "✓ Users table already exists\n";
    }
    
    echo "✅ Users table check completed\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Migration completed.\n";
