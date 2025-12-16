<?php
/**
 * Migration: Ensure admins table has correct structure
 * Adds missing columns and indexes if they don't exist
 */

require_once __DIR__ . '/../db_connect.php';

echo "Checking admins table structure...\n";

try {
    // Helper function to check if column exists
    function columnExists($conn, $table, $column) {
        $result = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
        return $result && $result->num_rows > 0;
    }
    
    // Check if table exists
    $table_exists = $conn->query("SHOW TABLES LIKE 'admins'");
    
    if ($table_exists->num_rows == 0) {
        // Create table if it doesn't exist
        $sql = "CREATE TABLE `admins` (
            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `username` VARCHAR(100) UNIQUE NOT NULL,
            `password` VARCHAR(255) NOT NULL,
            `email` VARCHAR(255) NULL,
            `full_name` VARCHAR(255) NULL,
            `role` ENUM('super_admin', 'admin', 'manager', 'staff') DEFAULT 'staff',
            `is_active` BOOLEAN DEFAULT TRUE,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `last_login` TIMESTAMP NULL,
            INDEX `idx_username` (`username`),
            INDEX `idx_role` (`role`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if ($conn->query($sql)) {
            echo "✅ Admins table created successfully\n";
            
            // Create default admin account
            $username = 'admin';
            $email = 'admin@barcie.com';
            $password = password_hash('admin', PASSWORD_BCRYPT);
            $role = 'super_admin';
            
            $stmt = $conn->prepare("INSERT INTO `admins` (`username`, `email`, `password`, `role`) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $email, $password, $role);
            
            if ($stmt->execute()) {
                echo "✅ Default admin account created (username: admin, password: admin)\n";
                echo "⚠️  IMPORTANT: Change the default password immediately!\n";
            }
            $stmt->close();
        } else {
            throw new Exception("Failed to create admins table: " . $conn->error);
        }
    } else {
        echo "✓ Admins table exists\n";
        
        // Add missing columns
        $columns_to_add = array(
            'full_name' => "ADD COLUMN `full_name` VARCHAR(255) NULL AFTER `email`",
            'role' => "ADD COLUMN `role` ENUM('super_admin', 'admin', 'manager', 'staff') DEFAULT 'staff' AFTER `full_name`",
            'is_active' => "ADD COLUMN `is_active` BOOLEAN DEFAULT TRUE AFTER `role`",
            'updated_at' => "ADD COLUMN `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`"
        );
        
        foreach ($columns_to_add as $column => $alter_sql) {
            if (!columnExists($conn, 'admins', $column)) {
                if ($conn->query("ALTER TABLE `admins` $alter_sql")) {
                    echo "✅ Added column: $column\n";
                } else {
                    echo "⚠️  Warning: Could not add column $column: " . $conn->error . "\n";
                }
            }
        }
        
        // Ensure username is unique
        $conn->query("ALTER TABLE `admins` MODIFY COLUMN `username` VARCHAR(100) UNIQUE NOT NULL");
        
        // Hash any plain text passwords
        $result = $conn->query("SELECT `id`, `username`, `password` FROM `admins`");
        $updated = 0;
        
        while ($row = $result->fetch_assoc()) {
            // Check if password is NOT hashed (bcrypt hashes are 60 chars and start with $2y$)
            if (strlen($row['password']) != 60 || substr($row['password'], 0, 4) != '$2y$') {
                $hashed = password_hash($row['password'], PASSWORD_BCRYPT);
                $stmt = $conn->prepare("UPDATE `admins` SET `password` = ? WHERE `id` = ?");
                $stmt->bind_param("si", $hashed, $row['id']);
                $stmt->execute();
                $stmt->close();
                $updated++;
            }
        }
        
        if ($updated > 0) {
            echo "✅ Hashed $updated plain text password(s)\n";
        }
    }
    
    echo "✅ Admins table structure is up to date\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Migration completed.\n";
