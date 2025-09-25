<?php
// ✅ Correct relative path for db_connect.php
include __DIR__ . '/db_connect.php';

// Auto-generate receipt number for reservations
function generateReceipt($conn) {
  $result = $conn->query("SELECT MAX(receipt_no) AS max_no FROM bookings WHERE type='reservation'");
  $row = $result->fetch_assoc();
  $next = ($row['max_no'] ?? 0) + 1;
  return str_pad($next, 4, "0", STR_PAD_LEFT);
}

$type = $_POST['type'] ?? '';
$status = "Pending";
$details = "";

if ($type == "reservation") {
    $receipt = generateReceipt($conn);
    $guest_name = $_POST['guest_name'];
    $contact = $_POST['contact_number'];
    $email = $_POST['email'];
    $checkin = $_POST['checkin'];
    $checkout = $_POST['checkout'];
    $occupants = $_POST['occupants'];
    $company = $_POST['company'];
    $company_contact = $_POST['company_contact'];

    $details = "Receipt: $receipt | Guest: $guest_name | Check-in: $checkin | Check-out: $checkout | Occupants: $occupants";
    $sql = "INSERT INTO bookings (type, receipt_no, details, status) 
            VALUES ('reservation', '$receipt', '$details', '$status')";

} elseif ($type == "pencil") {
    $date = $_POST['pencil_date'];
    $event = $_POST['event_type'];
    $hall = $_POST['hall'];
    $pax = $_POST['pax'];
    $time_from = $_POST['time_from'];
    $time_to = $_POST['time_to'];
    $caterer = $_POST['caterer'];
    $contact_person = $_POST['contact_person'];
    $contact_number = $_POST['contact_number'];
    $company = $_POST['company'];
    $company_number = $_POST['company_number'];

    $details = "Pencil Booking | Date: $date | Event: $event | Hall: $hall | Pax: $pax | Time: $time_from - $time_to | Contact: $contact_person ($contact_number)";
    $sql = "INSERT INTO bookings (type, details, status) 
            VALUES ('pencil', '$details', '$status')";
}

if (isset($sql) && $conn->query($sql)) {
    // ✅ Correct path to Guest.php (go up one folder from /database/)
    echo "<script>alert('Booking saved successfully!'); window.location='../Guest.php#reports';</script>";
} else {
    echo "Error: " . $conn->error;
}
?>
