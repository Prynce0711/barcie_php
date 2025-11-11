<?php
// Debug script to check addons in database
require_once 'database/db_connect.php';

echo "=== Checking Items Table for Add-ons ===\n\n";

$res = $conn->query("SELECT id, name, addons FROM items LIMIT 10");

if ($res) {
    while ($row = $res->fetch_assoc()) {
        echo "ID: {$row['id']}\n";
        echo "Name: {$row['name']}\n";
        echo "Addons Raw: " . ($row['addons'] ?: 'NULL/Empty') . "\n";
        
        if (!empty($row['addons'])) {
            $decoded = json_decode($row['addons'], true);
            if ($decoded !== null) {
                echo "Addons Decoded: " . print_r($decoded, true) . "\n";
            } else {
                echo "JSON decode failed, trying unserialize...\n";
                $unserialized = @unserialize($row['addons']);
                if ($unserialized !== false) {
                    echo "Addons Unserialized: " . print_r($unserialized, true) . "\n";
                } else {
                    echo "Not valid JSON or serialized data\n";
                }
            }
        }
        echo str_repeat('-', 50) . "\n\n";
    }
} else {
    echo "Query failed: " . $conn->error . "\n";
}

echo "\n=== Checking if addons column exists ===\n";
$chk = $conn->query("SHOW COLUMNS FROM items LIKE 'addons'");
if ($chk && $chk->num_rows > 0) {
    echo "✓ addons column EXISTS\n";
    $col = $chk->fetch_assoc();
    print_r($col);
} else {
    echo "✗ addons column DOES NOT EXIST\n";
}
