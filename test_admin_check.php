<?php
// Test admin login database setup
error_reporting(E_ALL);
ini_set('display_errors', 1);

include __DIR__ . '/database/db_connect.php';

echo "<h2>Admin Login Database Check</h2>";

// Check if connection is successful
if ($conn->connect_error) {
    die("<p style='color:red'>❌ Connection failed: " . $conn->connect_error . "</p>");
}
echo "<p style='color:green'>✅ Database connected successfully</p>";

// Check if admins table exists
$result = $conn->query("SHOW TABLES LIKE 'admins'");
if ($result->num_rows === 0) {
    echo "<p style='color:red'>❌ 'admins' table does not exist!</p>";
    echo "<h3>Creating admins table...</h3>";
    
    $create_table = "CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL
    )";
    
    if ($conn->query($create_table)) {
        echo "<p style='color:green'>✅ Admins table created successfully</p>";
    } else {
        echo "<p style='color:red'>❌ Error creating table: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color:green'>✅ 'admins' table exists</p>";
}

// Check table structure
echo "<h3>Table Structure:</h3>";
$result = $conn->query("DESCRIBE admins");
if ($result) {
    echo "<table border='1' cellpadding='5'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Count admins
$result = $conn->query("SELECT COUNT(*) as count FROM admins");
$row = $result->fetch_assoc();
echo "<h3>Total Admins: {$row['count']}</h3>";

// List all admins (without passwords)
echo "<h3>Admin Accounts:</h3>";
$result = $conn->query("SELECT id, username, email, created_at, last_login FROM admins");
if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'><tr><th>ID</th><th>Username</th><th>Email</th><th>Created</th><th>Last Login</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['username']}</td>";
        echo "<td>{$row['email']}</td>";
        echo "<td>{$row['created_at']}</td>";
        echo "<td>{$row['last_login']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:orange'>⚠️ No admin accounts found. You need to create one!</p>";
    echo "<p><a href='create_admin.php'>Create Admin Account</a></p>";
}

$conn->close();
?>
