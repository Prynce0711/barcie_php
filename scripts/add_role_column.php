<?php
// Quick script to add the role column to admins table if missing
// Run: php scripts/add_role_column.php

if (php_sapi_name() !== 'cli') {
    echo "This script must be run from the command line.\n";
    exit(1);
}

require __DIR__ . '/../database/db_connect.php';

echo "Checking admins table structure...\n";

// Check if role column exists
$result = $conn->query("SHOW COLUMNS FROM `admins` LIKE 'role'");

if ($result && $result->num_rows > 0) {
    echo "✓ Role column already exists.\n";
} else {
    echo "Adding role column to admins table...\n";
    
    // Add role column with default value of 'staff'
    $sql = "ALTER TABLE `admins` 
            ADD COLUMN `role` ENUM('super_admin', 'admin', 'manager', 'staff') DEFAULT 'staff' 
            AFTER `email`";
    
    if ($conn->query($sql)) {
        echo "✓ Role column added successfully.\n";    
        
        // Set first admin to super_admin if there are any admins
        $firstAdmin = $conn->query("SELECT id, username FROM admins ORDER BY id ASC LIMIT 1");
        if ($firstAdmin && $row = $firstAdmin->fetch_assoc()) {
            $conn->query("UPDATE admins SET role = 'super_admin' WHERE id = " . (int)$row['id']);
            echo "✓ Set first admin (username: {$row['username']}) to super_admin.\n";
        }
    } else {
        echo "✗ Failed to add role column: " . $conn->error . "\n";
        exit(1);
    }
}

// Now list all admins with their roles
echo "\nCurrent admins:\n";
echo str_repeat("-", 80) . "\n";
printf("%-5s | %-20s | %-30s | %-15s\n", "ID", "Username", "Email", "Role");
echo str_repeat("-", 80) . "\n";

$admins = $conn->query("SELECT id, username, email, role FROM admins ORDER BY id ASC");
if ($admins) {
    while ($admin = $admins->fetch_assoc()) {
        printf("%-5s | %-20s | %-30s | %-15s\n", 
            $admin['id'], 
            $admin['username'], 
            $admin['email'] ?: '(no email)', 
            $admin['role'] ?: 'staff'
        );
    }
}

echo str_repeat("-", 80) . "\n";
echo "\nTo set a user to super_admin, run:\n";
echo "  php scripts/admin_tool.php set-super <username>\n";

$conn->close();
