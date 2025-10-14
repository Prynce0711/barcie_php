<?php
// PHPMailer setup using Composer autoloader
function send_smtp_mail($to, $subject, $body, $altBody = '') {
    try {
        // Use Composer autoloader for PHPMailer
        $autoload_path = __DIR__ . '/../vendor/autoload.php';
        
        if (!file_exists($autoload_path)) {
            error_log('Composer autoloader not found. Please run "composer install".');
            return false;
        }
        
        require_once $autoload_path;
        
        $config_path = __DIR__ . '/../mail_config.php';
        if (!file_exists($config_path)) {
            error_log('Mail config file not found. Email functionality disabled.');
            return false;
        }
        
        $config = require $config_path;
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        $mail->SMTPSecure = $config['secure'];
        $mail->Port = $config['port'];
        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addAddress($to);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = $altBody ?: strip_tags($body);
        $mail->isHTML(true);
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('PHPMailer error: ' . $e->getMessage());
        return false;
    }
}
session_start();
include __DIR__ . '/db_connect.php';

// Helper function for redirect
function redirect($url) {
    header("Location: $url");
    exit;
}

// Helper function for AJAX responses
function handleResponse($message, $success = true, $redirectUrl = null) {
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
              strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'redirect' => $redirectUrl
        ]);
        exit;
    } else {
        // Traditional behavior for non-AJAX requests
        if ($success) {
            $_SESSION['booking_msg'] = $message;
        } else {
            $_SESSION['booking_msg'] = $message;
        }
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
   GET: fetch_items (JSON)
   Usage: database/user_auth.php?action=fetch_items
   --------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {

    if ($_GET['action'] === 'fetch_items') {
        header('Content-Type: application/json');
        $sql = "SELECT id, name, item_type, room_number, description, capacity, price, image FROM items ORDER BY created_at DESC";
        $res = $conn->query($sql);
        $items = [];
        while ($r = $res->fetch_assoc()) $items[] = $r;
        echo json_encode($items);
        $conn->close();
        exit;
    }

    if ($_GET['action'] === 'debug_bookings') {
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
                'error' => 'Failed to fetch availability data',
                'message' => $e->getMessage(),
                'debug' => 'Check server logs for details'
            ]);
        }
        
        $conn->close();
        exit;
    }

    if ($_GET['action'] === 'get_receipt_no') {
        header('Content-Type: application/json');
        try {
            // Get the current date for receipt format
            $currentDate = date('Ymd');
            
            // Check if receipt_no column exists and its type
            $checkColumn = $conn->query("SHOW COLUMNS FROM bookings LIKE 'receipt_no'");
            if ($checkColumn->num_rows == 0) {
                // Add receipt_no column as VARCHAR
                $conn->query("ALTER TABLE bookings ADD COLUMN receipt_no VARCHAR(50) NULL AFTER id");
            } else {
                // Check if it's the wrong type and fix it
                $columnInfo = $checkColumn->fetch_assoc();
                if (strpos(strtolower($columnInfo['Type']), 'int') !== false) {
                    // Drop and recreate as VARCHAR
                    $conn->query("ALTER TABLE bookings DROP COLUMN receipt_no");
                    $conn->query("ALTER TABLE bookings ADD COLUMN receipt_no VARCHAR(50) NULL AFTER id");
                }
            }
            
            // Get the highest receipt number for today from receipt_no column
            $stmt = $conn->prepare("SELECT receipt_no FROM bookings WHERE receipt_no LIKE ? ORDER BY receipt_no DESC LIMIT 1");
            $datePattern = "BARCIE-{$currentDate}-%";
            $stmt->bind_param("s", $datePattern);
            $stmt->execute();
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
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        $conn->close();
        exit;
    }

    if ($_GET['action'] === 'get_available_count') {
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

        // Add discount info to details
        $discount_info = '';
        if (!empty($discount_type)) {
            $discount_info = " | Discount: $discount_type | Discount Details: $discount_details | Proof: $discount_proof_path";
        }

        $details = "Receipt: $receipt_no | " . ucfirst($room_data['item_type']) . ": " . $room_data['name'] . " | Guest: $guest_name | Contact: $contact | Check-in: $checkin | Check-out: $checkout | Occupants: $occupants | Company: $company" . $discount_info;

        // Try to insert with room_id and receipt_no columns
        try {
            // Debug: Log what we're trying to insert
            error_log("Booking Debug - Type: $type, Room ID: $room_id, Receipt: $receipt_no");
            error_log("Booking Debug - Details: " . substr($details, 0, 100));
            error_log("Booking Debug - Status: $status, Checkin: $checkin, Checkout: $checkout");

            $stmt = $conn->prepare("INSERT INTO bookings (type, room_id, receipt_no, details, status, checkin, checkout) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }

            $stmt->bind_param("sisssss", $type, $room_id, $receipt_no, $details, $status, $checkin, $checkout);
            $success = $stmt->execute();

            if (!$success) {
                throw new Exception("Execute failed: " . $stmt->error);
            }

            if ($success) {
                // Update room status to reserved
                $update_status = $conn->prepare("UPDATE items SET room_status = 'reserved' WHERE id = ?");
                $update_status->bind_param("i", $room_id);
                $update_status->execute();
                $update_status->close();

                // Send email to admin if discount was applied
                if (!empty($discount_type)) {
                    $admin_email = 'pc.clemente11@gmail.com'; // Change to your admin email
                    $subject = "New Discount Application - $discount_type";
                    $message = "A guest has applied for a discount.<br><br>" .
                        "<b>Guest:</b> $guest_name<br>" .
                        "<b>Email:</b> $email<br>" .
                        "<b>Contact:</b> $contact<br>" .
                        "<b>Room/Facility:</b> " . $room_data['name'] . "<br>" .
                        "<b>Check-in:</b> $checkin<br>" .
                        "<b>Check-out:</b> $checkout<br>" .
                        "<b>Discount Type:</b> $discount_type<br>" .
                        "<b>Discount Details:</b> $discount_details<br>" .
                        (!empty($discount_proof_path) ? ("<b>Proof:</b> <a href='" . $discount_proof_path . "'>View Proof</a><br>") : "") .
                        "<br>Please review and approve/reject in the admin portal.";
                    send_smtp_mail($admin_email, $subject, $message);

                    // Send confirmation email to guest
                    if (!empty($email)) {
                        $guest_subject = 'Discount Application Received';
                        $guest_message = "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; border: 1px solid #eee; border-radius: 8px; padding: 24px; background: #fafbfc;'>"
                            . "<h2 style='color: #2d7be5;'>BarCIE International Center</h2>"
                            . "<p>Dear <b>" . htmlspecialchars($guest_name) . "</b>,</p>"
                            . "<p>We have <b>received your discount application</b> for your reservation request at BarCIE International Center.</p>"
                            . "<ul style='background: #f6f8fa; border-radius: 6px; padding: 16px; list-style: none;'>"
                            . "<li><b>Room/Facility:</b> " . htmlspecialchars($room_data['name']) . "</li>"
                            . "<li><b>Check-in:</b> " . htmlspecialchars($checkin) . "</li>"
                            . "<li><b>Check-out:</b> " . htmlspecialchars($checkout) . "</li>"
                            . "<li><b>Discount Type:</b> " . htmlspecialchars($discount_type) . "</li>"
                            . "</ul>"
                            . "<p style='margin-top: 18px;'>Our team will review your application and notify you by email once your discount is <b>approved or rejected</b>.</p>"
                            . "<p style='color: #888;'>If you have questions, please reply to this email or contact us at info@barcie.com.</p>"
                            . "<p style='margin-top: 32px; color: #2d7be5;'><b>Thank you for choosing BarCIE International Center!</b></p>"
                            . "</div>";
                        send_smtp_mail($email, $guest_subject, $guest_message);
                    }
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
   ADMIN: update booking
   --------------------------- */
if ($action === 'admin_update_booking') {
    if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        die("Access denied. Admin login required.");
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

    if ($success && $booking_data && $booking_data['room_id']) {
    // Notify guest about discount status if discount was applied
    if ($success && $booking_data && isset($booking_data['details'])) {
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
        if (preg_match('/Discount: ([^|]+)/', $details, $matches)) {
            $discount_type = trim($matches[1]);
        }
        if ($guest_email && $discount_type) {
            $subject = '';
            $message = '';
            if ($adminAction === 'approve') {
                $subject = 'Discount Application Approved';
                $message = "Dear $guest_name,<br><br>Your discount application ($discount_type) has been <b>approved</b>.<br>You may now continue your reservation with the discounted price.<br><br>Thank you for choosing BarCIE International Center.";
            } elseif ($adminAction === 'reject') {
                $subject = 'Discount Application Rejected';
                $message = "Dear $guest_name,<br><br>We regret to inform you that your discount application ($discount_type) was <b>not approved</b>.<br>The original price per day will apply to your reservation.<br><br>Thank you for your understanding.";
            }
            if ($subject && $message) {
                send_smtp_mail($guest_email, $subject, $message);
            }
        }
    }
        // Update room status based on booking status
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
   ADMIN: delete user
   --------------------------- */
if ($action === 'admin_delete_user') {
    if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        die("Access denied. Admin login required.");
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

$conn->close();
die("Invalid request.");
?>
