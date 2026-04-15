<?php

declare(strict_types=1);

date_default_timezone_set('Asia/Manila');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../../../database/db_connect.php';

function change_room_page_extract_detail_value(string $details, string $label): string
{
    $pattern = '/\b' . preg_quote($label, '/') . ':\s*([^|]+)/i';
    if (preg_match($pattern, $details, $m)) {
        return trim((string) ($m[1] ?? ''));
    }
    return '';
}

$bookingId = isset($_GET['booking_id']) ? (int) $_GET['booking_id'] : 0;
$email = trim((string) ($_GET['email'] ?? ''));
$receipt = trim((string) ($_GET['receipt'] ?? ''));

$projectBasePath = defined('APP_BASE_PATH') ? rtrim((string) APP_BASE_PATH, '/') : '';

// This page can be accessed directly under /components/guest/Booking, where
// APP_BASE_PATH may incorrectly include /components/guest. Normalize it to the
// project root so form actions always target /<project>/database/...
if ($projectBasePath !== '' && strpos($projectBasePath, '/components/') !== false) {
    $normalizedBasePath = preg_replace('#/components/.*$#', '', $projectBasePath);
    if (is_string($normalizedBasePath)) {
        $projectBasePath = rtrim($normalizedBasePath, '/');
    }
}

if ($projectBasePath === '') {
    $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $scriptDir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
    $normalizedFromScript = preg_replace('#/components/.*$#', '', $scriptDir);
    if (is_string($normalizedFromScript) && $normalizedFromScript !== '') {
        $projectBasePath = rtrim($normalizedFromScript, '/');
    }
}

$userAuthEndpointUrl = ($projectBasePath !== '' ? $projectBasePath : '') . '/database/index.php?endpoint=user_auth';

$flashMessage = '';
$flashSuccess = false;
if (!empty($_SESSION['booking_msg'])) {
    $flashMessage = (string) $_SESSION['booking_msg'];
    $flashSuccess = stripos($flashMessage, 'updated') !== false || stripos($flashMessage, 'success') !== false;
    unset($_SESSION['booking_msg']);
}

$errorMessage = '';
$booking = null;
$suggestedRooms = [];

if ($bookingId <= 0) {
    $errorMessage = 'Invalid booking link. Missing booking ID.';
} else {
    $bookingStmt = $conn->prepare(
        'SELECT b.id, b.receipt_no, b.room_id, b.details, b.checkin, b.checkout,
                i.name AS room_name, i.room_number, i.item_type
         FROM bookings b
         LEFT JOIN items i ON b.room_id = i.id
         WHERE b.id = ?
         LIMIT 1'
    );

    if (!$bookingStmt) {
        $errorMessage = 'Unable to load booking details.';
    } else {
        $bookingStmt->bind_param('i', $bookingId);
        $bookingStmt->execute();
        $bookingRes = $bookingStmt->get_result();
        $booking = $bookingRes ? $bookingRes->fetch_assoc() : null;
        $bookingStmt->close();

        if (!$booking) {
            $errorMessage = 'Booking not found.';
        }
    }

    if ($booking) {
        $details = (string) ($booking['details'] ?? '');
        $storedEmail = change_room_page_extract_detail_value($details, 'Email');

        if ($email === '' || strcasecmp($storedEmail, $email) !== 0) {
            $errorMessage = 'This link does not match the booking email.';
        }

        $storedReceipt = (string) ($booking['receipt_no'] ?? '');
        if ($errorMessage === '' && $receipt !== '' && strcasecmp($storedReceipt, $receipt) !== 0) {
            $errorMessage = 'This link does not match the booking receipt.';
        }
    }

    if ($booking && $errorMessage === '') {
        $roomId = (int) ($booking['room_id'] ?? 0);
        $roomName = (string) ($booking['room_name'] ?? '');
        $itemType = (string) ($booking['item_type'] ?? 'room');
        $checkin = (string) ($booking['checkin'] ?? '');
        $checkout = (string) ($booking['checkout'] ?? '');

        $suggestStmt = $conn->prepare(
            "SELECT i.id, i.name, i.room_number, i.capacity, i.price
             FROM items i
             WHERE i.id <> ?
               AND i.name = ?
               AND i.item_type = ?
               AND NOT EXISTS (
                   SELECT 1
                   FROM bookings b2
                   WHERE b2.room_id = i.id
                     AND b2.id <> ?
                     AND b2.status IN ('confirmed', 'approved', 'pending', 'checked_in')
                                         AND DATE(b2.checkin) <= DATE(?)
                                         AND DATE(b2.checkout) >= DATE(?)
               )
             ORDER BY i.room_number ASC, i.id ASC
             LIMIT 10"
        );

        if ($suggestStmt) {
            $suggestStmt->bind_param('ississ', $roomId, $roomName, $itemType, $bookingId, $checkout, $checkin);
            $suggestStmt->execute();
            $suggestRes = $suggestStmt->get_result();

            while ($suggestRes && ($row = $suggestRes->fetch_assoc())) {
                $suggestedRooms[] = $row;
            }

            $suggestStmt->close();
        }
    }
}

$roomDisplay = '';
$guestName = '';
$contactNumber = '';
if ($booking) {
    $roomDisplay = (string) ($booking['room_name'] ?? '');
    if (!empty($booking['room_number'])) {
        $roomDisplay .= ' #' . (string) $booking['room_number'];
    }

    $details = (string) ($booking['details'] ?? '');
    $guestName = change_room_page_extract_detail_value($details, 'Guest');
    $contactNumber = change_room_page_extract_detail_value($details, 'Contact');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Another Room Number</title>
    <style>
        :root {
            --bg-top: #eff9ff;
            --bg-bottom: #dce9ff;
            --card: #ffffff;
            --ink: #0e2a47;
            --muted: #4d6480;
            --brand: #0a7ea4;
            --brand-strong: #0a5f83;
            --danger: #b42318;
            --success: #157347;
            --border: rgba(10, 126, 164, 0.25);
            --shadow: 0 20px 44px rgba(15, 43, 70, 0.16);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, sans-serif;
            color: var(--ink);
            background: linear-gradient(140deg, var(--bg-top), var(--bg-bottom));
            min-height: 100vh;
            display: flex;
            justify-content: center;
            padding: 30px 14px;
        }

        .wrapper {
            width: min(980px, 100%);
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 18px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .hero {
            padding: 28px 24px 18px;
            background:
                radial-gradient(circle at 88% -10%, rgba(10, 126, 164, 0.2), transparent 45%),
                linear-gradient(180deg, #ffffff, #f8fcff);
            border-bottom: 1px solid var(--border);
        }

        .hero h1 {
            margin: 0;
            font-size: clamp(1.25rem, 2.2vw, 1.8rem);
            line-height: 1.25;
        }

        .hero p {
            margin: 10px 0 0;
            color: var(--muted);
            max-width: 70ch;
        }

        .content {
            padding: 22px 24px 28px;
        }

        .alert {
            border-radius: 12px;
            padding: 12px 14px;
            margin-bottom: 16px;
            border: 1px solid transparent;
            font-size: 0.95rem;
        }

        .alert.success {
            background: rgba(21, 115, 71, 0.08);
            border-color: rgba(21, 115, 71, 0.35);
            color: var(--success);
        }

        .alert.error {
            background: rgba(180, 35, 24, 0.09);
            border-color: rgba(180, 35, 24, 0.32);
            color: var(--danger);
        }

        .booking-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px 16px;
            padding: 14px;
            background: #f8fbff;
            border: 1px solid var(--border);
            border-radius: 12px;
            margin-bottom: 18px;
        }

        .booking-grid div {
            font-size: 0.94rem;
        }

        .booking-grid strong {
            display: block;
            color: var(--muted);
            font-size: 0.8rem;
            margin-bottom: 3px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .rooms {
            display: grid;
            gap: 12px;
            margin: 14px 0 20px;
        }

        .room-option {
            border: 1px solid rgba(10, 126, 164, 0.28);
            border-radius: 12px;
            background: #ffffff;
            transition: border-color 0.18s ease, box-shadow 0.18s ease, transform 0.12s ease;
        }

        .room-option:hover {
            border-color: rgba(10, 126, 164, 0.56);
            box-shadow: 0 10px 22px rgba(10, 126, 164, 0.16);
            transform: translateY(-1px);
        }

        .room-option label {
            display: flex;
            gap: 10px;
            padding: 12px 14px;
            cursor: pointer;
        }

        .room-option input[type="radio"] {
            margin-top: 4px;
            accent-color: var(--brand);
        }

        .room-main {
            font-weight: 700;
            margin-bottom: 3px;
        }

        .room-meta {
            color: var(--muted);
            font-size: 0.9rem;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        .btn {
            border: 0;
            border-radius: 10px;
            padding: 12px 16px;
            cursor: pointer;
            font-weight: 700;
            letter-spacing: 0.01em;
            transition: transform 0.12s ease, box-shadow 0.2s ease;
        }

        .btn.primary {
            background: linear-gradient(135deg, var(--brand), var(--brand-strong));
            color: #fff;
            box-shadow: 0 10px 24px rgba(10, 95, 131, 0.28);
        }

        .btn.primary:hover {
            transform: translateY(-1px);
        }

        .btn.link {
            background: #eef6ff;
            color: var(--ink);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        @media (max-width: 760px) {
            .booking-grid {
                grid-template-columns: 1fr;
            }

            .hero,
            .content {
                padding-left: 16px;
                padding-right: 16px;
            }
        }
    </style>
</head>

<body>
    <main class="wrapper">
        <section class="hero">
            <h1>Choose Another Room Number</h1>
            <p>Your original room number became unavailable because another booking for the same schedule was approved
                first. Select a new room number below to continue your booking.</p>
        </section>

        <section class="content">
            <?php if ($flashMessage !== ''): ?>
                <div class="alert <?php echo $flashSuccess ? 'success' : 'error'; ?>">
                    <?php echo htmlspecialchars($flashMessage, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <?php if ($errorMessage !== ''): ?>
                <div class="alert error"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php elseif ($booking): ?>
                <div class="booking-grid">
                    <div>
                        <strong>Receipt</strong>
                        <span><?php echo htmlspecialchars((string) ($booking['receipt_no'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    <div>
                        <strong>Guest</strong>
                        <span><?php echo htmlspecialchars($guestName !== '' ? $guestName : 'Guest', ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    <div>
                        <strong>Current Room</strong>
                        <span><?php echo htmlspecialchars($roomDisplay, ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    <div>
                        <strong>Contact</strong>
                        <span><?php echo htmlspecialchars($contactNumber !== '' ? $contactNumber : 'N/A', ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    <div>
                        <strong>Check-in</strong>
                        <span><?php echo htmlspecialchars((string) ($booking['checkin'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    <div>
                        <strong>Check-out</strong>
                        <span><?php echo htmlspecialchars((string) ($booking['checkout'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                </div>

                <?php if (empty($suggestedRooms)): ?>
                    <div class="alert error">No same-room alternatives are available right now for your selected dates. Please
                        contact the admin for assistance.</div>
                <?php else: ?>
                    <form method="POST" action="<?php echo htmlspecialchars($userAuthEndpointUrl, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="action" value="change_conflict_room">
                        <input type="hidden" name="booking_id" value="<?php echo (int) $bookingId; ?>">
                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="receipt"
                            value="<?php echo htmlspecialchars($receipt, ENT_QUOTES, 'UTF-8'); ?>">

                        <div class="rooms">
                            <?php foreach ($suggestedRooms as $idx => $room): ?>
                                <?php
                                $displayName = (string) ($room['name'] ?? 'Room');
                                if (!empty($room['room_number'])) {
                                    $displayName .= ' #' . (string) $room['room_number'];
                                }
                                ?>
                                <div class="room-option">
                                    <label>
                                        <input type="radio" name="selected_room_id" value="<?php echo (int) ($room['id'] ?? 0); ?>"
                                            <?php echo $idx === 0 ? 'checked' : ''; ?> required>
                                        <div>
                                            <div class="room-main">
                                                <?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?></div>
                                            <div class="room-meta">Capacity: <?php echo (int) ($room['capacity'] ?? 0); ?> | Rate:
                                                PHP <?php echo number_format((float) ($room['price'] ?? 0), 2); ?></div>
                                        </div>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="actions">
                            <button type="submit" class="btn primary">Confirm New Room Number</button>
                        </div>
                    </form>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert error">Booking details are unavailable.</div>
            <?php endif; ?>
        </section>
    </main>
</body>

</html>
