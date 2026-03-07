<?php
/* ---------------------------
   ADMIN: update booking status
   --------------------------- */
if ($action === 'admin_update_booking') {
    if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        // Return JSON for AJAX clients; for normal requests redirect with session message
        handleResponse('Access denied. Admin login required.', false, '../dashboard.php');
    }

    $bookingId = (int) ($_POST['booking_id'] ?? 0);
    $adminAction = $_POST['admin_action'] ?? '';

    $statusMap = [
        "approve" => "confirmed",
        "reject" => "rejected",
        "checkin" => "checked_in",
        "checkout" => "checked_out",
        "cancel" => "cancelled"
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

    // Get admin ID from session
    $admin_id = isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null;

    // Update booking status and set timestamps where appropriate
    if ($adminAction === 'approve' && $admin_id) {
        $stmt = $conn->prepare("UPDATE bookings SET status = ?, approved_by = ?, approved_at = NOW(), updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("sii", $newStatus, $admin_id, $bookingId);
    } elseif ($adminAction === 'checkout') {
        // when checking out, record checked_out_at
        $stmt = $conn->prepare("UPDATE bookings SET status = ?, checked_out_at = NOW(), updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("si", $newStatus, $bookingId);
    } elseif ($adminAction === 'checkin') {
        // when checking in, record checked_in_at
        $stmt = $conn->prepare("UPDATE bookings SET status = ?, checked_in_at = NOW(), updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("si", $newStatus, $bookingId);
    } else {
        $stmt = $conn->prepare("UPDATE bookings SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("si", $newStatus, $bookingId);
    }
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

                    // Calculate stay duration
                    $checkin_date_approve = new DateTime($checkin);
                    $checkout_date_approve = new DateTime($checkout);
                    $duration_approve = $checkin_date_approve->diff($checkout_date_approve);
                    $nights_approve = $duration_approve->days;

                    $emailContent = '
                        <div style="text-align: center; margin-bottom: 35px;">
                            <div style="display: inline-block; padding: 15px 35px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border-radius: 50px; font-size: 16px; font-weight: 700; box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);">
                                &#10004; BOOKING APPROVED
                            </div>
                        </div>
                        
                        <h2 style="margin: 0 0 15px 0; color: #28a745; font-size: 28px; font-weight: 700; text-align: center;">Your Reservation is Confirmed!</h2>
                        <p style="margin: 0 0 30px 0; color: #6c757d; font-size: 14px; text-align: center;">
                            Confirmation sent on ' . date('F j, Y \a\t g:i A') . '
                        </p>
                        
                        <p style="margin: 0 0 20px 0; color: #495057; font-size: 16px; line-height: 1.6;">
                            Dear <strong style="color: #1e3c72;">' . htmlspecialchars($guest_name) . '</strong>,
                        </p>
                        <p style="margin: 0 0 30px 0; color: #495057; font-size: 15px; line-height: 1.7;">
                            Excellent news! We are delighted to confirm that your reservation has been approved. We are excited to welcome you to BarCIE International Center and ensure you have a wonderful experience!
                        </p>
                        
                        <!-- Booking Details Card -->
                        <table role="presentation" style="width: 100%; border-collapse: collapse; background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); border-radius: 10px; margin-bottom: 25px; border: 3px solid #28a745; box-shadow: 0 4px 15px rgba(40, 167, 69, 0.2);" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="padding: 30px;">
                                    <div style="text-align: center; margin-bottom: 20px;">
                                        <span style="font-size: 48px;">&#127881;</span>
                                    </div>
                                    <h3 style="margin: 0 0 20px 0; color: #155724; font-size: 20px; font-weight: 700; text-align: center; border-bottom: 2px solid #28a745; padding-bottom: 12px;">
                                        Your Confirmed Reservation
                                    </h3>
                                    <table role="presentation" style="width: 100%; border-collapse: collapse;" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td style="padding: 12px 0; color: #155724; font-size: 14px; font-weight: 600; width: 45%;">
                                                &#127970; Room/Facility:
                                            </td>
                                            <td style="padding: 12px 0; color: #212529; font-size: 16px; font-weight: 700;">' . htmlspecialchars($room_name) . '</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 12px 0; color: #155724; font-size: 14px; font-weight: 600;">
                                                &#128198; Check-in:
                                            </td>
                                            <td style="padding: 12px 0; color: #212529; font-size: 16px; font-weight: 700;">' . date('l, F j, Y', strtotime($checkin)) . '</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 12px 0; color: #155724; font-size: 14px; font-weight: 600;">
                                                &#9201; Check-in Time:
                                            </td>
                                            <td style="padding: 12px 0; color: #212529; font-size: 16px; font-weight: 700;">2:00 PM onwards</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 12px 0; color: #155724; font-size: 14px; font-weight: 600;">
                                                &#128197; Check-out:
                                            </td>
                                            <td style="padding: 12px 0; color: #212529; font-size: 16px; font-weight: 700;">' . date('l, F j, Y', strtotime($checkout)) . '</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 12px 0; color: #155724; font-size: 14px; font-weight: 600;">
                                                &#9200; Check-out Time:
                                            </td>
                                            <td style="padding: 12px 0; color: #212529; font-size: 16px; font-weight: 700;">Before 12:00 PM (Noon)</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 12px 0; color: #155724; font-size: 14px; font-weight: 600;">
                                                &#128337; Duration:
                                            </td>
                                            <td style="padding: 12px 0; color: #212529; font-size: 16px; font-weight: 700;">' . $nights_approve . ' ' . ($nights_approve == 1 ? 'Night' : 'Nights') . '</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                        
                        <!-- Important Check-in Requirements -->
                        <div style="background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%); border-left: 5px solid #ffc107; padding: 22px 28px; margin-bottom: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(255, 193, 7, 0.2);">
                            <h4 style="margin: 0 0 15px 0; color: #856404; font-size: 17px; font-weight: 700;">
                                &#128221; Check-in Requirements
                            </h4>
                            <ul style="margin: 0; padding-left: 20px; color: #856404; font-size: 14px; line-height: 2;">
                                <li><strong>Valid Government-Issued ID</strong> (Driver\'s License, Passport, or National ID)</li>
                                <li><strong>Payment Confirmation</strong> receipt or reference number</li>
                                <li><strong>This confirmation email</strong> (printed or digital copy)</li>
                                <li>Arrive between <strong>2:00 PM - 6:00 PM</strong> for smooth check-in</li>
                            </ul>
                        </div>
                        
                        <!-- What to Expect -->
                        <div style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); border-left: 5px solid #2196F3; padding: 22px 28px; margin-bottom: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(33, 150, 243, 0.15);">
                            <h4 style="margin: 0 0 15px 0; color: #0d47a1; font-size: 17px; font-weight: 700;">
                                &#127775; What to Expect During Your Stay
                            </h4>
                            <ul style="margin: 0; padding-left: 20px; color: #1565c0; font-size: 14px; line-height: 2;">
                                <li>24/7 security and front desk assistance</li>
                                <li>Clean and comfortable accommodations</li>
                                <li>High-speed WiFi connectivity</li>
                                <li>Complimentary toiletries and linens</li>
                                <li>Access to common areas and facilities</li>
                            </ul>
                        </div>
                        
                        <div style="text-align: center; padding: 25px 0; border-top: 2px solid #e9ecef; margin-top: 30px;">
                            <p style="margin: 0 0 10px 0; color: #28a745; font-size: 20px; font-weight: 700;">
                                We can\'t wait to host you! &#128522;
                            </p>
                            <p style="margin: 0; color: #6c757d; font-size: 14px;">
                                For any questions or special requests, please contact us anytime.
                            </p>
                        </div>';
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
                    $emailSubject = 'Welcome! Check-in Confirmed - BarCIE International Center';

                    // Calculate remaining nights
                    $today_checkin = new DateTime();
                    $checkout_date_checkin = new DateTime($checkout);
                    $remaining_duration = $today_checkin->diff($checkout_date_checkin);
                    $remaining_nights = $remaining_duration->days;

                    $emailContent = '
                        <div style="text-align: center; margin-bottom: 35px;">
                            <div style="display: inline-block; padding: 15px 35px; background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); color: white; border-radius: 50px; font-size: 16px; font-weight: 700; box-shadow: 0 6px 20px rgba(23, 162, 184, 0.4);">
                                &#10004; CHECK-IN CONFIRMED
                            </div>
                        </div>
                        
                        <div style="text-align: center; margin-bottom: 30px;">
                            <span style="font-size: 64px;">&#127968;</span>
                        </div>
                        
                        <h2 style="margin: 0 0 15px 0; color: #17a2b8; font-size: 28px; font-weight: 700; text-align: center;">Welcome to BarCIE International Center!</h2>
                        <p style="margin: 0 0 30px 0; color: #6c757d; font-size: 14px; text-align: center;">
                            Checked in on ' . date('F j, Y \a\t g:i A') . '
                        </p>
                        
                        <p style="margin: 0 0 20px 0; color: #495057; font-size: 16px; line-height: 1.6;">
                            Dear <strong style="color: #1e3c72;">' . htmlspecialchars($guest_name) . '</strong>,
                        </p>
                        <p style="margin: 0 0 30px 0; color: #495057; font-size: 15px; line-height: 1.7;">
                            Welcome! You have been successfully checked in. We hope you have a comfortable and enjoyable stay with us. Our team is here to ensure your experience is exceptional!
                        </p>
                        
                        <!-- Stay Details Card -->
                        <table role="presentation" style="width: 100%; border-collapse: collapse; background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%); border-radius: 10px; margin-bottom: 25px; border: 3px solid #17a2b8; box-shadow: 0 4px 15px rgba(23, 162, 184, 0.2);" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="padding: 30px;">
                                    <h3 style="margin: 0 0 20px 0; color: #0c5460; font-size: 20px; font-weight: 700; text-align: center; border-bottom: 2px solid #17a2b8; padding-bottom: 12px;">
                                        Your Stay Information
                                    </h3>
                                    <table role="presentation" style="width: 100%; border-collapse: collapse;" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td style="padding: 12px 0; color: #0c5460; font-size: 14px; font-weight: 600; width: 45%;">
                                                &#127970; Room/Facility:
                                            </td>
                                            <td style="padding: 12px 0; color: #212529; font-size: 16px; font-weight: 700;">' . htmlspecialchars($room_name) . '</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 12px 0; color: #0c5460; font-size: 14px; font-weight: 600;">
                                                &#128197; Check-out Date:
                                            </td>
                                            <td style="padding: 12px 0; color: #212529; font-size: 16px; font-weight: 700;">' . date('l, F j, Y', strtotime($checkout)) . '</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 12px 0; color: #0c5460; font-size: 14px; font-weight: 600;">
                                                &#9200; Check-out Time:
                                            </td>
                                            <td style="padding: 12px 0; color: #212529; font-size: 16px; font-weight: 700;">Before 12:00 PM (Noon)</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 12px 0; color: #0c5460; font-size: 14px; font-weight: 600;">
                                                &#128337; Remaining Nights:
                                            </td>
                                            <td style="padding: 12px 0; color: #212529; font-size: 16px; font-weight: 700;">' . $remaining_nights . ' ' . ($remaining_nights == 1 ? 'Night' : 'Nights') . '</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                        
                        <!-- Facilities & Services -->
                        <div style="background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%); border-left: 5px solid #4caf50; padding: 22px 28px; margin-bottom: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(76, 175, 80, 0.15);">
                            <h4 style="margin: 0 0 15px 0; color: #2e7d32; font-size: 17px; font-weight: 700;">
                                &#127775; Available Facilities & Services
                            </h4>
                            <ul style="margin: 0; padding-left: 20px; color: #2e7d32; font-size: 14px; line-height: 2;">
                                <li><strong>24/7 Front Desk</strong> - Always available to assist you</li>
                                <li><strong>WiFi Access</strong> - High-speed internet throughout the facility</li>
                                <li><strong>Common Areas</strong> - Lounge, reading areas, and recreational spaces</li>
                                <li><strong>Housekeeping</strong> - Daily cleaning service (request at front desk)</li>
                                <li><strong>Security</strong> - Round-the-clock security personnel on duty</li>
                            </ul>
                        </div>
                        
                        <!-- Important Reminders -->
                        <div style="background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%); border-left: 5px solid #ffc107; padding: 22px 28px; margin-bottom: 25px; border-radius: 8px;">
                            <h4 style="margin: 0 0 15px 0; color: #856404; font-size: 17px; font-weight: 700;">
                                &#128221; Important Reminders During Your Stay
                            </h4>
                            <ul style="margin: 0; padding-left: 20px; color: #856404; font-size: 14px; line-height: 2;">
                                <li>Check-out time is <strong>12:00 PM (Noon)</strong> - Late check-out subject to availability</li>
                                <li>Keep your key card safe - Lost cards incur a replacement fee</li>
                                <li>Respect quiet hours: <strong>10:00 PM - 7:00 AM</strong></li>
                                <li>No smoking inside rooms or indoor facilities</li>
                                <li>Report any maintenance issues to the front desk immediately</li>
                                <li>Keep your valuables secured - Use the room safe if available</li>
                            </ul>
                        </div>
                        
                        <!-- Need Assistance -->
                        <div style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); border-left: 5px solid #2196F3; padding: 20px 25px; margin-bottom: 25px; border-radius: 8px;">
                            <p style="margin: 0 0 10px 0; color: #0d47a1; font-size: 15px; font-weight: 700;">
                                &#128222; Need Assistance?
                            </p>
                            <p style="margin: 0; color: #1565c0; font-size: 14px; line-height: 1.7;">
                                Our front desk is available 24/7 for any assistance you may need. Don\'t hesitate to reach out for recommendations, directions, or any special requests. We\'re here to make your stay comfortable!
                            </p>
                        </div>
                        
                        <div style="text-align: center; padding: 25px 0; border-top: 2px solid #e9ecef; margin-top: 30px;">
                            <p style="margin: 0 0 10px 0; color: #17a2b8; font-size: 20px; font-weight: 700;">
                                Enjoy Your Stay! &#127881;
                            </p>
                            <p style="margin: 0; color: #6c757d; font-size: 14px;">
                                Make yourself at home and let us know if you need anything.
                            </p>
                        </div>';
                    break;

                case 'checkout':
                    $emailSubject = 'Thank You for Your Stay - BarCIE International Center';

                    // Calculate total nights stayed
                    $checkin_date_out = new DateTime($checkin);
                    $checkout_date_out = new DateTime($checkout);
                    $stay_duration = $checkin_date_out->diff($checkout_date_out);
                    $total_nights = $stay_duration->days;

                    $emailContent = '
                        <div style="text-align: center; margin-bottom: 35px;">
                            <div style="display: inline-block; padding: 15px 35px; background: linear-gradient(135deg, #6f42c1 0%, #9b59b6 100%); color: white; border-radius: 50px; font-size: 16px; font-weight: 700; box-shadow: 0 6px 20px rgba(111, 66, 193, 0.4);">
                                &#10004; CHECK-OUT COMPLETE
                            </div>
                        </div>
                        
                        <div style="text-align: center; margin-bottom: 30px;">
                            <span style="font-size: 64px;">&#127775;</span>
                        </div>
                        
                        <h2 style="margin: 0 0 15px 0; color: #6f42c1; font-size: 28px; font-weight: 700; text-align: center;">Thank You for Staying With Us!</h2>
                        <p style="margin: 0 0 30px 0; color: #6c757d; font-size: 14px; text-align: center;">
                            Checked out on ' . date('F j, Y \a\t g:i A') . '
                        </p>
                        
                        <p style="margin: 0 0 20px 0; color: #495057; font-size: 16px; line-height: 1.6;">
                            Dear <strong style="color: #1e3c72;">' . htmlspecialchars($guest_name) . '</strong>,
                        </p>
                        <p style="margin: 0 0 30px 0; color: #495057; font-size: 15px; line-height: 1.7;">
                            Your check-out has been processed successfully. It was our pleasure to host you at BarCIE International Center. We hope you had a comfortable and memorable stay!
                        </p>
                        
                        <!-- Visit Summary Card -->
                        <table role="presentation" style="width: 100%; border-collapse: collapse; background: linear-gradient(135deg, #e2d9f3 0%, #d6c1f0 100%); border-radius: 10px; margin-bottom: 25px; border: 3px solid #6f42c1; box-shadow: 0 4px 15px rgba(111, 66, 193, 0.2);" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="padding: 30px;">
                                    <h3 style="margin: 0 0 20px 0; color: #4a148c; font-size: 20px; font-weight: 700; text-align: center; border-bottom: 2px solid #6f42c1; padding-bottom: 12px;">
                                        Your Visit Summary
                                    </h3>
                                    <table role="presentation" style="width: 100%; border-collapse: collapse;" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td style="padding: 12px 0; color: #4a148c; font-size: 14px; font-weight: 600; width: 45%;">
                                                &#127970; Room/Facility:
                                            </td>
                                            <td style="padding: 12px 0; color: #212529; font-size: 16px; font-weight: 700;">' . htmlspecialchars($room_name) . '</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 12px 0; color: #4a148c; font-size: 14px; font-weight: 600;">
                                                &#128198; Check-in Date:
                                            </td>
                                            <td style="padding: 12px 0; color: #212529; font-size: 16px; font-weight: 700;">' . date('F j, Y', strtotime($checkin)) . '</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 12px 0; color: #4a148c; font-size: 14px; font-weight: 600;">
                                                &#128197; Check-out Date:
                                            </td>
                                            <td style="padding: 12px 0; color: #212529; font-size: 16px; font-weight: 700;">' . date('F j, Y', strtotime($checkout)) . '</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 12px 0; color: #4a148c; font-size: 14px; font-weight: 600;">
                                                &#128337; Total Nights:
                                            </td>
                                            <td style="padding: 12px 0; color: #212529; font-size: 16px; font-weight: 700;">' . $total_nights . ' ' . ($total_nights == 1 ? 'Night' : 'Nights') . '</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                        
                        <!-- Feedback Request -->
                        <div style="background: linear-gradient(135deg, #fff9e6 0%, #ffe7b8 100%); border-left: 5px solid #ff9800; padding: 22px 28px; margin-bottom: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(255, 152, 0, 0.15);">
                            <h4 style="margin: 0 0 15px 0; color: #e65100; font-size: 17px; font-weight: 700;">
                                &#11088; We Value Your Feedback!
                            </h4>
                            <p style="margin: 0 0 12px 0; color: #e65100; font-size: 14px; line-height: 1.7;">
                                Your experience matters to us! Please take a moment to share your thoughts about your stay. Your feedback helps us continuously improve our services and facilities.
                            </p>
                            <p style="margin: 0; color: #e65100; font-size: 13px; font-style: italic;">
                                You can reply to this email with your comments, suggestions, or any concerns you may have had during your visit.
                            </p>
                        </div>
                        
                        <!-- Future Bookings -->
                        <div style="background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%); border-left: 5px solid #4caf50; padding: 22px 28px; margin-bottom: 25px; border-radius: 8px;">
                            <h4 style="margin: 0 0 15px 0; color: #2e7d32; font-size: 17px; font-weight: 700;">
                                &#127873; Planning Another Visit?
                            </h4>
                            <p style="margin: 0 0 12px 0; color: #2e7d32; font-size: 14px; line-height: 1.7;">
                                We would be delighted to host you again! Book your next stay with us and experience the same quality service and comfort you enjoyed this time.
                            </p>
                            <ul style="margin: 0; padding-left: 20px; color: #2e7d32; font-size: 14px; line-height: 1.8;">
                                <li><strong>Returning Guest Perks:</strong> Priority booking for your preferred rooms</li>
                                <li><strong>Special Offers:</strong> Exclusive discounts for repeat guests</li>
                                <li><strong>Easy Booking:</strong> Contact us directly for faster reservations</li>
                            </ul>
                        </div>
                        
                        <!-- Receipt & Records -->
                        <div style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); border-left: 5px solid #2196F3; padding: 20px 25px; margin-bottom: 25px; border-radius: 8px;">
                            <p style="margin: 0 0 10px 0; color: #0d47a1; font-size: 15px; font-weight: 700;">
                                &#128220; Receipt & Records
                            </p>
                            <p style="margin: 0; color: #1565c0; font-size: 14px; line-height: 1.7;">
                                This email serves as your check-out confirmation. For any billing inquiries or to request a detailed receipt, please contact us with your booking reference number.
                            </p>
                        </div>
                        
                        <!-- Lost & Found -->
                        <div style="background-color: #fff3cd; border-left: 5px solid #ffc107; padding: 20px 25px; margin-bottom: 25px; border-radius: 8px;">
                            <p style="margin: 0 0 10px 0; color: #856404; font-size: 15px; font-weight: 700;">
                                &#128269; Lost Something?
                            </p>
                            <p style="margin: 0; color: #856404; font-size: 14px; line-height: 1.7;">
                                If you left any personal belongings behind, please contact our front desk as soon as possible. We keep lost items for 30 days.
                            </p>
                        </div>
                        
                        <div style="text-align: center; padding: 30px 0; border-top: 2px solid #e9ecef; margin-top: 30px;">
                            <p style="margin: 0 0 15px 0; color: #6f42c1; font-size: 22px; font-weight: 700;">
                                Thank You for Choosing BarCIE! &#128591;
                            </p>
                            <p style="margin: 0 0 10px 0; color: #495057; font-size: 15px; line-height: 1.6;">
                                It was our pleasure to serve you. We hope to see you again soon!
                            </p>
                            <p style="margin: 0; color: #6c757d; font-size: 14px;">
                                Safe travels and best wishes! &#127796;
                            </p>
                        </div>';
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
            // Prepare refreshed room list and room events to allow frontend to update without full reload
            $roomList = [];
            $items_q = "SELECT id, name, room_number, item_type FROM items ORDER BY name ASC";
            $items_r = $conn->query($items_q);
            if ($items_r && $items_r->num_rows > 0) {
                while ($it = $items_r->fetch_assoc()) {
                    $roomList[] = [
                        'id' => (int) $it['id'],
                        'name' => $it['name'],
                        'room_number' => $it['room_number'],
                        'item_type' => $it['item_type']
                    ];
                }
            }

            // Build room events (limited range to past 1 year -> next 1 year)
            $roomEvents = [];
            $bookings_q = "SELECT b.*, i.name as item_name, i.item_type, i.room_number FROM bookings b LEFT JOIN items i ON b.room_id = i.id WHERE b.status IN ('approved', 'confirmed', 'checked_in', 'checked_out', 'pending') AND b.checkin >= DATE_SUB(CURDATE(), INTERVAL 365 DAY) AND b.checkin <= DATE_ADD(CURDATE(), INTERVAL 365 DAY) ORDER BY b.checkin ASC";
            $bookings_r = $conn->query($bookings_q);
            if ($bookings_r && $bookings_r->num_rows > 0) {
                while ($bk = $bookings_r->fetch_assoc()) {
                    $item_name = $bk['item_name'] ? $bk['item_name'] : 'Unassigned Room/Facility';
                    $room_number = $bk['room_number'] ? '#' . $bk['room_number'] : '';
                    $item_type = $bk['item_type'] ?: 'room';
                    $guest = 'Guest';
                    $status = $bk['status'];
                    $display_title = $item_name . ' ' . $room_number . ' - ' . $guest;
                    $color = '#28a745';
                    if ($status == 'checked_in')
                        $color = '#0d6efd';
                    if ($status == 'checked_out')
                        $color = '#6f42c1';
                    if ($status == 'pending')
                        $color = '#fd7e14';

                    $roomEvents[] = [
                        'id' => 'booking-' . $bk['id'],
                        'title' => $display_title,
                        'start' => $bk['checkin'],
                        'end' => date('Y-m-d', strtotime($bk['checkout'] . ' +1 day')),
                        'backgroundColor' => $color,
                        'borderColor' => $color,
                        'textColor' => '#ffffff',
                        'extendedProps' => [
                            'itemName' => $item_name,
                            'roomNumber' => $bk['room_number'] ?: '',
                            'itemType' => $item_type,
                            'guest' => $guest,
                            'status' => $status,
                            'checkin' => $bk['checkin'],
                            'checkout' => $bk['checkout'],
                            'roomId' => $bk['room_id'] ? (int) $bk['room_id'] : null
                        ]
                    ];
                }
            }

            echo json_encode([
                'success' => true,
                'message' => "Booking #$bookingId updated to $newStatus successfully.",
                'status' => $newStatus,
                'roomList' => $roomList,
                'roomEvents' => $roomEvents
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


