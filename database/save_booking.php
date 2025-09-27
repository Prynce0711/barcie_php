<?php
session_start();
include __DIR__ . '/db_connect.php';

$response = ['status' => 'error', 'message' => 'Invalid request'];

// ----------------------------
// 1. GUEST: Create booking
// ----------------------------
if (isset($_POST['type']) && !isset($_POST['action'])) {
    $type = $_POST['type'];
    $status = "Pending";

    if ($type === "reservation") {
        // Auto-generate receipt number
        $result = $conn->query("SELECT MAX(id) AS max_id FROM bookings WHERE type='reservation'");
        $row = $result->fetch_assoc();
        $receipt = str_pad(($row['max_id'] ?? 0) + 1, 4, "0", STR_PAD_LEFT);

        $guest_name = $_POST['guest_name'];
        $contact = $_POST['contact_number'];
        $email = $_POST['email'];
        $checkin = $_POST['checkin'];
        $checkout = $_POST['checkout'];
        $occupants = $_POST['occupants'];
        $company = $_POST['company'] ?? '';
        $company_contact = $_POST['company_contact'] ?? '';

        $details = "Receipt: $receipt | Guest: $guest_name | Contact: $contact | Check-in: $checkin | Check-out: $checkout | Occupants: $occupants | Company: $company";

        $stmt = $conn->prepare("INSERT INTO bookings (type, details, status, checkin, checkout) VALUES (?,?,?,?,?)");
        $stmt->bind_param("sssss", $type, $details, $status, $checkin, $checkout);

        if ($stmt->execute()) {
            $response = ['status' => 'success', 'message' => 'Reservation saved successfully!'];
        } else {
            $response['message'] = $stmt->error;
        }
        $stmt->close();
    }

    elseif ($type === "pencil") {
        $pencil_date = $_POST['pencil_date'];
        $event = $_POST['event_type'];
        $hall = $_POST['hall'];
        $pax = $_POST['pax'];
        $time_from = $_POST['time_from'];
        $time_to = $_POST['time_to'];
        $caterer = $_POST['caterer'];
        $contact_person = $_POST['contact_person'];
        $contact_number = $_POST['contact_number'];
        $company = $_POST['company'] ?? '';
        $company_number = $_POST['company_number'] ?? '';

        $details = "Pencil Booking | Date: $pencil_date | Event: $event | Hall: $hall | Pax: $pax | Time: $time_from-$time_to | Caterer: $caterer | Contact: $contact_person ($contact_number) | Company: $company";

        $stmt = $conn->prepare("INSERT INTO bookings (type, details, status, checkin) VALUES (?,?,?,?)");
        $stmt->bind_param("ssss", $type, $details, $status, $pencil_date);

        if ($stmt->execute()) {
            $response = ['status' => 'success', 'message' => 'Pencil booking saved successfully!'];
        } else {
            $response['message'] = $stmt->error;
        }
        $stmt->close();
    }

    $_SESSION['booking_msg'] = $response['message'];
    header("Location: ../guest.php");
    exit;
}

// ----------------------------
// 2. ADMIN: Update booking
// ----------------------------
    // ---- UPDATE BOOKING STATUS ----
if (isset($_POST['booking_id']) && isset($_POST['action'])) {
    $bookingId = intval($_POST['booking_id']);
    $action = $_POST['action'];

    // Map actions to statuses
    $statusMap = [
        "approve" => "confirmed",
        "reject"  => "rejected",
        "checkin" => "checked_in",
        "checkout"=> "checked_out",
        "cancel"  => "cancelled"
    ];

    if (array_key_exists($action, $statusMap)) {
        $newStatus = $statusMap[$action];
        $stmt = $conn->prepare("UPDATE bookings SET status=? WHERE id=?");
        $stmt->bind_param("si", $newStatus, $bookingId);

        if ($stmt->execute()) {
            $_SESSION['msg'] = "Booking #$bookingId updated to $newStatus!";
        } else {
            $_SESSION['msg'] = "Error updating booking!";
        }
        $stmt->close();
    }

    header("Location: ../dashboard.php");
    exit;
}


$conn->close();
?>
