<?php
// Test database connection
echo "<h2>Database Connection Test</h2>";
echo "<pre>";

echo "Testing connection to server database...\n\n";

$host = "10.20.0.2";
$user = "root";
$pass = "root";
$dbname = "barcie_db";

echo "Host: $host\n";
echo "User: $user\n";
echo "Database: $dbname\n\n";

try {
    $conn = new mysqli($host, $user, $pass, $dbname);
    
    if ($conn->connect_error) {
        echo "❌ Connection FAILED!\n";
        echo "Error: " . $conn->connect_error . "\n";
        echo "Error Code: " . $conn->connect_errno . "\n";
    } else {
        echo "✅ Connection SUCCESSFUL!\n\n";
        
        // Test if admins table exists
        $result = $conn->query("SHOW TABLES LIKE 'admins'");
        if ($result && $result->num_rows > 0) {
            echo "✅ 'admins' table exists\n\n";
            
            // Check admin records
            $adminCheck = $conn->query("SELECT COUNT(*) as count FROM admins");
            if ($adminCheck) {
                $row = $adminCheck->fetch_assoc();
                echo "Admin records found: " . $row['count'] . "\n";
            }
        } else {
            echo "❌ 'admins' table NOT found\n";
        }
        
        $conn->close();
    }
} catch (Exception $e) {
    echo "❌ Exception occurred!\n";
    echo "Error: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>
