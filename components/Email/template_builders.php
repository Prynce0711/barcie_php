<?php

declare(strict_types=1);

if (!function_exists('email_safe')) {
    function email_safe(?string $value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('email_format_date')) {
    function email_format_date(?string $value, string $format = 'F j, Y'): string
    {
        if (empty($value)) {
            return 'N/A';
        }

        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return email_safe($value);
        }

        return date($format, $timestamp);
    }
}

if (!function_exists('build_admin_booking_update_email')) {
    function build_admin_booking_update_email(string $action, array $data): ?array
    {
        $guestName = email_safe($data['guest_name'] ?? 'Guest');
        $roomName = email_safe($data['room_name'] ?? 'N/A');
        $checkin = email_format_date($data['checkin'] ?? null, 'l, F j, Y');
        $checkout = email_format_date($data['checkout'] ?? null, 'l, F j, Y');

        $nights = 0;
        if (!empty($data['checkin']) && !empty($data['checkout'])) {
            $in = strtotime((string) $data['checkin']);
            $out = strtotime((string) $data['checkout']);
            if ($in !== false && $out !== false && $out > $in) {
                $nights = (int) floor(($out - $in) / 86400);
            }
        }

        $details = '
            <table role="presentation" style="width:100%; border-collapse:collapse; background:#f8f9fa; border-radius:8px; margin:16px 0;" cellpadding="0" cellspacing="0">
                <tr><td style="padding:18px;">
                    <p style="margin:0 0 8px 0;"><strong>Room/Facility:</strong> ' . $roomName . '</p>
                    <p style="margin:0 0 8px 0;"><strong>Check-in:</strong> ' . $checkin . '</p>
                    <p style="margin:0 0 8px 0;"><strong>Check-out:</strong> ' . $checkout . '</p>
                    <p style="margin:0;"><strong>Duration:</strong> ' . $nights . ' ' . ($nights === 1 ? 'Night' : 'Nights') . '</p>
                </td></tr>
            </table>';

        switch ($action) {
            case 'approve':
                return [
                    'subject' => 'Booking Approved - BarCIE International Center',
                    'title' => 'Booking Approved',
                    'footer' => 'This is an automated message. Please do not reply directly to this email.',
                    'content' => '
                        <h2 style="margin:0 0 14px 0; color:#28a745;">Your Reservation Is Confirmed</h2>
                        <p>Dear <strong>' . $guestName . '</strong>,</p>
                        <p>Your reservation has been approved. We look forward to welcoming you.</p>
                        ' . $details . '
                        <p>Please bring a valid government-issued ID and this email on check-in day.</p>'
                ];
            case 'reject':
                return [
                    'subject' => 'Booking Status Update - BarCIE International Center',
                    'title' => 'Booking Not Approved',
                    'footer' => 'This is an automated message. Please do not reply directly to this email.',
                    'content' => '
                        <h2 style="margin:0 0 14px 0; color:#dc3545;">Reservation Update</h2>
                        <p>Dear <strong>' . $guestName . '</strong>,</p>
                        <p>We are unable to approve your reservation at this time.</p>
                        ' . $details . '
                        <p>Please contact us if you want to check alternative dates.</p>'
                ];
            case 'checkin':
                return [
                    'subject' => 'Welcome! Check-in Confirmed - BarCIE International Center',
                    'title' => 'Check-in Confirmed',
                    'footer' => 'This is an automated message. Please do not reply directly to this email.',
                    'content' => '
                        <h2 style="margin:0 0 14px 0; color:#17a2b8;">Welcome to BarCIE International Center</h2>
                        <p>Dear <strong>' . $guestName . '</strong>,</p>
                        <p>Your check-in is confirmed. We hope you enjoy your stay.</p>
                        ' . $details
                ];
            case 'checkout':
                return [
                    'subject' => 'Thank You for Your Stay - BarCIE International Center',
                    'title' => 'Check-out Complete',
                    'footer' => 'This is an automated message. Please do not reply directly to this email.',
                    'content' => '
                        <h2 style="margin:0 0 14px 0; color:#6f42c1;">Thank You for Staying With Us</h2>
                        <p>Dear <strong>' . $guestName . '</strong>,</p>
                        <p>Your check-out has been processed. Thank you for choosing BarCIE.</p>
                        ' . $details . '
                        <p>We would appreciate your feedback and hope to host you again.</p>'
                ];
            case 'cancel':
                return [
                    'subject' => 'Booking Cancelled - BarCIE International Center',
                    'title' => 'Booking Cancellation Notice',
                    'footer' => 'This is an automated message. Please do not reply directly to this email.',
                    'content' => '
                        <h2 style="margin:0 0 14px 0; color:#fd7e14;">Booking Cancelled</h2>
                        <p>Dear <strong>' . $guestName . '</strong>,</p>
                        <p>This confirms that your reservation has been cancelled.</p>
                        ' . $details . '
                        <p>If this cancellation was not requested by you, contact us immediately.</p>'
                ];
            default:
                return null;
        }
    }
}

if (!function_exists('build_admin_payment_update_email')) {
    function build_admin_payment_update_email(string $action, array $data): ?array
    {
        $guestName = email_safe($data['guest_name'] ?? 'Guest');
        $receipt = trim((string) ($data['receipt_no'] ?? ''));
        $receiptText = $receipt !== '' ? ' for receipt <strong>' . email_safe($receipt) . '</strong>' : '';

        if ($action === 'verify') {
            return [
                'subject' => 'Payment Verified - BarCIE International Center',
                'title' => 'Payment Received',
                'footer' => 'This is an automated message. Please do not reply directly to this email.',
                'content' => '
                    <h2 style="margin:0 0 14px 0; color:#28a745;">Payment Verified</h2>
                    <p>Dear <strong>' . $guestName . '</strong>,</p>
                    <p>We have verified your payment' . $receiptText . '. Your booking will be processed accordingly.</p>'
            ];
        }

        if ($action === 'reject') {
            return [
                'subject' => 'Payment Verification Failed - BarCIE International Center',
                'title' => 'Payment Could Not Be Verified',
                'footer' => 'This is an automated message. Please do not reply directly to this email.',
                'content' => '
                    <h2 style="margin:0 0 14px 0; color:#dc3545;">Payment Not Verified</h2>
                    <p>Dear <strong>' . $guestName . '</strong>,</p>
                    <p>We could not verify your submitted payment' . $receiptText . '. Please re-submit a clearer proof of payment or contact us.</p>'
            ];
        }

        return null;
    }
}

if (!function_exists('build_booking_confirmation_email')) {
    function build_booking_confirmation_email(array $data): array
    {
        $guestName = email_safe($data['guest_name'] ?? 'Guest');
        $receiptNo = email_safe($data['receipt_no'] ?? '');
        $roomName = email_safe($data['room_name'] ?? 'N/A');
        $checkin = email_format_date($data['checkin'] ?? null, 'l, F j, Y');
        $checkout = email_format_date($data['checkout'] ?? null, 'l, F j, Y');
        $nights = (int) ($data['nights'] ?? 0);
        $occupants = (int) ($data['occupants'] ?? 1);
        $discountType = trim((string) ($data['discount_type'] ?? ''));
        $discountStatus = trim((string) ($data['discount_status'] ?? ''));
        $discountPercentage = (float) ($data['discount_percentage'] ?? 0);
        $discountAmount = (float) ($data['discount_amount'] ?? 0);
        $cancelUrl = email_safe($data['cancel_url'] ?? '');

        $discountBlock = '';
        if ($discountType !== '' && $discountStatus === 'approved') {
            $discountBlock = '
                <p style="margin:8px 0 0 0; color:#155724;">
                    <strong>Discount Applied:</strong> ' . email_safe($discountType) . ' (' . rtrim(rtrim((string) $discountPercentage, '0'), '.') . '%) - <strong>PHP ' . number_format($discountAmount, 2) . '</strong>
                </p>';
        }

        $transferNote = trim((string) ($data['transfer_note'] ?? ''));
        $transferBlock = '';
        if ($transferNote !== '') {
            $transferBlock = '
                <p style="margin:10px 0 0 0; color:#0c5460; background:#d1ecf1; padding:10px 12px; border-radius:6px;">
                    <strong>Room Update:</strong> ' . email_safe($transferNote) . '
                </p>';
        }

        $cancelBlock = '';
        if ($cancelUrl !== '') {
            $cancelBlock = '
                <p style="margin-top:20px;">
                    Need to cancel? <a href="' . $cancelUrl . '">Cancel your booking here</a>.
                </p>';
        }

        return [
            'subject' => 'Booking Confirmation - BarCIE International Center',
            'title' => 'Booking Confirmation',
            'footer' => 'This is an automated message. Please do not reply directly to this email.',
            'content' => '
                <h2 style="margin:0 0 14px 0; color:#2a5298;">Reservation Received</h2>
                <p>Dear <strong>' . $guestName . '</strong>,</p>
                <p>Thank you for choosing BarCIE International Center. Your reservation request has been received and is pending payment verification.</p>
                <table role="presentation" style="width:100%; border-collapse:collapse; background:#f8f9fa; border-radius:8px; margin:16px 0;" cellpadding="0" cellspacing="0">
                    <tr><td style="padding:18px;">
                        <p style="margin:0 0 8px 0;"><strong>Receipt #:</strong> ' . $receiptNo . '</p>
                        <p style="margin:0 0 8px 0;"><strong>Room/Facility:</strong> ' . $roomName . '</p>
                        <p style="margin:0 0 8px 0;"><strong>Check-in:</strong> ' . $checkin . '</p>
                        <p style="margin:0 0 8px 0;"><strong>Check-out:</strong> ' . $checkout . '</p>
                        <p style="margin:0 0 8px 0;"><strong>Duration:</strong> ' . $nights . ' ' . ($nights === 1 ? 'Night' : 'Nights') . '</p>
                        <p style="margin:0;"><strong>Occupants:</strong> ' . $occupants . ' ' . ($occupants === 1 ? 'Guest' : 'Guests') . '</p>
                        ' . $discountBlock . '
                        ' . $transferBlock . '
                    </td></tr>
                </table>
                <p>Once payment is verified, you will receive a final approval email.</p>
                ' . $cancelBlock
        ];
    }
}

if (!function_exists('build_discount_admin_notification_email')) {
    function build_discount_admin_notification_email(array $data): array
    {
        $proofPath = trim((string) ($data['discount_proof_path'] ?? ''));
        $proofBlock = '';
        if ($proofPath !== '') {
            $safeProof = email_safe($proofPath);
            $proofBlock = '<p><b>Proof:</b> <a href="' . $safeProof . '">View Proof</a></p>';
        }

        return [
            'subject' => 'New Discount Application - ' . email_safe($data['discount_type'] ?? ''),
            'title' => 'New Discount Application',
            'footer' => 'Please review this discount application in the admin portal.',
            'content' => '
                <h3 style="margin:0 0 14px 0; color:#2d7be5;">Discount Application Details</h3>
                <p><b>Guest:</b> ' . email_safe($data['guest_name'] ?? '') . '</p>
                <p><b>Email:</b> ' . email_safe($data['email'] ?? '') . '</p>
                <p><b>Contact:</b> ' . email_safe($data['contact'] ?? '') . '</p>
                <p><b>Room/Facility:</b> ' . email_safe($data['room_name'] ?? '') . '</p>
                <p><b>Check-in:</b> ' . email_safe($data['checkin'] ?? '') . '</p>
                <p><b>Check-out:</b> ' . email_safe($data['checkout'] ?? '') . '</p>
                <p><b>Discount Type:</b> ' . email_safe($data['discount_type'] ?? '') . '</p>
                <p><b>Discount Details:</b> ' . email_safe($data['discount_details'] ?? '') . '</p>
                ' . $proofBlock
        ];
    }
}

if (!function_exists('build_simple_pencil_booking_received_email')) {
    function build_simple_pencil_booking_received_email(array $data): array
    {
        return [
            'subject' => 'BarCIE Pencil Booking Confirmation',
            'title' => 'Pencil Booking Received',
            'footer' => 'This is an automated message. Please do not reply directly to this email.',
            'content' => '
                <p>Dear Guest,</p>
                <p>Your pencil booking request has been received. Here are your details:</p>
                <p><strong>Facility:</strong> ' . email_safe($data['room_name'] ?? '') . '</p>
                <p><strong>Date:</strong> ' . email_safe($data['pencil_date'] ?? '') . '</p>
                <p><strong>Event:</strong> ' . email_safe($data['event'] ?? '') . '</p>
                <p><strong>Pax:</strong> ' . email_safe((string) ($data['pax'] ?? '')) . '</p>
                <p>We will review your booking and notify you once it is confirmed.</p>'
        ];
    }
}

if (!function_exists('build_draft_reservation_email')) {
    function build_draft_reservation_email(array $data): array
    {
        $guestName = email_safe($data['guest_name'] ?? 'Guest');
        $receiptNo = email_safe($data['receipt_no'] ?? '');
        $roomName = email_safe($data['room_name'] ?? 'N/A');
        $checkin = email_format_date($data['checkin'] ?? null, 'F j, Y g:i A');
        $checkout = email_format_date($data['checkout'] ?? null, 'F j, Y g:i A');
        $occupants = (int) ($data['occupants'] ?? 1);
        $totalPrice = (float) ($data['total_price'] ?? 0);
        $expiresAt = email_safe($data['expires_at'] ?? '');
        $conversionLink = email_safe($data['conversion_link'] ?? '');
        $qrLink = email_safe($data['qr_link'] ?? '');
        $cancelLink = email_safe($data['cancel_link'] ?? '');

        return [
            'subject' => 'Draft Reservation Confirmation - BarCIE International Center',
            'title' => 'Draft Reservation (Pencil Booking)',
            'footer' => 'This is an automated reminder. Please respond within 14 days to confirm your reservation.',
            'content' => '
                <p>Dear <strong>' . $guestName . '</strong>,</p>
                <p>Your draft reservation has been created. To secure your slot, confirm and complete payment within 14 days.</p>
                <p><strong>Confirmation Deadline:</strong> ' . $expiresAt . '</p>
                <p><a href="' . $conversionLink . '">Convert Draft to Full Reservation</a></p>
                <table role="presentation" style="width:100%; border-collapse:collapse; background:#f8f9fa; border-radius:8px; margin:16px 0;" cellpadding="0" cellspacing="0">
                    <tr><td style="padding:18px;">
                        <p style="margin:0 0 8px 0;"><strong>Pencil Booking #:</strong> ' . $receiptNo . '</p>
                        <p style="margin:0 0 8px 0;"><strong>Room/Facility:</strong> ' . $roomName . '</p>
                        <p style="margin:0 0 8px 0;"><strong>Check-in:</strong> ' . $checkin . '</p>
                        <p style="margin:0 0 8px 0;"><strong>Check-out:</strong> ' . $checkout . '</p>
                        <p style="margin:0 0 8px 0;"><strong>Occupants:</strong> ' . $occupants . '</p>
                        <p style="margin:0;"><strong>Estimated Price:</strong> PHP ' . number_format($totalPrice, 2) . '</p>
                    </td></tr>
                </table>
                <p>Payment QR code: <a href="' . $qrLink . '">Open QR page</a></p>
                <p>Need to cancel? <a href="' . $cancelLink . '">Cancel pencil booking</a></p>'
        ];
    }
}

if (!function_exists('build_pencil_status_email')) {
    function build_pencil_status_email(string $status, array $data): ?array
    {
        $guestName = email_safe($data['guest_name'] ?? 'Guest');
        $receiptNo = email_safe($data['receipt_no'] ?? '');

        if ($status === 'confirmed') {
            return [
                'subject' => 'Pencil Booking Confirmed - BarCIE International Center',
                'title' => 'Pencil Booking Confirmed',
                'footer' => '',
                'content' => '
                    <p>Dear <strong>' . $guestName . '</strong>,</p>
                    <p>Your pencil booking has been confirmed.</p>
                    <p><strong>Booking Number:</strong> ' . $receiptNo . '</p>
                    <p>We look forward to welcoming you at BarCIE International Center.</p>'
            ];
        }

        if ($status === 'rejected') {
            return [
                'subject' => 'Pencil Booking Update - BarCIE International Center',
                'title' => 'Pencil Booking Update',
                'footer' => '',
                'content' => '
                    <p>Dear <strong>' . $guestName . '</strong>,</p>
                    <p>Your pencil booking (<strong>' . $receiptNo . '</strong>) could not be confirmed at this time.</p>
                    <p>Please contact us for alternative dates or available rooms.</p>'
            ];
        }

        return null;
    }
}

if (!function_exists('build_cancellation_confirmation_email')) {
    function build_cancellation_confirmation_email(array $data): array
    {
        $receiptNo = email_safe($data['receipt_no'] ?? '');
        $guestName = email_safe($data['guest_name'] ?? 'Guest');
        $bookingType = email_safe($data['booking_type'] ?? 'Booking');

        return [
            'subject' => 'Booking Cancellation Confirmation - BarCIE International Center',
            'title' => 'Booking Cancellation',
            'footer' => 'This is an automated confirmation message.',
            'content' => '
                <h2 style="margin:0 0 14px 0; color:#dc3545;">Cancellation Confirmed</h2>
                <p>Dear <strong>' . $guestName . '</strong>,</p>
                <p>Your ' . $bookingType . ' has been cancelled successfully.</p>
                <p><strong>Receipt Number:</strong> ' . $receiptNo . '</p>
                <p><strong>Cancellation Date:</strong> ' . date('F j, Y g:i A') . '</p>
                <p>If you made a payment, our team will process your refund based on policy.</p>'
        ];
    }
}
