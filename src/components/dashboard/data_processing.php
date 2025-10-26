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
      
      // Get the image path before deleting
      $stmt = $conn->prepare("SELECT image FROM items WHERE id=?");
      $stmt->bind_param("i", $id);
      $stmt->execute();
      $stmt->bind_result($img);
      $stmt->fetch();
      $stmt->close();

      // Delete the image file if it exists
      if ($img) {
        $image_full_path = __DIR__ . "/../../../" . $img;
        if (file_exists($image_full_path)) {
          unlink($image_full_path);
          error_log("Deleted image file: $image_full_path");
        }
      }

      // Delete the database record
      $stmt = $conn->prepare("DELETE FROM items WHERE id=?");
      $stmt->bind_param("i", $id);
      
      if ($stmt->execute()) {
        error_log("Item deleted successfully: ID=$id");
        $_SESSION['success_message'] = "Item deleted successfully!";
      } else {
        error_log("Failed to delete item: " . $stmt->error);
        $_SESSION['error_message'] = "Failed to delete item: " . $stmt->error;
      }
      
      $stmt->close();
      header("Location: dashboard.php#rooms");
      exit;
    }

    // UPDATE ITEM
    if ($action === "update" && isset($_POST['id'])) {
      $id = intval($_POST['id']);
      $name = trim($_POST['name']);
      $type = trim($_POST['item_type']);
      $room_number = !empty($_POST['room_number']) ? trim($_POST['room_number']) : null;
      $description = !empty($_POST['description']) ? trim($_POST['description']) : null;
      $capacity = intval($_POST['capacity'] ?? 0);
      $price = floatval($_POST['price'] ?? 0);

      // Get current image path from database first
      $current_image_stmt = $conn->prepare("SELECT image FROM items WHERE id=?");
      $current_image_stmt->bind_param("i", $id);
      $current_image_stmt->execute();
      $current_image_stmt->bind_result($current_image);
      $current_image_stmt->fetch();
      $current_image_stmt->close();

      $image_path = $current_image; // Use current image as default
      
      // Handle new image upload
      if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        // Security: Validate file size (max 5MB)
        $max_file_size = 5 * 1024 * 1024; // 5MB in bytes
        if ($_FILES['image']['size'] > $max_file_size) {
          error_log("File too large: " . $_FILES['image']['size'] . " bytes");
          $_SESSION['error_message'] = "Image file is too large. Maximum size is 5MB.";
          header("Location: dashboard.php#rooms");
          exit;
        }
        
        $target_dir = __DIR__ . "/../../../uploads/";
        if (!file_exists($target_dir)) {
          mkdir($target_dir, 0755, true); // More secure permissions
        }
        
        // Generate unique filename
        $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($file_extension, $allowed_extensions)) {
          // Security: Verify it's actually an image using getimagesize
          $image_info = @getimagesize($_FILES["image"]["tmp_name"]);
          if ($image_info === false) {
            error_log("Invalid image file - not a real image");
            $_SESSION['error_message'] = "Invalid image file. Please upload a valid image.";
            header("Location: dashboard.php#rooms");
            exit;
          }
          
          // Security: Verify MIME type
          $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
          if (!in_array($image_info['mime'], $allowed_mime_types)) {
            error_log("Invalid MIME type: " . $image_info['mime']);
            $_SESSION['error_message'] = "Invalid image format. Please upload JPG, PNG, GIF, or WebP.";
            header("Location: dashboard.php#rooms");
            exit;
          }
          
          $unique_filename = time() . "_" . uniqid() . "." . $file_extension;
          $target_file = $target_dir . $unique_filename;
          
          if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            // Security: Set restrictive file permissions
            chmod($target_file, 0644);
            
            // Store relative path from root
            $image_path = "uploads/" . $unique_filename;
            
            // Delete old image if exists and is different
            if (!empty($current_image) && $current_image !== $image_path) {
              $old_image_full_path = __DIR__ . "/../../../" . $current_image;
              if (file_exists($old_image_full_path)) {
                unlink($old_image_full_path);
                error_log("Deleted old image: $old_image_full_path");
              }
            }
            
            error_log("New image uploaded: $image_path");
          } else {
            error_log("Failed to move uploaded file to: $target_file");
            $_SESSION['error_message'] = "Failed to upload image. Please try again.";
            header("Location: dashboard.php#rooms");
            exit;
          }
        } else {
          error_log("Invalid file extension: $file_extension");
          $_SESSION['error_message'] = "Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.";
          header("Location: dashboard.php#rooms");
          exit;
        }
      }

      // Update the database
      $stmt = $conn->prepare("UPDATE items SET name=?, item_type=?, room_number=?, description=?, capacity=?, price=?, image=? WHERE id=?");
      $stmt->bind_param("ssssidsi", $name, $type, $room_number, $description, $capacity, $price, $image_path, $id);
      
      if ($stmt->execute()) {
        error_log("Item updated successfully: ID=$id, Name=$name, Type=$type, Capacity=$capacity, Price=$price, Image=$image_path");
        $_SESSION['success_message'] = "Item updated successfully!";
      } else {
        error_log("Failed to update item: " . $stmt->error);
        $_SESSION['error_message'] = "Failed to update item: " . $stmt->error;
      }
      
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
    // DEBUG: Log incoming POST and FILES data
    error_log("=== ADD ITEM DEBUG START ===");
    error_log("POST data: " . print_r($_POST, true));
    error_log("FILES data: " . print_r($_FILES, true));
    error_log("Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'not set'));
    
    $name = trim($_POST['name']);
    $type = trim($_POST['item_type']);
    $room_number = !empty($_POST['room_number']) ? trim($_POST['room_number']) : null;
    $description = !empty($_POST['description']) ? trim($_POST['description']) : null;
    $capacity = intval($_POST['capacity'] ?? 0);
    $price = floatval($_POST['price'] ?? 0);

    // Handle image upload
    $image_path = null;
    error_log("Checking for image upload...");
    
    if (empty($_FILES['image']['name'])) {
      error_log("No image file uploaded - FILES[image][name] is empty");
    } elseif ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
      error_log("Image upload error code: " . $_FILES['image']['error']);
      $upload_errors = [
        UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
        UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
        UPLOAD_ERR_PARTIAL => 'File partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temp directory',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write to disk',
        UPLOAD_ERR_EXTENSION => 'PHP extension stopped upload'
      ];
      error_log("Upload error: " . ($upload_errors[$_FILES['image']['error']] ?? 'Unknown error'));
    }
    
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
      error_log("Image file received: " . $_FILES['image']['name'] . " (" . $_FILES['image']['size'] . " bytes)");
      
      // Security: Validate file size (max 5MB)
      $max_file_size = 5 * 1024 * 1024; // 5MB in bytes
      if ($_FILES['image']['size'] > $max_file_size) {
        error_log("File too large: " . $_FILES['image']['size'] . " bytes");
        $_SESSION['error_message'] = "Image file is too large. Maximum size is 5MB.";
        header("Location: dashboard.php#rooms");
        exit;
      }
      
      $target_dir = __DIR__ . "/../../../uploads/";
      if (!file_exists($target_dir)) {
        mkdir($target_dir, 0755, true); // More secure permissions
      }
      
      // Generate unique filename
      $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
      $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
      
      if (in_array($file_extension, $allowed_extensions)) {
        // Security: Verify it's actually an image using getimagesize
        $image_info = @getimagesize($_FILES["image"]["tmp_name"]);
        if ($image_info === false) {
          error_log("Invalid image file - not a real image");
          $_SESSION['error_message'] = "Invalid image file. Please upload a valid image.";
          header("Location: dashboard.php#rooms");
          exit;
        }
        
        // Security: Verify MIME type
        $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($image_info['mime'], $allowed_mime_types)) {
          error_log("Invalid MIME type: " . $image_info['mime']);
          $_SESSION['error_message'] = "Invalid image format. Please upload JPG, PNG, GIF, or WebP.";
          header("Location: dashboard.php#rooms");
          exit;
        }
        
        $unique_filename = time() . "_" . uniqid() . "." . $file_extension;
        $target_file = $target_dir . $unique_filename;
        
        error_log("Attempting to move uploaded file from: " . $_FILES["image"]["tmp_name"] . " to: " . $target_file);
        
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
          // Security: Set restrictive file permissions
          chmod($target_file, 0644);
          
          // Store relative path from root
          $image_path = "uploads/" . $unique_filename;
          error_log("Image uploaded successfully: $image_path (full path: $target_file)");
        } else {
          error_log("Failed to move uploaded file to: $target_file");
          error_log("Target directory exists: " . (is_dir($target_dir) ? 'YES' : 'NO'));
          error_log("Target directory writable: " . (is_writable($target_dir) ? 'YES' : 'NO'));
          error_log("Temp file exists: " . (file_exists($_FILES["image"]["tmp_name"]) ? 'YES' : 'NO'));
          $_SESSION['error_message'] = "Failed to upload image. Please try again.";
          header("Location: dashboard.php#rooms");
          exit;
        }
      } else {
        error_log("Invalid file extension: $file_extension");
        $_SESSION['error_message'] = "Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.";
        header("Location: dashboard.php#rooms");
        exit;
      }
    }

    // Insert with default room_status = 'available'
    error_log("Preparing to insert item with image_path: " . ($image_path ?? 'NULL'));
    
    $stmt = $conn->prepare("INSERT INTO items (name, item_type, room_number, description, capacity, price, image, room_status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'available', NOW())");
    $stmt->bind_param("ssssids", $name, $type, $room_number, $description, $capacity, $price, $image_path);
    
    if ($stmt->execute()) {
      $new_item_id = $conn->insert_id;
      error_log("New item added successfully: ID=$new_item_id, Name=$name, Type=$type, Capacity=$capacity, Price=$price, Image=$image_path");
      error_log("=== ADD ITEM DEBUG END (SUCCESS) ===");
      $_SESSION['success_message'] = "Item added successfully!";
    } else {
      error_log("Failed to insert item: " . $stmt->error);
      error_log("=== ADD ITEM DEBUG END (FAILED) ===");
      $_SESSION['error_message'] = "Failed to add item: " . $stmt->error;
    }
    
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