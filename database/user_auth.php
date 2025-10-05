<?php
session_start();
include __DIR__ . '/db_connect.php';

// Helper function for redirect
function redirect($url) {
    header("Location: $url");
    exit;
}

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
}

/* ---------------------------
   POST actions
   --------------------------- */
$action = $_POST['action'] ?? '';

/* ---------------------------
   SIGNUP
   --------------------------- */
if ($action === 'signup') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($username === '' || $email === '' || $password === '') {
        $_SESSION['signup_error'] = "Please fill required fields.";
        redirect('../index.php');
    }
    if ($password !== $confirm) {
        $_SESSION['signup_error'] = "Passwords do not match.";
        redirect('../index.php');
    }

    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $_SESSION['signup_error'] = "Username or email already exists.";
        $stmt->close();
        redirect('../index.php');
    }
    $stmt->close();

    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $hashed);

    if ($stmt->execute()) {
        $newUserId = $stmt->insert_id;
        $_SESSION['user_id'] = $newUserId;
        $_SESSION['username'] = $username;
        $_SESSION['is_admin'] = false;
        $_SESSION['signup_success'] = "Signup successful. You are now logged in.";
        redirect('../Guest.php');
    } else {
        $_SESSION['signup_error'] = "Error creating account.";
        redirect('../index.php');
    }
}

/* ---------------------------
   LOGIN
   --------------------------- */

$action = $_POST['action'] ?? '';

if ($action === 'login') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $_SESSION['login_error'] = "Fill both username and password.";
        header('Location: ../index.php'); // optional if you want to reload
        exit;
    }

    // Check users table
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();
    $stmt->close();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['username'] = $user['username'];

        // Check admin status
        $stmt = $conn->prepare("SELECT id FROM admins WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        $_SESSION['is_admin'] = ($stmt->num_rows > 0);
        $stmt->close();

        echo json_encode(['success' => true]); // return JSON success
        exit;
    }

    $_SESSION['login_error'] = "Invalid username or password.";
    echo json_encode(['success' => false, 'error' => $_SESSION['login_error']]);
    exit;
}


/* ---------------------------
   LOGOUT
   --------------------------- */
if ($action === 'logout') {
    session_unset();
    session_destroy();
    redirect('../index.php');
}

/* ---------------------------
   UPDATE PROFILE
   --------------------------- */
if ($action === 'update_profile') {
    if (!isset($_SESSION['user_id'])) die("You must be logged in.");
    $user_id = (int)$_SESSION['user_id'];
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $email === '') {
        $_SESSION['profile_error'] = "Username and email cannot be empty.";
        redirect('../Guest.php');
    }

    $stmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ? LIMIT 1");
    $stmt->bind_param("ssi", $username, $email, $user_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $_SESSION['profile_error'] = "Username or email already used by another account.";
        $stmt->close();
        redirect('../Guest.php');
    }
    $stmt->close();

    if (!empty($password)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET username=?, email=?, password=? WHERE id=?");
        $stmt->bind_param("sssi", $username, $email, $hashed, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET username=?, email=? WHERE id=?");
        $stmt->bind_param("ssi", $username, $email, $user_id);
    }

    if ($stmt->execute()) {
        $_SESSION['username'] = $username;
        $_SESSION['profile_success'] = "Profile updated.";
    } else {
        $_SESSION['profile_error'] = "Error updating profile.";
    }
    $stmt->close();
    redirect('../Guest.php');
}

/* ---------------------------
   CREATE BOOKING
   --------------------------- */
if ($action === 'create_booking') {
    if (!isset($_SESSION['user_id'])) die("You must be logged in to book.");
    $user_id = (int)$_SESSION['user_id'];
    $type = $_POST['booking_type'] ?? '';
    $status = "pending";

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

        $details = "Receipt: $receipt_no | Guest: $guest_name | Contact: $contact | Check-in: $checkin | Check-out: $checkout | Occupants: $occupants | Company: $company";

        // Try to insert with receipt_no column, fallback if column doesn't exist
        try {
            $stmt = $conn->prepare("INSERT INTO bookings (user_id, type, receipt_no, details, status, checkin, checkout) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issssss", $user_id, $type, $receipt_no, $details, $status, $checkin, $checkout);
            $success = $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            // If receipt_no column doesn't exist or is wrong type, try to fix it
            if (strpos($e->getMessage(), 'receipt_no') !== false) {
                try {
                    // Try to add/fix the column
                    $conn->query("ALTER TABLE bookings DROP COLUMN IF EXISTS receipt_no");
                    $conn->query("ALTER TABLE bookings ADD COLUMN receipt_no VARCHAR(50) NULL AFTER id");
                    
                    // Retry the insert
                    $stmt = $conn->prepare("INSERT INTO bookings (user_id, type, receipt_no, details, status, checkin, checkout) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("issssss", $user_id, $type, $receipt_no, $details, $status, $checkin, $checkout);
                    $success = $stmt->execute();
                } catch (Exception $e2) {
                    // Fallback to original format without receipt_no column
                    $stmt = $conn->prepare("INSERT INTO bookings (user_id, type, details, status, checkin, checkout) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("isssss", $user_id, $type, $details, $status, $checkin, $checkout);
                    $success = $stmt->execute();
                }
            } else {
                throw $e;
            }
        }
        
        $_SESSION['booking_msg'] = $success ? "Reservation saved with receipt number: $receipt_no" : "Error: " . $stmt->error;
        $stmt->close();
    } elseif ($type === 'pencil') {
        $pencil_date = $_POST['pencil_date'] ?? null;
        $event = $conn->real_escape_string($_POST['event_type'] ?? '');
        $hall = $conn->real_escape_string($_POST['hall'] ?? '');
        $pax = (int)($_POST['pax'] ?? 1);
        $time_from = $_POST['time_from'] ?? '';
        $time_to = $_POST['time_to'] ?? '';
        $caterer = $conn->real_escape_string($_POST['caterer'] ?? '');
        $contact_person = $conn->real_escape_string($_POST['contact_person'] ?? '');
        $contact_number = $conn->real_escape_string($_POST['contact_number'] ?? '');
        $company = $conn->real_escape_string($_POST['company'] ?? '');

        $details = "Pencil Booking | Date: $pencil_date | Event: $event | Hall: $hall | Pax: $pax | Time: $time_from-$time_to | Caterer: $caterer | Contact: $contact_person ($contact_number) | Company: $company";

        $stmt = $conn->prepare("INSERT INTO bookings (user_id, type, details, status, checkin) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $user_id, $type, $details, $status, $pencil_date);
        $_SESSION['booking_msg'] = $stmt->execute() ? "Pencil booking saved." : "Error: " . $stmt->error;
        $stmt->close();
    } else {
        $_SESSION['booking_msg'] = "Unknown booking type.";
    }

    redirect('../Guest.php');
}

/* ---------------------------
   SUBMIT FEEDBACK
   --------------------------- */
if ($action === 'submit_feedback') {
    if (!isset($_SESSION['user_id'])) die("You must be logged in to submit feedback.");
    $user_id = (int)$_SESSION['user_id'];
    $message = trim($_POST['message'] ?? '');
    if ($message === '') {
        $_SESSION['feedback_error'] = "Feedback cannot be empty.";
        redirect('../Guest.php#feedback');
    }
    $stmt = $conn->prepare("INSERT INTO feedback (user_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $message);
    $_SESSION['feedback_success'] = $stmt->execute() ? "Feedback submitted. Thank you." : "Error: " . $stmt->error;
    $stmt->close();
    redirect('../Guest.php#feedback');
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

    $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $newStatus = $statusMap[$adminAction];
    $stmt->bind_param("si", $newStatus, $bookingId);
    $_SESSION['msg'] = $stmt->execute() ? "Booking #$bookingId updated." : "Error updating booking.";
    $stmt->close();
    redirect('../dashboard.php');
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


$conn->close();
die("Invalid request.");
?>
