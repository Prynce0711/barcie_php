<?php
/**
 * Add birthdate column to bookings and pencil_bookings tables
 * This allows auto-fill from ID OCR and age calculation
 */

require_once __DIR__ . '/../db_connect.php';

try {
    echo "Starting birthdate column migration...\n<br>";
    
    // Function to check if column exists
    function columnExists($conn, $table, $column) {
        $result = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
        return $result && $result->num_rows > 0;
    }
    
    // Add birthdate to bookings table
    if (!columnExists($conn, 'bookings', 'guest_birthdate')) {
        $sql = "ALTER TABLE bookings ADD COLUMN guest_birthdate DATE NULL AFTER guest_age";
        if ($conn->query($sql)) {
            echo "✓ Added guest_birthdate column to bookings table\n<br>";
        } else {
            throw new Exception("Failed to add guest_birthdate to bookings: " . $conn->error);
        }
    } else {
        echo "• guest_birthdate column already exists in bookings table\n<br>";
    }
    
    // Add birthdate to pencil_bookings table
    if (!columnExists($conn, 'pencil_bookings', 'guest_birthdate')) {
        $sql = "ALTER TABLE pencil_bookings ADD COLUMN guest_birthdate DATE NULL AFTER email";
        if ($conn->query($sql)) {
            echo "✓ Added guest_birthdate column to pencil_bookings table\n<br>";
        } else {
            throw new Exception("Failed to add guest_birthdate to pencil_bookings: " . $conn->error);
        }
    } else {
        echo "• guest_birthdate column already exists in pencil_bookings table\n<br>";
    }
    
    // Add guest_age to pencil_bookings if it doesn't exist (for consistency)
    if (!columnExists($conn, 'pencil_bookings', 'guest_age')) {
        $sql = "ALTER TABLE pencil_bookings ADD COLUMN guest_age INT NULL AFTER guest_birthdate";
        if ($conn->query($sql)) {
            echo "✓ Added guest_age column to pencil_bookings table\n<br>";
        } else {
            echo "⚠ Warning: Could not add guest_age to pencil_bookings: " . $conn->error . "\n<br>";
        }
    } else {
        echo "• guest_age column already exists in pencil_bookings table\n<br>";
    }
    
    echo "\n<br><strong>Migration completed successfully!</strong>\n<br>";
    echo "You can now use the birthdate field in both regular and pencil bookings.\n<br>";
    
} catch (Exception $e) {
    echo "<strong>Error during migration:</strong> " . $e->getMessage() . "\n<br>";
    exit(1);
}
?>
