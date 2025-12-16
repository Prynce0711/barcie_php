<?php
require_once 'database/db_connect.php';

$result = $conn->query("SHOW TABLES LIKE 'feedback'");
if ($result->num_rows > 0) {
    echo "feedback table exists\n";
    
    // Check structure
    $structure = $conn->query("DESCRIBE feedback");
    echo "\nTable structure:\n";
    while ($row = $structure->fetch_assoc()) {
        echo "  - " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} else {
    echo "feedback table NOT found\n";
}
