<?php
// Add access_level column and populate based on roles
// Run: php scripts/add_access_level.php

if (php_sapi_name() !== 'cli') {
    echo "This script must be run from the command line.\n";
    exit(1);
}

require __DIR__ . '/../database/db_connect.php';

echo "Adding access_level column to admins table...\n";

// Check if access_level column exists
$result = $conn->query("SHOW COLUMNS FROM `admins` LIKE 'access_level'");

if ($result && $result->num_rows > 0) {
    echo "✓ access_level column already exists.\n";
} else {
    // Add access_level column
    $sql = "ALTER TABLE `admins` 
            ADD COLUMN `access_level` VARCHAR(50) DEFAULT 'View Only' 
            AFTER `role`";
    
    if ($conn->query($sql)) {
        echo "✓ access_level column added successfully.\n";
    } else {
        echo "✗ Failed to add access_level column: " . $conn->error . "\n";
        exit(1);
    }
}

// Update access_level based on roles
echo "\nSetting access levels based on roles...\n";

$updates = [
    "UPDATE admins SET access_level = 'Full Access' WHERE role = 'super_admin'",
    "UPDATE admins SET access_level = 'Full Access' WHERE role = 'manager'",
    "UPDATE admins SET access_level = 'Manage Bookings & Payments' WHERE role = 'admin'",
    "UPDATE admins SET access_level = 'View Only' WHERE role = 'staff'"
];

foreach ($updates as $sql) {
    if ($conn->query($sql)) {
        echo "✓ Updated access levels\n";
    }
}

// Show current admins with their access levels
echo "\nCurrent admins with access levels:\n";
echo str_repeat("-", 100) . "\n";
printf("%-5s | %-20s | %-30s | %-15s | %-25s\n", "ID", "Username", "Email", "Role", "Access Level");
echo str_repeat("-", 100) . "\n";

$admins = $conn->query("SELECT id, username, email, role, access_level FROM admins ORDER BY id ASC");
if ($admins) {
    while ($admin = $admins->fetch_assoc()) {
        printf("%-5s | %-20s | %-30s | %-15s | %-25s\n", 
            $admin['id'], 
            $admin['username'], 
            $admin['email'] ?: '(no email)', 
            $admin['role'] ?: 'staff',
            $admin['access_level'] ?: 'View Only'
        );
    }
}

echo str_repeat("-", 100) . "\n";
echo "\n✓ Done! Refresh your dashboard to see the updated access levels.\n";

$conn->close();
