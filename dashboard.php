<?php
// dashboard.php
session_start();
require __DIR__ . '/database/db_connect.php';

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

// Calendar Events
$events = [];
$result = $conn->query("SELECT * FROM bookings ORDER BY id DESC");
while ($row = $result->fetch_assoc()) {
  $events[] = [
    'id' => $row['id'],
    'title' => "Room " . $row['details'] . " (" . $row['type'] . ")",
    'start' => $row['checkin'],
    'end' => $row['checkout'],
    'status' => $row['status']
  ];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/png" href="assets/images/imageBg/barcie_logo.jpg">
  <title>Admin Dashboard</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <!-- Chart.js -->
  <!-- FullCalendar CSS & JS -->
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="assets/css/dashboard.css">
</head>


<body>

  <!-- Dark Mode Toggle -->
  <button class="dark-toggle" onclick="toggleDarkMode()">
    <i class="fas fa-moon"></i>
  </button>

  <!-- Mobile Menu Toggle -->
  <button class="mobile-menu-toggle d-lg-none" onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
  </button>

  <!-- Sidebar -->
  <div class="sidebar">
    <h2><i class="fas fa-hotel me-2"></i>Hotel Admin</h2>
    <a href="#" class="nav-link-custom" data-section="dashboard-section">
      <i class="fas fa-tachometer-alt me-2"></i>Dashboard
    </a>
    <a href="#" class="nav-link-custom" data-section="calendar-section">
      <i class="fas fa-calendar-check me-2"></i>Calendar & Items
    </a>
    <a href="#" class="nav-link-custom" data-section="rooms">
      <i class="fas fa-door-open me-2"></i>Rooms & Facilities
    </a>
    <a href="#" class="nav-link-custom" data-section="bookings">
      <i class="fas fa-calendar-alt me-2"></i>Bookings
    </a>

    <a href="index.php" class="btn btn-danger mt-3">
      <i class="fas fa-sign-out-alt me-2"></i>Logout
    </a>
  </div>


  <!-- Main Content -->
  <div class="main-content">
    <div class="container-fluid px-2" style="max-width: 100%;">
      <div class="row">
        <div class="col-12">

        </div>
      </div>

      <!-- Dashboard Section -->
      <section id="dashboard-section" class="content-section active">

        <!-- Welcome Header -->
        <div class="row mb-4">
          <div class="col-12">
            <div class="card bg-gradient-primary text-white">
              <div class="card-body">
                <div class="row align-items-center">
                  <div class="col-md-8">
                    <h3 class="card-title mb-2">
                      <i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard
                    </h3>
                    <p class="card-text mb-0 opacity-90">
                      Welcome back! Here's an overview of your hotel management system.
                    </p>
                    <small class="opacity-75">
                      <i class="fas fa-clock me-1"></i>Last updated: <?php echo date('M d, Y - H:i'); ?>
                    </small>
                  </div>
                  <div class="col-md-4 text-center">
                    <i class="fas fa-hotel fa-4x opacity-75"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Key Performance Metrics -->
        <div class="row g-4 mb-4">
          <div class="col-xl-3 col-lg-6">
            <div class="card bg-gradient-primary text-white h-100 border-0 shadow">
              <div class="card-body">
                <div class="row align-items-center">
                  <div class="col">
                    <div class="text-xs mb-2 opacity-75">Total Inventory</div>
                    <div class="h4 mb-1 fw-bold"><?php echo $total_rooms + $total_facilities; ?></div>
                    <div class="text-xs opacity-75">
                      <i class="fas fa-bed me-1"></i><?php echo $total_rooms; ?> rooms
                      <span class="mx-1">•</span>
                      <i class="fas fa-building me-1"></i><?php echo $total_facilities; ?> facilities
                    </div>
                  </div>
                  <div class="col-auto">
                    <div class="icon-circle bg-white bg-opacity-25">
                      <i class="fas fa-building fa-lg"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-lg-6">
            <div class="card bg-gradient-success text-white h-100 border-0 shadow">
              <div class="card-body">
                <div class="row align-items-center">
                  <div class="col">
                    <div class="text-xs mb-2 opacity-75">Active Bookings</div>
                    <div class="h4 mb-1 fw-bold"><?php echo $active_bookings; ?></div>
                    <div class="text-xs opacity-75">
                      <i class="fas fa-calendar-check me-1"></i>Currently occupied
                    </div>
                  </div>
                  <div class="col-auto">
                    <div class="icon-circle bg-white bg-opacity-25">
                      <i class="fas fa-calendar-check fa-lg"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-lg-6">
            <div class="card bg-gradient-warning text-white h-100 border-0 shadow">
              <div class="card-body">
                <div class="row align-items-center">
                  <div class="col">
                    <div class="text-xs mb-2 opacity-75">Guest Satisfaction</div>
                    <div class="h4 mb-1 fw-bold">
                      <?php echo number_format($feedback_stats['avg_rating'], 1); ?>/5.0
                    </div>
                    <div class="text-xs opacity-75">
                      <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i
                          class="fas fa-star <?php echo $i <= round($feedback_stats['avg_rating']) ? '' : 'opacity-50'; ?>"></i>
                      <?php endfor; ?>
                    </div>
                  </div>
                  <div class="col-auto">
                    <div class="icon-circle bg-white bg-opacity-25">
                      <i class="fas fa-star fa-lg"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-lg-6">
            <div class="card bg-gradient-info text-white h-100 border-0 shadow">
              <div class="card-body">
                <div class="row align-items-center">
                  <div class="col">
                    <div class="text-xs mb-2 opacity-75">Total Reviews</div>
                    <div class="h4 mb-1 fw-bold"><?php echo $feedback_stats['total_feedback']; ?></div>
                    <div class="text-xs opacity-75">
                      <i class="fas fa-thumbs-up me-1"></i><?php echo $feedback_stats['five_star']; ?> five-star
                    </div>
                  </div>
                  <div class="col-auto">
                    <div class="icon-circle bg-white bg-opacity-25">
                      <i class="fas fa-comments fa-lg"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Actions Panel -->
        <div class="row mb-4">
          <div class="col-12">
            <div class="card border-0 shadow-sm">
              <div class="card-header bg-white border-bottom">
                <h6 class="m-0 text-dark fw-bold">
                  <i class="fas fa-bolt me-2 text-primary"></i>Quick Actions
                </h6>
              </div>
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-lg-3 col-md-6">
                    <div class="quick-action-card" onclick="showSection('bookings')">
                      <div class="action-icon bg-primary">
                        <i class="fas fa-calendar-plus text-white"></i>
                      </div>
                      <div class="action-content">
                        <h6 class="mb-1">Manage Bookings</h6>
                        <small class="text-muted">View and update reservations</small>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-3 col-md-6">
                    <div class="quick-action-card" onclick="showSection('rooms')">
                      <div class="action-icon bg-success">
                        <i class="fas fa-plus-circle text-white"></i>
                      </div>
                      <div class="action-content">
                        <h6 class="mb-1">Add Room/Facility</h6>
                        <small class="text-muted">Create new inventory items</small>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-3 col-md-6">
                    <div class="quick-action-card" onclick="showSection('calendar-section')">
                      <div class="action-icon bg-info">
                        <i class="fas fa-calendar-alt text-white"></i>
                      </div>
                      <div class="action-content">
                        <h6 class="mb-1">View Calendar</h6>
                        <small class="text-muted">Check availability overview</small>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-3 col-md-6">
                    <div class="quick-action-card" onclick="showSection('communication')">
                      <div class="action-icon bg-warning">
                        <i class="fas fa-comments text-white"></i>
                      </div>
                      <div class="action-content">
                        <h6 class="mb-1">Guest Messages</h6>
                        <small class="text-muted">View feedback and support</small>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Analytics Dashboard -->
        <div class="row g-4 mb-4">
          <!-- Booking Trends Chart -->
          <div class="col-xl-8">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-header bg-white border-bottom">
                <div class="row align-items-center">
                  <div class="col">
                    <h6 class="m-0 text-dark fw-bold">
                      <i class="fas fa-chart-line me-2 text-primary"></i>Booking Trends
                    </h6>
                  </div>
                  <div class="col-auto">
                    <div class="btn-group btn-group-sm">
                      <button class="btn btn-outline-primary" type="button" onclick="refreshChart('7days')">7
                        Days</button>
                      <button class="btn btn-outline-primary" type="button" onclick="refreshChart('30days')">30
                        Days</button>
                      <button class="btn btn-outline-primary active" type="button"
                        onclick="refreshChart('12months')">Year</button>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-body">
                <div style="height: 300px;">
                  <canvas id="bookingsChart" width="100%" height="300"></canvas>
                </div>
              </div>
            </div>
          </div>

          <!-- Status Distribution -->
          <div class="col-xl-4">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-header bg-white border-bottom">
                <h6 class="m-0 text-dark fw-bold">
                  <i class="fas fa-chart-pie me-2 text-primary"></i>Booking Status
                </h6>
              </div>
              <div class="card-body">
                <div style="height: 200px;" class="mb-3">
                  <canvas id="statusChart" width="100%" height="200"></canvas>
                </div>
                <div class="status-legend">
                  <?php
                  $total_for_percentage = $total_bookings > 0 ? $total_bookings : 1;
                  $status_colors = [
                    'pending' => 'warning',
                    'approved' => 'success',
                    'confirmed' => 'success',
                    'checked_in' => 'info',
                    'checked_out' => 'secondary',
                    'cancelled' => 'danger',
                    'rejected' => 'danger'
                  ];

                  foreach ($status_distribution as $status => $count):
                    if ($count > 0):
                      $percentage = round(($count / $total_for_percentage) * 100, 1);
                      $color_class = $status_colors[$status] ?? 'secondary';
                      $display_name = ucfirst(str_replace('_', ' ', $status));
                      ?>
                      <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="d-flex align-items-center">
                          <div class="legend-dot bg-<?php echo $color_class; ?> me-2"></div>
                          <small><?php echo $display_name; ?></small>
                        </div>
                        <small class="text-muted fw-bold"><?php echo $percentage; ?>%</small>
                      </div>
                      <?php
                    endif;
                  endforeach;

                  if ($total_bookings == 0):
                    ?>
                    <div class="text-center text-muted">
                      <small>No bookings data</small>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Guest Satisfaction & Recent Activity -->
        <div class="row g-4">
          <!-- Guest Satisfaction Detailed -->
          <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-header bg-white border-bottom">
                <h6 class="m-0 text-dark fw-bold">
                  <i class="fas fa-chart-bar me-2 text-warning"></i>Guest Satisfaction Analysis
                </h6>
              </div>
              <div class="card-body">
                <div class="text-center mb-4">
                  <?php
                  $avg_rating = $feedback_stats['avg_rating'];
                  if ($avg_rating >= 4.5) {
                    $status = 'Excellent';
                    $color = 'success';
                    $icon = 'fa-trophy';
                  } elseif ($avg_rating >= 4.0) {
                    $status = 'Very Good';
                    $color = 'info';
                    $icon = 'fa-thumbs-up';
                  } elseif ($avg_rating >= 3.5) {
                    $status = 'Good';
                    $color = 'warning';
                    $icon = 'fa-star';
                  } elseif ($avg_rating >= 3.0) {
                    $status = 'Average';
                    $color = 'secondary';
                    $icon = 'fa-minus-circle';
                  } else {
                    $status = 'Needs Improvement';
                    $color = 'danger';
                    $icon = 'fa-exclamation-triangle';
                  }
                  ?>
                  <div
                    class="satisfaction-badge bg-<?php echo $color; ?> bg-opacity-10 text-<?php echo $color; ?> rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                    style="width: 80px; height: 80px;">
                    <i class="fas <?php echo $icon; ?> fa-2x"></i>
                  </div>
                  <h4 class="text-<?php echo $color; ?> mb-2"><?php echo $status; ?></h4>
                  <div class="mb-3">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                      <i
                        class="fas fa-star <?php echo $i <= round($avg_rating) ? 'text-warning' : 'text-muted'; ?> fa-lg"></i>
                    <?php endfor; ?>
                  </div>
                  <h3 class="text-primary mb-1"><?php echo number_format($feedback_stats['avg_rating'], 1); ?>/5.0</h3>
                  <p class="text-muted small">Based on <?php echo $feedback_stats['total_feedback']; ?> guest reviews
                  </p>
                </div>

                <!-- Rating Distribution -->
                <?php
                $total_reviews = $feedback_stats['total_feedback'];
                $ratings = [
                  5 => ['count' => $feedback_stats['five_star'], 'color' => 'success'],
                  4 => ['count' => $feedback_stats['four_star'], 'color' => 'info'],
                  3 => ['count' => $feedback_stats['three_star'], 'color' => 'warning'],
                  2 => ['count' => $feedback_stats['two_star'], 'color' => 'danger'],
                  1 => ['count' => $feedback_stats['one_star'], 'color' => 'dark']
                ];
                foreach ($ratings as $star => $data):
                  $percentage = $total_reviews > 0 ? ($data['count'] / $total_reviews * 100) : 0;
                  ?>
                  <div class="d-flex align-items-center mb-2">
                    <div class="me-2" style="width: 20px;">
                      <small class="text-muted"><?php echo $star; ?>★</small>
                    </div>
                    <div class="flex-grow-1 me-2">
                      <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-<?php echo $data['color']; ?>"
                          style="width: <?php echo $percentage; ?>%"></div>
                      </div>
                    </div>
                    <span class="text-muted small" style="width: 50px;">
                      <?php echo $data['count']; ?> (<?php echo number_format($percentage, 1); ?>%)
                    </span>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>

          <!-- Recent Activity Feed -->
          <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-header bg-white border-bottom">
                <div class="row align-items-center">
                  <div class="col">
                    <h6 class="m-0 text-dark fw-bold">
                      <i class="fas fa-clock me-2 text-primary"></i>Recent Activity
                    </h6>
                  </div>
                  <div class="col-auto">
                    <button class="btn btn-outline-primary btn-sm" onclick="location.reload()">
                      <i class="fas fa-sync-alt me-1"></i>Refresh
                    </button>
                  </div>
                </div>
              </div>
              <div class="card-body p-0">
                <div class="activity-timeline" style="max-height: 400px; overflow-y: auto;">
                  <?php if (empty($recent_activities)): ?>
                    <div class="text-center text-muted py-5">
                      <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i>
                      <h6 class="text-muted">No Recent Activity</h6>
                      <p class="small mb-0">New activities will appear here</p>
                    </div>
                  <?php else: ?>
                    <?php foreach ($recent_activities as $index => $activity): ?>
                      <div
                        class="activity-item d-flex p-3 <?php echo $index < count($recent_activities) - 1 ? 'border-bottom' : ''; ?>">
                        <div class="activity-icon me-3">
                          <div class="icon-circle bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-circle fa-xs"></i>
                          </div>
                        </div>
                        <div class="flex-grow-1">
                          <div class="activity-content">
                            <h6 class="mb-1 text-dark"><?php echo htmlspecialchars($activity['type']); ?></h6>
                            <p class="text-muted small mb-1"><?php echo htmlspecialchars($activity['details']); ?></p>
                            <div class="text-muted small">
                              <i class="fas fa-user me-1"></i>Guest •
                              <i
                                class="fas fa-clock me-1"></i><?php echo date('M d, H:i', strtotime($activity['created_at'])); ?>
                            </div>
                          </div>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <?php
      // Load booking events for calendar
      $events = [];

      $calendar_query = "SELECT b.* FROM bookings b WHERE b.status != 'rejected' ORDER BY b.id DESC";
      $result = $conn->query($calendar_query);

      if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
          $room_facility = 'Booking #' . $row['id'];

          // Try to extract guest name from details
          if (strpos($row['details'], 'Guest:') !== false) {
            $parts = explode('|', $row['details']);
            foreach ($parts as $part) {
              if (strpos($part, 'Guest:') !== false) {
                $room_facility = trim(str_replace('Guest:', '', $part));
                break;
              }
            }
          }

          $title = '';
          $color = '#007bff';

          // Status-based styling
          if ($row['status'] == 'confirmed' || $row['status'] == 'approved') {
            $title = "✅ Approved: " . $room_facility;
            $color = '#10b981';  // Green - matches success color
          } elseif ($row['status'] == 'checked_in') {
            $title = "🏠 Checked In: " . $room_facility;
            $color = '#3b82f6';  // Blue - matches info/primary color
          } elseif ($row['status'] == 'checked_out') {
            $title = "🚪 Checked Out: " . $room_facility;
            $color = '#8b5cf6';  // Purple - matches custom purple color
          } elseif ($row['status'] == 'pending') {
            $title = "⏳ Pending: " . $room_facility;
            $color = '#f59e0b';  // Orange - matches warning color
          } elseif ($row['status'] == 'cancelled' || $row['status'] == 'rejected') {
            $title = "❌ Cancelled: " . $room_facility;
            $color = '#ef4444';  // Red - matches danger color
          } else {
            $title = ucfirst($row['status']) . ": " . $room_facility;
            $color = '#6c757d';  // Gray for unknown status
          }

          // No username needed since we removed user system
          $title .= " - Guest";

          $start_date = $row['checkin'] ? $row['checkin'] : date('Y-m-d');
          $end_date = $row['checkout'] ? $row['checkout'] : date('Y-m-d', strtotime($start_date . ' +1 day'));

          $events[] = [
            'id' => 'booking-' . $row['id'],
            'title' => $title,
            'start' => $start_date,
            'end' => $end_date,
            'backgroundColor' => $color,
            'borderColor' => $color,
            'textColor' => '#ffffff'
          ];
        }
      }

      // Test event
      $events[] = [
        'id' => 'test-today',
        'title' => '🧪 Test - Today',
        'start' => date('Y-m-d'),
        'backgroundColor' => '#dc3545'
      ];
      ?>
      <script>
        // Data for dashboard charts and calendar - directly from database
        window.calendarEvents = <?php echo json_encode($events); ?>;
        window.monthlyBookingsData = <?php echo json_encode($monthly_bookings); ?>;
        window.statusDistributionData = <?php echo json_encode($status_distribution); ?>;
        window.dashboardStats = {
          totalRooms: <?php echo $total_rooms; ?>,
          totalFacilities: <?php echo $total_facilities; ?>,
          activeBookings: <?php echo $active_bookings; ?>,
          pendingApprovals: <?php echo $pending_approvals; ?>,
          totalRevenue: <?php echo $total_revenue; ?>,
          totalBookings: <?php echo $total_bookings; ?>,
          activeBookingsCount: <?php echo $active_bookings_count; ?>,
          pendingBookingsCount: <?php echo $pending_bookings_count; ?>,
          completedBookingsCount: <?php echo $completed_bookings_count; ?>,
          feedbackStats: <?php echo json_encode($feedback_stats); ?>
        };

        // Initialize dashboard when document is ready
        document.addEventListener('DOMContentLoaded', function () {
          // Set data for charts
          setDashboardData(
            window.calendarEvents,
            window.monthlyBookingsData,
            window.statusDistributionData,
            window.dashboardStats
          );
        });
      </script>



      <!-- Calendar & Rooms Section -->
      <section id="calendar-section" class="content-section">
        <div class="row mb-4c:\xampp\htdocs\barcie_php\dashboard.php">
          <div class="col-12">
            <div class="card">
              <div class="card-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                  <h5 class="mb-0">
                    <i class="fas fa-calendar-check me-2"></i>Calendar & Room/Facility Management
                  </h5>
                  <!-- Navigation tabs -->
                  <nav class="nav nav-pills" id="calendar-nav">
                    <button class="nav-link nav-link-white active" id="calendar-view-btn" data-view="calendar">
                      <i class="fas fa-calendar-alt me-1"></i>Calendar View
                    </button>
                    <button class="nav-link nav-link-white" id="room-list-btn" data-view="room-list">
                      <i class="fas fa-list me-1"></i>Room List
                    </button>
                  </nav>
                </div>
              </div>
              <div class="card-body p-0">

                <!-- Calendar View -->
                <div id="calendar-view-content" class="calendar-content">
                  <div class="p-3 border-bottom bg-light">
                    <div class="row align-items-center">
                      <div class="col-md-8">
                        <h6 class="mb-1">Room & Facility Reservation Calendar</h6>
                        <small class="text-muted">View room and facility availability and reservations status</small>
                      </div>
                      <div class="col-md-4 text-end">
                        <div class="btn-group btn-group-sm">
                          <button class="btn btn-outline-primary"
                            onclick="calendarInstance.changeView('dayGridMonth')">Month</button>
                          <button class="btn btn-outline-primary"
                            onclick="calendarInstance.changeView('timeGridWeek')">Week</button>
                          <button class="btn btn-outline-primary"
                            onclick="calendarInstance.changeView('timeGridDay')">Day</button>
                        </div>
                      </div>
                    </div>
                    <div class="mt-2">
                      <small class="me-3">
                        <span class="badge bg-success me-1">●</span>Approved/Confirmed
                      </small>
                      <small class="me-3">
                        <span class="badge bg-primary me-1">●</span>Checked-in
                      </small>
                      <small class="me-3">
                        <span class="badge bg-purple me-1">●</span>Checked-out
                      </small>
                      <small class="me-3">
                        <span class="badge bg-warning me-1">●</span>Pending
                      </small>
                      <small class="me-3">
                        <span class="badge bg-danger me-1">●</span>Cancelled
                      </small>
                      <small class="text-muted">
                        Empty days = No reservations
                      </small>
                    </div>
                  </div>
                  <div class="p-3">
                    <div id="roomCalendar"></div>
                  </div>
                </div>

                <!-- Room List View -->
                <div id="room-list-content" class="calendar-content" style="display: none;">
                  <div class="p-3 border-bottom bg-light">
                    <div class="row align-items-center">
                      <div class="col-md-8">
                        <h6 class="mb-1">Room & Facility Status Overview</h6>
                        <small class="text-muted">Current status and upcoming reservations for all rooms and
                          facilities</small>
                      </div>
                      <div class="col-md-4 text-end">
                        <div class="input-group input-group-sm">
                          <span class="input-group-text">
                            <i class="fas fa-search"></i>
                          </span>
                          <input type="text" class="form-control" placeholder="Search rooms & facilities..."
                            id="room-search">
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="room-list-container" style="max-height: 600px; overflow-y: auto;">

                    <?php
                    // Fetch all rooms AND facilities with their current booking status
                    $items_query = "SELECT * FROM items WHERE item_type IN ('room', 'facility') ORDER BY item_type DESC, room_number ASC, name ASC";
                    $items_result = $conn->query($items_query);

                    if ($items_result && $items_result->num_rows > 0) {
                      while ($item = $items_result->fetch_assoc()) {
                        // Get current reservation for this item
                        $today = date('Y-m-d');
                        $item_id = $item['id'];
                        $item_name = $item['name'];
                        $item_type = $item['item_type'];
                        $room_number = $item['room_number'] ?: 'N/A';

                        // Check for active bookings (today or ongoing)
                        $booking_query = "SELECT b.* 
                                            FROM bookings b 
                                            WHERE b.details LIKE '%$item_name%' 
                                            AND b.status IN ('approved', 'confirmed', 'checked_in') 
                                            AND DATE(b.checkin) <= '$today' 
                                            AND DATE(b.checkout) >= '$today'
                                            ORDER BY b.checkin ASC LIMIT 1";
                        $booking_result = $conn->query($booking_query);
                        $current_booking = $booking_result ? $booking_result->fetch_assoc() : null;

                        // Get next upcoming booking
                        $next_booking_query = "SELECT b.* 
                                                 FROM bookings b 
                                                 WHERE b.details LIKE '%$item_name%' 
                                                 AND b.status IN ('approved', 'confirmed', 'pending') 
                                                 AND DATE(b.checkin) > '$today'
                                                 ORDER BY b.checkin ASC LIMIT 1";
                        $next_booking_result = $conn->query($next_booking_query);
                        $next_booking = $next_booking_result ? $next_booking_result->fetch_assoc() : null;

                        // Determine status
                        $status = 'available';
                        $status_class = 'success';
                        $status_text = 'Available';
                        $status_icon = 'check-circle';

                        if ($current_booking) {
                          if ($current_booking['status'] == 'checked_in') {
                            $status = 'occupied';
                            $status_class = 'info';
                            $status_text = $item_type == 'room' ? 'Occupied' : 'In Use';
                            $status_icon = $item_type == 'room' ? 'user' : 'cog';
                          } else {
                            $status = 'reserved';
                            $status_class = 'warning';
                            $status_text = 'Reserved';
                            $status_icon = 'calendar-check';
                          }
                        } elseif (!$next_booking) {
                          $status = 'no-reservation';
                          $status_class = 'secondary';
                          $status_text = 'No Reservations';
                          $status_icon = 'calendar-times';
                        }

                        // Different icons for different types
                        $type_icon = $item_type == 'room' ? 'door-open' : 'building';
                        $type_label = ucfirst($item_type);
                        $capacity_label = $item_type == 'room' ? 'guests' : 'people';
                        $price_label = $item_type == 'room' ? '/night' : '/day';
                        ?>

                        <div class="room-card p-3 border-bottom room-item" data-room-name="<?= strtolower($item_name) ?>"
                          data-room-number="<?= strtolower($room_number) ?>" data-item-type="<?= $item_type ?>">
                          <div class="row align-items-center">
                            <div class="col-md-2">
                              <?php if ($item['image'] && file_exists($item['image'])): ?>
                                <img src="<?= htmlspecialchars($item['image']) ?>" class="img-fluid rounded"
                                  style="width: 80px; height: 60px; object-fit: cover;"
                                  alt="<?= htmlspecialchars($item['name']) ?>">
                              <?php else: ?>
                                <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                  style="width: 80px; height: 60px;">
                                  <i class="fas fa-<?= $type_icon ?> text-muted fa-2x"></i>
                                </div>
                              <?php endif; ?>
                            </div>
                            <div class="col-md-3">
                              <h6 class="mb-1">
                                <?= htmlspecialchars($item['name']) ?>
                                <small class="badge bg-primary ms-1"><?= $type_label ?></small>
                              </h6>
                              <small class="text-muted">
                                <?php if ($item_type == 'room'): ?>
                                  Room #<?= htmlspecialchars($room_number) ?> • <?= $item['capacity'] ?>
                                  <?= $capacity_label ?>
                                <?php else: ?>
                                  Facility • <?= $item['capacity'] ?>       <?= $capacity_label ?>
                                <?php endif; ?>
                              </small>
                              <div class="mt-1">
                                <small class="text-success">₱<?= number_format($item['price']) ?><?= $price_label ?></small>
                              </div>
                            </div>
                            <div class="col-md-2">
                              <span class="badge bg-<?= $status_class ?> px-3 py-2">
                                <i class="fas fa-<?= $status_icon ?> me-1"></i><?= $status_text ?>
                              </span>
                            </div>
                            <div class="col-md-5">
                              <?php if ($current_booking): ?>
                                <div class="current-booking mb-2">
                                  <strong class="text-<?= $status_class ?>">Current
                                    <?= $item_type == 'room' ? 'Guest' : 'User' ?>:</strong>
                                  <div class="small">
                                    Guest
                                    <span class="text-muted">
                                      • <?= date('M j', strtotime($current_booking['checkin'])) ?> -
                                      <?= date('M j', strtotime($current_booking['checkout'])) ?>
                                    </span>
                                  </div>
                                </div>
                              <?php endif; ?>

                              <?php if ($next_booking): ?>
                                <div class="next-booking">
                                  <strong class="text-primary">Next Reservation:</strong>
                                  <div class="small">
                                    Guest
                                    <span class="text-muted">
                                      • <?= date('M j', strtotime($next_booking['checkin'])) ?> -
                                      <?= date('M j', strtotime($next_booking['checkout'])) ?>
                                    </span>
                                  </div>
                                </div>
                              <?php elseif (!$current_booking): ?>
                                <div class="text-muted small">
                                  <i class="fas fa-calendar-times me-1"></i>No upcoming reservations
                                </div>
                              <?php endif; ?>
                            </div>
                          </div>
                        </div>

                        <?php
                      }
                    } else {
                      echo '<div class="text-center text-muted p-4">
                                <i class="fas fa-building fa-3x mb-3 opacity-50"></i>
                                <p>No rooms or facilities found</p>
                                <small>Add rooms and facilities in the Rooms & Facilities section</small>
                              </div>';
                    }
                    ?>

                  </div>
                </div>

              </div>
            </div>
          </div>
        </div>
      </section>

      <section id="rooms" class="content-section">
        <!-- Rooms & Facilities Header -->
        <div class="row mb-4">
          <div class="col-12">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
              <div class="card-body text-white">
                <div class="text-center">
                  <h2 class="mb-1"><i class="fas fa-building me-2"></i>Rooms & Facilities Management</h2>
                  <p class="mb-0 opacity-75">Manage your property inventory and amenities</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Filter Controls -->
        <div class="row mb-4">
          <div class="col-12">
            <div class="card border-0 shadow-sm">
              <div class="card-body">
                <div class="row align-items-center">
                  <div class="col-md-6">
                    <h5 class="mb-3"><i class="fas fa-filter me-2 text-primary"></i>Filter & Search</h5>
                    <div class="btn-group w-100 item-filters" role="group" aria-label="Type filter">
                      <input type="radio" class="btn-check type-filter" name="type_filter" id="filter-all" value="all"
                        checked>
                      <label class="btn btn-outline-primary" for="filter-all">
                        <i class="fas fa-list me-1"></i>All
                        <span class="badge bg-primary ms-1 type-count" data-type="all">0</span>
                      </label>

                      <input type="radio" class="btn-check type-filter" name="type_filter" id="filter-room"
                        value="room">
                      <label class="btn btn-outline-primary" for="filter-room">
                        <i class="fas fa-bed me-1"></i>Rooms
                        <span class="badge bg-primary ms-1 type-count" data-type="room">0</span>
                      </label>

                      <input type="radio" class="btn-check type-filter" name="type_filter" id="filter-facility"
                        value="facility">
                      <label class="btn btn-outline-primary" for="filter-facility">
                        <i class="fas fa-building me-1"></i>Facilities
                        <span class="badge bg-primary ms-1 type-count" data-type="facility">0</span>
                      </label>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="form-label">Search Items</label>
                      <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" id="searchItems"
                          placeholder="Search by name, room number, or description...">
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div> <!-- Items Grid -->
        <div class="row" id="items-container">
          <?php
          $res = $conn->query("SELECT * FROM items ORDER BY item_type, created_at DESC");
          while ($item = $res->fetch_assoc()): ?>
            <div class="col-lg-4 col-md-6 mb-4 item-card" data-type="<?= $item['item_type'] ?>"
              data-searchable="<?= strtolower($item['name'] . ' ' . $item['room_number'] . ' ' . $item['description']) ?>">
              <div class="card border-0 shadow-sm h-100 hover-lift">
                <!-- Item Image -->
                <div class="position-relative">
                  <?php if ($item['image'] && file_exists($item['image'])): ?>
                    <img src="<?= htmlspecialchars($item['image']) ?>" class="card-img-top"
                      style="height: 200px; object-fit: cover;" alt="<?= htmlspecialchars($item['name']) ?>">
                  <?php else: ?>
                    <div class="card-img-top d-flex align-items-center justify-content-center"
                      style="height: 200px; background: linear-gradient(45deg, #f8f9fa, #e9ecef);">
                      <i
                        class="fas fa-<?= $item['item_type'] === 'room' ? 'bed' : ($item['item_type'] === 'facility' ? 'swimming-pool' : 'concierge-bell') ?> fa-3x text-muted"></i>
                    </div>
                  <?php endif; ?>

                  <!-- Type Badge -->
                  <div class="position-absolute top-0 end-0 m-2">
                    <span
                      class="badge <?= $item['item_type'] === 'room' ? 'bg-primary' : ($item['item_type'] === 'facility' ? 'bg-success' : 'bg-info') ?> px-3 py-2">
                      <i
                        class="fas fa-<?= $item['item_type'] === 'room' ? 'bed' : ($item['item_type'] === 'facility' ? 'swimming-pool' : 'concierge-bell') ?> me-1"></i>
                      <?= ucfirst($item['item_type']) ?>
                    </span>
                  </div>
                </div>

                <!-- Item Details -->
                <div class="card-body d-flex flex-column">
                  <div class="flex-grow-1">
                    <h5 class="card-title mb-2"><?= htmlspecialchars($item['name']) ?></h5>

                    <?php if ($item['room_number']): ?>
                      <p class="text-muted mb-2">
                        <i class="fas fa-door-open me-1"></i>Room #<?= htmlspecialchars($item['room_number']) ?>
                      </p>
                    <?php endif; ?>

                    <p class="card-text text-muted small mb-3"><?= htmlspecialchars($item['description']) ?></p>

                    <div class="row text-center mb-3">
                      <div class="col-6">
                        <div class="border-end">
                          <h6 class="text-primary mb-1">₱<?= number_format($item['price']) ?></h6>
                          <small class="text-muted"><?= $item['item_type'] === 'room' ? 'per night' : 'per day' ?></small>
                        </div>
                      </div>
                      <div class="col-6">
                        <h6 class="text-success mb-1"><?= $item['capacity'] ?></h6>
                        <small class="text-muted"><?= $item['item_type'] === 'room' ? 'guests' : 'people' ?></small>
                      </div>
                    </div>
                  </div>

                  <!-- Action Buttons -->
                  <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary flex-fill edit-toggle-btn"
                      data-item-id="<?= $item['id'] ?>">
                      <i class="fas fa-edit me-1"></i>Edit
                    </button>
                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal"
                      data-bs-target="#deleteModal<?= $item['id'] ?>">
                      <i class="fas fa-trash me-1"></i>Delete
                    </button>
                  </div>

                  <!-- Hidden Edit Form -->
                  <div class="edit-form-container mt-3" id="editForm<?= $item['id'] ?>" style="display: none;">
                    <form method="POST" enctype="multipart/form-data" class="border-top pt-3">
                      <input type="hidden" name="action" value="update">
                      <input type="hidden" name="id" value="<?= $item['id'] ?>">
                      <input type="hidden" name="old_image" value="<?= $item['image'] ?>">

                      <div class="row">
                        <div class="col-12 mb-3">
                          <label class="form-label">Name</label>
                          <input type="text" class="form-control" name="name"
                            value="<?= htmlspecialchars($item['name']) ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                          <label class="form-label">Type</label>
                          <select name="item_type" class="form-select">
                            <option value="room" <?= $item['item_type'] == 'room' ? 'selected' : '' ?>>Room</option>
                            <option value="facility" <?= $item['item_type'] == 'facility' ? 'selected' : '' ?>>Facility
                            </option>
                            <option value="amenities" <?= $item['item_type'] == 'amenities' ? 'selected' : '' ?>>Amenities
                            </option>
                          </select>
                        </div>

                        <div class="col-md-6 mb-3">
                          <label class="form-label">Room Number</label>
                          <input type="text" class="form-control" name="room_number"
                            value="<?= htmlspecialchars($item['room_number']) ?>">
                        </div>

                        <div class="col-12 mb-3">
                          <label class="form-label">Description</label>
                          <textarea class="form-control" name="description"
                            rows="3"><?= htmlspecialchars($item['description']) ?></textarea>
                        </div>

                        <div class="col-md-6 mb-3">
                          <label class="form-label">Capacity</label>
                          <input type="number" class="form-control" name="capacity" value="<?= $item['capacity'] ?>"
                            required>
                        </div>

                        <div class="col-md-6 mb-3">
                          <label class="form-label">Price (₱)</label>
                          <input type="number" class="form-control" name="price" value="<?= $item['price'] ?>" required>
                        </div>

                        <div class="col-12 mb-3">
                          <label class="form-label">Change Image</label>
                          <input type="file" class="form-control" name="image" accept="image/*">
                        </div>
                      </div>

                      <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill">
                          <i class="fas fa-save me-1"></i>Update
                        </button>
                        <button type="button" class="btn btn-secondary edit-cancel-btn" data-item-id="<?= $item['id'] ?>">
                          Cancel
                        </button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>

            <!-- Delete Confirmation Modal -->
            <div class="modal fade" id="deleteModal<?= $item['id'] ?>" data-bs-backdrop="false" tabindex="-1">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    <p>Are you sure you want to delete <strong><?= htmlspecialchars($item['name']) ?></strong>?</p>
                    <p class="text-muted small">This action cannot be undone.</p>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" class="d-inline">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?= $item['id'] ?>">
                      <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          <?php endwhile; ?>
        </div>


        <!-- Add Item Modal -->
        <div class="modal fade" id="addItemModal" data-bs-backdrop="false" tabindex="-1">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">
                  <i class="fas fa-plus me-2"></i>Add New Room / Facility / Amenities
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                  <input type="hidden" name="add_item" value="1">

                  <div class="row">
                    <div class="col-12 mb-3">
                      <label class="form-label">Name <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" name="name" required>
                    </div>

                    <div class="col-md-6 mb-3">
                      <label class="form-label">Type <span class="text-danger">*</span></label>
                      <select name="item_type" class="form-select" required>
                        <option value="">Select Type</option>
                        <option value="room">Room</option>
                        <option value="facility">Facility</option>
                        
                      </select>
                    </div>

                    <div class="col-md-6 mb-3">
                      <label class="form-label">Room Number</label>
                      <input type="text" class="form-control" name="room_number" placeholder="Optional">
                    </div>

                    <div class="col-12 mb-3">
                      <label class="form-label">Description</label>
                      <textarea class="form-control" name="description" rows="3"
                        placeholder="Brief description of the room or facility"></textarea>
                    </div>

                    <div class="col-md-6 mb-3">
                      <label class="form-label">Capacity <span class="text-danger">*</span></label>
                      <input type="number" class="form-control" name="capacity" min="1" required>
                    </div>

                    <div class="col-md-6 mb-3">
                      <label class="form-label">Price (₱) <span class="text-danger">*</span></label>
                      <input type="number" class="form-control" name="price" min="0" step="1" required>
                    </div>

                    <div class="col-12 mb-3">
                      <label class="form-label">Image</label>
                      <input type="file" class="form-control" name="image" accept="image/*">
                      <div class="form-text">Optional: Upload an image for this room or facility</div>
                    </div>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                  </button>
                  <button type="submit" class="btn btn-success">
                    <i class="fas fa-plus me-1"></i>Add Item
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>

      </section>

      <!-- Bookings Management -->

      <section id="bookings" class="content-section">
        <div class="row mb-4">
          <div class="col-12">
            <div class="card">
              <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                  <i class="fas fa-calendar-alt me-2"></i>Bookings Management
                </h5>
                <small class="opacity-75">Manage all guest reservations and bookings</small>
              </div>
              <div class="card-body">
                <!-- Filter Controls -->
                <div class="row mb-3">
                  <div class="col-md-3">
                    <label class="form-label">Filter by Status:</label>
                    <select class="form-select" id="statusFilter" onchange="filterBookings()">
                      <option value="">All Statuses</option>
                      <option value="pending">Pending</option>
                      <option value="approved">Approved</option>
                      <option value="confirmed">Confirmed</option>
                      <option value="checked_in">Checked In</option>
                      <option value="checked_out">Checked Out</option>
                      <option value="cancelled">Cancelled</option>
                      <option value="rejected">Rejected</option>
                    </select>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Filter by Type:</label>
                    <select class="form-select" id="typeFilter" onchange="filterBookings()">
                      <option value="">All Types</option>
                      <option value="reservation">Reservation</option>
                      <option value="pencil">Pencil Booking</option>
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Search Guest:</label>
                    <input type="text" class="form-control" id="guestSearch"
                      placeholder="Search by guest name or contact..." onkeyup="filterBookings()">
                  </div>
                  <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button class="btn btn-outline-secondary w-100" onclick="resetFilters()">
                      <i class="fas fa-undo me-1"></i>Reset
                    </button>
                  </div>
                </div>

                <!-- Bookings Table -->
                <div class="table-responsive">
                  <table class="table table-striped table-hover" id="bookingsTable">
                    <thead class="table-dark">
                      <tr>
                        <th>Receipt #</th>
                        <th>Room/Facility</th>
                        <th>Type</th>
                        <th>Guest Details</th>
                        <th>Schedule</th>
                        <th>Booking Status</th>
                        <th>Discount Status</th>
                        <th>Created</th>
                        <th>Booking Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      // Fetch all bookings with room details
                      $bookings_query = "SELECT b.*, i.name as room_name, i.item_type, i.room_number, i.capacity, i.price 
                                       FROM bookings b 
                                       LEFT JOIN items i ON b.room_id = i.id 
                                       ORDER BY b.created_at DESC";
                      $bookings_result = $conn->query($bookings_query);

                      if ($bookings_result && $bookings_result->num_rows > 0) {
                        while ($booking = $bookings_result->fetch_assoc()) {
                          // Extract guest information from details
                          $guest_name = 'Guest';
                          $contact = '';
                          $email = '';
                          if (!empty($booking['details'])) {
                            if (preg_match('/Guest:\s*([^|]+)/', $booking['details'], $matches)) {
                              $guest_name = trim($matches[1]);
                            }
                            if (preg_match('/Contact:\s*([^|]+)/', $booking['details'], $matches)) {
                              $contact = trim($matches[1]);
                            }
                            if (preg_match('/Email:\s*([^|]+)/', $booking['details'], $matches)) {
                              $email = trim($matches[1]);
                            }
                          }

                          // Determine room/facility display
                          $room_display = $booking['room_name'] ?: 'Unknown';
                          if ($booking['room_number']) {
                            $room_display .= " (#" . $booking['room_number'] . ")";
                          }

                          // Status badge styling
                          $status_class = 'secondary';
                          switch (strtolower($booking['status'])) {
                            case 'approved':
                            case 'confirmed':
                              $status_class = 'success'; // Green
                              break;
                            case 'pending':
                              $status_class = 'warning'; // Orange
                              break;
                            case 'checked_in':
                              $status_class = 'primary'; // Blue
                              break;
                            case 'checked_out':
                              $status_class = 'purple'; // Purple (custom class)
                              break;
                            case 'cancelled':
                            case 'rejected':
                              $status_class = 'danger';
                              break;
                          }

                          echo "<tr data-booking-id='" . $booking['id'] . "' data-status='" . strtolower($booking['status']) . "' data-type='" . strtolower($booking['type']) . "' data-guest='" . strtolower($guest_name . ' ' . $contact) . "'>";
                          echo "<td><code>" . htmlspecialchars($booking['receipt_no'] ?: 'N/A') . "</code></td>";
                          echo "<td>";
                          echo "<strong>" . htmlspecialchars($room_display) . "</strong><br>";
                          echo "<small class='text-muted'>" . ucfirst($booking['item_type'] ?: 'Unknown') . "</small>";
                          if ($booking['capacity']) {
                            echo "<br><small class='text-info'>Capacity: " . $booking['capacity'] . "</small>";
                          }
                          echo "</td>";
                          echo "<td><span class='badge bg-light text-dark'>" . htmlspecialchars(ucfirst($booking['type'])) . "</span></td>";
                          echo "<td>";
                          echo "<strong>" . htmlspecialchars($guest_name) . "</strong>";
                          if ($contact) {
                            echo "<br><small class='text-muted'><i class='fas fa-phone me-1'></i>" . htmlspecialchars($contact) . "</small>";
                          }
                          if ($email) {
                            echo "<br><small class='text-muted'><i class='fas fa-envelope me-1'></i>" . htmlspecialchars($email) . "</small>";
                          }
                          echo "</td>";
                          echo "<td>";
                          if ($booking['checkin']) {
                            echo "<strong>In:</strong> " . date('M j, Y H:i', strtotime($booking['checkin'])) . "<br>";
                          }
                          if ($booking['checkout']) {
                            echo "<strong>Out:</strong> " . date('M j, Y H:i', strtotime($booking['checkout']));
                          }
                          if (!$booking['checkin'] && !$booking['checkout']) {
                            echo "<small class='text-muted'>No schedule set</small>";
                          }
                          echo "</td>";
                          echo "<td><span class='badge bg-" . $status_class . "'>" . htmlspecialchars(ucfirst($booking['status'])) . "</span></td>";

                          // Discount Application Column
                          echo "<td>";
                          $discount_type = '';
                          $discount_details = '';
                          $discount_proof = '';
                          $discount_status = $booking['discount_status'] ?? 'none';

                          if (!empty($booking['details'])) {
                            if (preg_match('/Discount: ([^|]+)/', $booking['details'], $matches)) {
                              $discount_type = trim($matches[1]);
                            }
                            if (preg_match('/Discount Details: ([^|]+)/', $booking['details'], $matches)) {
                              $discount_details = trim($matches[1]);
                            }
                            if (preg_match('/Proof: ([^|]+)/', $booking['details'], $matches)) {
                              $discount_proof = trim($matches[1]);
                            }
                          }

                          if ($discount_type) {
                            echo "<div class='mb-2'>";
                            echo "<span class='badge bg-warning text-dark mb-1'>" . htmlspecialchars($discount_type) . "</span><br>";

                            // Discount Status Badge
                            if ($discount_status === 'approved') {
                              echo "<span class='badge bg-success mb-1'><i class='fas fa-check me-1'></i>Approved</span><br>";
                            } elseif ($discount_status === 'rejected') {
                              echo "<span class='badge bg-danger mb-1'><i class='fas fa-times me-1'></i>Rejected</span><br>";
                            } elseif ($discount_status === 'pending') {
                              echo "<span class='badge bg-info mb-1'><i class='fas fa-clock me-1'></i>Pending Review</span><br>";
                            }

                            if ($discount_details) {
                              echo "<small class='text-muted d-block mb-1'>" . htmlspecialchars($discount_details) . "</small>";
                            }
                            if ($discount_proof) {
                              echo "<a href='" . htmlspecialchars($discount_proof) . "' target='_blank' class='btn btn-link btn-sm p-0 mb-1'><i class='fas fa-file-image me-1'></i>View Proof</a><br>";
                            }
                            echo "</div>";

                            // Discount approval buttons (separate from booking approval)
                            if ($discount_status === 'pending' || $discount_status === 'none') {
                              echo "<div class='btn-group btn-group-sm' role='group'>";
                              echo "<button class='btn btn-success btn-sm' onclick='updateDiscountStatus(" . $booking['id'] . ", \"approve\")' title='Approve Discount'>";
                              echo "<i class='fas fa-check-circle me-1'></i>Approve";
                              echo "</button>";
                              echo "<button class='btn btn-danger btn-sm' onclick='updateDiscountStatus(" . $booking['id'] . ", \"reject\")' title='Reject Discount'>";
                              echo "<i class='fas fa-times-circle me-1'></i>Reject";
                              echo "</button>";
                              echo "</div>";
                            }
                          } else {
                            echo "<span class='text-muted'>None</span>";
                          }
                          echo "</td>";
                          echo "<td><small class='text-muted'>" . date('M j, Y H:i', strtotime($booking['created_at'])) . "</small></td>";
                          echo "<td>";

                          // Admin Action Buttons
                          $status = $booking['status'];
                          echo "<div class='btn-group-vertical btn-group-sm' role='group'>";

                          // View Details Button
                          echo "<button class='btn btn-outline-primary btn-sm mb-1' onclick='viewBookingDetails(" . $booking['id'] . ")' title='View Details'>";
                          echo "<i class='fas fa-eye me-1'></i>View";
                          echo "</button>";

                          // Status Update Buttons
                          if ($status === 'pending') {
                            echo "<button class='btn btn-success btn-sm mb-1' onclick='updateBookingStatus(" . $booking['id'] . ", \"approved\")' title='Approve Booking'>";
                            echo "<i class='fas fa-check me-1'></i>Approve";
                            echo "</button>";
                            echo "<button class='btn btn-danger btn-sm mb-1' onclick='updateBookingStatus(" . $booking['id'] . ", \"rejected\")' title='Reject Booking'>";
                            echo "<i class='fas fa-times me-1'></i>Reject";
                            echo "</button>";
                          }

                          if ($status === 'approved' || $status === 'confirmed') {
                            echo "<button class='btn btn-info btn-sm mb-1' onclick='updateBookingStatus(" . $booking['id'] . ", \"checked_in\")' title='Check In Guest'>";
                            echo "<i class='fas fa-sign-in-alt me-1'></i>Check In";
                            echo "</button>";
                          }

                          if ($status === 'checked_in') {
                            echo "<button class='btn btn-primary btn-sm mb-1' onclick='updateBookingStatus(" . $booking['id'] . ", \"checked_out\")' title='Check Out Guest'>";
                            echo "<i class='fas fa-sign-out-alt me-1'></i>Check Out";
                            echo "</button>";
                          }

                          // Cancel button (available for most statuses except completed ones)
                          if (!in_array($status, ['checked_out', 'cancelled', 'rejected'])) {
                            echo "<button class='btn btn-outline-danger btn-sm' onclick='updateBookingStatus(" . $booking['id'] . ", \"cancelled\")' title='Cancel Booking'>";
                            echo "<i class='fas fa-ban me-1'></i>Cancel";
                            echo "</button>";
                          }

                          echo "</div>";
                          echo "</td>";
                          echo "</tr>";
                        }
                      } else {
                        echo "<tr><td colspan='8' class='text-center text-muted py-4'>";
                        echo "<i class='fas fa-calendar-times fa-3x mb-3 opacity-50'></i><br>";
                        echo "No bookings found.";
                        echo "</td></tr>";
                      }
                      ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="row mb-4">
          <div class="col-12">
            <div class="card">
              <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">
                  <i class="fas fa-percent me-2"></i>Discount Applications
                </h5>
                <small class="opacity-75">Review and approve/reject guest discount requests</small>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                      <tr>
                        <th>Receipt #</th>
                        <th>Guest Name</th>
                        <th>Room/Facility</th>
                        <th>Discount Type</th>
                        <th>Details</th>
                        <th>Proof</th>
                        <th>Schedule</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      // Fetch bookings with discount applications (pending or any with discount)
                      $discount_query = "SELECT b.*, i.name as room_name, i.item_type, i.room_number 
                                        FROM bookings b 
                                        LEFT JOIN items i ON b.room_id = i.id 
                                        WHERE b.details LIKE '%Discount:%' 
                                        ORDER BY 
                                          CASE b.discount_status 
                                            WHEN 'pending' THEN 1
                                            WHEN 'none' THEN 2
                                            WHEN 'approved' THEN 3
                                            WHEN 'rejected' THEN 4
                                          END,
                                          b.created_at DESC";
                      $discount_result = $conn->query($discount_query);
                      if ($discount_result && $discount_result->num_rows > 0) {
                        while ($booking = $discount_result->fetch_assoc()) {
                          $guest_name = 'Guest';
                          $discount_type = '';
                          $discount_details = '';
                          $discount_proof = '';
                          $discount_status = $booking['discount_status'] ?? 'none';
                          $email = '';

                          if (!empty($booking['details'])) {
                            if (preg_match('/Guest:\s*([^|]+)/', $booking['details'], $matches)) {
                              $guest_name = trim($matches[1]);
                            }
                            if (preg_match('/Discount: ([^|]+)/', $booking['details'], $matches)) {
                              $discount_type = trim($matches[1]);
                            }
                            if (preg_match('/Discount Details: ([^|]+)/', $booking['details'], $matches)) {
                              $discount_details = trim($matches[1]);
                            }
                            if (preg_match('/Proof: ([^|]+)/', $booking['details'], $matches)) {
                              $discount_proof = trim($matches[1]);
                            }
                            if (preg_match('/Email:\s*([^|]+)/', $booking['details'], $matches)) {
                              $email = trim($matches[1]);
                            }
                          }

                          $room_display = $booking['room_name'] ?: 'Unknown';
                          if ($booking['room_number']) {
                            $room_display .= " (#" . $booking['room_number'] . ")";
                          }

                          // Status badge class
                          $status_badge_class = 'secondary';
                          $status_text = ucfirst($discount_status);
                          if ($discount_status === 'approved') {
                            $status_badge_class = 'success';
                            $status_text = '✓ Approved';
                          } elseif ($discount_status === 'rejected') {
                            $status_badge_class = 'danger';
                            $status_text = '✗ Rejected';
                          } elseif ($discount_status === 'pending') {
                            $status_badge_class = 'warning text-dark';
                            $status_text = '⏳ Pending';
                          }

                          echo "<tr>";
                          echo "<td><code>" . htmlspecialchars($booking['receipt_no'] ?: 'N/A') . "</code></td>";
                          echo "<td>" . htmlspecialchars($guest_name) . "<br><small class='text-muted'>" . htmlspecialchars($email) . "</small></td>";
                          echo "<td>" . htmlspecialchars($room_display) . "<br><small class='text-muted'>" . htmlspecialchars(ucfirst($booking['item_type'])) . "</small></td>";
                          echo "<td>";
                          echo "<span class='badge bg-warning text-dark'>" . htmlspecialchars($discount_type) . "</span><br>";
                          echo "<span class='badge bg-" . $status_badge_class . " mt-1'>" . $status_text . "</span>";
                          echo "</td>";
                          echo "<td>" . htmlspecialchars($discount_details) . "</td>";
                          echo "<td>";
                          if ($discount_proof) {
                            echo "<a href='" . htmlspecialchars($discount_proof) . "' target='_blank' class='btn btn-outline-primary btn-sm'><i class='fas fa-file-image me-1'></i>View Proof</a>";
                          } else {
                            echo "<span class='text-muted'>None</span>";
                          }
                          echo "</td>";
                          echo "<td>";
                          if ($booking['checkin']) {
                            echo "<strong>In:</strong> " . date('M j, Y', strtotime($booking['checkin'])) . "<br>";
                          }
                          if ($booking['checkout']) {
                            echo "<strong>Out:</strong> " . date('M j, Y', strtotime($booking['checkout']));
                          }
                          echo "</td>";
                          echo "<td>";

                          // Show action buttons only for pending discounts
                          if ($discount_status === 'pending' || $discount_status === 'none') {
                            echo "<div class='btn-group btn-group-sm' role='group'>";
                            echo "<button class='btn btn-success' onclick='updateDiscountStatus(" . $booking['id'] . ", \"approve\")' title='Approve Discount'>";
                            echo "<i class='fas fa-check-circle me-1'></i>Approve";
                            echo "</button>";
                            echo "<button class='btn btn-danger' onclick='updateDiscountStatus(" . $booking['id'] . ", \"reject\")' title='Reject Discount'>";
                            echo "<i class='fas fa-times-circle me-1'></i>Reject";
                            echo "</button>";
                            echo "</div>";
                          } else {
                            echo "<small class='text-muted'>Already " . ($discount_status === 'approved' ? 'approved' : 'rejected') . "</small>";
                          }

                          echo "</td>";
                          echo "</tr>";
                        }
                      } else {
                        echo "<tr><td colspan='8' class='text-center text-muted py-4'><i class='fas fa-percent fa-2x mb-2'></i><br>No discount applications found.</td></tr>";
                      }
                      ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>








      <!-- Footer -->
      <div class="footer">
        <p>&copy; <?php echo date("Y"); ?> Hotel Management System</p>
      </div>















      <!-- Custom JavaScript for Calendar Section -->
      <script>
        // Initialize room calendar when the document is ready
        document.addEventListener('DOMContentLoaded', function () {
          initializeRoomCalendar();
          initializeCalendarNavigation();
          initializeRoomSearch();
        });

        function initializeRoomCalendar() {
          const calendarEl = document.getElementById('roomCalendar');
          if (!calendarEl) return;

          // Generate room events based on current booking data
          const roomEvents = generateRoomEvents();

          calendarInstance = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
              left: 'prev,next today',
              center: 'title',
              right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: roomEvents,
            eventDisplay: 'block',
            dayMaxEvents: true, // When too many events, show "+X more"
            height: 'auto',
            aspectRatio: 1.8,
            eventOverlap: false, // Prevent event overlap
            slotEventOverlap: false,
            displayEventTime: true,
            displayEventEnd: true,
            nowIndicator: true, // Show current time indicator
            businessHours: {
              daysOfWeek: [0, 1, 2, 3, 4, 5, 6], // 0=Sunday, 1=Monday, etc.
              startTime: '08:00',
              endTime: '20:00',
            },
            eventClick: function (info) {
              // Show event details
              const itemType = info.event.extendedProps.itemType || 'Item';
              const itemName = info.event.extendedProps.itemName || info.event.title;
              const roomNumber = info.event.extendedProps.roomNumber || '';
              const guest = info.event.extendedProps.guest || 'Unknown';
              const status = info.event.extendedProps.status || 'Unknown';
              const checkin = info.event.extendedProps.checkin || 'Unknown';
              const checkout = info.event.extendedProps.checkout || 'Unknown';
              const details = info.event.extendedProps.details || 'No details';

              const roomInfo = roomNumber ? `\nRoom Number: #${roomNumber}` : '';
              alert(`${itemType}: ${itemName}${roomInfo}\nGuest: ${guest}\nStatus: ${status}\nCheck-in: ${checkin}\nCheck-out: ${checkout}\nBooking Details: ${details}`);
            },
            dateClick: function (info) {
              // Handle date click - show available items for that date
              console.log('Date clicked:', info.dateStr);
              // You could open a modal here to show all items available on this date
            },
            eventDidMount: function (info) {
              // Add custom styling or tooltips
              if (!info.event.extendedProps.hasReservation) {
                info.el.style.opacity = '0.6';
              }
            }
          });

          calendarInstance.render();
        }

        // Generate PHP room events and make them globally available
        window.roomEvents = [];
        <?php
        // Generate JavaScript events using proper room_id relationship
        $bookings_query = "SELECT b.*, i.name as item_name, i.item_type, i.room_number
                         FROM bookings b 
                         LEFT JOIN items i ON b.room_id = i.id
                         WHERE b.status IN ('approved', 'confirmed', 'checked_in', 'checked_out', 'pending')
                         AND b.checkin >= CURDATE() - INTERVAL 7 DAY
                         AND b.checkin <= CURDATE() + INTERVAL 30 DAY
                         ORDER BY b.checkin ASC";
        $bookings_result = $conn->query($bookings_query);

        if ($bookings_result && $bookings_result->num_rows > 0) {
          while ($booking = $bookings_result->fetch_assoc()) {
            // Use room/facility name from proper JOIN
            $item_name = $booking['item_name'] ? addslashes($booking['item_name']) : 'Unassigned Room/Facility';
            $room_number = $booking['room_number'] ? '#' . $booking['room_number'] : '';
            $item_type = $booking['item_type'] ?: 'room';

            $guest = 'Guest';
            $status = $booking['status'];

            // Create display title with room number if available
            $display_title = $item_name . $room_number . ' - ' . $guest;

            // Color based on status
            $color = '#28a745'; // green for approved/confirmed
            if ($status == 'checked_in')
              $color = '#0d6efd'; // blue (primary)
            if ($status == 'checked_out')
              $color = '#6f42c1'; // purple
            if ($status == 'pending')
              $color = '#fd7e14'; // orange (warning)
        
            echo "window.roomEvents.push({\n";
            echo "  id: 'booking-{$booking['id']}',\n";
            echo "  title: '{$display_title}',\n";
            echo "  start: '{$booking['checkin']}',\n";
            echo "  end: '" . date('Y-m-d', strtotime($booking['checkout'] . ' +1 day')) . "',\n";
            echo "  backgroundColor: '{$color}',\n";
            echo "  borderColor: '{$color}',\n";
            echo "  textColor: '#ffffff',\n";
            echo "  extendedProps: {\n";
            echo "    itemName: '{$item_name}',\n";
            echo "    roomNumber: '" . ($booking['room_number'] ?: '') . "',\n";
            echo "    itemType: '{$item_type}',\n";
            echo "    guest: '{$guest}',\n";
            echo "    status: '{$status}',\n";
            echo "    checkin: '{$booking['checkin']}',\n";
            echo "    checkout: '{$booking['checkout']}',\n";
            echo "    roomId: " . ($booking['room_id'] ?: 'null') . "\n";
            echo "  }\n";
            echo "});\n";
          }
        }
        ?>







      </script>

      <!-- Rooms & Facilities JavaScript -->
      <script>
        // Initialize rooms and facilities functionality
        document.addEventListener('DOMContentLoaded', function () {
          initializeRoomsFiltering();
          initializeRoomsSearch();
          initializeEditForms();
        });
      </script>

      <!-- All styles moved to dashboard.css for better organization -->

      <!-- Additional Edit Form Initialization -->
      <script>
        // Ensure edit forms work immediately after page load
        document.addEventListener('DOMContentLoaded', function () {
          // Wait for everything to load, then force re-initialize edit forms
          setTimeout(function () {
            console.log('Forcing edit form initialization...');

            // Initialize edit forms directly
            if (typeof setupEditFormToggles === 'function') {
              setupEditFormToggles();
            }

            // Debug: log all edit buttons and forms found
            const editButtons = document.querySelectorAll('.edit-toggle-btn');
            const editForms = document.querySelectorAll('[id^="editForm"]');

            console.log('Edit buttons found:', editButtons.length);
            console.log('Edit forms found:', editForms.length);

            editButtons.forEach((btn, index) => {
              console.log(`Edit button ${index + 1} - Item ID:`, btn.getAttribute('data-item-id'));
            });
          }, 1000);
        });

        // Backup function to manually initialize edit forms if needed
        function forceInitializeEditForms() {
          if (typeof setupEditFormToggles === 'function') {
            setupEditFormToggles();
            console.log('Edit forms manually re-initialized');
          }
        }

        // Make it globally accessible for debugging
        window.forceInitializeEditForms = forceInitializeEditForms;
      </script>

      <!-- Load JavaScript files at the end of body for better performance -->
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
      <script src="assets/js/dashboard-bootstrap.js"></script>



</body>

</html>