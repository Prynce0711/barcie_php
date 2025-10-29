<?php
// Catch all errors and convert to JSON for API endpoints
function handleFatalError() {
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

// Start output buffering to catch any stray output
ob_start();

// Enable error display temporarily for debugging on live server
ini_set('display_errors', 0); // Changed to 0 to prevent HTML errors
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Add CORS headers FIRST before any output
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Check if vendor autoload exists (optional - only needed for email features)
$vendor_available = file_exists(__DIR__ . '/../../vendor/autoload.php');
if ($vendor_available) {
    require __DIR__ . '/../../vendor/autoload.php';
}

// Helper function to create professional email template
function create_email_template($title, $content, $footerText = '') {
    $currentYear = date('Y');
    
    // Get base64 encoded logo
    $logo_path = __DIR__ . '/../assets/images/imageBg/barcie_logo.jpg';
    $logo_data = '';
    if (file_exists($logo_path)) {
        $logo_base64 = base64_encode(file_get_contents($logo_path));
        $logo_data = 'data:image/jpeg;base64,' . $logo_base64;
    }
    
    return '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . htmlspecialchars($title) . '</title>
    </head>
    <body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, \'Helvetica Neue\', Arial, sans-serif; background-color: #f4f4f4;">
        <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #f4f4f4;" cellpadding="0" cellspacing="0">
            <tr>
                <td align="center" style="padding: 40px 0;">
                    <!-- Main Container -->
                    <table role="presentation" style="width: 600px; max-width: 100%; border-collapse: collapse; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);" cellpadding="0" cellspacing="0">
                        
                        <!-- Header -->
                        <tr>
                            <td style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); padding: 30px; text-align: center; border-radius: 8px 8px 0 0;">
                                ' . ($logo_data ? '<img src="' . $logo_data . '" alt="BarCIE Logo" style="width: 80px; height: 80px; margin-bottom: 15px; border-radius: 50%; border: 3px solid #ffffff; box-shadow: 0 2px 8px rgba(0,0,0,0.2);" />' : '') . '
                                <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 600; letter-spacing: -0.5px;">BarCIE International Center</h1>
                                <p style="margin: 10px 0 0 0; color: #f0f0f0; font-size: 14px;">La Consolacion University Philippines</p>
                            </td>
                        </tr>
                        
                        <!-- Content -->
                        <tr>
                            <td style="padding: 40px 30px;">
                                ' . $content . '
                            </td>
                        </tr>
                        
                        <!-- Footer -->
                        <tr>
                            <td style="background-color: #f8f9fa; padding: 30px; text-align: center; border-radius: 0 0 8px 8px; border-top: 1px solid #e9ecef;">
                                ' . ($footerText ? '<p style="margin: 0 0 15px 0; color: #6c757d; font-size: 13px;">' . $footerText . '</p>' : '') . '
                                <p style="margin: 0 0 5px 0; color: #6c757d; font-size: 13px;">
                                    <strong>BarCIE International Center</strong><br>
                                    La Consolacion University Philippines<br>
                                    Email: pc.clemente11@gmail.com
                                </p>
                                <p style="margin: 15px 0 0 0; color: #adb5bd; font-size: 12px;">
                                    © ' . $currentYear . ' BarCIE International Center. All rights reserved.
                                </p>
                            </td>
                        </tr>
                        
                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>';
}

// PHPMailer setup using Composer autoloader
function send_smtp_mail($to, $subject, $body, $altBody = '') {
    global $vendor_available;
    
    // If vendor folder not available, skip email silently
    if (!$vendor_available) {
        error_log("Email skipped: Vendor folder not available");
        return true; // Return true so booking continues without email
    }
    
    try {
        // Debug logging
        error_log("=== EMAIL ATTEMPT ===");
        error_log("To: " . $to);
        error_log("Subject: " . $subject);
        
        $config_path = __DIR__ . '/mail_config.php';
        error_log("Looking for config at: " . $config_path);
        
        if (!file_exists($config_path)) {
            error_log('ERROR: Mail config file not found at: ' . $config_path);
            return false;
        }
        
        $config = require $config_path;
        error_log("Config loaded successfully");
        error_log("SMTP Host: " . $config['host']);
        error_log("SMTP User: " . $config['username']);
        
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        
        // Enable verbose debug output
        $mail->SMTPDebug = 2;
        $mail->Debugoutput = function($str, $level) {
            error_log("PHPMailer: " . $str);
        };
        
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        $mail->SMTPSecure = $config['secure'];
        $mail->Port = $config['port'];
        
        // SSL/TLS options for local development
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addAddress($to);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = $altBody ?: strip_tags($body);
        $mail->isHTML(true);
        
        $result = $mail->send();
        error_log("Email sent successfully to: " . $to);
        error_log("=== EMAIL SUCCESS ===");
        return $result;
    } catch (\PHPMailer\PHPMailer\Exception $e) {
        error_log('=== EMAIL FAILED ===');
        error_log('PHPMailer error: ' . $e->getMessage());
        error_log('Error trace: ' . $e->getTraceAsString());
        return false;
    } catch (Exception $e) {
        error_log('=== EMAIL FAILED (General) ===');
        error_log('Error: ' . $e->getMessage());
        return false;
    }
}


session_start();

// Include database connection with error handling
try {
    // Check if db_connect.php exists
    $db_file = __DIR__ . '/db_connect.php';
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
                'file_exists' => file_exists(__DIR__ . '/db_connect.php'),
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
function redirect($url) {
    header("Location: $url");
    exit;
}

// Helper function for AJAX responses
function handleResponse($message, $success = true, $redirectUrl = null) {
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
function ensureDatabaseStructure($conn) {
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
        function columnExists($conn, $table, $column) {
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
function fixMissingRoomIds($conn) {
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

/* ---------------------------
    POST/REQUEST actions
    Accept action from either POST (preferred for form submissions) or GET (for convenience).
    Using $_REQUEST allows fetch() clients that send 'action' in the URL query or request body to be accepted.
*/
$action = $_REQUEST['action'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {

    // Simple ping endpoint - no database required
    if ($_GET['action'] === 'ping') {
        // Clear output buffer
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'API is responding',
            'timestamp' => date('Y-m-d H:i:s'),
            'php_version' => phpversion(),
            'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'
        ]);
        exit;
    }

    // Debug endpoint
    if ($_GET['action'] === 'debug_connection') {
        // Clear output buffer
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json');
        try {
            $debug_info = [
                'success' => true,
                'php_version' => phpversion(),
                'mysqli_extension' => extension_loaded('mysqli'),
                'json_extension' => extension_loaded('json'),
                'db_file_exists' => file_exists(__DIR__ . '/db_connect.php'),
                'db_connected' => isset($conn) && !$conn->connect_error,
                'db_error' => isset($conn) && $conn->connect_error ? $conn->connect_error : null,
                'current_time' => date('Y-m-d H:i:s'),
                'get_params' => $_GET,
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown'
            ];
            
            if (isset($conn) && !$conn->connect_error) {
                // Test query
                $test_query = $conn->query("SELECT COUNT(*) as count FROM items");
                if ($test_query) {
                    $test_result = $test_query->fetch_assoc();
                    $debug_info['items_count'] = $test_result['count'];
                } else {
                    $debug_info['items_query_error'] = $conn->error;
                }
                
                // Check bookings table
                $test_query2 = $conn->query("SELECT COUNT(*) as count FROM bookings");
                if ($test_query2) {
                    $test_result2 = $test_query2->fetch_assoc();
                    $debug_info['bookings_count'] = $test_result2['count'];
                } else {
                    $debug_info['bookings_query_error'] = $conn->error;
                }
            }
            
            echo json_encode($debug_info, JSON_PRETTY_PRINT);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Debug failed',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        exit;
    }

    if ($_GET['action'] === 'fetch_items') {
        // Clear output buffer
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json');
        try {
            // Check database connection
            if (!isset($conn)) {
                throw new Exception("Database connection not initialized");
            }
            
            if ($conn->connect_error) {
                throw new Exception("Database connection failed: " . $conn->connect_error);
            }
            
            // Check if items table exists
            $table_check = $conn->query("SHOW TABLES LIKE 'items'");
            if (!$table_check || $table_check->num_rows == 0) {
                throw new Exception("Items table does not exist in database");
            }
            
            $sql = "SELECT id, name, item_type, room_number, description, capacity, price, image, room_status FROM items ORDER BY created_at DESC";
            $res = $conn->query($sql);
            
            if (!$res) {
                throw new Exception("Query failed: " . $conn->error);
            }
            
            $items = [];
            while ($r = $res->fetch_assoc()) {
                $items[] = $r;
            }
            
            // Always return success response with items array
            echo json_encode([
                'success' => true,
                'items' => $items,
                'count' => count($items)
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Failed to fetch items',
                'message' => $e->getMessage(),
                'file' => basename($e->getFile()),
                'line' => $e->getLine()
            ]);
            error_log("fetch_items error: " . $e->getMessage());
        }
        $conn->close();
        exit;
    }

    if ($_GET['action'] === 'debug_bookings') {
        // Clear output buffer
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json');
        
        try {
            // Get all bookings for debugging
            $query = "SELECT id, details, checkin, checkout, status, created_at FROM bookings ORDER BY id DESC LIMIT 10";
            $result = $conn->query($query);
            
            $bookings = [];
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $bookings[] = [
                        'id' => $row['id'],
                        'details' => substr($row['details'], 0, 100),
                        'checkin' => $row['checkin'],
                        'checkout' => $row['checkout'],
                        'status' => $row['status'],
                        'created_at' => $row['created_at'],
                        'current_date' => date('Y-m-d')
                    ];
                }
            }
            
            echo json_encode([
                'total_bookings' => count($bookings),
                'current_date' => date('Y-m-d'),
                'bookings' => $bookings
            ]);
            
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        
        $conn->close();
        exit;
    }

    if ($_GET['action'] === 'fetch_guest_availability') {
        // Clear output buffer
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET');
        
        try {
            // Check if connection exists and is valid
            if (!isset($conn)) {
                throw new Exception("Database connection not established");
            }
            
            if ($conn->connect_error) {
                throw new Exception("Database connection failed: " . $conn->connect_error);
            }
            
            // Check if required tables exist
            $bookingsCheck = $conn->query("SHOW TABLES LIKE 'bookings'");
            if (!$bookingsCheck || $bookingsCheck->num_rows == 0) {
                throw new Exception("Bookings table does not exist");
            }
            
            $itemsCheck = $conn->query("SHOW TABLES LIKE 'items'");
            if (!$itemsCheck || $itemsCheck->num_rows == 0) {
                throw new Exception("Items table does not exist");
            }
            
            // Fetch bookings with room/facility information
            // Show confirmed, approved, pending, and checked_in bookings
            // Include recent and future bookings (last 7 days to future)
            $query = "SELECT 
                        b.details,
                        b.checkin,
                        b.checkout,
                        b.status,
                        b.room_id,
                        i.name as room_name,
                        i.room_number
                      FROM bookings b
                      LEFT JOIN items i ON b.room_id = i.id
                      WHERE b.status IN ('confirmed', 'approved', 'pending', 'checked_in')
                      AND (b.checkin >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) OR b.checkin IS NULL)
                      ORDER BY b.checkin ASC";
            
            $result = $conn->query($query);
            
            if (!$result) {
                throw new Exception("Query failed: " . $conn->error);
            }
            
            $events = [];
            
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    // Get room/facility name from the database join
                    $room_facility = 'Room/Facility';
                    
                    // Use the actual room name from the database
                    if (!empty($row['room_name'])) {
                        $room_facility = $row['room_name'];
                        
                        // Add room number if available
                        if (!empty($row['room_number'])) {
                            $room_facility .= " (Room #" . $row['room_number'] . ")";
                        }
                    } else {
                        // Enhanced fallback: try to extract room info from details
                        if (!empty($row['details'])) {
                            $details = $row['details'];
                            
                            // Try multiple patterns to extract room/facility names
                            if (strpos($details, 'Item:') !== false) {
                                preg_match('/Item:\s*([^|]+)/i', $details, $matches);
                                if (!empty($matches[1])) {
                                    $room_facility = trim($matches[1]);
                                }
                            } elseif (strpos($details, 'Room:') !== false) {
                                preg_match('/Room:\s*([^|]+)/i', $details, $matches);
                                if (!empty($matches[1])) {
                                    $room_facility = trim($matches[1]);
                                }
                            } elseif (strpos($details, 'Facility:') !== false) {
                                preg_match('/Facility:\s*([^|]+)/i', $details, $matches);
                                if (!empty($matches[1])) {
                                    $room_facility = trim($matches[1]);
                                }
                            } else {
                                // Try to extract any meaningful room information
                                $patterns = [
                                    '/(?:Room|Facility|Item)\s*[:#]\s*([^|,\n]+)/i',
                                    '/([A-Za-z\s]+(?:Room|Suite|Hall|Facility))/i',
                                    '/^([^|]+)/'  // First part before any separator
                                ];
                                
                                foreach ($patterns as $pattern) {
                                    if (preg_match($pattern, $details, $matches)) {
                                        $potential_name = trim($matches[1]);
                                        if (strlen($potential_name) > 3 && strlen($potential_name) < 50) {
                                            $room_facility = $potential_name;
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }
                    
                    // Set dates - use today if checkin is null
                    $start_date = $row['checkin'] ?: date('Y-m-d');
                    $end_date = $row['checkout'] ?: date('Y-m-d', strtotime($start_date . ' +1 day'));
                    
                    // For FullCalendar: end date should be the day AFTER the last day for all-day events
                    $calendar_end_date = date('Y-m-d', strtotime($end_date . ' +1 day'));
                    
                    // Calculate duration for display
                    $duration_days = (strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24);
                    $duration_text = $duration_days > 1 ? " ({$duration_days} days)" : " (1 day)";
                    
                    // Set color based on status
                    $color = '#dc3545'; // Default red for occupied
                    $status_text = 'Occupied';
                    
                    if ($row['status'] == 'pending') {
                        $color = '#ffc107'; // Yellow for pending
                        $status_text = 'Pending';
                    } elseif ($row['status'] == 'checked_in') {
                        $color = '#17a2b8'; // Blue for checked in
                        $status_text = 'Occupied';
                    }
                    
                    // Create calendar event with duration info
                    $events[] = [
                        'title' => $room_facility . ' - ' . $status_text . $duration_text,
                        'start' => $start_date,
                        'end' => $calendar_end_date,
                        'backgroundColor' => $color,
                        'borderColor' => $color,
                        'textColor' => '#ffffff',
                        'allDay' => true, // Changed to true for proper multi-day display
                        'extendedProps' => [
                            'facility' => $room_facility,
                            'status' => strtolower($status_text),
                            'booking_status' => $row['status'],
                            'checkin_date' => $start_date,
                            'checkout_date' => $end_date,
                            'duration_days' => $duration_days
                        ]
                    ];
                }
            }
            
            // Return JSON response
            error_log("Calendar API: Returning " . count($events) . " events");
            foreach ($events as $event) {
                error_log("Event: " . $event['title']);
            }
            echo json_encode($events);
            
        } catch (Exception $e) {
            // Log error for debugging
            error_log("Guest availability error: " . $e->getMessage());
            
            // Return error response
            http_response_code(500); 
            echo json_encode([
                'success' => false,
                'error' => 'Failed to fetch availability data',
                'message' => $e->getMessage(),
                'events' => [] // Return empty array as fallback
            ]);
        }
        
        $conn->close();
        exit;
    }

    if ($_GET['action'] === 'get_receipt_no') {
        // Clear output buffer
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json');
        try {
            // Get the current date for receipt format
            $currentDate = date('Ymd');
            
            // Check if bookings table exists
            $tableCheck = $conn->query("SHOW TABLES LIKE 'bookings'");
            if (!$tableCheck || $tableCheck->num_rows == 0) {
                throw new Exception("Bookings table does not exist");
            }
            
            // Check if receipt_no column exists and its type
            $checkColumn = $conn->query("SHOW COLUMNS FROM bookings LIKE 'receipt_no'");
            if (!$checkColumn) {
                throw new Exception("Failed to check table structure: " . $conn->error);
            }
            
            if ($checkColumn->num_rows == 0) {
                // Add receipt_no column as VARCHAR
                $alterResult = $conn->query("ALTER TABLE bookings ADD COLUMN receipt_no VARCHAR(50) NULL AFTER id");
                if (!$alterResult) {
                    throw new Exception("Failed to add receipt_no column: " . $conn->error);
                }
            } else {
                // Check if it's the wrong type and fix it
                $columnInfo = $checkColumn->fetch_assoc();
                if (strpos(strtolower($columnInfo['Type']), 'int') !== false) {
                    // Drop and recreate as VARCHAR
                    $conn->query("ALTER TABLE bookings DROP COLUMN receipt_no");
                    $alterResult = $conn->query("ALTER TABLE bookings ADD COLUMN receipt_no VARCHAR(50) NULL AFTER id");
                    if (!$alterResult) {
                        throw new Exception("Failed to fix receipt_no column: " . $conn->error);
                    }
                }
            }
            
            // Get the highest receipt number for today from receipt_no column
            $stmt = $conn->prepare("SELECT receipt_no FROM bookings WHERE receipt_no LIKE ? ORDER BY receipt_no DESC LIMIT 1");
            if (!$stmt) {
                throw new Exception("Failed to prepare statement: " . $conn->error);
            }
            
            $datePattern = "BARCIE-{$currentDate}-%";
            $stmt->bind_param("s", $datePattern);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to execute query: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                // Extract the last number from existing receipt
                $lastReceipt = $row['receipt_no'];
                $parts = explode('-', $lastReceipt);
                $lastNumber = isset($parts[2]) ? intval($parts[2]) : 0;
                $nextNumber = $lastNumber + 1;
            } else {
                // First receipt of the day
                $nextNumber = 1;
            }
            
            // Format with leading zeros (e.g., 0001, 0002, etc.)
            $formattedNumber = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            
            // Create receipt number: BARCIE-YYYYMMDD-0001
            $receiptNumber = "BARCIE-{$currentDate}-{$formattedNumber}";
            
            echo json_encode([
                'success' => true,
                'receipt_no' => $receiptNumber,
                'next_number' => $nextNumber,
                'date' => $currentDate
            ]);
            
            $stmt->close();
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Failed to generate receipt number',
                'message' => $e->getMessage()
            ]);
            error_log("get_receipt_no error: " . $e->getMessage());
        }
        $conn->close();
        exit;
    }

    if ($_GET['action'] === 'get_available_count') {
        // Clear output buffer
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET');
        
        try {
            // Check if connection exists and is valid
            if (!isset($conn)) {
                throw new Exception("Database connection not established");
            }
            
            if ($conn->connect_error) {
                throw new Exception("Database connection failed: " . $conn->connect_error);
            }
            
            // Count available rooms and facilities
            // Available means: room_status is 'available' or 'clean' AND not currently booked
            $query = "SELECT COUNT(*) as available_count FROM items i 
                      WHERE (i.room_status = 'available' OR i.room_status = 'clean')
                      AND i.id NOT IN (
                          SELECT DISTINCT b.room_id 
                          FROM bookings b 
                          WHERE b.room_id IS NOT NULL 
                          AND b.status IN ('confirmed', 'approved', 'pending', 'checked_in')
                          AND (
                              (b.checkin <= CURDATE() AND b.checkout >= CURDATE()) OR
                              (b.checkin = CURDATE())
                          )
                      )";
            
            $result = $conn->query($query);
            
            if (!$result) {
                throw new Exception("Query failed: " . $conn->error);
            }
            
            $row = $result->fetch_assoc();
            $availableCount = $row['available_count'] ?? 0;
            
            echo json_encode([
                'success' => true,
                'available_count' => (int)$availableCount,
                'query_time' => date('Y-m-d H:i:s')
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'available_count' => 0
            ]);
        }
        
        $conn->close();
        exit;
    }

    if ($_GET['action'] === 'fix_receipt_db') {
        header('Content-Type: application/json');
        try {
            echo json_encode(['message' => 'Checking bookings table structure...']);
            
            // Get current table structure
            $result = $conn->query('DESCRIBE bookings');
            $columns = [];
            while ($row = $result->fetch_assoc()) {
                $columns[] = $row['Field'] . ' - ' . $row['Type'];
            }
            
            // Fix receipt_no column
            $conn->query('ALTER TABLE bookings DROP COLUMN IF EXISTS receipt_no');
            $conn->query('ALTER TABLE bookings ADD COLUMN receipt_no VARCHAR(50) NULL AFTER id');
            
            // Get updated structure
            $result = $conn->query('DESCRIBE bookings');
            $updatedColumns = [];
            while ($row = $result->fetch_assoc()) {
                $updatedColumns[] = $row['Field'] . ' - ' . $row['Type'];
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Database schema fixed successfully!',
                'before' => $columns,
                'after' => $updatedColumns
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        $conn->close();
        exit;
    }

    // Get chat messages
    if ($_GET['action'] === 'get_chat_messages') {
        header('Content-Type: application/json');
        
        // Temporarily disabled to fix feedback system
        echo json_encode(['success' => false, 'error' => 'Chat system temporarily disabled']);
        exit;
    }

    // Get chat conversations
    if ($_GET['action'] === 'get_chat_conversations') {
        header('Content-Type: application/json');
        
        // Temporarily disabled to fix feedback system
        echo json_encode(['success' => false, 'error' => 'Chat system temporarily disabled']);
        exit;
    }

    // Get unread count
    if ($_GET['action'] === 'get_unread_count') {
        header('Content-Type: application/json');
        
        // Temporarily disabled to fix feedback system
        echo json_encode(['success' => true, 'unread_count' => 0]);
        exit;
    }

    // Initialize chat system tables
    if ($_GET['action'] === 'init_chat') {
        header('Content-Type: application/json');
        
        try {
            initializeChatTables($conn);
            echo json_encode([
                'success' => true, 
                'message' => 'Chat system database initialization completed!'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false, 
                'error' => 'Error initializing chat system: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    // Initialize feedback table
    if ($_GET['action'] === 'init_feedback_table') {
        header('Content-Type: application/json');
        
        try {
            // Create feedback table if it doesn't exist
            $conn->query("CREATE TABLE IF NOT EXISTS feedback (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                rating INT NOT NULL DEFAULT 5 CHECK (rating >= 1 AND rating <= 5),
                message TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user_id (user_id),
                INDEX idx_rating (rating),
                INDEX idx_created_at (created_at)
            )");
            
            // Check if rating column exists, add if missing
            $result = $conn->query("SHOW COLUMNS FROM feedback LIKE 'rating'");
            if ($result->num_rows == 0) {
                $conn->query("ALTER TABLE feedback ADD COLUMN rating INT NOT NULL DEFAULT 5 AFTER user_id");
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Feedback table initialized successfully!'
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Error initializing feedback table: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    // Fix missing room IDs in bookings
    if ($_GET['action'] === 'fix_room_ids') {
        header('Content-Type: application/json');
        
        try {
            fixMissingRoomIds($conn);
            
            // Get count of fixed bookings
            $count_query = "SELECT COUNT(*) as fixed_count FROM bookings WHERE room_id IS NOT NULL";
            $count_result = $conn->query($count_query);
            $fixed_count = $count_result ? $count_result->fetch_assoc()['fixed_count'] : 0;
            
            $remaining_query = "SELECT COUNT(*) as remaining_count FROM bookings WHERE room_id IS NULL";
            $remaining_result = $conn->query($remaining_query);
            $remaining_count = $remaining_result ? $remaining_result->fetch_assoc()['remaining_count'] : 0;
            
            echo json_encode([
                'success' => true,
                'message' => 'Room ID fixing completed!',
                'fixed_bookings' => $fixed_count,
                'remaining_null' => $remaining_count
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Error fixing room IDs: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    // Get feedback data for admin
    if ($_GET['action'] === 'get_feedback_data') {
        header('Content-Type: application/json');
        
        try {
            // Ensure feedback table exists first
            $conn->query("CREATE TABLE IF NOT EXISTS feedback (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                rating INT NOT NULL DEFAULT 5 CHECK (rating >= 1 AND rating <= 5),
                message TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user_id (user_id),
                INDEX idx_rating (rating),
                INDEX idx_created_at (created_at)
            )");
            
            // Check if rating column exists, add if missing
            $result = $conn->query("SHOW COLUMNS FROM feedback LIKE 'rating'");
            if ($result->num_rows == 0) {
                $conn->query("ALTER TABLE feedback ADD COLUMN rating INT NOT NULL DEFAULT 5 AFTER user_id");
            }
            
            $limit = (int)($_GET['limit'] ?? 50);
            $offset = (int)($_GET['offset'] ?? 0);
            
            // Get feedback without user details (anonymous feedback)
            $stmt = $conn->prepare("SELECT f.id, f.rating, f.message, f.created_at
                                   FROM feedback f 
                                   ORDER BY f.created_at DESC 
                                   LIMIT ? OFFSET ?");
            $stmt->bind_param("ii", $limit, $offset);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $feedback = [];
            while ($row = $result->fetch_assoc()) {
                // Add anonymous guest identifier
                $row['username'] = 'Guest';
                $row['email'] = '';
                $feedback[] = $row;
            }
            $stmt->close();
            
            // Get summary statistics
            $stats_query = "SELECT 
                COUNT(*) as total_feedback,
                AVG(rating) as avg_rating,
                COUNT(CASE WHEN rating = 5 THEN 1 END) as five_star,
                COUNT(CASE WHEN rating = 4 THEN 1 END) as four_star,
                COUNT(CASE WHEN rating = 3 THEN 1 END) as three_star,
                COUNT(CASE WHEN rating = 2 THEN 1 END) as two_star,
                COUNT(CASE WHEN rating = 1 THEN 1 END) as one_star
                FROM feedback";
            $stats_result = $conn->query($stats_query);
            $stats = $stats_result->fetch_assoc();
            
            echo json_encode([
                'success' => true,
                'feedback' => $feedback,
                'stats' => $stats
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Database error: ' . $e->getMessage()
            ]);
        }
        exit;
    }


}

/* ---------------------------
   POST actions
   --------------------------- */
$action = $_POST['action'] ?? '';


// SECURITY: Disable legacy guest user login/signup via this endpoint.
// If a POST contains a 'password' field it is likely a login/signup attempt from
// the old guest auth UI. We intentionally block these requests and return a
// clear JSON response so external callers know guest accounts are disabled.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['password'])) {
    // Clear any output buffer
    while (ob_get_level()) ob_end_clean();
    header('Content-Type: application/json');
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'error' => 'Guest accounts disabled',
        'message' => 'User login/signup is no longer supported. Please contact the administrator for access.'
    ]);
    $conn->close();
    exit;
}



/* ---------------------------
   CREATE BOOKING
   --------------------------- */
if ($action === 'create_booking') {

    // No user_id needed for guest bookings
    $type = $_POST['booking_type'] ?? '';
    $status = "pending";
    $room_id = (int)($_POST['room_id'] ?? 0);

    // Discount application fields
    $discount_type = $_POST['discount_type'] ?? '';
    $discount_details = $_POST['discount_details'] ?? '';
    $discount_proof_path = '';

    // Handle file upload for discount proof
    // NOTE: Client-side validation (filename heuristics) is performed in the browser, but
    // server-side validation is still required for security and correctness. Recommended server-side checks:
    //  - Validate MIME type and extension (image/pdf) and enforce a reasonable max filesize.
    //  - Scan the uploaded filename or run OCR (Tesseract) to detect keywords like
    //    "la consolacion", "lcup", "senior", "senior citizen" for automated hints.
    //  - Always store the original and a safely-named copy; do not trust user-provided filenames.
    //  - Keep discount_status = 'pending' and allow manual admin review of the uploaded proof.
    if (!empty($discount_type) && isset($_FILES['discount_proof']) && $_FILES['discount_proof']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_tmp = $_FILES['discount_proof']['tmp_name'];
        $file_ext = pathinfo($_FILES['discount_proof']['name'], PATHINFO_EXTENSION);
        $file_name = time() . '_' . uniqid() . '.' . $file_ext;
        $target_path = $upload_dir . $file_name;
        if (move_uploaded_file($file_tmp, $target_path)) {
            $discount_proof_path = 'uploads/' . $file_name;
            error_log("Discount proof uploaded to: " . $discount_proof_path);
        }
    }

    // Validate room/facility selection
    if ($room_id <= 0) {
        handleResponse("Please select a room or facility.", false, '../Guest.php');
    }

    // Get room/facility details for validation and details
    $room_stmt = $conn->prepare("SELECT id, name, item_type, room_status, capacity, price FROM items WHERE id = ?");
    $room_stmt->bind_param("i", $room_id);
    $room_stmt->execute();
    $room_result = $room_stmt->get_result();
    $room_data = $room_result->fetch_assoc();
    $room_stmt->close();

    if (!$room_data) {
        handleResponse("Selected room/facility not found.", false, '../Guest.php');
    }

    // Check if room/facility is available
    if (!in_array($room_data['room_status'], ['available', 'clean'])) {
        handleResponse("Selected " . $room_data['item_type'] . " is not available. Current status: " . $room_data['room_status'], false, '../Guest.php');
    }

    if ($type === 'reservation') {
        // Get the receipt number from the form
        $receipt_no = $_POST['receipt_no'] ?? '';
        
        // If no receipt number provided, generate one
        if (empty($receipt_no)) {
            $currentDate = date('Ymd');
            $stmt = $conn->prepare("SELECT receipt_no FROM bookings WHERE receipt_no LIKE ? ORDER BY receipt_no DESC LIMIT 1");
            $datePattern = "BARCIE-{$currentDate}-%";
            $stmt->bind_param("s", $datePattern);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                $lastReceipt = $row['receipt_no'];
                $parts = explode('-', $lastReceipt);
                $lastNumber = isset($parts[2]) ? intval($parts[2]) : 0;
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }
            
            $formattedNumber = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            $receipt_no = "BARCIE-{$currentDate}-{$formattedNumber}";
            $stmt->close();
        }

        $guest_name = $conn->real_escape_string($_POST['guest_name'] ?? '');
        $contact = $conn->real_escape_string($_POST['contact_number'] ?? '');
        $email = $conn->real_escape_string($_POST['email'] ?? '');
        $checkin = $_POST['checkin'] ?? null;
        $checkout = $_POST['checkout'] ?? null;
        $occupants = (int)($_POST['occupants'] ?? 1);
        $company = $conn->real_escape_string($_POST['company'] ?? '');

        // Validate dates
        if (empty($checkin) || empty($checkout)) {
            handleResponse("Please provide check-in and check-out dates.", false, '../Guest.php');
        }

        if (strtotime($checkin) >= strtotime($checkout)) {
            handleResponse("Check-out date must be after check-in date.", false, '../Guest.php');
        }

        // Check for double booking
        $conflict_stmt = $conn->prepare("SELECT id FROM bookings WHERE room_id = ? AND status IN ('confirmed', 'approved', 'pending', 'checked_in') AND ((checkin BETWEEN ? AND ?) OR (checkout BETWEEN ? AND ?) OR (checkin <= ? AND checkout >= ?))");
        $conflict_stmt->bind_param("issssss", $room_id, $checkin, $checkout, $checkin, $checkout, $checkin, $checkout);
        $conflict_stmt->execute();
        $conflict_result = $conflict_stmt->get_result();
        
        if ($conflict_result->num_rows > 0) {
            $conflict_stmt->close();
            handleResponse("Sorry, the selected " . $room_data['item_type'] . " is already booked for the requested dates.", false, '../Guest.php');
        }
        $conflict_stmt->close();

        // Validate occupancy
        if ($occupants > $room_data['capacity']) {
            handleResponse("Number of occupants (" . $occupants . ") exceeds " . $room_data['item_type'] . " capacity (" . $room_data['capacity'] . ").", false, '../Guest.php');
        }

        // Add discount info to details and set discount_status
        $discount_info = '';
        $discount_status = 'none';
        // store a separate column value for proof path (nullable)
        $proof_of_id = null;
        if (!empty($discount_type)) {
            $discount_info = " | Discount: $discount_type | Discount Details: $discount_details | Proof: $discount_proof_path";
            $discount_status = 'pending'; // Set discount status to pending for admin review
            if (!empty($discount_proof_path)) $proof_of_id = $discount_proof_path;
        }

        $details = "Receipt: $receipt_no | " . ucfirst($room_data['item_type']) . ": " . $room_data['name'] . " | Guest: $guest_name | Email: $email | Contact: $contact | Check-in: $checkin | Check-out: $checkout | Occupants: $occupants | Company: $company" . $discount_info;

        // Try to insert with room_id and receipt_no columns
        try {
            // Debug: Log what we're trying to insert
            error_log("Booking Debug - Type: $type, Room ID: $room_id, Receipt: $receipt_no");
            error_log("Booking Debug - Details: " . substr($details, 0, 100));
            error_log("Booking Debug - Status: $status, Checkin: $checkin, Checkout: $checkout");

            // Include proof_of_id column so uploaded proof path is stored separately
            $stmt = $conn->prepare("INSERT INTO bookings (type, room_id, receipt_no, details, status, discount_status, proof_of_id, checkin, checkout) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("sisssssss", $type, $room_id, $receipt_no, $details, $status, $discount_status, $proof_of_id, $checkin, $checkout);
            $success = $stmt->execute();

            if (!$success) {
                throw new Exception("Execute failed: " . $stmt->error);
            }

            // Log stored proof path for debugging
            if (!empty($proof_of_id)) {
                error_log("Stored proof_of_id for booking (receipt: $receipt_no): " . $proof_of_id);
            } else {
                error_log("No proof_of_id stored for booking (receipt: $receipt_no)");
            }

            if ($success) {
                // Update room status to reserved
                $update_status = $conn->prepare("UPDATE items SET room_status = 'reserved' WHERE id = ?");
                $update_status->bind_param("i", $room_id);
                $update_status->execute();
                $update_status->close();

                // Always send confirmation email to guest
                if (!empty($email)) {
                    error_log("========================================");
                    error_log("BOOKING EMAIL - Starting email send process");
                    error_log("Recipient: " . $email);
                    error_log("Guest: " . $guest_name);
                    error_log("Receipt: " . $receipt_no);
                    error_log("========================================");
                    
                    $subject = "Booking Confirmation - BarCIE International Center";
                    
                    // Create professional email content
                    $emailContent = '
                        <h2 style="margin: 0 0 20px 0; color: #212529; font-size: 24px; font-weight: 600;">Booking Confirmation</h2>
                        <p style="margin: 0 0 20px 0; color: #495057; font-size: 16px; line-height: 1.6;">
                            Dear <strong>' . htmlspecialchars($guest_name) . '</strong>,
                        </p>
                        <p style="margin: 0 0 25px 0; color: #495057; font-size: 15px; line-height: 1.6;">
                            Thank you for your booking! We have received your reservation request with the following details:
                        </p>
                        
                        <!-- Booking Details Card -->
                        <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #f8f9fa; border-radius: 6px; margin-bottom: 25px;" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="padding: 25px;">
                                    <table role="presentation" style="width: 100%; border-collapse: collapse;" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td style="padding: 8px 0; color: #6c757d; font-size: 14px; width: 40%;">Receipt Number:</td>
                                            <td style="padding: 8px 0; color: #212529; font-size: 14px; font-weight: 600;">' . htmlspecialchars($receipt_no) . '</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 8px 0; color: #6c757d; font-size: 14px;">Room/Facility:</td>
                                            <td style="padding: 8px 0; color: #212529; font-size: 14px; font-weight: 600;">' . htmlspecialchars($room_data['name']) . '</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 8px 0; color: #6c757d; font-size: 14px;">Check-in Date:</td>
                                            <td style="padding: 8px 0; color: #212529; font-size: 14px; font-weight: 600;">' . date('F j, Y', strtotime($checkin)) . '</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 8px 0; color: #6c757d; font-size: 14px;">Check-out Date:</td>
                                            <td style="padding: 8px 0; color: #212529; font-size: 14px; font-weight: 600;">' . date('F j, Y', strtotime($checkout)) . '</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 8px 0; color: #6c757d; font-size: 14px;">Number of Occupants:</td>
                                            <td style="padding: 8px 0; color: #212529; font-size: 14px; font-weight: 600;">' . htmlspecialchars($occupants) . '</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 8px 0; color: #6c757d; font-size: 14px;">Booking Status:</td>
                                            <td style="padding: 8px 0;">
                                                <span style="display: inline-block; padding: 4px 12px; background-color: #ffc107; color: #000; font-size: 13px; font-weight: 600; border-radius: 4px;">Pending Approval</span>
                                            </td>
                                        </tr>';
                    
                    if (!empty($discount_type)) {
                        $emailContent .= '
                                        <tr>
                                            <td style="padding: 8px 0; color: #6c757d; font-size: 14px;">Discount Applied:</td>
                                            <td style="padding: 8px 0;">
                                                <span style="display: inline-block; padding: 4px 12px; background-color: #17a2b8; color: #fff; font-size: 13px; font-weight: 600; border-radius: 4px;">' . htmlspecialchars($discount_type) . '</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 8px 0; color: #6c757d; font-size: 14px;">Discount Status:</td>
                                            <td style="padding: 8px 0;">
                                                <span style="display: inline-block; padding: 4px 12px; background-color: #ffc107; color: #000; font-size: 13px; font-weight: 600; border-radius: 4px;">Pending Review</span>
                                            </td>
                                        </tr>';
                    }
                    
                    $emailContent .= '
                                    </table>
                                </td>
                            </tr>
                        </table>
                        
                        <!-- Next Steps -->
                        <div style="background-color: #e7f3ff; border-left: 4px solid #2196F3; padding: 15px 20px; margin-bottom: 25px; border-radius: 4px;">
                            <p style="margin: 0; color: #1976D2; font-size: 14px; line-height: 1.6;">
                                <strong>📋 What happens next?</strong><br>
                                Our team will review your booking request and notify you via email once it has been approved. Please keep this receipt number for your records.
                            </p>
                        </div>
                        
                        <p style="margin: 0 0 15px 0; color: #495057; font-size: 15px; line-height: 1.6;">
                            If you have any questions or need to make changes to your booking, please contact us with your receipt number.
                        </p>
                        <p style="margin: 0; color: #495057; font-size: 15px; line-height: 1.6;">
                            Thank you for choosing BarCIE International Center!
                        </p>';
                    
                    $emailBody = create_email_template('Booking Confirmation', $emailContent, 'This is an automated message. Please do not reply directly to this email.');
                    
                    error_log("BOOKING EMAIL - Calling send_smtp_mail()");
                    $mail_sent = send_smtp_mail($email, $subject, $emailBody);
                    error_log("BOOKING EMAIL - Send result: " . ($mail_sent ? "SUCCESS" : "FAILED"));
                    error_log("========================================");

                    // If there's a discount, also notify admin
                    if (!empty($discount_type)) {
                        error_log("DISCOUNT EMAIL - Sending admin notification");
                        $admin_email = 'pc.clemente11@gmail.com';
                        $admin_subject = "New Discount Application - " . htmlspecialchars($discount_type);
                        $admin_message = '<div style="font-family: Arial, sans-serif; padding: 20px;">
                                <h3 style="color: #2d7be5;">New Discount Application</h3>
                                <div style="background: #f5f5f5; padding: 15px; border-radius: 5px;">
                                    <p><b>Guest:</b> ' . htmlspecialchars($guest_name) . '</p>
                                    <p><b>Email:</b> ' . htmlspecialchars($email) . '</p>
                                    <p><b>Contact:</b> ' . htmlspecialchars($contact) . '</p>
                                    <p><b>Room/Facility:</b> ' . htmlspecialchars($room_data['name']) . '</p>
                                    <p><b>Check-in:</b> ' . htmlspecialchars($checkin) . '</p>
                                    <p><b>Check-out:</b> ' . htmlspecialchars($checkout) . '</p>
                                    <p><b>Discount Type:</b> ' . htmlspecialchars($discount_type) . '</p>
                                    <p><b>Discount Details:</b> ' . htmlspecialchars($discount_details) . '</p>';
                        
                        if (!empty($discount_proof_path)) {
                            $admin_message .= '<p><b>Proof:</b> <a href="' . htmlspecialchars($discount_proof_path) . '">View Proof</a></p>';
                        }
                        
                        $admin_message .= '</div>
                                <p style="margin-top: 20px;"><em>Please review this discount application in the admin portal.</em></p>
                            </div>';
                        
                        $admin_mail_sent = send_smtp_mail($admin_email, $admin_subject, $admin_message);
                        error_log("DISCOUNT EMAIL - Admin notification result: " . ($admin_mail_sent ? "SUCCESS" : "FAILED"));
                    }
                } else {
                    error_log("BOOKING EMAIL - Skipped: No email address provided");
                }

                handleResponse("Reservation saved successfully with receipt number: $receipt_no for " . $room_data['name'], true, '../Guest.php');
            } else {
                handleResponse("Error saving reservation: " . $stmt->error, false, '../Guest.php');
                error_log("Booking insert error: " . $stmt->error);
            }
            $stmt->close();
        } catch (mysqli_sql_exception $e) {
            handleResponse("Database error: " . $e->getMessage(), false, '../Guest.php');
            error_log("Booking creation exception: " . $e->getMessage());
        } catch (Exception $e) {
            handleResponse("Unexpected error: " . $e->getMessage(), false, '../Guest.php');
            error_log("Booking creation general exception: " . $e->getMessage());
        }
        
    } elseif ($type === 'pencil') {
        $pencil_date = $_POST['pencil_date'] ?? null;
        $event = $conn->real_escape_string($_POST['event_type'] ?? '');
        $pax = (int)($_POST['pax'] ?? 1);
        $time_from = $_POST['time_from'] ?? '';
        $time_to = $_POST['time_to'] ?? '';
        $caterer = $conn->real_escape_string($_POST['caterer'] ?? '');
        $contact_person = $conn->real_escape_string($_POST['contact_person'] ?? '');
        $contact_number = $conn->real_escape_string($_POST['contact_number'] ?? '');
        $company = $conn->real_escape_string($_POST['company'] ?? '');

        // Validate facility type for pencil booking
        if ($room_data['item_type'] !== 'facility') {
            handleResponse("Pencil bookings are only available for facilities/function halls.", false, '../Guest.php');
        }

        // Validate pax capacity
        if ($pax > $room_data['capacity']) {
            handleResponse("Number of guests (" . $pax . ") exceeds facility capacity (" . $room_data['capacity'] . ").", false, '../Guest.php');
        }

        // Check for conflicts on the same date
        if (!empty($pencil_date)) {
            $conflict_stmt = $conn->prepare("SELECT id FROM bookings WHERE room_id = ? AND status IN ('confirmed', 'approved', 'pending') AND DATE(checkin) = ?");
            $conflict_stmt->bind_param("is", $room_id, $pencil_date);
            $conflict_stmt->execute();
            $conflict_result = $conflict_stmt->get_result();
            
            if ($conflict_result->num_rows > 0) {
                $conflict_stmt->close();
                handleResponse("Sorry, the selected facility is already booked for " . $pencil_date . ".", false, '../Guest.php');
            }
            $conflict_stmt->close();
        }

        $details = "Pencil Booking | Facility: " . $room_data['name'] . " | Date: $pencil_date | Event: $event | Pax: $pax | Time: $time_from-$time_to | Caterer: $caterer | Contact: $contact_person ($contact_number) | Company: $company";

        try {
            $stmt = $conn->prepare("INSERT INTO bookings (type, room_id, details, status, checkin) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sisss", $type, $room_id, $details, $status, $pencil_date);
            $success = $stmt->execute();
            
            if ($success) {
                // Update facility status to reserved
                $update_status = $conn->prepare("UPDATE items SET room_status = 'reserved' WHERE id = ?");
                $update_status->bind_param("i", $room_id);
                $update_status->execute();
                $update_status->close();
                
                // Send confirmation email to guest
                if (!empty($contact_number) && preg_match('/@gmail\.com$/i', $contact_number)) {
                    $subject = 'BarCIE Pencil Booking Confirmation';
                    $message = "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; border: 1px solid #eee; border-radius: 8px; padding: 24px; background: #fafbfc;'>"
                        . "<h2 style='color: #2d7be5;'>BarCIE International Center</h2>"
                        . "<p>Dear Guest,</p>"
                        . "<p>Your pencil booking request has been <b>received</b>! Here are your details:</p>"
                        . "<ul style='background: #f6f8fa; border-radius: 6px; padding: 16px; list-style: none;'>"
                        . "<li><b>Facility:</b> " . htmlspecialchars($room_data['name']) . "</li>"
                        . "<li><b>Date:</b> " . htmlspecialchars($pencil_date) . "</li>"
                        . "<li><b>Event:</b> " . htmlspecialchars($event) . "</li>"
                        . "<li><b>Pax:</b> " . htmlspecialchars($pax) . "</li>"
                        . "</ul>"
                        . "<p style='margin-top: 18px;'>We will review your booking and notify you once it is confirmed.</p>"
                        . "<p style='color: #888;'>If you have questions, please reply to this email or contact us at info@barcie.com.</p>"
                        . "<p style='margin-top: 32px; color: #2d7be5;'><b>Thank you for choosing BarCIE International Center!</b></p>"
                        . "</div>";
                    send_smtp_mail($contact_number, $subject, $message);
                }
                
                handleResponse("Pencil booking saved for " . $room_data['name'] . " on " . $pencil_date, true, '../Guest.php');
            } else {
                handleResponse("Error: " . $stmt->error, false, '../Guest.php');
            }
            $stmt->close();
        } catch (mysqli_sql_exception $e) {
            handleResponse("Database error: " . $e->getMessage(), false, '../Guest.php');
        }
    } else {
        handleResponse("Unknown booking type.", false, '../Guest.php');
    }
}

/* ---------------------------
   SUBMIT FEEDBACK
   --------------------------- */
if ($action === 'submit_feedback' || $action === 'feedback') {
    // No user_id needed for guest feedback
    $message = trim($_POST['message'] ?? '');
    $rating = (int)($_POST['rating'] ?? 0);
    
    if ($rating < 1 || $rating > 5) {
        handleResponse("Please select a star rating.", false, '../Guest.php#feedback');
    }
    
    // Create feedback table if it doesn't exist (no user_id needed)
    try {
        $conn->query("CREATE TABLE IF NOT EXISTS feedback (
            id INT AUTO_INCREMENT PRIMARY KEY,
            rating INT NOT NULL DEFAULT 5,
            message TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_rating (rating),
            INDEX idx_created_at (created_at)
        )");
        
        // Check if rating column exists, add if missing
        $result = $conn->query("SHOW COLUMNS FROM feedback LIKE 'rating'");
        if ($result && $result->num_rows == 0) {
            $conn->query("ALTER TABLE feedback ADD COLUMN rating INT NOT NULL DEFAULT 5");
        }
        
        // Remove user_id column if it exists
        $user_id_exists = $conn->query("SHOW COLUMNS FROM feedback LIKE 'user_id'");
        if ($user_id_exists && $user_id_exists->num_rows > 0) {
            $conn->query("ALTER TABLE feedback DROP COLUMN user_id");
        }
        
        // Try to add check constraint (ignore if already exists)
        try {
            $conn->query("ALTER TABLE feedback ADD CONSTRAINT chk_rating CHECK (rating >= 1 AND rating <= 5)");
        } catch (Exception $constraintError) {
            // Ignore constraint errors - it might already exist
        }
    } catch (Exception $e) {
        error_log("Error creating/updating feedback table: " . $e->getMessage());
    }
    
    $stmt = $conn->prepare("INSERT INTO feedback (rating, message) VALUES (?, ?)");
    $stmt->bind_param("is", $rating, $message);
    
    if ($stmt->execute()) {
        handleResponse("Thank you for your " . $rating . "-star feedback!", true, '../Guest.php#feedback');
    } else {
        handleResponse("Error submitting feedback. Please try again.", false, '../Guest.php#feedback');
        error_log("Feedback submission error: " . $stmt->error);
    }
    $stmt->close();
}

/* ---------------------------
   ADMIN: update booking status
   --------------------------- */
if ($action === 'admin_update_booking') {
    if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        // Return JSON for AJAX clients; for normal requests redirect with session message
        handleResponse('Access denied. Admin login required.', false, '../dashboard.php');
    }

    $bookingId = (int)($_POST['booking_id'] ?? 0);
    $adminAction = $_POST['admin_action'] ?? '';

    $statusMap = [
        "approve" => "confirmed",
        "reject"  => "rejected",
        "checkin" => "checked_in",
        "checkout"=> "checked_out",
        "cancel"  => "cancelled"
    ];

    if (!array_key_exists($adminAction, $statusMap)) {
        $_SESSION['msg'] = "Unknown admin action.";
        redirect('../dashboard.php');
    }

    $newStatus = $statusMap[$adminAction];
    
    // Get booking details first
    $booking_stmt = $conn->prepare("SELECT room_id, status, details FROM bookings WHERE id = ?");
    $booking_stmt->bind_param("i", $bookingId);
    $booking_stmt->execute();
    $booking_result = $booking_stmt->get_result();
    $booking_data = $booking_result->fetch_assoc();
    $booking_stmt->close();

    // Update booking status
    $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $newStatus, $bookingId);
    $success = $stmt->execute();
    $stmt->close();

    if ($success && $booking_data) {
        // Extract guest email and name from details
        $details = $booking_data['details'];
        $guest_email = '';
        $guest_name = 'Guest';
        $room_name = '';
        $checkin = '';
        $checkout = '';
        
        if (preg_match('/Email:\s*([^|]+)/', $details, $matches)) {
            $guest_email = trim($matches[1]);
        }
        if (preg_match('/Guest:\s*([^|]+)/', $details, $matches)) {
            $guest_name = trim($matches[1]);
        }
        if (preg_match('/(?:Room|Facility):\s*([^|]+)/', $details, $matches)) {
            $room_name = trim($matches[1]);
        }
        if (preg_match('/Check-in:\s*([^|]+)/', $details, $matches)) {
            $checkin = trim($matches[1]);
        }
        if (preg_match('/Check-out:\s*([^|]+)/', $details, $matches)) {
            $checkout = trim($matches[1]);
        }

        // Send email notification to guest for every status change
        if (!empty($guest_email)) {
            error_log("========================================");
            error_log("ADMIN UPDATE EMAIL - Booking ID: $bookingId");
            error_log("Action: $adminAction → Status: $newStatus");
            error_log("Guest: $guest_name");
            error_log("Email: $guest_email");
            error_log("========================================");
            
            $emailSubject = '';
            $emailContent = '';
            
            switch ($adminAction) {
                case 'approve':
                    $emailSubject = 'Booking Approved - BarCIE International Center';
                    $emailContent = '
                        <div style="text-align: center; margin-bottom: 30px;">
                            <div style="display: inline-block; background-color: #28a745; color: white; padding: 12px 24px; border-radius: 50px; font-size: 14px; font-weight: 600;">
                                ✓ APPROVED
                            </div>
                        </div>
                        
                        <h2 style="margin: 0 0 20px 0; color: #212529; font-size: 24px; font-weight: 600; text-align: center;">Your Booking Has Been Approved!</h2>
                        
                        <p style="margin: 0 0 25px 0; color: #495057; font-size: 16px; line-height: 1.6; text-align: center;">
                            Dear <strong>' . htmlspecialchars($guest_name) . '</strong>,
                        </p>
                        
                        <p style="margin: 0 0 25px 0; color: #495057; font-size: 15px; line-height: 1.6;">
                            Great news! Your reservation has been confirmed. We are pleased to welcome you to BarCIE International Center.
                        </p>
                        
                        <!-- Booking Details Card -->
                        <table role="presentation" style="width: 100%; border-collapse: collapse; background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); border-radius: 8px; margin-bottom: 25px; border: 2px solid #28a745;" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="padding: 25px;">
                                    <h3 style="margin: 0 0 15px 0; color: #155724; font-size: 18px;">Reservation Details</h3>
                                    <table role="presentation" style="width: 100%; border-collapse: collapse;" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td style="padding: 8px 0; color: #155724; font-size: 14px; font-weight: 600;">Room/Facility:</td>
                                            <td style="padding: 8px 0; color: #212529; font-size: 14px;">' . htmlspecialchars($room_name) . '</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 8px 0; color: #155724; font-size: 14px; font-weight: 600;">Check-in Date:</td>
                                            <td style="padding: 8px 0; color: #212529; font-size: 14px;">' . date('F j, Y', strtotime($checkin)) . '</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 8px 0; color: #155724; font-size: 14px; font-weight: 600;">Check-out Date:</td>
                                            <td style="padding: 8px 0; color: #212529; font-size: 14px;">' . date('F j, Y', strtotime($checkout)) . '</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                        
                        <div style="background-color: #e7f3ff; border-left: 4px solid #2196F3; padding: 15px 20px; margin-bottom: 20px; border-radius: 4px;">
                            <p style="margin: 0; color: #1976D2; font-size: 14px; line-height: 1.6;">
                                <strong>📌 Important:</strong> Please arrive during check-in hours and bring a valid ID. If you have any special requests, feel free to contact us in advance.
                            </p>
                        </div>
                        
                        <p style="margin: 0; color: #495057; font-size: 15px; line-height: 1.6; text-align: center;">
                            We look forward to welcoming you!
                        </p>';
                    break;
                    
                case 'reject':
                    $emailSubject = 'Booking Status Update - BarCIE International Center';
                    $emailContent = '
                        <div style="text-align: center; margin-bottom: 30px;">
                            <div style="display: inline-block; background-color: #dc3545; color: white; padding: 12px 24px; border-radius: 50px; font-size: 14px; font-weight: 600;">
                                ✗ NOT APPROVED
                            </div>
                        </div>
                        
                        <h2 style="margin: 0 0 20px 0; color: #212529; font-size: 24px; font-weight: 600; text-align: center;">Booking Status Update</h2>
                        
                        <p style="margin: 0 0 25px 0; color: #495057; font-size: 16px; line-height: 1.6; text-align: center;">
                            Dear <strong>' . htmlspecialchars($guest_name) . '</strong>,
                        </p>
                        
                        <p style="margin: 0 0 25px 0; color: #495057; font-size: 15px; line-height: 1.6;">
                            Thank you for your interest in BarCIE International Center. Unfortunately, we are unable to approve your reservation request at this time.
                        </p>
                        
                        <!-- Booking Details Card -->
                        <table role="presentation" style="width: 100%; border-collapse: collapse; background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%); border-radius: 8px; margin-bottom: 25px; border: 2px solid #dc3545;" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="padding: 25px;">
                                    <h3 style="margin: 0 0 15px 0; color: #721c24; font-size: 18px;">Reservation Details</h3>
                                    <table role="presentation" style="width: 100%; border-collapse: collapse;" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td style="padding: 8px 0; color: #721c24; font-size: 14px; font-weight: 600;">Room/Facility:</td>
                                            <td style="padding: 8px 0; color: #212529; font-size: 14px;">' . htmlspecialchars($room_name) . '</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 8px 0; color: #721c24; font-size: 14px; font-weight: 600;">Check-in Date:</td>
                                            <td style="padding: 8px 0; color: #212529; font-size: 14px;">' . date('F j, Y', strtotime($checkin)) . '</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 8px 0; color: #721c24; font-size: 14px; font-weight: 600;">Check-out Date:</td>
                                            <td style="padding: 8px 0; color: #212529; font-size: 14px;">' . date('F j, Y', strtotime($checkout)) . '</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                        
                        <p style="margin: 0 0 15px 0; color: #495057; font-size: 15px; line-height: 1.6;">
                            This may be due to availability conflicts or other operational requirements. We apologize for any inconvenience.
                        </p>
                        
                        <p style="margin: 0; color: #495057; font-size: 15px; line-height: 1.6;">
                            If you have questions or would like to discuss alternative dates, please don\'t hesitate to contact us.
                        </p>';
                    break;
                    
                case 'checkin':
                    $emailSubject = 'Check-in Confirmed - BarCIE International Center';
                    $emailContent = '
                        <div style="text-align: center; margin-bottom: 30px;">
                            <div style="display: inline-block; background-color: #17a2b8; color: white; padding: 12px 24px; border-radius: 50px; font-size: 14px; font-weight: 600;">
                                ✓ CHECKED IN
                            </div>
                        </div>
                        
                        <h2 style="margin: 0 0 20px 0; color: #212529; font-size: 24px; font-weight: 600; text-align: center;">Welcome to BarCIE!</h2>
                        
                        <p style="margin: 0 0 25px 0; color: #495057; font-size: 16px; line-height: 1.6; text-align: center;">
                            Dear <strong>' . htmlspecialchars($guest_name) . '</strong>,
                        </p>
                        
                        <p style="margin: 0 0 25px 0; color: #495057; font-size: 15px; line-height: 1.6;">
                            You have been successfully checked in. We hope you enjoy your stay at BarCIE International Center!
                        </p>
                        
                        <!-- Stay Details Card -->
                        <table role="presentation" style="width: 100%; border-collapse: collapse; background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%); border-radius: 8px; margin-bottom: 25px; border: 2px solid #17a2b8;" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="padding: 25px;">
                                    <h3 style="margin: 0 0 15px 0; color: #0c5460; font-size: 18px;">Your Stay</h3>
                                    <table role="presentation" style="width: 100%; border-collapse: collapse;" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td style="padding: 8px 0; color: #0c5460; font-size: 14px; font-weight: 600;">Room/Facility:</td>
                                            <td style="padding: 8px 0; color: #212529; font-size: 14px;">' . htmlspecialchars($room_name) . '</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 8px 0; color: #0c5460; font-size: 14px; font-weight: 600;">Check-out Date:</td>
                                            <td style="padding: 8px 0; color: #212529; font-size: 14px;">' . date('F j, Y', strtotime($checkout)) . '</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                        
                        <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px 20px; margin-bottom: 20px; border-radius: 4px;">
                            <p style="margin: 0; color: #856404; font-size: 14px; line-height: 1.6;">
                                <strong>💡 Reminder:</strong> Please remember your check-out date. If you need any assistance during your stay, our staff is here to help!
                            </p>
                        </div>
                        
                        <p style="margin: 0; color: #495057; font-size: 15px; line-height: 1.6; text-align: center;">
                            Enjoy your stay!
                        </p>';
                    break;
                    
                case 'checkout':
                    $emailSubject = 'Check-out Complete - BarCIE International Center';
                    $emailContent = '
                        <div style="text-align: center; margin-bottom: 30px;">
                            <div style="display: inline-block; background-color: #6f42c1; color: white; padding: 12px 24px; border-radius: 50px; font-size: 14px; font-weight: 600;">
                                ✓ CHECKED OUT
                            </div>
                        </div>
                        
                        <h2 style="margin: 0 0 20px 0; color: #212529; font-size: 24px; font-weight: 600; text-align: center;">Thank You for Staying With Us!</h2>
                        
                        <p style="margin: 0 0 25px 0; color: #495057; font-size: 16px; line-height: 1.6; text-align: center;">
                            Dear <strong>' . htmlspecialchars($guest_name) . '</strong>,
                        </p>
                        
                        <p style="margin: 0 0 25px 0; color: #495057; font-size: 15px; line-height: 1.6;">
                            Your check-out has been processed successfully. Thank you for choosing BarCIE International Center!
                        </p>
                        
                        <!-- Visit Summary Card -->
                        <table role="presentation" style="width: 100%; border-collapse: collapse; background: linear-gradient(135deg, #e2d9f3 0%, #d6c1f0 100%); border-radius: 8px; margin-bottom: 25px; border: 2px solid #6f42c1;" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="padding: 25px;">
                                    <h3 style="margin: 0 0 15px 0; color: #4a148c; font-size: 18px;">Visit Summary</h3>
                                    <table role="presentation" style="width: 100%; border-collapse: collapse;" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td style="padding: 8px 0; color: #4a148c; font-size: 14px; font-weight: 600;">Room/Facility:</td>
                                            <td style="padding: 8px 0; color: #212529; font-size: 14px;">' . htmlspecialchars($room_name) . '</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                        
                        <div style="background-color: #d4edda; border-left: 4px solid #28a745; padding: 15px 20px; margin-bottom: 20px; border-radius: 4px;">
                            <p style="margin: 0; color: #155724; font-size: 14px; line-height: 1.6;">
                                <strong>🌟 We hope you enjoyed your stay!</strong><br>
                                Your feedback is important to us. If you have any comments or suggestions, please feel free to reach out.
                            </p>
                        </div>
                        
                        <p style="margin: 0; color: #495057; font-size: 15px; line-height: 1.6; text-align: center;">
                            We look forward to welcoming you back in the future!
                        </p>';
                    break;
                    
                case 'cancel':
                    $emailSubject = 'Booking Cancelled - BarCIE International Center';
                    $emailContent = '
                        <div style="text-align: center; margin-bottom: 30px;">
                            <div style="display: inline-block; background-color: #fd7e14; color: white; padding: 12px 24px; border-radius: 50px; font-size: 14px; font-weight: 600;">
                                ⚠ CANCELLED
                            </div>
                        </div>
                        
                        <h2 style="margin: 0 0 20px 0; color: #212529; font-size: 24px; font-weight: 600; text-align: center;">Booking Cancellation Notice</h2>
                        
                        <p style="margin: 0 0 25px 0; color: #495057; font-size: 16px; line-height: 1.6; text-align: center;">
                            Dear <strong>' . htmlspecialchars($guest_name) . '</strong>,
                        </p>
                        
                        <p style="margin: 0 0 25px 0; color: #495057; font-size: 15px; line-height: 1.6;">
                            This is to confirm that your reservation has been cancelled.
                        </p>
                        
                        <!-- Cancelled Booking Card -->
                        <table role="presentation" style="width: 100%; border-collapse: collapse; background: linear-gradient(135deg, #ffe8d1 0%, #fdd9b5 100%); border-radius: 8px; margin-bottom: 25px; border: 2px solid #fd7e14;" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="padding: 25px;">
                                    <h3 style="margin: 0 0 15px 0; color: #8a4000; font-size: 18px;">Cancelled Reservation</h3>
                                    <table role="presentation" style="width: 100%; border-collapse: collapse;" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td style="padding: 8px 0; color: #8a4000; font-size: 14px; font-weight: 600;">Room/Facility:</td>
                                            <td style="padding: 8px 0; color: #212529; font-size: 14px;">' . htmlspecialchars($room_name) . '</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 8px 0; color: #8a4000; font-size: 14px; font-weight: 600;">Check-in Date:</td>
                                            <td style="padding: 8px 0; color: #212529; font-size: 14px;">' . date('F j, Y', strtotime($checkin)) . '</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 8px 0; color: #8a4000; font-size: 14px; font-weight: 600;">Check-out Date:</td>
                                            <td style="padding: 8px 0; color: #212529; font-size: 14px;">' . date('F j, Y', strtotime($checkout)) . '</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                        
                        <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px 20px; margin-bottom: 20px; border-radius: 4px;">
                            <p style="margin: 0; color: #856404; font-size: 14px; line-height: 1.6;">
                                <strong>⚠️ Important:</strong> If you did not request this cancellation or if this was done in error, please contact us immediately.
                            </p>
                        </div>
                        
                        <p style="margin: 0; color: #495057; font-size: 15px; line-height: 1.6;">
                            If you would like to make a new reservation, you are welcome to submit another booking request.
                        </p>';
                    break;
            }
            
            if ($emailSubject && $emailContent) {
                error_log("ADMIN UPDATE EMAIL - Sending email...");
                error_log("Subject: $emailSubject");
                $emailBody = create_email_template($emailSubject, $emailContent, 'This is an automated message. Please do not reply directly to this email.');
                $email_sent = send_smtp_mail($guest_email, $emailSubject, $emailBody);
                error_log("ADMIN UPDATE EMAIL - Result: " . ($email_sent ? "SUCCESS" : "FAILED"));
                error_log("========================================");
            } else {
                error_log("ADMIN UPDATE EMAIL - Skipped: No email template for action '$adminAction'");
                error_log("========================================");
            }
        } else {
            error_log("ADMIN UPDATE EMAIL - Skipped: No email address found in booking details");
            error_log("Booking ID: $bookingId");
            error_log("========================================");
        }

        // Update room status based on booking status
        if ($booking_data['room_id']) {
            $room_id = $booking_data['room_id'];
            $room_status = 'available'; // default
            
            switch ($adminAction) {
                case 'approve':
                    $room_status = 'reserved';
                    break;
                case 'checkin':
                    $room_status = 'occupied';
                    break;
                case 'checkout':
                    $room_status = 'dirty'; // needs cleaning after checkout
                    break;
                case 'reject':
                case 'cancel':
                    // Check if there are other active bookings for this room
                    $check_stmt = $conn->prepare("SELECT COUNT(*) as active_bookings FROM bookings WHERE room_id = ? AND status IN ('confirmed', 'approved', 'pending', 'checked_in') AND id != ?");
                    $check_stmt->bind_param("ii", $room_id, $bookingId);
                    $check_stmt->execute();
                    $check_result = $check_stmt->get_result();
                    $check_data = $check_result->fetch_assoc();
                    $check_stmt->close();
                    
                    if ($check_data['active_bookings'] == 0) {
                        $room_status = 'available';
                    } else {
                        $room_status = 'reserved'; // keep as reserved if other bookings exist
                    }
                    break;
            }
            
            // Update room status
            $room_update = $conn->prepare("UPDATE items SET room_status = ? WHERE id = ?");
            $room_update->bind_param("si", $room_status, $room_id);
            $room_update->execute();
            $room_update->close();
        }
    }

    // Check if this is an AJAX request
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
              strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    
    if ($isAjax) {
        // Return JSON response for AJAX requests
        header('Content-Type: application/json');
        if ($success) {
            echo json_encode([
                'success' => true, 
                'message' => "Booking #$bookingId updated to $newStatus successfully.",
                'status' => $newStatus
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'error' => "Error updating booking #$bookingId."
            ]);
        }
        exit;
    } else {
        // Traditional redirect for non-AJAX requests
        $_SESSION['msg'] = $success ? "Booking #$bookingId updated to $newStatus." : "Error updating booking.";
        redirect('../dashboard.php');
    }
}

/* ---------------------------
   ADMIN: update discount status (SEPARATE ACTION)
   --------------------------- */
if ($action === 'admin_update_discount') {
    if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        // Return JSON for AJAX clients; for normal requests redirect with session message
        handleResponse('Access denied. Admin login required.', false, '../dashboard.php');
    }

    $bookingId = (int)($_POST['booking_id'] ?? 0);
    $discountAction = $_POST['discount_action'] ?? ''; // 'approve' or 'reject'

    if (!in_array($discountAction, ['approve', 'reject'])) {
        $_SESSION['msg'] = "Unknown discount action.";
        redirect('../dashboard.php');
    }

    $newDiscountStatus = $discountAction === 'approve' ? 'approved' : 'rejected';
    
    // Get booking details first
    $booking_stmt = $conn->prepare("SELECT details, discount_status FROM bookings WHERE id = ?");
    $booking_stmt->bind_param("i", $bookingId);
    $booking_stmt->execute();
    $booking_result = $booking_stmt->get_result();
    $booking_data = $booking_result->fetch_assoc();
    $booking_stmt->close();

    // Update discount status only
    $stmt = $conn->prepare("UPDATE bookings SET discount_status = ? WHERE id = ?");
    $stmt->bind_param("si", $newDiscountStatus, $bookingId);
    $success = $stmt->execute();
    $stmt->close();

    if ($success && $booking_data) {
        // Extract guest info from details
        $details = $booking_data['details'];
        $guest_email = '';
        $guest_name = 'Guest';
        $discount_type = '';
        
        if (preg_match('/Email:\s*([^|]+)/', $details, $matches)) {
            $guest_email = trim($matches[1]);
        }
        if (preg_match('/Guest:\s*([^|]+)/', $details, $matches)) {
            $guest_name = trim($matches[1]);
        }
        if (preg_match('/Discount:\s*([^|]+)/', $details, $matches)) {
            $discount_type = trim($matches[1]);
        }

        // Send email notification about discount decision
        if (!empty($guest_email) && !empty($discount_type)) {
            error_log("========================================");
            error_log("DISCOUNT UPDATE EMAIL - Booking ID: $bookingId");
            error_log("Action: $discountAction");
            error_log("Discount Type: $discount_type");
            error_log("Guest: $guest_name");
            error_log("Email: $guest_email");
            error_log("========================================");
            
            $emailSubject = '';
            $emailContent = '';
            
            if ($discountAction === 'approve') {
                $emailSubject = 'Discount Application Approved - BarCIE';
                $emailContent = '
                    <div style="text-align: center; margin-bottom: 30px;">
                        <div style="display: inline-block; background-color: #28a745; color: white; padding: 12px 24px; border-radius: 50px; font-size: 14px; font-weight: 600;">
                            ✓ DISCOUNT APPROVED
                        </div>
                    </div>
                    
                    <h2 style="margin: 0 0 20px 0; color: #212529; font-size: 24px; font-weight: 600; text-align: center;">Your Discount Has Been Approved!</h2>
                    
                    <p style="margin: 0 0 25px 0; color: #495057; font-size: 16px; line-height: 1.6; text-align: center;">
                        Dear <strong>' . htmlspecialchars($guest_name) . '</strong>,
                    </p>
                    
                    <p style="margin: 0 0 25px 0; color: #495057; font-size: 15px; line-height: 1.6;">
                        Great news! After reviewing your application, we are pleased to approve your discount request.
                    </p>
                    
                    <!-- Discount Details Card -->
                    <table role="presentation" style="width: 100%; border-collapse: collapse; background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); border-radius: 8px; margin-bottom: 25px; border: 2px solid #28a745;" cellpadding="0" cellspacing="0">
                        <tr>
                            <td style="padding: 25px;">
                                <h3 style="margin: 0 0 15px 0; color: #155724; font-size: 18px; text-align: center;">Approved Discount</h3>
                                <table role="presentation" style="width: 100%; border-collapse: collapse;" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="padding: 12px 0; text-align: center;">
                                            <div style="display: inline-block; background-color: #28a745; color: white; padding: 15px 30px; border-radius: 8px; font-size: 18px; font-weight: 700;">
                                                ' . htmlspecialchars($discount_type) . '
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    
                    <div style="background-color: #d1ecf1; border-left: 4px solid #17a2b8; padding: 15px 20px; margin-bottom: 20px; border-radius: 4px;">
                        <p style="margin: 0; color: #0c5460; font-size: 14px; line-height: 1.6;">
                            <strong>💡 Important:</strong> The discounted rate will be applied to your booking. Please note that your booking itself still requires separate approval if it hasn\'t been approved yet.
                        </p>
                    </div>
                    
                    <p style="margin: 0; color: #495057; font-size: 15px; line-height: 1.6; text-align: center;">
                        Thank you for choosing BarCIE International Center!
                    </p>';
            } else {
                $emailSubject = 'Discount Application Update - BarCIE';
                $emailContent = '
                    <div style="text-align: center; margin-bottom: 30px;">
                        <div style="display: inline-block; background-color: #dc3545; color: white; padding: 12px 24px; border-radius: 50px; font-size: 14px; font-weight: 600;">
                            ✗ DISCOUNT NOT APPROVED
                        </div>
                    </div>
                    
                    <h2 style="margin: 0 0 20px 0; color: #212529; font-size: 24px; font-weight: 600; text-align: center;">Discount Application Update</h2>
                    
                    <p style="margin: 0 0 25px 0; color: #495057; font-size: 16px; line-height: 1.6; text-align: center;">
                        Dear <strong>' . htmlspecialchars($guest_name) . '</strong>,
                    </p>
                    
                    <p style="margin: 0 0 25px 0; color: #495057; font-size: 15px; line-height: 1.6;">
                        Thank you for submitting your discount application. After careful review, we are unable to approve your discount request at this time.
                    </p>
                    
                    <!-- Discount Details Card -->
                    <table role="presentation" style="width: 100%; border-collapse: collapse; background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%); border-radius: 8px; margin-bottom: 25px; border: 2px solid #dc3545;" cellpadding="0" cellspacing="0">
                        <tr>
                            <td style="padding: 25px;">
                                <h3 style="margin: 0 0 15px 0; color: #721c24; font-size: 18px; text-align: center;">Discount Application</h3>
                                <table role="presentation" style="width: 100%; border-collapse: collapse;" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="padding: 12px 0; text-align: center;">
                                            <div style="display: inline-block; background-color: rgba(0,0,0,0.1); color: #721c24; padding: 15px 30px; border-radius: 8px; font-size: 18px; font-weight: 700;">
                                                ' . htmlspecialchars($discount_type) . '
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    
                    <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px 20px; margin-bottom: 20px; border-radius: 4px;">
                        <p style="margin: 0; color: #856404; font-size: 14px; line-height: 1.6;">
                            <strong>💡 Note:</strong> The standard rate will apply to your booking. Your booking can still be approved separately and is not affected by this discount decision.
                        </p>
                    </div>
                    
                    <p style="margin: 0; color: #495057; font-size: 15px; line-height: 1.6;">
                        If you have questions about this decision or would like to discuss alternative options, please feel free to contact us.
                    </p>';
            }
            
            if ($emailSubject && $emailContent) {
                error_log("DISCOUNT UPDATE EMAIL - Sending email...");
                error_log("Subject: $emailSubject");
                $emailBody = create_email_template($emailSubject, $emailContent, 'This is an automated message. Please do not reply directly to this email.');
                $email_sent = send_smtp_mail($guest_email, $emailSubject, $emailBody);
                error_log("DISCOUNT UPDATE EMAIL - Result: " . ($email_sent ? "SUCCESS" : "FAILED"));
            }
        }
    }

    // Check if this is an AJAX request
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
              strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    
    if ($isAjax) {
        header('Content-Type: application/json');
        if ($success) {
            echo json_encode([
                'success' => true, 
                'message' => "Discount " . ($discountAction === 'approve' ? 'approved' : 'rejected') . " successfully.",
                'discount_status' => $newDiscountStatus
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'error' => "Error updating discount status."
            ]);
        }
        exit;
    } else {
        $_SESSION['msg'] = $success ? "Discount " . ($discountAction === 'approve' ? 'approved' : 'rejected') . " successfully." : "Error updating discount.";
        redirect('../dashboard.php');
    }
}

/* ---------------------------
   ADMIN: delete user
   --------------------------- */
if ($action === 'admin_delete_user') {
    if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        // Return JSON for AJAX clients; for normal requests redirect with session message
        handleResponse('Access denied. Admin login required.', false, '../dashboard.php');
    }

    $userId = (int)($_POST['user_id'] ?? 0);

    if (isset($_SESSION['admin_id']) && $userId === (int)$_SESSION['admin_id']) {
        $_SESSION['msg'] = "You cannot delete your own account.";
        redirect('../dashboard.php');
    }

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();
    $_SESSION['msg'] = "User deleted.";
    redirect('../dashboard.php');
}

/* ---------------------------
   CHAT SYSTEM FUNCTIONS
   --------------------------- */

// Initialize chat tables if they don't exist
function initializeChatTables($conn) {
    try {
        // Create chat_messages table
        $sql1 = "CREATE TABLE IF NOT EXISTS chat_messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            sender_id INT NOT NULL,
            sender_type ENUM('admin', 'guest') NOT NULL,
            receiver_id INT NOT NULL,
            receiver_type ENUM('admin', 'guest') NOT NULL,
            message TEXT NOT NULL,
            is_read BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            INDEX idx_sender (sender_id, sender_type),
            INDEX idx_receiver (receiver_id, receiver_type),
            INDEX idx_conversation (sender_id, sender_type, receiver_id, receiver_type),
            INDEX idx_created_at (created_at),
            INDEX idx_unread (is_read, receiver_id, receiver_type)
        )";

        if ($conn->query($sql1) !== TRUE) {
            throw new Exception("Error creating chat_messages table: " . $conn->error);
        }

        // Create chat_conversations table
        $sql2 = "CREATE TABLE IF NOT EXISTS chat_conversations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            admin_id INT NOT NULL,
            guest_id INT NOT NULL,
            last_message_id INT NULL,
            last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            admin_unread_count INT DEFAULT 0,
            guest_unread_count INT DEFAULT 0,
            
            UNIQUE KEY unique_conversation (admin_id, guest_id),
            INDEX idx_last_activity (last_activity),
            INDEX idx_admin_id (admin_id),
            INDEX idx_guest_id (guest_id)
        )";

        if ($conn->query($sql2) !== TRUE) {
            throw new Exception("Error creating chat_conversations table: " . $conn->error);
        }
        
    } catch (Exception $e) {
        throw new Exception("Chat table initialization failed: " . $e->getMessage());
    }
}

// Send chat message
if ($action === 'send_chat_message') {
    header('Content-Type: application/json');
    
    // Temporarily disabled to fix feedback system
    echo json_encode(['success' => false, 'error' => 'Chat system temporarily disabled']);
    exit;
}

/* ---------------------------
   GET BOOKING DETAILS (ADMIN ONLY)
   --------------------------- */
if ($action === 'get_booking_details') {
    header('Content-Type: application/json');
    
    // Admin access check
    if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        echo json_encode(['success' => false, 'error' => 'Admin access required']);
        exit;
    }
    
    $booking_id = (int)($_POST['booking_id'] ?? 0);
    
    if ($booking_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid booking ID']);
        exit;
    }
    
    try {
        $stmt = $conn->prepare("
            SELECT b.*, i.name as room_name, i.item_type, i.room_number, i.capacity, i.price
            FROM bookings b 
            LEFT JOIN items i ON b.room_id = i.id 
            WHERE b.id = ?
        ");
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($booking = $result->fetch_assoc()) {
            echo json_encode([
                'success' => true,
                'booking' => $booking
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Booking not found']);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
    
    exit;
}

// Ensure any remaining requests get a JSON error instead of plain text
if (!headers_sent()) {
    header('Content-Type: application/json');
}
echo json_encode([ 'success' => false, 'error' => 'Invalid request.' ]);
$conn->close();
exit;
?>
