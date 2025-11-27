<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Dashboard Diagnostic Check</h2>";
echo "<hr>";

// Check 1: Session
session_start();
echo "<h3>1. Session Check</h3>";
echo "Session ID: " . session_id() . "<br>";
echo "Admin logged in: " . (isset($_SESSION['admin_logged_in']) ? 'YES' : 'NO') . "<br>";
echo "Admin ID: " . ($_SESSION['admin_id'] ?? 'Not set') . "<br>";
echo "Admin Username: " . ($_SESSION['admin_username'] ?? 'Not set') . "<br>";
echo "<hr>";

// Check 2: Database Connection
echo "<h3>2. Database Connection</h3>";
try {
    require 'database/db_connect.php';
    
    if ($conn->ping()) {
        echo "✓ Connection successful<br>";
        echo "Database: " . $dbname . "<br>";
        echo "Host: " . $host . "<br>";
    } else {
        echo "✗ Connection ping failed<br>";
    }
} catch (Exception $e) {
    echo "✗ Connection error: " . $e->getMessage() . "<br>";
}
echo "<hr>";

// Check 3: Tables
echo "<h3>3. Required Tables</h3>";
$tables = ['items', 'bookings', 'pencil_bookings', 'admins', 'feedback'];
foreach ($tables as $table) {
    $result = $conn->query("SELECT COUNT(*) as count FROM $table");
    if ($result) {
        $count = $result->fetch_assoc()['count'];
        echo "✓ $table: $count records<br>";
    } else {
        echo "✗ $table: ERROR - " . $conn->error . "<br>";
    }
}
echo "<hr>";

// Check 4: Admin Accounts
echo "<h3>4. Admin Accounts</h3>";
$result = $conn->query("SELECT id, username, created_at FROM admins");
if ($result && $result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Username</th><th>Created</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "✗ No admin accounts found<br>";
}
echo "<hr>";

// Check 5: Recent Bookings
echo "<h3>5. Recent Bookings</h3>";
$result = $conn->query("SELECT id, status, checkin, checkout FROM bookings ORDER BY created_at DESC LIMIT 5");
if ($result && $result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Status</th><th>Check-in</th><th>Check-out</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "<td>" . $row['checkin'] . "</td>";
        echo "<td>" . $row['checkout'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No bookings found<br>";
}
echo "<hr>";

echo "<h3>✅ Diagnostic Complete</h3>";
echo "<p><a href='dashboard.php'>Go to Dashboard</a> | <a href='index.php'>Go to Login</a></p>";
?>
