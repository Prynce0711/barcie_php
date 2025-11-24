<?php
/**
 * Pencil Bookings Table Migration
 * Creates or updates the pencil_bookings table with all necessary columns
 */

require_once __DIR__ . '/db_connect.php';

try {
    echo "Starting pencil_bookings table migration...\n<br>";
    
    // Create table if it doesn't exist
    $create_table_sql = "CREATE TABLE IF NOT EXISTS pencil_bookings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        receipt_no VARCHAR(50) UNIQUE NOT NULL,
        room_id INT NOT NULL,
        guest_name VARCHAR(255) NOT NULL,
        contact_number VARCHAR(50) NOT NULL,
        email VARCHAR(255) NOT NULL,
        checkin DATETIME NOT NULL,
        checkout DATETIME NOT NULL,
        occupants INT NOT NULL DEFAULT 1,
        company VARCHAR(255) NULL,
        company_contact VARCHAR(50) NULL,
        discount_code VARCHAR(50) NULL,
        discount_proof_path VARCHAR(255) NULL,
        discount_amount DECIMAL(10,2) DEFAULT 0.00,
        base_price DECIMAL(10,2) NOT NULL,
        total_price DECIMAL(10,2) NOT NULL,
        status ENUM('pending', 'confirmed', 'approved', 'converted', 'expired', 'cancelled') DEFAULT 'pending',
        terms_acknowledged TINYINT(1) DEFAULT 0,
        acknowledgment_timestamp DATETIME NULL,
        conversion_token VARCHAR(255) NULL UNIQUE,
        token_expires_at DATETIME NULL,
        converted_booking_receipt VARCHAR(50) NULL,
        details TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_receipt_no (receipt_no),
        INDEX idx_room_id (room_id),
        INDEX idx_email (email),
        INDEX idx_status (status),
        INDEX idx_conversion_token (conversion_token),
        INDEX idx_token_expires (token_expires_at),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($create_table_sql)) {
        echo "✓ pencil_bookings table created/verified successfully\n<br>";
    } else {
        throw new Exception("Failed to create table: " . $conn->error);
    }
    
    // Function to check if column exists
    function columnExists($conn, $table, $column) {
        $result = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
        return $result && $result->num_rows > 0;
    }
    
    // Add missing columns if they don't exist
    $columns_to_add = [
        'conversion_token' => 'VARCHAR(255) NULL UNIQUE',
        'token_expires_at' => 'DATETIME NULL',
        'converted_booking_receipt' => 'VARCHAR(50) NULL'
    ];
    
    foreach ($columns_to_add as $column => $definition) {
        if (!columnExists($conn, 'pencil_bookings', $column)) {
            $alter_sql = "ALTER TABLE pencil_bookings ADD COLUMN `$column` $definition";
            if ($conn->query($alter_sql)) {
                echo "✓ Added column: $column\n<br>";
            } else {
                echo "⚠ Warning: Could not add column $column: " . $conn->error . "\n<br>";
            }
        } else {
            echo "• Column already exists: $column\n<br>";
        }
    }
    
    // Add index for conversion_token if it doesn't exist
    try {
        $conn->query("ALTER TABLE pencil_bookings ADD INDEX idx_conversion_token (conversion_token)");
        echo "✓ Added index for conversion_token\n<br>";
    } catch (Exception $e) {
        echo "• Index already exists or could not be added: conversion_token\n<br>";
    }
    
    // Add index for token_expires_at if it doesn't exist
    try {
        $conn->query("ALTER TABLE pencil_bookings ADD INDEX idx_token_expires (token_expires_at)");
        echo "✓ Added index for token_expires_at\n<br>";
    } catch (Exception $e) {
        echo "• Index already exists or could not be added: token_expires_at\n<br>";
    }
    
    // Update status enum to include 'converted' if not already present
    try {
        $conn->query("ALTER TABLE pencil_bookings MODIFY COLUMN status ENUM('pending', 'confirmed', 'approved', 'converted', 'expired', 'cancelled') DEFAULT 'pending'");
        echo "✓ Updated status enum to include 'converted'\n<br>";
    } catch (Exception $e) {
        echo "⚠ Warning: Could not update status enum: " . $e->getMessage() . "\n<br>";
    }
    
    echo "\n<br><strong>Migration completed successfully!</strong>\n<br>";
    echo "<a href='../Guest.php'>← Back to Guest Page</a>\n<br>";
    
} catch (Exception $e) {
    echo "<strong style='color: red;'>Error:</strong> " . $e->getMessage() . "\n<br>";
    error_log("Pencil bookings migration error: " . $e->getMessage());
}

$conn->close();
?>
