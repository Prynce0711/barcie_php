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
        // Generate next reservation receipt number
        $result = $conn->query("SELECT MAX(id) AS max_id FROM bookings WHERE type='reservation'");
        $row = $result->fetch_assoc();
        $nextId = ($row['max_id'] ?? 0) + 1;
        $receipt_no = str_pad($nextId, 4, "0", STR_PAD_LEFT);
        header('Content-Type: application/json');
        echo json_encode(['receipt_no' => $receipt_no]);
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
        // Generate receipt no
        $result = $conn->query("SELECT MAX(id) AS max_id FROM bookings WHERE type='reservation'");
        $row = $result->fetch_assoc();
        $receipt_no = str_pad((($row['max_id'] ?? 0) + 1), 4, "0", STR_PAD_LEFT);

        $guest_name = $conn->real_escape_string($_POST['guest_name'] ?? '');
        $contact = $conn->real_escape_string($_POST['contact_number'] ?? '');
        $email = $conn->real_escape_string($_POST['email'] ?? '');
        $checkin = $_POST['checkin'] ?? null;
        $checkout = $_POST['checkout'] ?? null;
        $occupants = (int)($_POST['occupants'] ?? 1);
        $company = $conn->real_escape_string($_POST['company'] ?? '');

        $details = "Receipt: $receipt_no | Guest: $guest_name | Contact: $contact | Check-in: $checkin | Check-out: $checkout | Occupants: $occupants | Company: $company";

        $stmt = $conn->prepare("INSERT INTO bookings (user_id, type, details, status, checkin, checkout) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $user_id, $type, $details, $status, $checkin, $checkout);
        $_SESSION['booking_msg'] = $stmt->execute() ? "Reservation saved." : "Error: " . $stmt->error;
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
