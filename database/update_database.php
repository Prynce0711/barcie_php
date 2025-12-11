<?php
/**
 * Database Structure Update Script
 * Run this to update your existing database with any missing columns/indexes
 * Safe to run multiple times - won't destroy existing data
 * 
 * Access via: http://localhost/barcie_php/database/update_database.php
 * Or CLI: php C:\xampp\htdocs\barcie_php\database\update_database.php
 */

require_once __DIR__ . '/db_connect.php';

// Check if this is CLI or browser
$is_cli = php_sapi_name() === 'cli';

function output($message, $is_cli) {
    if ($is_cli) {
        echo $message . "\n";
    } else {
        echo $message . "<br>\n";
    }
}

function outputHeader($message, $is_cli) {
    if ($is_cli) {
        echo "\n" . str_repeat("=", 70) . "\n";
        echo $message . "\n";
        echo str_repeat("=", 70) . "\n";
    } else {
        echo "<h2 style='color: #4ec9b0; margin-top: 20px; border-bottom: 2px solid #4ec9b0; padding-bottom: 5px;'>{$message}</h2>\n";
    }
}

// Start output
if (!$is_cli) {
    echo "<!DOCTYPE html>
<html>
<head>
    <title>BarCIE Database Update</title>
    <style>
        body { font-family: 'Consolas', 'Monaco', monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; }
        h1 { color: #4ec9b0; text-align: center; }
        h2 { color: #4ec9b0; border-bottom: 2px solid #4ec9b0; padding-bottom: 5px; }
        .success { color: #4ec9b0; }
        .error { color: #f48771; }
        .info { color: #dcdcaa; }
        .warning { color: #ce9178; }
        .container { max-width: 1000px; margin: 0 auto; }
        .back-link { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #264f78; color: #fff; text-decoration: none; border-radius: 5px; }
        .back-link:hover { background: #1a3a5a; }
        pre { background: #252526; padding: 10px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
<div class='container'>
<h1>BarCIE Database Structure Update</h1>";
}

outputHeader("Database Structure Update", $is_cli);
output("Database: " . DB_NAME, $is_cli);
output("Host: " . DB_HOST, $is_cli);
output("", $is_cli);
output("<span class='info'>This script will update your database schema without destroying existing data.</span>", $is_cli);
output("", $is_cli);

// Define migration files in order
$migrations = array(
    '001_update_items_table.php',
    '002_update_admins_table.php',
    '003_update_bookings_table.php',
    '004_verify_pencil_bookings_table.php',
    '005_update_feedback_table.php',
    '006_verify_news_updates_table.php',
    '007_create_users_table.php'
);

$migration_dir = __DIR__ . '/migrations/';
$success_count = 0;
$error_count = 0;

// Run each migration
foreach ($migrations as $index => $migration) {
    $migration_path = $migration_dir . $migration;
    
    outputHeader("Step " . ($index + 1) . "/" . count($migrations) . ": {$migration}", $is_cli);
    
    if (!file_exists($migration_path)) {
        output("<span class='error'>❌ Migration file not found: {$migration}</span>", $is_cli);
        $error_count++;
        continue;
    }
    
    // Capture output from migration
    ob_start();
    try {
        include $migration_path;
        $migration_output = ob_get_clean();
        
        // Check if migration was successful
        if (strpos($migration_output, '✅') !== false || strpos($migration_output, 'successfully') !== false) {
            output("<span class='success'>{$migration_output}</span>", $is_cli);
            $success_count++;
        } else {
            output("<span class='info'>{$migration_output}</span>", $is_cli);
            $success_count++;
        }
    } catch (Exception $e) {
        ob_end_clean();
        output("<span class='error'>❌ Error: " . $e->getMessage() . "</span>", $is_cli);
        $error_count++;
    }
    
    output("", $is_cli);
}

// Summary
outputHeader("Update Summary", $is_cli);
output("Total migrations: " . count($migrations), $is_cli);
output("<span class='success'>✅ Successful: {$success_count}</span>", $is_cli);
if ($error_count > 0) {
    output("<span class='error'>❌ Failed: {$error_count}</span>", $is_cli);
}
output("", $is_cli);

if ($error_count === 0) {
    output("<span class='success'>🎉 Database structure update completed successfully!</span>", $is_cli);
    output("", $is_cli);
    output("Your database is now up to date with the latest schema.", $is_cli);
} else {
    output("<span class='error'>⚠️  Database update completed with errors.</span>", $is_cli);
    output("Please check the error messages above and fix any issues.", $is_cli);
}

output("", $is_cli);
output("For full schema documentation, see: <span class='info'>database/SCHEMA.md</span>", $is_cli);

if (!$is_cli) {
    echo "<br><br><a href='../index.php' class='back-link'>← Back to Home</a>";
    echo " <a href='../dashboard.php' class='back-link'>Go to Dashboard</a>";
    echo " <a href='check_structure.php' class='back-link'>View Database Structure</a>";
    echo "</div></body></html>";
}

$conn->close();
