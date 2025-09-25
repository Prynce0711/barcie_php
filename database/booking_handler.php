<?php
// ✅ Connect to database
require __DIR__ . "/db_connect.php";

// ✅ Only handle form submission via POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get booking type from form (pencil or reservation)
    $bookingType = $_POST['bookingType'];

    // ===============================
    // CASE 1: Pencil Booking
    // ===============================
    if ($bookingType === "pencil") {
        // Prepare INSERT query for pencil bookings
        $stmt = $conn->prepare("INSERT INTO bookings 
    (booking_type, event_type, function_hall, num_pax, event_date, time_from, time_to, caterer, contact_person, contact_numbers, company_affiliation, company_contact_number, front_desk_officer) 
    VALUES ('pencil', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "ssisssssssss",
            $_POST['event_type'],
            $_POST['function_hall'],
            $_POST['num_pax'],
            $_POST['event_date'],
            $_POST['time_from'],
            $_POST['time_to'],
            $_POST['caterer'],
            $_POST['contact_person'],
            $_POST['contact_numbers'],   // ✅ fixed to match HTML
            $_POST['company_affiliation'],
            $_POST['company_contact_number'],
            $_POST['front_desk_officer']
        );

        // ===============================
        // CASE 2: Reservation Booking
        // ===============================
    } else {
        // Prepare INSERT query for reservation bookings
        $stmt = $conn->prepare("INSERT INTO bookings 
    (booking_type, guest_name, guest_contact, check_in, check_out, num_pax, company_affiliation, company_contact_number, front_desk_officer, official_receipt, special_request, status) 
    VALUES ('reservation', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')"
        );

        $stmt->bind_param(
            "ssssisssss",
            $_POST['guest_name'],
            $_POST['guest_contact'],
            $_POST['check_in'],
            $_POST['check_out'],
            $_POST['num_occupants'],   // ✅ fixed to match HTML
            $_POST['company_affiliation'],
            $_POST['company_contact_number'],
            $_POST['front_desk_officer'],
            $_POST['official_receipt'],
            $_POST['special_request']
        );
    }

    // ===============================
    // EXECUTE & REDIRECT
    // ===============================
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        header("Location: ../guest.php?success=1");
        exit;
    } else {
        // Log server-side error (check xampp/php/logs/php_error_log)
        error_log("Booking insert error: " . $stmt->error);
        $stmt->close();
        $conn->close();
        header("Location: ../guest.php?error=1");
        exit;
    }
}


// ✅ Close DB connection
$conn->close();
?>