<?php
/**
 * Database Structure Checker
 * Lists all tables and their structures in the database
 */

require_once __DIR__ . '/db_connect.php';

echo "=== BARCIE DATABASE STRUCTURE ===\n";
echo "Database: " . DB_NAME . "\n";
echo "Host: " . DB_HOST . "\n\n";

// Get all tables
$tables_result = $conn->query("SHOW TABLES");

if (!$tables_result) {
    die("Error getting tables: " . $conn->error . "\n");
}

$tables = [];
while ($row = $tables_result->fetch_array()) {
    $tables[] = $row[0];
}

echo "Found " . count($tables) . " tables:\n";
echo str_repeat("=", 70) . "\n\n";

foreach ($tables as $table) {
    echo "TABLE: $table\n";
    echo str_repeat("-", 70) . "\n";
    
    // Get table structure
    $columns_result = $conn->query("SHOW COLUMNS FROM `$table`");
    
    if ($columns_result) {
        echo sprintf("%-25s %-30s %-10s %-10s\n", "Column", "Type", "Null", "Key");
        echo str_repeat("-", 70) . "\n";
        
        while ($col = $columns_result->fetch_assoc()) {
            echo sprintf("%-25s %-30s %-10s %-10s\n", 
                $col['Field'], 
                $col['Type'], 
                $col['Null'], 
                $col['Key']
            );
        }
        
        // Get row count
        $count_result = $conn->query("SELECT COUNT(*) as count FROM `$table`");
        if ($count_result) {
            $count = $count_result->fetch_assoc()['count'];
            echo "\nRow count: $count\n";
        }
    }
    
    echo "\n";
}

// Get foreign keys
echo "\n=== FOREIGN KEY CONSTRAINTS ===\n";
echo str_repeat("=", 70) . "\n";

$fk_query = "SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = DATABASE()
AND REFERENCED_TABLE_NAME IS NOT NULL
ORDER BY TABLE_NAME, COLUMN_NAME";

$fk_result = $conn->query($fk_query);
if ($fk_result && $fk_result->num_rows > 0) {
    echo sprintf("%-20s %-20s %-25s %-20s\n", "Table", "Column", "References", "Foreign Column");
    echo str_repeat("-", 70) . "\n";
    
    while ($fk = $fk_result->fetch_assoc()) {
        echo sprintf("%-20s %-20s %-25s %-20s\n",
            $fk['TABLE_NAME'],
            $fk['COLUMN_NAME'],
            $fk['REFERENCED_TABLE_NAME'],
            $fk['REFERENCED_COLUMN_NAME']
        );
    }
} else {
    echo "No foreign key constraints found.\n";
}

echo "\n=== INDEXES ===\n";
echo str_repeat("=", 70) . "\n";

foreach ($tables as $table) {
    $idx_result = $conn->query("SHOW INDEX FROM `$table`");
    
    if ($idx_result && $idx_result->num_rows > 0) {
        echo "\nTable: $table\n";
        $indexes = [];
        while ($idx = $idx_result->fetch_assoc()) {
            $key_name = $idx['Key_name'];
            if (!isset($indexes[$key_name])) {
                $indexes[$key_name] = [];
            }
            $indexes[$key_name][] = $idx['Column_name'];
        }
        
        foreach ($indexes as $key_name => $columns) {
            echo "  - " . $key_name . ": " . implode(", ", $columns) . "\n";
        }
    }
}

$conn->close();

echo "\n=== SCAN COMPLETE ===\n";
