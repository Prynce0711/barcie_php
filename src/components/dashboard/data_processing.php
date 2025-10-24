<?php
// Data Processing File
// This file contains all the PHP logic for dashboard data processing
// Include this file in the main dashboard.php

session_start();
require __DIR__ . '/../../database/db_connect.php';

// ✅ Auth check: only admins can access
if (!isset($_SESSION['admin_id'])) {
  header("Location: index.php");
  exit;
}

// ------------------ HANDLE ITEM ADD/UPDATE/DELETE ------------------
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  if (isset($_POST['action'])) {
    $action = $_POST['action'];

    // DELETE ITEM
    if ($action === "delete" && isset($_POST['id'])) {
      $id = intval($_POST['id']);
      $stmt = $conn->prepare("SELECT image FROM items WHERE id=?");
      $stmt->bind_param("i", $id);
      $stmt->execute();
      $stmt->bind_result($img);
      $stmt->fetch();
      $stmt->close();

      if ($img && file_exists($img))
        unlink($img);

      $stmt = $conn->prepare("DELETE FROM items WHERE id=?");
      $stmt->bind_param("i", $id);
      $stmt->execute();
      $stmt->close();
      header("Location: dashboard.php#rooms");
      exit;
    }

    // UPDATE ITEM
    if ($action === "update" && isset($_POST['id'])) {
      $id = intval($_POST['id']);
      $name = $_POST['name'];
      $type = $_POST['item_type'];
      $room_number = $_POST['room_number'] ?: null;
      $description = $_POST['description'] ?: null;
      $capacity = $_POST['capacity'] ?: 0;
      $price = $_POST['price'] ?: 0;

      $image_path = $_POST['old_image'] ?? null;
      if (!empty($_FILES['image']['name'])) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir))
          mkdir($target_dir, 0777, true);
        $target_file = $target_dir . time() . "_" . basename($_FILES["image"]["name"]);
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
          $image_path = $target_file;
          if (!empty($_POST['old_image']) && file_exists($_POST['old_image']))
            unlink($_POST['old_image']);
        }
      }

      $stmt = $conn->prepare("UPDATE items SET name=?, item_type=?, room_number=?, description=?, capacity=?, price=?, image=? WHERE id=?");
      $stmt->bind_param("ssssidsi", $name, $type, $room_number, $description, $capacity, $price, $image_path, $id);
      $stmt->execute();
      $stmt->close();
      header("Location: dashboard.php#rooms");
      exit;
    }

    // UPDATE BOOKING STATUS
    if ($action === "update_booking_status" && isset($_POST['booking_id']) && isset($_POST['new_status'])) {
      $booking_id = intval($_POST['booking_id']);
      $new_status = $_POST['new_status'];
      
      $stmt = $conn->prepare("UPDATE bookings SET status=? WHERE id=?");
      $stmt->bind_param("si", $new_status, $booking_id);
      $stmt->execute();
      $stmt->close();
      header("Location: dashboard.php#bookings");
      exit;
    }

    // DELETE BOOKING
    if ($action === "delete_booking" && isset($_POST['booking_id'])) {
      $booking_id = intval($_POST['booking_id']);
      
      $stmt = $conn->prepare("DELETE FROM bookings WHERE id=?");
      $stmt->bind_param("i", $booking_id);
      $stmt->execute();
      $stmt->close();
      header("Location: dashboard.php#bookings");
      exit;
    }

    // PROCESS DISCOUNT APPLICATION
    if ($action === "process_discount" && isset($_POST['discount_id']) && isset($_POST['discount_action'])) {
      $discount_id = intval($_POST['discount_id']);
      $discount_action = $_POST['discount_action'];
      
      $stmt = $conn->prepare("UPDATE discount_applications SET status=? WHERE id=?");
      $stmt->bind_param("si", $discount_action, $discount_id);
      $stmt->execute();
      $stmt->close();
      header("Location: dashboard.php#bookings");
      exit;
    }
  }

  // ADD ITEM
  if (isset($_POST['add_item'])) {
    $name = $_POST['name'];
    $type = $_POST['item_type'];
    $room_number = $_POST['room_number'] ?: null;
    $description = $_POST['description'] ?: null;
    $capacity = $_POST['capacity'] ?: 0;
    $price = $_POST['price'] ?: 0;

    $image_path = null;
    if (!empty($_FILES['image']['name'])) {
      $target_dir = "uploads/";
      if (!file_exists($target_dir))
        mkdir($target_dir, 0777, true);
      $target_file = $target_dir . time() . "_" . basename($_FILES["image"]["name"]);
      if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        $image_path = $target_file;
      }
    }

    $stmt = $conn->prepare("INSERT INTO items (name, item_type, room_number, description, capacity, price, image, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssssids", $name, $type, $room_number, $description, $capacity, $price, $image_path);
    $stmt->execute();
    $stmt->close();
    header("Location: dashboard.php#rooms");
    exit;
  }
}

// ------------------ DASHBOARD DATA ------------------
// Total Rooms
$total_rooms_result = $conn->query("SELECT COUNT(*) AS count FROM items WHERE item_type='room'");
$total_rooms = $total_rooms_result->fetch_assoc()['count'];

// Total Facilities
$total_facilities_result = $conn->query("SELECT COUNT(*) AS count FROM items WHERE item_type='facility'");
$total_facilities = $total_facilities_result->fetch_assoc()['count'];

// Active Bookings
$active_bookings = $conn->query("SELECT COUNT(*) AS count FROM bookings WHERE status='approved'")->fetch_assoc()['count'];

// Pending Approvals
$pending_approvals = $conn->query("SELECT COUNT(*) AS count FROM bookings WHERE status='pending'")->fetch_assoc()['count'];

// Total Revenue (assuming you have a price/payment system)
$total_revenue_result = $conn->query("SELECT SUM(CAST(SUBSTRING_INDEX(details, 'Price: P', -1) AS DECIMAL(10,2))) as revenue FROM bookings WHERE status='approved'");
$total_revenue = $total_revenue_result->fetch_assoc()['revenue'] ?? 0;

// Monthly bookings for chart (last 12 months)
$monthly_bookings = [];
for ($i = 11; $i >= 0; $i--) {
  $month = date('Y-m', strtotime("-$i months"));
  $month_name = date('M Y', strtotime("-$i months"));
  $result = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE DATE_FORMAT(created_at, '%Y-%m') = '$month'");
  $count = $result ? $result->fetch_assoc()['count'] : 0;
  $monthly_bookings[] = ['month' => $month_name, 'count' => (int) $count];
}

// Booking status distribution
$status_distribution = [];
$statuses = ['pending', 'approved', 'confirmed', 'checked_in', 'checked_out', 'cancelled', 'rejected'];
foreach ($statuses as $status) {
  $result = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status='$status'");
  $count = $result ? $result->fetch_assoc()['count'] : 0;
  $status_distribution[$status] = (int) $count;
}

// Additional booking statistics
$total_bookings = array_sum($status_distribution);
$active_bookings_count = $status_distribution['approved'] + $status_distribution['confirmed'] + $status_distribution['checked_in'];
$pending_bookings_count = $status_distribution['pending'];
$completed_bookings_count = $status_distribution['checked_out'];

// Recent Activities (no user join needed since we removed user_id)
$recent_activity_result = $conn->query("SELECT b.type, b.details, b.created_at 
    FROM bookings b 
    ORDER BY b.created_at DESC LIMIT 8");
$recent_activities = [];
while ($row = $recent_activity_result->fetch_assoc()) {
  $recent_activities[] = $row;
}

// Feedback Statistics
$feedback_stats_result = $conn->query("SELECT 
    COUNT(*) as total_feedback,
    COALESCE(AVG(rating), 0) as avg_rating,
    COUNT(CASE WHEN rating = 5 THEN 1 END) as five_star,
    COUNT(CASE WHEN rating = 4 THEN 1 END) as four_star,
    COUNT(CASE WHEN rating = 3 THEN 1 END) as three_star,
    COUNT(CASE WHEN rating = 2 THEN 1 END) as two_star,
    COUNT(CASE WHEN rating = 1 THEN 1 END) as one_star
    FROM feedback");
$feedback_stats = $feedback_stats_result ? $feedback_stats_result->fetch_assoc() : [
  'total_feedback' => 0,
  'avg_rating' => 0,
  'five_star' => 0,
  'four_star' => 0,
  'three_star' => 0,
  'two_star' => 0,
  'one_star' => 0
];

// Calendar Events for JavaScript
$events = [];
$bookings_query = "SELECT b.*, i.name as item_name, i.item_type, i.room_number
                 FROM bookings b 
                 LEFT JOIN items i ON b.room_id = i.id
                 WHERE b.status IN ('approved', 'confirmed', 'checked_in', 'checked_out', 'pending')
                 AND b.checkin >= CURDATE() - INTERVAL 7 DAY
                 AND b.checkin <= CURDATE() + INTERVAL 30 DAY
                 ORDER BY b.checkin ASC";
$bookings_result = $conn->query($bookings_query);

$room_events = [];
if ($bookings_result && $bookings_result->num_rows > 0) {
  while ($booking = $bookings_result->fetch_assoc()) {
    $item_name = $booking['item_name'] ? $booking['item_name'] : 'Unassigned Room/Facility';
    $room_number = $booking['room_number'] ? '#' . $booking['room_number'] : '';
    $item_type = $booking['item_type'] ?: 'room';
    $guest = 'Guest';
    $status = $booking['status'];
    $display_title = $item_name . $room_number . ' - ' . $guest;
    
    // Color based on status
    $color = '#28a745'; // green for approved/confirmed
    if ($status == 'checked_in') $color = '#0d6efd'; // blue (primary)
    if ($status == 'checked_out') $color = '#6f42c1'; // purple
    if ($status == 'pending') $color = '#fd7e14'; // orange (warning)

    $room_events[] = [
      'id' => 'booking-' . $booking['id'],
      'title' => $display_title,
      'start' => $booking['checkin'],
      'end' => date('Y-m-d', strtotime($booking['checkout'] . ' +1 day')),
      'backgroundColor' => $color,
      'borderColor' => $color,
      'textColor' => '#ffffff',
      'extendedProps' => [
        'itemName' => $item_name,
        'roomNumber' => $booking['room_number'] ?: '',
        'itemType' => $item_type,
        'guest' => $guest,
        'status' => $status,
        'checkin' => $booking['checkin'],
        'checkout' => $booking['checkout'],
        'roomId' => $booking['room_id'] ?: null
      ]
    ];
  }
}
?>