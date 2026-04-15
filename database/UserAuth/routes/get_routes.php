<?php
/* ---------------------------
    POST/REQUEST actions
    Accept action from either POST (preferred for form submissions) or GET (for convenience).
    Using $_REQUEST allows fetch() clients that send 'action' in the URL query or request body to be accepted.
*/
$action = $_REQUEST['action'] ?? '';

// Dedicated GET handlers (each exits when matched).
require_once __DIR__ . '/../handlers/get/get_room_reviews.php';

function feedbackColumnExists(mysqli $conn, string $column): bool
{
    $columnEscaped = $conn->real_escape_string($column);
    $result = $conn->query("SHOW COLUMNS FROM feedback LIKE '{$columnEscaped}'");
    return $result && $result->num_rows > 0;
}

function feedbackIndexExists(mysqli $conn, string $indexName): bool
{
    $indexEscaped = $conn->real_escape_string($indexName);
    $result = $conn->query("SHOW INDEX FROM feedback WHERE Key_name = '{$indexEscaped}'");
    return $result && $result->num_rows > 0;
}

function ensureFeedbackTableSchema(mysqli $conn): void
{
    $conn->query("CREATE TABLE IF NOT EXISTS feedback (
        id INT AUTO_INCREMENT PRIMARY KEY,
        room_id INT NULL,
        rating INT NOT NULL DEFAULT 5,
        message TEXT,
        feedback_name VARCHAR(255) NULL,
        feedback_email VARCHAR(255) NULL,
        is_anonymous TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    $columnsToAdd = [
        'room_id' => 'INT NULL AFTER id',
        'rating' => 'INT NOT NULL DEFAULT 5',
        'message' => 'TEXT NULL',
        'feedback_name' => 'VARCHAR(255) NULL',
        'feedback_email' => 'VARCHAR(255) NULL',
        'is_anonymous' => 'TINYINT(1) NOT NULL DEFAULT 0',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
    ];

    foreach ($columnsToAdd as $column => $definition) {
        if (!feedbackColumnExists($conn, $column)) {
            $conn->query("ALTER TABLE feedback ADD COLUMN {$column} {$definition}");
        }
    }

    if (feedbackColumnExists($conn, 'user_id')) {
        $conn->query('ALTER TABLE feedback MODIFY COLUMN user_id INT NULL');
    }

    $indexesToAdd = [
        'idx_room_id' => 'room_id',
        'idx_rating' => 'rating',
        'idx_created_at' => 'created_at'
    ];

    foreach ($indexesToAdd as $indexName => $column) {
        if (!feedbackIndexExists($conn, $indexName)) {
            $conn->query("CREATE INDEX {$indexName} ON feedback ({$column})");
        }
    }
}

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

            $sql = "SELECT id, name, item_type, room_number, description, capacity, price, image, images, room_status FROM items ORDER BY created_at DESC";
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
                'available_count' => (int) $availableCount,
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

    // Get chat conversations tables
    if ($_GET['action'] === 'init_feedback_table') {
        header('Content-Type: application/json');

        try {
            ensureFeedbackTableSchema($conn);

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
            ensureFeedbackTableSchema($conn);

            $limit = (int) ($_GET['limit'] ?? 50);
            $offset = (int) ($_GET['offset'] ?? 0);

            // Get feedback with room details
            $stmt = $conn->prepare("SELECT f.id, f.room_id, f.rating, f.message, f.created_at, 
                                   f.feedback_name, f.is_anonymous,
                                   i.name as room_name, i.item_type as room_type
                                   FROM feedback f 
                                   LEFT JOIN items i ON f.room_id = i.id
                                   ORDER BY f.created_at DESC 
                                   LIMIT ? OFFSET ?");

            if (!$stmt) {
                throw new Exception('Failed to prepare feedback query: ' . $conn->error);
            }

            $stmt->bind_param("ii", $limit, $offset);
            $stmt->execute();
            $result = $stmt->get_result();

            $feedback = [];
            while ($row = $result->fetch_assoc()) {
                // Display name based on anonymous flag
                // If anonymous and has feedback_name (e.g., "Ba**"), use it; otherwise show "Anonymous"
                if ($row['is_anonymous']) {
                    $row['username'] = !empty($row['feedback_name']) ? $row['feedback_name'] : 'Anonymous';
                } else {
                    $row['username'] = !empty($row['feedback_name']) ? $row['feedback_name'] : 'Guest';
                }
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


