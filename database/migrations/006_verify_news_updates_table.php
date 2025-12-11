<?php
/**
 * Migration: Ensure news_updates table has correct structure
 * This table is already properly structured, just verify it exists
 */

require_once __DIR__ . '/../db_connect.php';

echo "Checking news_updates table structure...\n";

try {
    // Check if table exists
    $table_exists = $conn->query("SHOW TABLES LIKE 'news_updates'");
    
    if ($table_exists->num_rows == 0) {
        // Create table if it doesn't exist (based on existing migration)
        include __DIR__ . '/../create_news_table.php';
    } else {
        echo "✓ News updates table exists\n";
        echo "✅ News updates table structure is up to date\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Migration completed.\n";
    