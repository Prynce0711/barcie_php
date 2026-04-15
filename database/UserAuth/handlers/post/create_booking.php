<?php
/* ---------------------------
   CREATE BOOKING
   --------------------------- */
if ($action === 'create_booking') {
    require_once __DIR__ . '/../../../modules/discount_rules.php';

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
    $payment_method = strtolower(trim((string) ($_POST['payment_method'] ?? 'cash')));

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

    $is_bank_transfer = in_array($payment_method, ['bank', 'bank_transfer', 'bank transfer'], true);
    if ($is_bank_transfer && $payment_proof_path === '') {
        handleResponse('Please upload your payment receipt when choosing Bank Transfer.', false, '../index.php?view=guest');
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
        handleResponse("Please select a room or facility.", false, '../index.php?view=guest');
    }

    // Get room/facility details for validation and details
    $room_stmt = $conn->prepare("SELECT id, name, item_type, room_status, capacity, price FROM items WHERE id = ?");
    $room_stmt->bind_param("i", $room_id);
    $room_stmt->execute();
    $room_result = $room_stmt->get_result();
    $room_data = $room_result->fetch_assoc();
    $room_stmt->close();

    if (!$room_data) {
        handleResponse("Selected room/facility not found.", false, '../index.php?view=guest');
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

        $requested_room_id = $room_id;
        $requested_room_name = $room_data['name'] ?? '';
        $requested_room_number = $room_data['room_number'] ?? '';
        $auto_transfer_note = '';

        // Validate dates
        if (empty($checkin) || empty($checkout)) {
            handleResponse("Please provide check-in and check-out dates.", false, '../index.php?view=guest');
        }

        if (strtotime($checkin) >= strtotime($checkout)) {
            handleResponse("Check-out date must be after check-in date.", false, '../index.php?view=guest');
        }

        // Check for double booking - only conflicts if date ranges actually overlap (not just touching)
        // A booking conflicts if: new_checkin < existing_checkout AND new_checkout > existing_checkin
        $conflict_stmt = $conn->prepare("SELECT id FROM bookings WHERE room_id = ? AND status IN ('confirmed', 'approved', 'pending', 'checked_in') AND checkin < ? AND checkout > ?");
        $conflict_stmt->bind_param("iss", $room_id, $checkout, $checkin);
        $conflict_stmt->execute();
        $conflict_result = $conflict_stmt->get_result();

        if ($conflict_result->num_rows > 0) {
            $conflict_stmt->close();

            // Auto-transfer is only attempted for normal new reservations, not pencil conversions.
            if ($converted_from_pencil_id <= 0) {
                $requested_type = (string) ($room_data['item_type'] ?? 'room');
                $requested_price = (float) ($room_data['price'] ?? 0);
                $alt_stmt = $conn->prepare("SELECT i.id, i.name, i.room_number, i.item_type, i.capacity, i.price
                                            FROM items i
                                            WHERE i.id <> ?
                                              AND i.item_type = ?
                                              AND i.capacity >= ?
                                              AND NOT EXISTS (
                                                SELECT 1 FROM bookings b
                                                WHERE b.room_id = i.id
                                                  AND b.status IN ('confirmed', 'approved', 'pending', 'checked_in')
                                                  AND b.checkin < ?
                                                  AND b.checkout > ?
                                              )
                                            ORDER BY ABS(i.price - ?) ASC, i.capacity ASC, i.room_number ASC, i.id ASC
                                            LIMIT 1");

                if ($alt_stmt) {
                    $alt_stmt->bind_param("isissd", $room_id, $requested_type, $occupants, $checkout, $checkin, $requested_price);
                    $alt_stmt->execute();
                    $alt_result = $alt_stmt->get_result();
                    $alt_room = $alt_result ? $alt_result->fetch_assoc() : null;
                    $alt_stmt->close();

                    if ($alt_room) {
                        $room_id = (int) $alt_room['id'];
                        $room_data = $alt_room;

                        $requested_label = $requested_room_name;
                        if (!empty($requested_room_number)) {
                            $requested_label .= ' #' . $requested_room_number;
                        }

                        $assigned_label = (string) ($room_data['name'] ?? 'Alternative');
                        if (!empty($room_data['room_number'])) {
                            $assigned_label .= ' #' . $room_data['room_number'];
                        }

                        $auto_transfer_note = 'Requested ' . $requested_label . ' was already booked for your selected dates. We automatically transferred your reservation to ' . $assigned_label . '.';
                    } else {
                        handleResponse("Sorry, the selected " . $room_data['item_type'] . " is already booked for the requested dates and no matching alternative is currently available.", false, '../index.php?view=guest');
                    }
                } else {
                    handleResponse("Sorry, the selected " . $room_data['item_type'] . " is already booked for the requested dates.", false, '../index.php?view=guest');
                }
            } else {
                handleResponse("Sorry, the selected " . $room_data['item_type'] . " is already booked for the requested dates.", false, '../index.php?view=guest');
            }
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
                    handleResponse("Cannot convert pencil booking: another draft or confirmed pencil booking overlaps the requested dates.", false, '../index.php?view=guest');
                }
                $pencil_conflict->close();
            } catch (Exception $e) {
                error_log("Pencil conflict check failed: " . $e->getMessage());
            }
        }

        // Validate occupancy with detailed error message
        if ($occupants > $room_data['capacity']) {
            $error_msg = "⚠️ CAPACITY EXCEEDED: The number of guests (" . $occupants . ") exceeds the maximum allowed capacity for " . $room_data['name'] . ". Maximum capacity: " . $room_data['capacity'] . " persons. Please select a larger room or reduce the number of occupants.";
            handleResponse($error_msg, false, '../index.php?view=guest');
        }

        // Add discount info to details and set discount_status
        // AUTO-APPROVE: When discount proof is uploaded, automatically approve the discount
        $discount_info = '';
        $discount_status = 'none';
        $discount_amount = 0;
        $discount_percentage = 0;

        // Use id_upload_path for proof_of_id column, fallback to discount proof if ID not uploaded
        $proof_of_id = !empty($id_upload_path) ? $id_upload_path : (!empty($discount_proof_path) ? $discount_proof_path : null);

        $selected_id_type = trim((string) ($_POST['id_type'] ?? ''));
        $discount_map = discount_get_rule_map($conn, true);

        if (!empty($discount_type) && !empty($proof_of_id) && isset($discount_map[$discount_type])) {
            // Automatically approve discount when proof is uploaded
            $discount_status = 'approved';

            $rule = $discount_map[$discount_type];
            if (!discount_rule_accepts_id_type($rule, $selected_id_type)) {
                $discount_status = 'none';
                $discount_info = " | Discount not applied: selected ID type is not allowed for $discount_type";
                error_log("Discount not applied due to ID type mismatch. Type=$discount_type, IDType=$selected_id_type");
            } else {
                $discount_percentage = (float) $rule['percentage'];

                // Calculate discount amount
                $discount_amount = ($amount * $discount_percentage) / 100;
                $amount = $amount - $discount_amount; // Apply discount to total amount

                $discount_info = " | Discount: $discount_type ($discount_percentage%) | Discount Amount: ₱" . number_format($discount_amount, 2) . " | Discount Details: $discount_details | Proof: $discount_proof_path";

                error_log("Auto-approved discount: Type=$discount_type, Percentage=$discount_percentage%, Amount=₱$discount_amount, New Total=₱$amount");
            }
        } elseif (!empty($discount_type) && empty($proof_of_id)) {
            // If discount type is selected but no proof uploaded, don't apply discount
            $discount_status = 'none';
            error_log("Discount not applied: No proof uploaded for discount type $discount_type");
        }

        $transfer_details = '';
        if (!empty($auto_transfer_note)) {
            $transfer_details = " | Auto-Transfer: " . $auto_transfer_note;
        }

        $details = "Receipt: $receipt_no | " . ucfirst($room_data['item_type']) . ": " . $room_data['name'] . " | Guest: $guest_name | Email: $email | Contact: $contact | Check-in: $checkin | Check-out: $checkout | Occupants: $occupants | Company: $company" . $discount_info . $transfer_details;

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

                    $checkin_date = new DateTime($checkin);
                    $checkout_date = new DateTime($checkout);
                    $duration = $checkin_date->diff($checkout_date);
                    $nights = $duration->days;
                    $cancelUrl = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/barcie_php/api/CancelBooking.php?receipt=' . urlencode($receipt_no) . '&email=' . urlencode($email);

                    $emailTemplate = build_booking_confirmation_email([
                        'guest_name' => $guest_name,
                        'receipt_no' => $receipt_no,
                        'room_name' => $room_data['name'],
                        'checkin' => $checkin,
                        'checkout' => $checkout,
                        'nights' => $nights,
                        'occupants' => $occupants,
                        'discount_type' => $discount_type,
                        'discount_status' => $discount_status,
                        'discount_percentage' => $discount_percentage,
                        'discount_amount' => $discount_amount,
                        'cancel_url' => $cancelUrl,
                        'transfer_note' => $auto_transfer_note,
                    ]);

                    $emailBody = create_email_template($emailTemplate['title'], $emailTemplate['content'], $emailTemplate['footer']);

                    error_log("BOOKING EMAIL - Calling send_smtp_mail()");
                    $mail_sent = send_smtp_mail($email, $subject, $emailBody);
                    error_log("BOOKING EMAIL - Send result: " . ($mail_sent ? "SUCCESS" : "FAILED"));
                    error_log("========================================");

                    // If there's a discount, also notify admin
                    if (!empty($discount_type)) {
                        error_log("DISCOUNT EMAIL - Sending admin notification");
                        $admin_email = 'pc.clemente11@gmail.com';
                        $adminEmailTemplate = build_discount_admin_notification_email([
                            'guest_name' => $guest_name,
                            'email' => $email,
                            'contact' => $contact,
                            'room_name' => $room_data['name'],
                            'checkin' => $checkin,
                            'checkout' => $checkout,
                            'discount_type' => $discount_type,
                            'discount_details' => $discount_details,
                            'discount_proof_path' => $discount_proof_path,
                        ]);
                        $admin_message = create_email_template($adminEmailTemplate['title'], $adminEmailTemplate['content'], $adminEmailTemplate['footer']);
                        $admin_mail_sent = send_smtp_mail($admin_email, $adminEmailTemplate['subject'], $admin_message);
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

                $success_message = "Reservation saved successfully with receipt number: $receipt_no for " . $room_data['name'];
                if (!empty($auto_transfer_note)) {
                    $success_message .= ". " . $auto_transfer_note;
                }

                handleResponse($success_message, true, '../index.php?view=guest');
            } else {
                handleResponse("Error saving reservation: " . $stmt->error, false, '../index.php?view=guest');
                error_log("Booking insert error: " . $stmt->error);
            }
            $stmt->close();
        } catch (mysqli_sql_exception $e) {
            handleResponse("Database error: " . $e->getMessage(), false, '../index.php?view=guest');
            error_log("Booking creation exception: " . $e->getMessage());
        } catch (Exception $e) {
            handleResponse("Unexpected error: " . $e->getMessage(), false, '../index.php?view=guest');
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
            handleResponse("Pencil bookings are only available for facilities/function halls.", false, '../index.php?view=guest');
        }

        // Validate pax capacity
        if ($pax > $room_data['capacity']) {
            handleResponse("Number of guests (" . $pax . ") exceeds facility capacity (" . $room_data['capacity'] . ").", false, '../index.php?view=guest');
        }

        // Check for conflicts on the same date
        if (!empty($pencil_date)) {
            $conflict_stmt = $conn->prepare("SELECT id FROM bookings WHERE room_id = ? AND status IN ('confirmed', 'approved', 'pending') AND DATE(checkin) = ?");
            $conflict_stmt->bind_param("is", $room_id, $pencil_date);
            $conflict_stmt->execute();
            $conflict_result = $conflict_stmt->get_result();

            if ($conflict_result->num_rows > 0) {
                $conflict_stmt->close();
                handleResponse("Sorry, the selected facility is already booked for " . $pencil_date . ".", false, '../index.php?view=guest');
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
                    $simplePencilTemplate = build_simple_pencil_booking_received_email([
                        'room_name' => $room_data['name'],
                        'pencil_date' => $pencil_date,
                        'event' => $event,
                        'pax' => $pax,
                    ]);
                    $message = create_email_template($simplePencilTemplate['title'], $simplePencilTemplate['content'], $simplePencilTemplate['footer']);
                    send_smtp_mail($contact_number, $simplePencilTemplate['subject'], $message);
                }

                handleResponse("Pencil booking saved for " . $room_data['name'] . " on " . $pencil_date, true, '../index.php?view=guest');
            } else {
                handleResponse("Error: " . $stmt->error, false, '../index.php?view=guest');
            }
            $stmt->close();
        } catch (mysqli_sql_exception $e) {
            handleResponse("Database error: " . $e->getMessage(), false, '../index.php?view=guest');
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
            handleResponse("You must acknowledge the two-week policy to proceed with pencil booking.", false, '../index.php?view=guest');
        }

        // Validate dates
        if (empty($checkin) || empty($checkout)) {
            handleResponse("Please provide check-in and check-out dates.", false, '../index.php?view=guest');
        }

        if (strtotime($checkin) >= strtotime($checkout)) {
            handleResponse("Check-out date must be after check-in date.", false, '../index.php?view=guest');
        }

        // Check for double booking - only conflicts if date ranges actually overlap (not just touching)
        // A booking conflicts if: new_checkin < existing_checkout AND new_checkout > existing_checkin
        $conflict_stmt = $conn->prepare("SELECT id FROM pencil_bookings WHERE room_id = ? AND status IN ('confirmed', 'approved', 'pending') AND checkin < ? AND checkout > ?");
        $conflict_stmt->bind_param("iss", $room_id, $checkout, $checkin);
        $conflict_stmt->execute();
        $conflict_result = $conflict_stmt->get_result();

        if ($conflict_result->num_rows > 0) {
            $conflict_stmt->close();
            handleResponse("Sorry, the selected " . $room_data['item_type'] . " is already pencil booked for the requested dates.", false, '../index.php?view=guest');
        }
        $conflict_stmt->close();

        // Validate occupancy with detailed error message
        if ($occupants > $room_data['capacity']) {
            $error_msg = "⚠️ CAPACITY EXCEEDED: The number of guests (" . $occupants . ") exceeds the maximum allowed capacity for " . $room_data['name'] . ". Maximum capacity: " . $room_data['capacity'] . " persons. Please select a larger room or reduce the number of occupants.";
            handleResponse($error_msg, false, '../index.php?view=guest');
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

                $selected_id_type = trim((string) ($_POST['id_type'] ?? ''));
                $discount_map = discount_get_rule_map($conn, true);
                if (isset($discount_map[$discount_code])) {
                    $rule = $discount_map[$discount_code];
                    if (discount_rule_accepts_id_type($rule, $selected_id_type)) {
                        $discount_percentage = (float) $rule['percentage'];
                    }
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

                    $qrLink = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/barcie_php/components/guest/bank_qr.php';
                    $cancelLink = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/barcie_php/api/CancelBooking.php?receipt=' . urlencode($receipt_no) . '&email=' . urlencode($email) . '&type=pencil';

                    $draftTemplate = build_draft_reservation_email([
                        'guest_name' => $guest_name,
                        'receipt_no' => $receipt_no,
                        'room_name' => $room_data['name'],
                        'checkin' => $checkin,
                        'checkout' => $checkout,
                        'occupants' => $occupants,
                        'total_price' => $total_price,
                        'expires_at' => $expiresAt,
                        'conversion_link' => $conversion_link,
                        'qr_link' => $qrLink,
                        'cancel_link' => $cancelLink,
                    ]);

                    $emailBody = create_email_template($draftTemplate['title'], $draftTemplate['content'], $draftTemplate['footer']);

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

                handleResponse("Draft reservation (pencil booking) saved successfully! Receipt number: $receipt_no. Please confirm within 14 days to fully secure your reservation.", true, '../index.php?view=guest');
            } else {
                handleResponse("Error saving pencil booking: " . $insert_stmt->error, false, '../index.php?view=guest');
                error_log("Pencil booking insert error: " . $insert_stmt->error);
            }
            $insert_stmt->close();
        } catch (mysqli_sql_exception $e) {
            handleResponse("Database error: " . $e->getMessage(), false, '../index.php?view=guest');
            error_log("Pencil booking creation exception: " . $e->getMessage());
        } catch (Exception $e) {
            handleResponse("Unexpected error: " . $e->getMessage(), false, '../index.php?view=guest');
            error_log("Pencil booking creation general exception: " . $e->getMessage());
        }
    } else {
        handleResponse("Unknown booking type.", false, '../index.php?view=guest');
    }
}


