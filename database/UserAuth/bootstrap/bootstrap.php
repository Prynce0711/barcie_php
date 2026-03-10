<?php
// Set timezone first to ensure consistent time across all operations
date_default_timezone_set('Asia/Manila');

// Catch all errors and convert to JSON for API endpoints
function handleFatalError()
{
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        // Clear any output buffer
        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Fatal Error',
            'message' => $error['message'],
            'file' => basename($error['file']),
            'line' => $error['line']
        ]);
        exit;
    }
}
register_shutdown_function('handleFatalError');

// Load application configuration from the shared database config.
require_once __DIR__ . '/../../config.php';

// Start output buffering to catch any stray output
ob_start();

// Error handling is now controlled by config.php
// Debug mode: DEBUG_MODE constant

// Add CORS headers FIRST before any output
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Email helpers are now centralized under Components/Email.
require_once __DIR__ . '/../../../Components/Email/email_template.php';
require_once __DIR__ . '/../../../Components/Email/smtp_mailer.php';
require_once __DIR__ . '/../../../Components/Email/template_builders.php';


session_start();

// Include database connection with error handling
try {
    // Check if db_connect.php exists
    $db_file = __DIR__ . '/../../db_connect.php';
    if (!file_exists($db_file)) {
        throw new Exception("Database configuration file not found: db_connect.php");
    }

    include $db_file;

    // Check if connection was successful
    if (!isset($conn)) {
        throw new Exception("Database connection object not created. Check db_connect.php");
    }

    if ($conn->connect_error) {
        throw new Exception("MySQL connection failed: " . $conn->connect_error);
    }

} catch (Exception $e) {
    // Clear any output buffer
    while (ob_get_level()) {
        ob_end_clean();
    }

    // Log error
    error_log("Database connection error: " . $e->getMessage());

    // For API requests, return JSON error
    if (isset($_GET['action']) || isset($_POST['action'])) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Database connection failed',
            'message' => $e->getMessage(),
            'debug_info' => [
                'file_exists' => file_exists(__DIR__ . '/../../db_connect.php'),
                'php_version' => phpversion(),
                'mysqli_extension' => extension_loaded('mysqli')
            ]
        ]);
        exit;
    } else {
        // For regular page requests, show error
        die("Database connection error. Please try again later.");
    }
}

// Helper function for redirect
function redirect($url)
{
    header("Location: $url");
    exit;
}

// Helper function for AJAX responses
function handleResponse($message, $success = true, $redirectUrl = null)
{
    // Detect AJAX/Fetch requests by checking for XMLHttpRequest header or JSON content type preference
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ||
        (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

    if ($isAjax) {
        // Clear any output buffer to prevent mixed content
        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: application/json');
        http_response_code($success ? 200 : 400);
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'redirect' => $redirectUrl
        ]);
        exit;
    } else {
        // Traditional behavior for non-AJAX requests
        $_SESSION['booking_msg'] = $message;
        redirect($redirectUrl);
    }
}

// Function to ensure database structure is correct
function ensureDatabaseStructure($conn)
{
    try {
        // Ensure bookings table exists with proper structure
        $conn->query("CREATE TABLE IF NOT EXISTS bookings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            receipt_no VARCHAR(50) NULL,
            room_id INT NULL,
            type VARCHAR(50) NOT NULL DEFAULT 'reservation',
            details TEXT,
            status VARCHAR(50) DEFAULT 'pending',
            discount_status VARCHAR(50) DEFAULT 'none',
            checkin DATETIME NULL,
            checkout DATETIME NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");

        // Function to check if column exists
        function columnExists($conn, $table, $column)
        {
            $result = $conn->query("SHOW COLUMNS FROM $table LIKE '$column'");
            return $result && $result->num_rows > 0;
        }

        // Add missing columns if they don't exist
        $columns_to_add = [
            'receipt_no' => 'VARCHAR(50) NULL',
            'room_id' => 'INT NULL',
            'type' => 'VARCHAR(50) NOT NULL DEFAULT "reservation"',
            'details' => 'TEXT',
            'status' => 'VARCHAR(50) DEFAULT "pending"',
            'discount_status' => 'VARCHAR(50) DEFAULT "none"',
            // Dedicated column to store path to uploaded discount proof (if any)
            'proof_of_id' => 'VARCHAR(255) NULL',
            'guest_age' => 'INT NULL',
            'amount' => 'DECIMAL(10,2) NULL',
            'add_ons' => 'TEXT NULL',
            // Payment verification columns
            'payment_status' => 'VARCHAR(50) DEFAULT "none"',
            'proof_of_payment' => 'VARCHAR(255) NULL',
            // Audit trail for payment verification
            'payment_verified_by' => 'INT NULL',
            'payment_verified_at' => 'DATETIME NULL',
            'checkin' => 'DATETIME NULL',
            'checkout' => 'DATETIME NULL',
            'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ];

        foreach ($columns_to_add as $column => $definition) {
            if (!columnExists($conn, 'bookings', $column)) {
                $sql = "ALTER TABLE bookings ADD COLUMN $column $definition";
                $conn->query($sql);
            }
        }

        // Migration: if an older typo'd column 'proof_of_if' exists, rename it to 'proof_of_id'
        if (columnExists($conn, 'bookings', 'proof_of_if') && !columnExists($conn, 'bookings', 'proof_of_id')) {
            $conn->query("ALTER TABLE bookings CHANGE COLUMN `proof_of_if` `proof_of_id` VARCHAR(255) NULL");
        }

        // Remove user_id column if it exists (handle foreign key constraints first)
        if (columnExists($conn, 'bookings', 'user_id')) {
            // Drop foreign key constraints first
            $conn->query("ALTER TABLE bookings DROP FOREIGN KEY fk_bookings_user");
            $conn->query("ALTER TABLE bookings DROP FOREIGN KEY bookings_ibfk_1");
            $conn->query("ALTER TABLE bookings DROP INDEX fk_bookings_user");
            $conn->query("ALTER TABLE bookings DROP INDEX user_id");
            // Now drop the column
            $conn->query("ALTER TABLE bookings DROP COLUMN user_id");
        }

        // Ensure items table has room_status column
        if (!columnExists($conn, 'items', 'room_status')) {
            $conn->query("ALTER TABLE items ADD COLUMN room_status ENUM('available', 'reserved', 'occupied', 'clean', 'dirty', 'maintenance', 'out_of_order') DEFAULT 'available'");
        }

        // Update room status based on current bookings
        $conn->query("UPDATE items i 
            SET room_status = CASE 
                WHEN EXISTS (
                    SELECT 1 FROM bookings b 
                    WHERE b.room_id = i.id 
                    AND b.status = 'checked_in' 
                    AND CURDATE() BETWEEN DATE(b.checkin) AND DATE(b.checkout)
                ) THEN 'occupied'
                WHEN EXISTS (
                    SELECT 1 FROM bookings b 
                    WHERE b.room_id = i.id 
                    AND b.status IN ('approved', 'confirmed') 
                    AND DATE(b.checkin) >= CURDATE()
                ) THEN 'reserved'
                ELSE 'available'
            END
            WHERE i.item_type IN ('room', 'facility')");

    } catch (Exception $e) {
        error_log("Database structure error: " . $e->getMessage());
    }
}

// Ensure database structure is correct on every request
ensureDatabaseStructure($conn);

// Function to fix bookings with missing room_id
function fixMissingRoomIds($conn)
{
    try {
        // Get bookings that have NULL room_id but have details
        $query = "SELECT id, details FROM bookings WHERE room_id IS NULL AND details IS NOT NULL";
        $result = $conn->query($query);

        if ($result && $result->num_rows > 0) {
            while ($booking = $result->fetch_assoc()) {
                $details = $booking['details'];
                $booking_id = $booking['id'];

                // Try to extract room/facility name from details and find matching item
                $room_name = '';

                // Look for different patterns in details
                if (preg_match('/(?:Room|Facility):\s*([^|]+)/i', $details, $matches)) {
                    $room_name = trim($matches[1]);
                } elseif (preg_match('/Item:\s*([^|]+)/i', $details, $matches)) {
                    $room_name = trim($matches[1]);
                }

                if (!empty($room_name)) {
                    // Find matching item in items table
                    $item_query = "SELECT id FROM items WHERE name = ? LIMIT 1";
                    $item_stmt = $conn->prepare($item_query);
                    $item_stmt->bind_param("s", $room_name);
                    $item_stmt->execute();
                    $item_result = $item_stmt->get_result();

                    if ($item_row = $item_result->fetch_assoc()) {
                        $room_id = $item_row['id'];

                        // Update booking with correct room_id
                        $update_query = "UPDATE bookings SET room_id = ? WHERE id = ?";
                        $update_stmt = $conn->prepare($update_query);
                        $update_stmt->bind_param("ii", $room_id, $booking_id);
                        $update_stmt->execute();
                        $update_stmt->close();
                    }
                    $item_stmt->close();
                }
            }
        }
    } catch (Exception $e) {
        error_log("Error fixing missing room IDs: " . $e->getMessage());
    }
}

// Fix missing room IDs on startup (run once)
fixMissingRoomIds($conn);


