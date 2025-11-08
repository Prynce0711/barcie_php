<?php
/**
 * Database Migration: Add images column to items table
 * This script adds support for multiple images per room/facility
 * 
 * Run this once to upgrade your database schema
 */

require_once __DIR__ . '/db_connect.php';

echo "Starting database migration to add images column...\n";

try {
    // Check if images column already exists
    $check_query = "SHOW COLUMNS FROM items LIKE 'images'";
    $result = $conn->query($check_query);
    
    if ($result->num_rows > 0) {
        echo "✓ Images column already exists. No migration needed.\n";
    } else {
        // Add images column
        $alter_query = "ALTER TABLE items ADD COLUMN images TEXT NULL AFTER image";
        if ($conn->query($alter_query)) {
            echo "✓ Successfully added 'images' column to items table.\n";
            
            // Migrate existing single images to the new images array format
            echo "Migrating existing single images to array format...\n";
            
            $migrate_query = "UPDATE items SET images = JSON_ARRAY(image) WHERE image IS NOT NULL AND image != '' AND (images IS NULL OR images = '')";
            if ($conn->query($migrate_query)) {
                echo "✓ Successfully migrated " . $conn->affected_rows . " existing images.\n";
            } else {
                echo "⚠ Warning: Could not migrate existing images: " . $conn->error . "\n";
            }
            
        } else {
            throw new Exception("Failed to add images column: " . $conn->error);
        }
    }
    
    echo "\n✓ Database migration completed successfully!\n";
    echo "\nYou can now upload multiple images for each room/facility.\n";
    
} catch (Exception $e) {
    echo "✗ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}

$conn->close();
?>
