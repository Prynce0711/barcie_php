<?php

declare(strict_types=1);

// POST handlers are loaded in a fixed order.
require_once __DIR__ . '/../handlers/post/create_booking.php';
require_once __DIR__ . '/../handlers/post/submit_feedback.php';
require_once __DIR__ . '/../handlers/post/room_feedback.php';
require_once __DIR__ . '/../handlers/post/approve_reject_feedback.php';
require_once __DIR__ . '/../handlers/post/admin_update_booking.php';
require_once __DIR__ . '/../handlers/post/admin_update_discount.php';
require_once __DIR__ . '/../handlers/post/admin_update_payment.php';
require_once __DIR__ . '/../handlers/post/get_booking_details_payment.php';
require_once __DIR__ . '/../handlers/post/admin_delete_user.php';
require_once __DIR__ . '/../handlers/post/get_booking_details_admin.php';
require_once __DIR__ . '/../handlers/post/get_pencil_booking_details.php';
require_once __DIR__ . '/../handlers/post/update_pencil_booking_status.php';
require_once __DIR__ . '/../handlers/post/request_cancellation.php';
require_once __DIR__ . '/../handlers/post/checkout_booking.php';
