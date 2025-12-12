<?php
/**
 * Database Migration Script for Admins Table
 * Adds email, created_at, and last_login columns if they don't exist
 */

require_once __DIR__ . '/db_connect.php';

echo "Starting admins table migration...\n";

try {
    // First, check if admins table exists
    $check_table = $conn->query("SHOW TABLES LIKE 'admins'");
    
    if ($check_table->num_rows == 0) {
        // Create admins table if it doesn't exist
        echo "Creating admins table...\n";
        $create_sql = "CREATE TABLE admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_login TIMESTAMP NULL DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        if ($conn->query($create_sql)) {
            echo "✅ Admins table created successfully\n";
            
            // Insert default admin if table is new
            $default_password = password_hash('admin', PASSWORD_BCRYPT);
            $insert_sql = "INSERT INTO admins (username, email, password) VALUES ('admin', 'admin@barcie.com', ?)";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param("s", $default_password);
            
            if ($stmt->execute()) {
                echo "✅ Default admin account created (username: admin, password: admin)\n";
            }
            $stmt->close();
        } else {
            throw new Exception("Failed to create admins table: " . $conn->error);
        }
    } else {
        echo "Admins table exists. Checking columns...\n";
        
        // Check and add email column if it doesn't exist
        $check_email = $conn->query("SHOW COLUMNS FROM admins LIKE 'email'");
        if ($check_email->num_rows == 0) {
            echo "Adding email column...\n";
            $conn->query("ALTER TABLE admins ADD COLUMN email VARCHAR(255) DEFAULT NULL");
            echo "✅ Email column added\n";
        } else {
            echo "✓ Email column exists\n";
        }
        
        // Check and add created_at column if it doesn't exist
        $check_created = $conn->query("SHOW COLUMNS FROM admins LIKE 'created_at'");
        if ($check_created->num_rows == 0) {
            echo "Adding created_at column...\n";
            $conn->query("ALTER TABLE admins ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
            echo "✅ created_at column added\n";
        } else {
            echo "✓ created_at column exists\n";
        }
        
        // Check and add last_login column if it doesn't exist
        $check_login = $conn->query("SHOW COLUMNS FROM admins LIKE 'last_login'");
        if ($check_login->num_rows == 0) {
            echo "Adding last_login column...\n";
            $conn->query("ALTER TABLE admins ADD COLUMN last_login TIMESTAMP NULL DEFAULT NULL");
            echo "✅ last_login column added\n";
        } else {
            echo "✓ last_login column exists\n";
        }
        
        // Check if passwords need to be hashed (for existing plain text passwords)
        echo "Checking password hashing...\n";
        $result = $conn->query("SELECT id, username, password FROM admins");
        $updated = 0;
        
        while ($row = $result->fetch_assoc()) {
            // Check if password is NOT hashed (bcrypt hashes are 60 chars and start with $2y$)
            if (strlen($row['password']) != 60 || substr($row['password'], 0, 4) != '$2y$') {
                echo "  Hashing password for user: {$row['username']}\n";
                $hashed = password_hash($row['password'], PASSWORD_BCRYPT);
                $stmt = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $hashed, $row['id']);
                $stmt->execute();
                $stmt->close();
                $updated++;
            }
        }
        
        if ($updated > 0) {
            echo "✅ Hashed {$updated} password(s)\n";
        } else {
            echo "✓ All passwords are already hashed\n";
        }
    }
    
    echo "\n✅ Migration completed successfully!\n\n";
    
    // Display current admins
    echo "Current admins:\n";
    echo "-----------------------------------\n";
    $result = $conn->query("SELECT id, username, email, created_at, last_login FROM admins");
    while ($row = $result->fetch_assoc()) {
        echo "ID: {$row['id']} | Username: {$row['username']} | Email: " . ($row['email'] ?: 'N/A') . "\n";
    }
    echo "-----------------------------------\n";
    
} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}

$conn->close();
echo "\nMigration script finished.\n";
