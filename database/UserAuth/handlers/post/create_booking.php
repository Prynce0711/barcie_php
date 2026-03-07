<?php
/* ---------------------------
   CREATE BOOKING
   --------------------------- */
if ($action === 'create_booking') {

    // No user_id needed for guest bookings
    $type = $_POST['booking_type'] ?? '';
    $status = "pending";
    $room_id = (int) ($_POST['room_id'] ?? 0);

    // Discount application fields
    $discount_type = $_POST['discount_type'] ?? '';
    $discount_details = $_POST['discount_details'] ?? '';
    $discount_proof_path = '';

    // Payment proof (e.g., bank transfer receipt) path
    $payment_proof_path = '';

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


    // Handle file upload for payment proof (bank transfer receipts)
    if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_tmp = $_FILES['payment_proof']['tmp_name'];
        $file_ext = pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION);
        $file_name = time() . '_pay_' . uniqid() . '.' . $file_ext;
        $target_path = $upload_dir . $file_name;
        if (move_uploaded_file($file_tmp, $target_path)) {
            $payment_proof_path = 'uploads/' . $file_name;
            error_log("Payment proof uploaded to: " . $payment_proof_path);
        }
    }

    // Handle file upload for ID upload (separate from discount proof)
    $id_upload_path = '';
    if (isset($_FILES['id_upload']) && $_FILES['id_upload']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_tmp = $_FILES['id_upload']['tmp_name'];
        $file_ext = pathinfo($_FILES['id_upload']['name'], PATHINFO_EXTENSION);
        $file_name = time() . '_id_' . uniqid() . '.' . $file_ext;
        $target_path = $upload_dir . $file_name;
        if (move_uploaded_file($file_tmp, $target_path)) {
            $id_upload_path = 'uploads/' . $file_name;
            error_log("ID upload saved to: " . $id_upload_path);
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

    // Note: Room status check removed - availability is determined by date conflicts only
    // This allows the same room to be booked for different dates/times

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
        error_log("DEBUG: Email value from form: '" . $email . "'");
        $checkin = $_POST['checkin'] ?? null;
        $checkout = $_POST['checkout'] ?? null;
        $occupants = (int) ($_POST['occupants'] ?? 1);
        $guest_age = (int) ($_POST['age'] ?? 0);
        $company = $conn->real_escape_string($_POST['company'] ?? '');

        // Get add-ons data if provided
        $add_ons_json = $_POST['add_ons'] ?? '';

        // Calculate amount based on room price and duration
        $checkin_date = new DateTime($checkin);
        $checkout_date = new DateTime($checkout);
        $duration = $checkin_date->diff($checkout_date);
        $nights = $duration->days;
        $amount = $room_data['price'] * $nights;

        // Check if this is a conversion from pencil booking
        $converted_from_pencil_id = isset($_POST['converted_from_pencil_id']) ? (int) $_POST['converted_from_pencil_id'] : 0;

        // Validate dates
        if (empty($checkin) || empty($checkout)) {
            handleResponse("Please provide check-in and check-out dates.", false, '../Guest.php');
        }

        if (strtotime($checkin) >= strtotime($checkout)) {
            handleResponse("Check-out date must be after check-in date.", false, '../Guest.php');
        }

        // Check for double booking - only conflicts if date ranges actually overlap (not just touching)
        // A booking conflicts if: new_checkin < existing_checkout AND new_checkout > existing_checkin
        $conflict_stmt = $conn->prepare("SELECT id FROM bookings WHERE room_id = ? AND status IN ('confirmed', 'approved', 'pending', 'checked_in') AND checkin < ? AND checkout > ?");
        $conflict_stmt->bind_param("iss", $room_id, $checkout, $checkin);
        $conflict_stmt->execute();
        $conflict_result = $conflict_stmt->get_result();

        if ($conflict_result->num_rows > 0) {
            $conflict_stmt->close();
            handleResponse("Sorry, the selected " . $room_data['item_type'] . " is already booked for the requested dates.", false, '../Guest.php');
        }
        $conflict_stmt->close();

        // If this reservation is being created as a conversion from a pencil booking,
        // ensure there are no other pencil bookings (different id) that overlap the same range.
        if ($converted_from_pencil_id > 0) {
            try {
                $pencil_conflict = $conn->prepare("SELECT id FROM pencil_bookings WHERE room_id = ? AND id != ? AND status IN ('pending','confirmed','approved') AND checkin < ? AND checkout > ?");
                $pencil_conflict->bind_param("iiss", $room_id, $converted_from_pencil_id, $checkout, $checkin);
                $pencil_conflict->execute();
                $pencil_conflict_result = $pencil_conflict->get_result();
                if ($pencil_conflict_result && $pencil_conflict_result->num_rows > 0) {
                    $pencil_conflict->close();
                    handleResponse("Cannot convert pencil booking: another draft or confirmed pencil booking overlaps the requested dates.", false, '../Guest.php');
                }
                $pencil_conflict->close();
            } catch (Exception $e) {
                error_log("Pencil conflict check failed: " . $e->getMessage());
            }
        }

        // Validate occupancy with detailed error message
        if ($occupants > $room_data['capacity']) {
            $error_msg = "⚠️ CAPACITY EXCEEDED: The number of guests (" . $occupants . ") exceeds the maximum allowed capacity for " . $room_data['name'] . ". Maximum capacity: " . $room_data['capacity'] . " persons. Please select a larger room or reduce the number of occupants.";
            handleResponse($error_msg, false, '../Guest.php');
        }

        // Add discount info to details and set discount_status
        // AUTO-APPROVE: When discount proof is uploaded, automatically approve the discount
        $discount_info = '';
        $discount_status = 'none';
        $discount_amount = 0;
        $discount_percentage = 0;

        // Use id_upload_path for proof_of_id column, fallback to discount proof if ID not uploaded
        $proof_of_id = !empty($id_upload_path) ? $id_upload_path : (!empty($discount_proof_path) ? $discount_proof_path : null);

        if (!empty($discount_type) && !empty($proof_of_id)) {
            // Automatically approve discount when proof is uploaded
            $discount_status = 'approved';

            // Calculate discount percentage based on discount type
            if ($discount_type === 'pwd_senior') {
                $discount_percentage = 20; // 20% for PWD/Senior
            } elseif ($discount_type === 'lcuppersonnel') {
                $discount_percentage = 10; // 10% for LCUP Personnel
            } elseif ($discount_type === 'lcupstudent') {
                $discount_percentage = 7; // 7% for LCUP Student/Alumni
            }

            // Calculate discount amount
            $discount_amount = ($amount * $discount_percentage) / 100;
            $amount = $amount - $discount_amount; // Apply discount to total amount

            $discount_info = " | Discount: $discount_type ($discount_percentage%) | Discount Amount: ₱" . number_format($discount_amount, 2) . " | Discount Details: $discount_details | Proof: $discount_proof_path";

            error_log("Auto-approved discount: Type=$discount_type, Percentage=$discount_percentage%, Amount=₱$discount_amount, New Total=₱$amount");
        } elseif (!empty($discount_type) && empty($proof_of_id)) {
            // If discount type is selected but no proof uploaded, don't apply discount
            $discount_status = 'none';
            error_log("Discount not applied: No proof uploaded for discount type $discount_type");
        }

        $details = "Receipt: $receipt_no | " . ucfirst($room_data['item_type']) . ": " . $room_data['name'] . " | Guest: $guest_name | Email: $email | Contact: $contact | Check-in: $checkin | Check-out: $checkout | Occupants: $occupants | Company: $company" . $discount_info;

        // Try to insert with room_id and receipt_no columns
        try {
            // Debug: Log what we're trying to insert
            error_log("Booking Debug - Type: $type, Room ID: $room_id, Receipt: $receipt_no");
            error_log("Booking Debug - Details: " . substr($details, 0, 100));
            error_log("Booking Debug - Status: $status, Checkin: $checkin, Checkout: $checkout");

            // Include proof_of_id and proof_of_payment columns so uploaded proof paths are stored separately
            // Set initial payment_status as 'pending' so booking goes to payment verification first
            $payment_status = 'pending';
            $stmt = $conn->prepare("INSERT INTO bookings (type, room_id, receipt_no, details, status, discount_status, proof_of_id, proof_of_payment, payment_status, guest_age, amount, add_ons, checkin, checkout) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("sississssidsss", $type, $room_id, $receipt_no, $details, $status, $discount_status, $proof_of_id, $payment_proof_path, $payment_status, $guest_age, $amount, $add_ons_json, $checkin, $checkout);
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
                // DO NOT update room status here - room is only marked as taken when payment is verified
                // This allows the room to show as available until admin approves payment

                // Always send confirmation email to guest
                if (!empty($email)) {
                    error_log("========================================");
                    error_log("BOOKING EMAIL - Starting email send process");
                    error_log("Recipient: " . $email);
                    error_log("Guest: " . $guest_name);
                    error_log("Receipt: " . $receipt_no);
                    error_log("========================================");

                    $subject = "Booking Confirmation - BarCIE International Center";

                    // Calculate stay duration
                    $checkin_date = new DateTime($checkin);
                    $checkout_date = new DateTime($checkout);
                    $duration = $checkin_date->diff($checkout_date);
                    $nights = $duration->days;

                    // Create professional email content
                    $emailContent = '
                        <div style="text-align: center; margin-bottom: 30px;">
                            <div style="display: inline-block; padding: 12px 28px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 50px; font-size: 15px; font-weight: 700; box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);">
                                &#128205; RESERVATION RECEIVED
                            </div>
                        </div>
                        
                        <h2 style="margin: 0 0 15px 0; color: #212529; font-size: 26px; font-weight: 700; text-align: center;">Booking Confirmation</h2>
                        <p style="margin: 0 0 25px 0; color: #6c757d; font-size: 14px; text-align: center;">
                            Receipt #<strong style="color: #2a5298;">' . htmlspecialchars($receipt_no) . '</strong>
                        </p>
                        
                        <p style="margin: 0 0 20px 0; color: #495057; font-size: 16px; line-height: 1.6;">
                            Dear <strong style="color: #1e3c72;">' . htmlspecialchars($guest_name) . '</strong>,
                        </p>
                        <p style="margin: 0 0 30px 0; color: #495057; font-size: 15px; line-height: 1.7;">
                            Thank you for choosing BarCIE International Center! We have successfully received your reservation request. Our admin team will now verify your payment and approve your booking shortly.
                        </p>
                        
                        <!-- Booking Details Card -->
                        <table role="presentation" style="width: 100%; border-collapse: collapse; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 10px; margin-bottom: 25px; border: 2px solid #dee2e6;" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="padding: 28px;">
                                    <h3 style="margin: 0 0 20px 0; color: #212529; font-size: 18px; font-weight: 700; border-bottom: 2px solid #2a5298; padding-bottom: 10px;">
                                        &#128197; Reservation Details
                                    </h3>
                                    <table role="presentation" style="width: 100%; border-collapse: collapse;" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td style="padding: 10px 0; color: #6c757d; font-size: 14px; width: 45%;">
                                                <span style="font-weight: 600;">&#127970; Room/Facility:</span>
                                            </td>
                                            <td style="padding: 10px 0; color: #212529; font-size: 15px; font-weight: 700;">' . htmlspecialchars($room_data['name']) . '</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 10px 0; color: #6c757d; font-size: 14px;">
                                                <span style="font-weight: 600;">&#128198; Check-in:</span>
                                            </td>
                                            <td style="padding: 10px 0; color: #212529; font-size: 15px; font-weight: 700;">' . date('l, F j, Y', strtotime($checkin)) . '</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 10px 0; color: #6c757d; font-size: 14px;">
                                                <span style="font-weight: 600;">&#128197; Check-out:</span>
                                            </td>
                                            <td style="padding: 10px 0; color: #212529; font-size: 15px; font-weight: 700;">' . date('l, F j, Y', strtotime($checkout)) . '</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 10px 0; color: #6c757d; font-size: 14px;">
                                                <span style="font-weight: 600;">&#128337; Duration:</span>
                                            </td>
                                            <td style="padding: 10px 0; color: #212529; font-size: 15px; font-weight: 700;">' . $nights . ' ' . ($nights == 1 ? 'Night' : 'Nights') . '</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 10px 0; color: #6c757d; font-size: 14px;">
                                                <span style="font-weight: 600;">&#128101; Occupants:</span>
                                            </td>
                                            <td style="padding: 10px 0; color: #212529; font-size: 15px; font-weight: 700;">' . htmlspecialchars($occupants) . ' ' . ($occupants == 1 ? 'Guest' : 'Guests') . '</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 10px 0; color: #6c757d; font-size: 14px;">
                                                <span style="font-weight: 600;">&#128274; Status:</span>
                                            </td>
                                            <td style="padding: 10px 0;">
                                                <span style="display: inline-block; padding: 6px 16px; background-color: #ffc107; color: #000; font-size: 13px; font-weight: 700; border-radius: 20px; box-shadow: 0 2px 6px rgba(255, 193, 7, 0.3);">&#9200; Pending Approval</span>
                                            </td>
                                        </tr>';

                    if (!empty($discount_type) && $discount_status === 'approved') {
                        $emailContent .= '
                                        <tr>
                                            <td colspan="2" style="padding-top: 15px;">
                                                <div style="background-color: #d4edda; border-left: 4px solid #28a745; padding: 12px 15px; border-radius: 4px;">
                                                    <p style="margin: 0 0 5px 0; color: #155724; font-size: 14px; font-weight: 600;">
                                                        &#127991; Discount Applied: ' . htmlspecialchars($discount_type) . ' (' . $discount_percentage . '%)
                                                    </p>
                                                    <p style="margin: 0; color: #155724; font-size: 13px;">
                                                        Status: <strong>✓ Automatically Approved</strong><br>
                                                        Discount Amount: <strong>₱' . number_format($discount_amount, 2) . '</strong>
                                                    </p>
                                                </div>
                                            </td>
                                        </tr>';
                    }

                    $emailContent .= '
                                    </table>
                                </td>
                            </tr>
                        </table>
                        
                        <!-- Next Steps -->
                        <div style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); border-left: 5px solid #2196F3; padding: 20px 25px; margin-bottom: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(33, 150, 243, 0.15);">
                            <h4 style="margin: 0 0 12px 0; color: #0d47a1; font-size: 16px; font-weight: 700;">
                                &#128221; What Happens Next?
                            </h4>
                            <ol style="margin: 0; padding-left: 20px; color: #1565c0; font-size: 14px; line-height: 1.8;">
                                <li><strong>Payment Verification:</strong> Our admin team will verify your submitted payment proof (usually within 24 hours)</li>
                                <li><strong>Booking Approval:</strong> Once payment is verified, your booking will be officially approved and confirmed</li>
                                <li><strong>Confirmation Email:</strong> You will receive a final confirmation email once your booking is approved</li>
                                <li><strong>Prepare for Check-in:</strong> Bring a valid government-issued ID on your check-in date</li>
                            </ol>
                            <div style="margin-top: 15px; padding: 12px; background-color: #fff3cd; border-radius: 6px;">
                                <p style="margin: 0; color: #856404; font-size: 13px; font-weight: 600; text-align: center;">
                                    ⏳ Please wait for admin approval - Your reservation will be confirmed once payment is verified
                                </p>
                            </div>
                        </div>
                        
                        <!-- Important Reminders -->
                        <div style="background-color: #fff3cd; border-left: 5px solid #ffc107; padding: 20px 25px; margin-bottom: 25px; border-radius: 8px;">
                            <h4 style="margin: 0 0 12px 0; color: #856404; font-size: 16px; font-weight: 700;">
                                &#9888; Important Reminders
                            </h4>
                            <ul style="margin: 0; padding-left: 20px; color: #856404; font-size: 14px; line-height: 1.8;">
                                <li>Check-in time: 2:00 PM | Check-out time: 12:00 PM</li>
                                <li>Please bring a valid government-issued ID upon check-in</li>
                                <li>Payment must be completed before check-in date</li>
                                <li>Cancellations must be made 48 hours in advance</li>
                            </ul>
                        </div>
                        
                        <p style="margin: 0 0 15px 0; color: #495057; font-size: 15px; line-height: 1.7; text-align: center;">
                            For questions or modifications to your booking, please contact us with your receipt number <strong style="color: #2a5298;">' . htmlspecialchars($receipt_no) . '</strong>
                        </p>
                        
                        <!-- Cancel Booking Section - Only shown for pending bookings -->
                        <div style="text-align: center; margin: 25px 0; padding: 20px; background-color: #f8f9fa; border-radius: 8px;">
                            <p style="margin: 0 0 15px 0; color: #6c757d; font-size: 14px;">
                                Need to cancel your booking?
                            </p>
                            <a href="https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/barcie_php/api/cancel_booking.php?receipt=' . urlencode($receipt_no) . '&email=' . urlencode($email) . '" style="display: inline-block; padding: 12px 28px; background-color: #dc3545; color: white; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 14px;">
                                Cancel Booking
                            </a>
                            <p style="margin: 15px 0 0 0; color: #6c757d; font-size: 12px; font-style: italic;">
                                Cancellations must be made at least 48 hours before check-in
                            </p>
                        </div>
                        
                        <div style="text-align: center; padding: 20px 0; border-top: 2px solid #e9ecef; margin-top: 25px;">
                            <p style="margin: 0; color: #1e3c72; font-size: 16px; font-weight: 600;">
                                We look forward to welcoming you! &#127881;
                            </p>
                        </div>';

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

                // If this booking was converted from a pencil booking, update the pencil booking status
                if ($converted_from_pencil_id > 0) {
                    try {
                        $update_pencil = $conn->prepare("UPDATE pencil_bookings SET status = 'converted', converted_booking_receipt = ? WHERE id = ?");
                        $update_pencil->bind_param("si", $receipt_no, $converted_from_pencil_id);
                        $update_pencil->execute();
                        $update_pencil->close();
                        error_log("Marked pencil booking #$converted_from_pencil_id as converted to $receipt_no");
                    } catch (Exception $e) {
                        error_log("Failed to update pencil booking status: " . $e->getMessage());
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
        $pax = (int) ($_POST['pax'] ?? 1);
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
                // DO NOT update room status for pencil bookings
                // Pencil bookings are tentative and should not block availability

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
    } elseif ($type === 'pencil_booking') {
        // PENCIL BOOKING - Draft Reservation with 2-week confirmation period

        // Get form data
        $guest_name = $conn->real_escape_string($_POST['guest_name'] ?? '');
        $contact = $conn->real_escape_string($_POST['contact_number'] ?? '');
        $email = $conn->real_escape_string($_POST['email'] ?? '');
        $checkin = $_POST['checkin'] ?? null;
        $checkout = $_POST['checkout'] ?? null;
        $occupants = (int) ($_POST['occupants'] ?? 1);
        $company = $conn->real_escape_string($_POST['company'] ?? '');
        $company_contact = $conn->real_escape_string($_POST['company_contact'] ?? '');
        $terms_acknowledged = isset($_POST['terms_acknowledged']) && $_POST['terms_acknowledged'] === 'on' ? 1 : 0;

        // Validate terms acknowledgment
        if (!$terms_acknowledged) {
            handleResponse("You must acknowledge the two-week policy to proceed with pencil booking.", false, '../Guest.php');
        }

        // Validate dates
        if (empty($checkin) || empty($checkout)) {
            handleResponse("Please provide check-in and check-out dates.", false, '../Guest.php');
        }

        if (strtotime($checkin) >= strtotime($checkout)) {
            handleResponse("Check-out date must be after check-in date.", false, '../Guest.php');
        }

        // Check for double booking - only conflicts if date ranges actually overlap (not just touching)
        // A booking conflicts if: new_checkin < existing_checkout AND new_checkout > existing_checkin
        $conflict_stmt = $conn->prepare("SELECT id FROM pencil_bookings WHERE room_id = ? AND status IN ('confirmed', 'approved', 'pending') AND checkin < ? AND checkout > ?");
        $conflict_stmt->bind_param("iss", $room_id, $checkout, $checkin);
        $conflict_stmt->execute();
        $conflict_result = $conflict_stmt->get_result();

        if ($conflict_result->num_rows > 0) {
            $conflict_stmt->close();
            handleResponse("Sorry, the selected " . $room_data['item_type'] . " is already pencil booked for the requested dates.", false, '../Guest.php');
        }
        $conflict_stmt->close();

        // Validate occupancy with detailed error message
        if ($occupants > $room_data['capacity']) {
            $error_msg = "⚠️ CAPACITY EXCEEDED: The number of guests (" . $occupants . ") exceeds the maximum allowed capacity for " . $room_data['name'] . ". Maximum capacity: " . $room_data['capacity'] . " persons. Please select a larger room or reduce the number of occupants.";
            handleResponse($error_msg, false, '../Guest.php');
        }

        // Generate receipt number
        $currentDate = date('Ymd');
        $stmt = $conn->prepare("SELECT receipt_no FROM pencil_bookings WHERE receipt_no LIKE ? ORDER BY receipt_no DESC LIMIT 1");
        $datePattern = "PENCIL-{$currentDate}-%";
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
        $receipt_no = "PENCIL-{$currentDate}-{$formattedNumber}";
        $stmt->close();

        // Calculate pricing
        $base_price = $room_data['price'];
        $total_price = $base_price;

        // Handle discount application - AUTO-APPROVE when proof is uploaded
        $discount_code = $_POST['discount_type'] ?? '';
        $discount_amount = 0;
        $discount_proof_path = null;
        $discount_percentage = 0;

        if (!empty($discount_code) && isset($_FILES['discount_proof']) && $_FILES['discount_proof']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../uploads/discount_proofs/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $file_tmp = $_FILES['discount_proof']['tmp_name'];
            $file_ext = pathinfo($_FILES['discount_proof']['name'], PATHINFO_EXTENSION);
            $file_name = 'pencil_' . time() . '_' . uniqid() . '.' . $file_ext;
            $target_path = $upload_dir . $file_name;
            if (move_uploaded_file($file_tmp, $target_path)) {
                $discount_proof_path = 'uploads/discount_proofs/' . $file_name;

                // Calculate discount percentage based on discount type
                if ($discount_code === 'pwd_senior') {
                    $discount_percentage = 20; // 20% for PWD/Senior
                } elseif ($discount_code === 'lcuppersonnel') {
                    $discount_percentage = 10; // 10% for LCUP Personnel
                } elseif ($discount_code === 'lcupstudent') {
                    $discount_percentage = 7; // 7% for LCUP Student/Alumni
                }

                // Calculate discount amount automatically
                $discount_amount = ($base_price * $discount_percentage) / 100;
                $total_price = $base_price - $discount_amount;

                error_log("Pencil booking discount proof uploaded to: " . $discount_proof_path);
                error_log("Auto-calculated discount: Type=$discount_code, Percentage=$discount_percentage%, Amount=₱$discount_amount, New Total=₱$total_price");
            }
        }

        // Create details text
        $details = "Guest: $guest_name | Email: $email | Contact: $contact | Company: $company | Company Contact: $company_contact";

        // Insert into pencil_bookings table
        try {
            $insert_stmt = $conn->prepare("INSERT INTO pencil_bookings (receipt_no, room_id, guest_name, contact_number, email, checkin, checkout, occupants, company, company_contact, discount_code, discount_proof_path, discount_amount, base_price, total_price, status, terms_acknowledged, acknowledgment_timestamp, details) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, NOW(), ?)");

            if (!$insert_stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }

            // Type string breakdown: s=string, i=integer, d=double/decimal
            // receipt_no(s), room_id(i), guest_name(s), contact(s), email(s), checkin(s), checkout(s), 
            // occupants(i), company(s), company_contact(s), discount_code(s), discount_proof_path(s), 
            // discount_amount(d), base_price(d), total_price(d), terms_acknowledged(i), details(s)
            // Correct type string: there are 17 placeholders — three decimal values (discount_amount, base_price, total_price)
            $insert_stmt->bind_param("sisssssissssdddis", $receipt_no, $room_id, $guest_name, $contact, $email, $checkin, $checkout, $occupants, $company, $company_contact, $discount_code, $discount_proof_path, $discount_amount, $base_price, $total_price, $terms_acknowledged, $details);

            $success = $insert_stmt->execute();

            if (!$success) {
                throw new Exception("Execute failed: " . $insert_stmt->error);
            }

            if ($success) {
                // Capture inserted pencil ID for AJAX responses or further processing
                $pencil_id = (int) $conn->insert_id;
                // Send email reminder about draft status
                if (!empty($email)) {
                    error_log("========================================");
                    error_log("PENCIL BOOKING EMAIL - Starting email send process");
                    error_log("Recipient: " . $email);
                    error_log("Guest: " . $guest_name);
                    error_log("Receipt: " . $receipt_no);
                    error_log("========================================");

                    $subject = "Draft Reservation Confirmation - BarCIE International Center";

                    // Calculate expiration date (2 weeks from now)
                    $expiresAt = date('F j, Y', strtotime('+14 days'));

                    // Generate conversion token for secure link
                    $conversion_token = bin2hex(random_bytes(32));

                    // Store the token in the pencil_bookings table
                    try {
                        // Add conversion_token column if not exists
                        $conn->query("ALTER TABLE pencil_bookings ADD COLUMN IF NOT EXISTS conversion_token VARCHAR(255) NULL");
                        $conn->query("ALTER TABLE pencil_bookings ADD COLUMN IF NOT EXISTS token_expires_at DATETIME NULL");

                        $token_expires = date('Y-m-d H:i:s', strtotime('+14 days'));
                        $update_token = $conn->prepare("UPDATE pencil_bookings SET conversion_token = ?, token_expires_at = ? WHERE receipt_no = ?");
                        $update_token->bind_param("sss", $conversion_token, $token_expires, $receipt_no);
                        $update_token->execute();
                        $update_token->close();
                    } catch (Exception $e) {
                        error_log("Failed to store conversion token: " . $e->getMessage());
                    }

                    // Create conversion link
                    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                    $conversion_link = "http://{$host}/barcie_php/components/guest/convert_pencil.php?token=" . urlencode($conversion_token);

                    $emailContent = '
                        <h2 style="margin: 0 0 20px 0; color: #856404; font-size: 24px; font-weight: 600;">📝 Draft Reservation (Pencil Booking)</h2>
                        <p style="margin: 0 0 20px 0; color: #495057; font-size: 16px; line-height: 1.6;">
                            Dear <strong>' . htmlspecialchars($guest_name) . '</strong>,
                        </p>
                        <p style="margin: 0 0 25px 0; color: #495057; font-size: 15px; line-height: 1.6;">
                            Thank you for submitting your draft reservation (pencil booking)! This is a <strong>temporary hold</strong> on your selected room/facility while you finalize your plans.
                        </p>
                        
                        <!-- Important Notice -->
                        <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px 20px; margin-bottom: 25px; border-radius: 4px;">
                            <p style="margin: 0 0 10px 0; color: #856404; font-size: 15px; font-weight: 600;">
                                ⚠️ Important: This is a DRAFT reservation only
                            </p>
                            <p style="margin: 0; color: #856404; font-size: 14px; line-height: 1.6;">
                                To secure your reservation, you must confirm and complete payment within <strong>14 days (by ' . $expiresAt . ')</strong>. 
                                Once you complete payment, our admin team will verify and approve your booking. If we do not receive confirmation and payment within this timeframe, your reservation slot may be released to other guests.
                            </p>
                        </div>
                        
                        <!-- Convert to Full Reservation Button -->
                        <div style="text-align: center; margin: 30px 0;">
                            <a href="' . $conversion_link . '" style="display: inline-block; padding: 15px 35px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4); transition: all 0.3s ease;">
                                🎯 Click Here to Proceed to Full Reservation
                            </a>
                            <p style="margin: 15px 0 0 0; color: #6c757d; font-size: 13px;">
                                <em>This link will pre-fill your booking information for easy confirmation</em>
                            </p>
                        </div>
                        
                        <!-- Booking Details Card -->
                        <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #f8f9fa; border-radius: 6px; margin-bottom: 25px;" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="padding: 25px;">
                                    <table role="presentation" style="width: 100%; border-collapse: collapse;" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td style="padding: 8px 0; color: #6c757d; font-size: 14px; width: 40%;">Pencil Booking Number:</td>
                                            <td style="padding: 8px 0; color: #212529; font-size: 14px; font-weight: 600;">' . htmlspecialchars($receipt_no) . '</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 8px 0; color: #6c757d; font-size: 14px;">Room/Facility:</td>
                                            <td style="padding: 8px 0; color: #212529; font-size: 14px; font-weight: 600;">' . htmlspecialchars($room_data['name']) . '</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 8px 0; color: #6c757d; font-size: 14px;">Check-in Date:</td>
                                            <td style="padding: 8px 0; color: #212529; font-size: 14px; font-weight: 600;">' . date('F j, Y g:i A', strtotime($checkin)) . '</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 8px 0; color: #6c757d; font-size: 14px;">Check-out Date:</td>
                                            <td style="padding: 8px 0; color: #212529; font-size: 14px; font-weight: 600;">' . date('F j, Y g:i A', strtotime($checkout)) . '</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 8px 0; color: #6c757d; font-size: 14px;">Number of Occupants:</td>
                                            <td style="padding: 8px 0; color: #212529; font-size: 14px; font-weight: 600;">' . htmlspecialchars($occupants) . '</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 8px 0; color: #6c757d; font-size: 14px;">Estimated Price:</td>
                                            <td style="padding: 8px 0; color: #212529; font-size: 14px; font-weight: 600;">₱' . number_format($total_price, 2) . '</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 8px 0; color: #6c757d; font-size: 14px;">Status:</td>
                                            <td style="padding: 8px 0;">
                                                <span style="display: inline-block; padding: 4px 12px; background-color: #ffc107; color: #000; font-size: 13px; font-weight: 600; border-radius: 4px;">Draft - Pending Confirmation</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 8px 0; color: #6c757d; font-size: 14px;">Expires On:</td>
                                            <td style="padding: 8px 0; color: #dc3545; font-size: 14px; font-weight: 600;">' . $expiresAt . '</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                        
                        <!-- Next Steps -->
                        <div style="background-color: #e7f3ff; border-left: 4px solid #2196F3; padding: 15px 20px; margin-bottom: 25px; border-radius: 4px;">
                            <p style="margin: 0 0 10px 0; color: #1976D2; font-size: 15px; font-weight: 600;">
                                📋 Next Steps to Confirm Your Reservation:
                            </p>
                            <ol style="margin: 0; padding-left: 20px; color: #1976D2; font-size: 14px; line-height: 1.8;">
                                <li>Click the button above to convert your draft to a full reservation</li>
                                <li>Complete the payment process via bank transfer or GCash</li>
                                <li>Upload your payment receipt/proof</li>
                                <li>Wait for admin verification and approval (usually within 24 hours)</li>
                                <li>Receive your final booking confirmation email</li>
                            </ol>
                        </div>
                        
                        <!-- Payment Information -->
                        <div style="background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); border-left: 5px solid #28a745; padding: 20px 25px; margin-bottom: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(40, 167, 69, 0.15);">
                            <h4 style="margin: 0 0 15px 0; color: #155724; font-size: 16px; font-weight: 700;">
                                &#128179; Payment Information
                            </h4>
                            <p style="margin: 0 0 15px 0; color: #155724; font-size: 14px; line-height: 1.6;">
                                <strong>Bank Transfer Details:</strong>
                            </p>
                            <table role="presentation" style="width: 100%; border-collapse: collapse; margin-bottom: 15px;" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding: 5px 0; color: #155724; font-size: 14px;">Bank Name:</td>
                                    <td style="padding: 5px 0; color: #212529; font-size: 14px; font-weight: 600;">BDO / BPI</td>
                                </tr>
                                <tr>
                                    <td style="padding: 5px 0; color: #155724; font-size: 14px;">Account Name:</td>
                                    <td style="padding: 5px 0; color: #212529; font-size: 14px; font-weight: 600;">BarCIE International Center</td>
                                </tr>
                                <tr>
                                    <td style="padding: 5px 0; color: #155724; font-size: 14px;">Account Number:</td>
                                    <td style="padding: 5px 0; color: #212529; font-size: 14px; font-weight: 600;">XXXX-XXXX-XXXX</td>
                                </tr>
                            </table>
                            <div style="text-align: center; margin: 15px 0;">
                                <a href="http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/barcie_php/components/guest/bank_qr.php" style="display: inline-block; padding: 14px 30px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; text-decoration: none; border-radius: 25px; font-weight: 700; font-size: 15px; box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);">
                                    &#128241; View Payment QR Code
                                </a>
                            </div>
                            <p style="margin: 0 0 10px 0; color: #155724; font-size: 13px; line-height: 1.6; font-style: italic; text-align: center;">
                                Scan the QR code with your banking app or use the account details above. Upload your payment receipt after completing the transaction.
                            </p>
                            <div style="background-color: #fff3cd; padding: 12px; border-radius: 6px; margin-top: 15px;">
                                <p style="margin: 0; color: #856404; font-size: 13px; line-height: 1.6; font-weight: 600; text-align: center;">
                                    ⚠️ <strong>Payment is required before check-in and is non-refundable.</strong>' . (strtotime($checkout) - strtotime($checkin) <= 86400 ? ' <br><em>Note: For 1-day bookings, we recommend walk-in reservations for more flexibility.</em>' : '') . '
                                </p>
                            </div>
                        </div>
                        
                        <p style="margin: 0 0 15px 0; color: #495057; font-size: 15px; line-height: 1.6;">
                            Please keep this pencil booking number for your records. If you have any questions or need to make changes, contact us with your booking number.
                        </p>
                        
                        <!-- Cancel Pencil Booking Section -->
                        <div style="text-align: center; margin: 25px 0; padding: 20px; background-color: #f8f9fa; border-radius: 8px;">
                            <p style="margin: 0 0 15px 0; color: #6c757d; font-size: 14px;">
                                Need to cancel your pencil booking?
                            </p>
                            <a href="https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/barcie_php/api/cancel_booking.php?receipt=' . urlencode($receipt_no) . '&email=' . urlencode($email) . '&type=pencil" style="display: inline-block; padding: 12px 28px; background-color: #dc3545; color: white; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 14px;">
                                Cancel Pencil Booking
                            </a>
                        </div>
                        
                        <p style="margin: 0; color: #495057; font-size: 15px; line-height: 1.6;">
                            Thank you for choosing BarCIE International Center!
                        </p>';

                    $emailBody = create_email_template('Draft Reservation Confirmation', $emailContent, 'This is an automated reminder. Please respond within 14 days to confirm your reservation.');

                    error_log("PENCIL BOOKING EMAIL - Calling send_smtp_mail()");
                    $mail_sent = send_smtp_mail($email, $subject, $emailBody);
                    error_log("PENCIL BOOKING EMAIL - Send result: " . ($mail_sent ? "SUCCESS" : "FAILED"));
                    error_log("========================================");
                }

                // If this was an AJAX request, return JSON with the created pencil id and receipt
                $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
                if ($isAjax) {
                    header('Content-Type: application/json');
                    $resp = [
                        'success' => true,
                        'message' => "Draft reservation (pencil booking) saved successfully.",
                        'pencil_id' => $pencil_id,
                        'receipt_no' => $receipt_no
                    ];
                    if (!empty($conversion_token)) {
                        $resp['conversion_token'] = $conversion_token;
                    }
                    echo json_encode($resp);
                    exit();
                }

                handleResponse("Draft reservation (pencil booking) saved successfully! Receipt number: $receipt_no. Please confirm within 14 days to fully secure your reservation.", true, '../Guest.php');
            } else {
                handleResponse("Error saving pencil booking: " . $insert_stmt->error, false, '../Guest.php');
                error_log("Pencil booking insert error: " . $insert_stmt->error);
            }
            $insert_stmt->close();
        } catch (mysqli_sql_exception $e) {
            handleResponse("Database error: " . $e->getMessage(), false, '../Guest.php');
            error_log("Pencil booking creation exception: " . $e->getMessage());
        } catch (Exception $e) {
            handleResponse("Unexpected error: " . $e->getMessage(), false, '../Guest.php');
            error_log("Pencil booking creation general exception: " . $e->getMessage());
        }
    } else {
        handleResponse("Unknown booking type.", false, '../Guest.php');
    }
}


