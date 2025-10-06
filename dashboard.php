<?php
// dashboard.php
session_start();
require __DIR__ . '/database/db_connect.php';

// ✅ Auth check: only admins can access
if (!isset($_SESSION['admin_id'])) {
  header("Location: admin_login.php");
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

// Monthly bookings for chart
$monthly_bookings = [];
for ($i = 11; $i >= 0; $i--) {
  $month = date('Y-m', strtotime("-$i months"));
  $month_name = date('M Y', strtotime("-$i months"));
  $count = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE DATE_FORMAT(created_at, '%Y-%m') = '$month'")->fetch_assoc()['count'];
  $monthly_bookings[] = ['month' => $month_name, 'count' => $count];
}

// Booking status distribution
$status_distribution = [];
$statuses = ['pending', 'approved', 'checked_in', 'checked_out', 'cancelled'];
foreach ($statuses as $status) {
  $count = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status='$status'")->fetch_assoc()['count'];
  $status_distribution[$status] = $count;
}

// Recent Activities
$recent_activity_result = $conn->query("SELECT b.type, b.details, b.created_at, u.username 
    FROM bookings b 
    LEFT JOIN users u ON b.user_id = u.id 
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
    'total_feedback' => 0, 'avg_rating' => 0, 'five_star' => 0, 'four_star' => 0, 
    'three_star' => 0, 'two_star' => 0, 'one_star' => 0
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
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <!-- FullCalendar CSS & JS -->
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
  <!-- Bootstrap JavaScript -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Custom JavaScript -->
  <script src="assets/js/dashboard-bootstrap.js" defer></script>
  <!-- Custom CSS -->
  <link rel="stylesheet" href="assets/css/dashboard.css">
  <link rel="stylesheet" href="assets/css/dashboard-enhanced.css">
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
    <a href="#" class="nav-link-custom" data-section="rooms">
      <i class="fas fa-door-open me-2"></i>Rooms & Facilities
    </a>
    <a href="#" class="nav-link-custom" data-section="bookings">
      <i class="fas fa-calendar-alt me-2"></i>Bookings
    </a>

    <a href="#" class="nav-link-custom" data-section="users">
      <i class="fas fa-users me-2"></i>Users
    </a>
    <a href="#" class="nav-link-custom" data-section="communication">
      <i class="fas fa-comments me-2"></i>Customer Support
    </a>
    <a href="index.php" class="btn btn-danger mt-3">
      <i class="fas fa-sign-out-alt me-2"></i>Logout
    </a>
  </div>


  <!-- Main Content -->
  <div class="main-content">
    <div class="container-fluid">
      <!-- Header -->
      <header class="mb-5">
        <div class="row align-items-center">
          <div class="col-md-8">
            <h1 class="h2 mb-1 text-dark">Dashboard</h1>
            <p class="mb-0 text-muted">Welcome back! Here's your hotel overview.</p>
          </div>
          <div class="col-md-4 text-md-end">
            <div class="text-muted">
              <i class="fas fa-calendar me-1"></i>
              <?php echo date('F j, Y'); ?>
            </div>
          </div>
        </div>
      </header>

      <!-- Dashboard Section -->
      <section id="dashboard-section" class="content-section active">

        <!-- Stats Cards Row -->
        <div class="row g-4 mb-4">
          <div class="col-xl-3 col-lg-6">
            <div class="card bg-primary h-100">
              <div class="card-body">
                <div class="row align-items-center">
                  <div class="col">
                    <div class="text-xs mb-1">Total Rooms & Facilities</div>
                    <div class="h5 mb-0"><?php echo $total_rooms + $total_facilities; ?></div>
                    <div class="text-xs text-muted">
                      <?php echo $total_rooms; ?> rooms • <?php echo $total_facilities; ?> facilities
                    </div>
                  </div>
                  <div class="col-auto">
                    <i class="fas fa-building fa-lg"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-lg-6">
            <div class="card bg-success h-100">
              <div class="card-body">
                <div class="row align-items-center">
                  <div class="col">
                    <div class="text-xs mb-1">Active Bookings</div>
                    <div class="h5 mb-0"><?php echo $active_bookings; ?></div>
                  </div>
                  <div class="col-auto">
                    <i class="fas fa-calendar-check fa-lg"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-lg-6">
            <div class="card bg-warning h-100">
              <div class="card-body">
                <div class="row align-items-center">
                  <div class="col">
                    <div class="text-xs mb-1">Average Rating</div>
                    <div class="h5 mb-0">
                      <?php echo number_format($feedback_stats['avg_rating'], 1); ?>
                      <small class="h6 text-muted">
                        <?php for($i = 1; $i <= 5; $i++): ?>
                          <i class="fas fa-star <?php echo $i <= round($feedback_stats['avg_rating']) ? 'text-warning' : 'text-muted'; ?>"></i>
                        <?php endfor; ?>
                      </small>
                    </div>
                  </div>
                  <div class="col-auto">
                    <i class="fas fa-star fa-lg"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-lg-6">
            <div class="card bg-info h-100">
              <div class="card-body">
                <div class="row align-items-center">
                  <div class="col">
                    <div class="text-xs mb-1">Total Feedback</div>
                    <div class="h5 mb-0"><?php echo $feedback_stats['total_feedback']; ?></div>
                    <div class="text-xs text-muted">
                      <?php echo $feedback_stats['five_star']; ?> five-star reviews
                    </div>
                  </div>
                  <div class="col-auto">
                    <i class="fas fa-comments fa-lg"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Feedback Metrics Row -->
        <div class="row g-4 mb-4">
          <div class="col-lg-8">
            <div class="card">
              <div class="card-header bg-white py-3">
                <h6 class="m-0 text-dark fw-bold">
                  <i class="fas fa-chart-bar me-2 text-warning"></i>Guest Satisfaction Overview
                </h6>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-6">
                    <h5 class="text-primary mb-3">Rating Breakdown</h5>
                    <?php 
                    $total_reviews = $feedback_stats['total_feedback'];
                    $ratings = [
                        5 => ['count' => $feedback_stats['five_star'], 'color' => 'success'],
                        4 => ['count' => $feedback_stats['four_star'], 'color' => 'info'],
                        3 => ['count' => $feedback_stats['three_star'], 'color' => 'warning'],
                        2 => ['count' => $feedback_stats['two_star'], 'color' => 'danger'],
                        1 => ['count' => $feedback_stats['one_star'], 'color' => 'dark']
                    ];
                    foreach($ratings as $star => $data): 
                        $percentage = $total_reviews > 0 ? ($data['count'] / $total_reviews * 100) : 0;
                    ?>
                    <div class="d-flex align-items-center mb-2">
                        <div class="me-2" style="width: 60px;">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?php echo $i <= $star ? 'text-warning' : 'text-muted'; ?>" style="font-size: 12px;"></i>
                            <?php endfor; ?>
                        </div>
                        <div class="flex-grow-1 me-2">
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-<?php echo $data['color']; ?>" 
                                     style="width: <?php echo $percentage; ?>%"></div>
                            </div>
                        </div>
                        <span class="text-muted" style="width: 50px; font-size: 12px;">
                            <?php echo $data['count']; ?> (<?php echo number_format($percentage, 1); ?>%)
                        </span>
                    </div>
                    <?php endforeach; ?>
                  </div>
                  <div class="col-md-6">
                    <h5 class="text-primary mb-3">Satisfaction Metrics</h5>
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="border rounded p-2">
                                <div class="h4 text-success mb-1">
                                    <?php 
                                    $positive_reviews = $feedback_stats['five_star'] + $feedback_stats['four_star'];
                                    $positive_percentage = $total_reviews > 0 ? ($positive_reviews / $total_reviews * 100) : 0;
                                    echo number_format($positive_percentage, 1); 
                                    ?>%
                                </div>
                                <small class="text-muted">Positive<br>(4-5 stars)</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="border rounded p-2">
                                <div class="h4 text-warning mb-1">
                                    <?php 
                                    $neutral_percentage = $total_reviews > 0 ? ($feedback_stats['three_star'] / $total_reviews * 100) : 0;
                                    echo number_format($neutral_percentage, 1); 
                                    ?>%
                                </div>
                                <small class="text-muted">Neutral<br>(3 stars)</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2">
                                <div class="h4 text-danger mb-1">
                                    <?php 
                                    $negative_reviews = $feedback_stats['two_star'] + $feedback_stats['one_star'];
                                    $negative_percentage = $total_reviews > 0 ? ($negative_reviews / $total_reviews * 100) : 0;
                                    echo number_format($negative_percentage, 1); 
                                    ?>%
                                </div>
                                <small class="text-muted">Negative<br>(1-2 stars)</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2">
                                <div class="h4 text-primary mb-1">
                                    <?php echo number_format($feedback_stats['avg_rating'], 2); ?>
                                </div>
                                <small class="text-muted">Average<br>Rating</small>
                            </div>
                        </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-4">
            <div class="card">
              <div class="card-header bg-white py-3">
                <h6 class="m-0 text-dark fw-bold">
                  <i class="fas fa-trophy me-2 text-warning"></i>Guest Satisfaction Status
                </h6>
              </div>
              <div class="card-body text-center">
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
                <div class="mb-3">
                    <i class="fas <?php echo $icon; ?> fa-3x text-<?php echo $color; ?>"></i>
                </div>
                <h4 class="text-<?php echo $color; ?> mb-2"><?php echo $status; ?></h4>
                <p class="text-muted mb-3">
                    Overall guest satisfaction based on <?php echo $total_reviews; ?> reviews
                </p>
                <div class="mb-3">
                    <?php for($i = 1; $i <= 5; $i++): ?>
                        <i class="fas fa-star <?php echo $i <= round($avg_rating) ? 'text-warning' : 'text-muted'; ?> fa-lg"></i>
                    <?php endfor; ?>
                </div>
                <a href="#" class="btn btn-outline-<?php echo $color; ?> btn-sm" onclick="document.querySelector('[data-section=\"feedback\"]').click()">
                    <i class="fas fa-chart-line me-1"></i>View Details
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- Charts Row -->
        <div class="row g-4 mb-4">
          <div class="col-xl-8">
            <div class="card">
              <div class="card-header bg-white py-3">
                <h6 class="m-0 text-dark fw-bold">
                  <i class="fas fa-chart-line me-2 text-primary"></i>Bookings Overview
                </h6>
              </div>
              <div class="card-body p-4">
                <div class="mb-3">
                  <canvas id="bookingsChart" width="100%" height="40"></canvas>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-4">
            <div class="card">
              <div class="card-header bg-white py-3">
                <h6 class="m-0 text-dark fw-bold">
                  <i class="fas fa-chart-pie me-2 text-primary"></i>Booking Status
                </h6>
              </div>
              <div class="card-body p-4">
                <div class="mb-3">
                  <canvas id="statusChart" width="100%" height="100"></canvas>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Calendar and Activity Row -->
        <div class="row g-4">
          <div class="col-xl-8">
            <div class="card">
              <div class="card-header bg-white py-3">
                <h6 class="m-0 text-dark fw-bold">
                  <i class="fas fa-calendar-alt me-2 text-primary"></i>Booking Calendar - Rooms & Facilities
                </h6>
                <div class="mt-2">
                  <small class="text-muted">
                    <span class="badge bg-success me-1">📅</span>Approved 
                    <span class="badge bg-info me-1">🏠</span>Checked-in 
                    <span class="badge bg-warning me-1">🔑</span>Check-in Events 
                    <span class="badge bg-danger me-1">🚪</span>Check-out Events
                  </small>
                </div>
              </div>
              <div class="card-body">
                <div id="dashboardCalendar"></div>
              </div>
            </div>
          </div>

          <div class="col-xl-4">
            <div class="card">
              <div class="card-header bg-white py-3">
                <h6 class="m-0 text-dark fw-bold">
                  <i class="fas fa-clock me-2 text-primary"></i>Recent Activity
                </h6>
              </div>
              <div class="card-body">
                <div class="activity-list" style="max-height: 400px; overflow-y: auto;">
                  <?php if (empty($recent_activities)): ?>
                    <div class="text-center text-muted py-4">
                      <i class="fas fa-inbox fa-2x mb-3 opacity-50"></i>
                      <p>No recent activity</p>
                    </div>
                  <?php else: ?>
                    <?php foreach ($recent_activities as $activity): ?>
                      <div class="d-flex align-items-start mb-3 pb-3 border-bottom">
                        <div class="activity-icon me-3 mt-1">
                          <i class="fas fa-circle"></i>
                        </div>
                        <div class="flex-grow-1">
                          <div class="fw-semibold text-dark mb-1"><?php echo htmlspecialchars($activity['type']); ?></div>
                          <div class="text-muted small mb-1"><?php echo htmlspecialchars($activity['details']); ?></div>
                          <div class="text-muted small">
                            <?php if (isset($activity['username'])): ?>
                              by <?php echo htmlspecialchars($activity['username']); ?> •
                            <?php endif; ?>
                            <?php echo date('M d, H:i', strtotime($activity['created_at'])); ?>
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
      
      $calendar_query = "SELECT b.*, u.username FROM bookings b LEFT JOIN users u ON b.user_id = u.id WHERE b.status != 'rejected' ORDER BY b.id DESC";
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
            $color = '#28a745';
          } elseif ($row['status'] == 'checked_in') {
            $title = "🏠 Checked In: " . $room_facility;
            $color = '#17a2b8';
          } elseif ($row['status'] == 'checked_out') {
            $title = "🚪 Checked Out: " . $room_facility;
            $color = '#6c757d';
          } elseif ($row['status'] == 'pending') {
            $title = "⏳ Pending: " . $room_facility;
            $color = '#ffc107';
          } else {
            $title = ucfirst($row['status']) . ": " . $room_facility;
            $color = '#6c757d';
          }
          
          if ($row['username']) {
            $title .= " - " . $row['username'];
          }
          
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
        // Data for dashboard charts and calendar
        const bookingEvents = <?php echo json_encode($events); ?>;
        const monthlyBookingsData = <?php echo json_encode($monthly_bookings); ?>;
        const statusDistributionData = <?php echo json_encode($status_distribution); ?>;
        const dashboardStats = {
          totalRooms: <?php echo $total_rooms; ?>,
          totalFacilities: <?php echo $total_facilities; ?>,
          activeBookings: <?php echo $active_bookings; ?>,
          pendingApprovals: <?php echo $pending_approvals; ?>,
          totalRevenue: <?php echo $total_revenue; ?>,
          feedbackStats: <?php echo json_encode($feedback_stats); ?>
        };
        
        // Make variables globally accessible
        window.bookingEvents = bookingEvents;
        window.monthlyBookingsData = monthlyBookingsData;
        window.statusDistributionData = statusDistributionData;
        window.dashboardStats = dashboardStats;
        
        // Debug: Log the events to console
        console.log('Booking Events:', bookingEvents);
        console.log('Events Length:', bookingEvents.length);
      </script>



      <section id="rooms" class="content-section">
        <!-- ✅ Add Item Form -->
        <h2>Add Room / Facility</h2>
        <form method="POST" enctype="multipart/form-data">
          <input type="hidden" name="add_item" value="1">
          <label>Name:</label><input type="text" name="name" required>
          <label>Type:</label>
          <select name="item_type" required>
            <option value="room">Room</option>
            <option value="facility">Facility</option>
          </select>
          <label>Room Number (optional):</label><input type="text" name="room_number">
          <label>Description:</label><textarea name="description"></textarea>
          <label>Capacity:</label><input type="number" name="capacity" required>
          <label>Price:</label><input type="number" step="1" name="price" required>
          <label>Image (optional):</label><input type="file" name="image" accept="image/*">
          <button type="submit">Add Item</button>
        </form>

        <!-- ✅ Existing Items -->
        <h2>Existing Items</h2>
        <label>Filter Type:
          <input type="radio" name="type_filter" value="room" checked> Room
          <input type="radio" name="type_filter" value="facility"> Facility
        </label>

        <div class="cards-grid" id="cards-grid">
          <?php
          $res = $conn->query("SELECT * FROM items ORDER BY created_at DESC");
          while ($item = $res->fetch_assoc()): ?>
            <div class="card" data-type="<?= $item['item_type'] ?>">
              <?php if ($item['image']): ?>
                <img src="<?= $item['image'] ?>" style="width:100%;height:150px;object-fit:cover;">
              <?php endif; ?>

              <h3><?= $item['name'] ?></h3>
              <?= $item['room_number'] ? "<p>Room Number: " . $item['room_number'] . "</p>" : "" ?>
              <p>Capacity: <?= $item['capacity'] ?>   <?= $item['item_type'] === 'room' ? 'persons' : 'people' ?></p>
              <p>Price: P<?= $item['price'] ?><?= $item['item_type'] === 'room' ? '/night' : '/day' ?></p>
              <p><?= $item['description'] ?></p>

              <!-- ✅ Edit Toggle Button -->
              <button type="button" class="edit-form">Edit</button>

              <!-- ✅ Edit Form (hidden by default) -->
              <form method="POST" enctype="multipart/form-data" class="edit-form" style="display:none;">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?= $item['id'] ?>">
                <input type="hidden" name="old_image" value="<?= $item['image'] ?>">

                <label>Name: <input type="text" name="name" value="<?= $item['name'] ?>" required></label>
                <label>Type:
                  <select name="item_type">
                    <option value="room" <?= $item['item_type'] == 'room' ? 'selected' : '' ?>>Room</option>
                    <option value="facility" <?= $item['item_type'] == 'facility' ? 'selected' : '' ?>>Facility</option>
                  </select>
                </label>
                <label>Room Number: <input type="text" name="room_number" value="<?= $item['room_number'] ?>"></label>
                <label>Description: <textarea name="description"><?= $item['description'] ?></textarea></label>
                <label>Capacity: <input type="number" name="capacity" value="<?= $item['capacity'] ?>" required></label>
                <label>Price: <input type="number" step="1" name="price" value="<?= $item['price'] ?>" required></label>
                <label>Change Image: <input type="file" name="image"></label>
                <button type="submit">Update</button>
              </form>

              <!-- ✅ Delete Form -->
              <form method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $item['id'] ?>">
                <button type="submit" style="background:red;color:white;">Delete</button>
              </form>
            </div>
          <?php endwhile; ?>
        </div>

      </section>



      <!-- Bookings -->


      <section id="bookings" class="content-section">

        <!-- Select Booking Type -->
        <label><input type="radio" name="bookingType" value="reservation" checked onchange="toggleBookingForm()">
          Reservation</label>
        <label><input type="radio" name="bookingType" value="pencil" onchange="toggleBookingForm()"> Pencil Booking
          (Function Hall)</label>

        <form id="reservationForm" method="POST" action="database/user_auth.php">
          <input type="hidden" name="action" value="create_booking">
          <input type="hidden" name="booking_type" value="reservation">



          <h3>Reservation Form</h3>
          <label>Official Receipt No.: <input type="text" name="receipt_no" id="receipt_no" readonly></label>
          <label>Guest Name: <input type="text" name="guest_name" required></label>
          <label>Contact Number: <input type="text" name="contact_number" required></label>
          <label>Email Address: <input type="email" name="email" required></label>
          <label>Check-in Date & Time: <input type="datetime-local" name="checkin" required></label>
          <label>Check-out Date & Time: <input type="datetime-local" name="checkout" required></label>
          <label>Number of Occupants: <input type="number" name="occupants" min="1" required></label>
          <label>Company Affiliation (optional): <input type="text" name="company"></label>
          <label>Company Contact Number (optional): <input type="text" name="company_contact"></label>
          <input type="hidden" name="type" value="reservation">
          <button type="submit">Confirm Reservation</button>
        </form>

        <!-- Pencil Booking Form -->
        <form id="pencilForm" method="POST" action="database/user_auth.php" style="display:none;">
          <input type="hidden" name="action" value="create_booking">
          <input type="hidden" name="booking_type" value="pencil">

          <h3>Pencil Booking Form (Function Hall)</h3>
          <label>Date of Pencil Booking: <input type="date" name="pencil_date" value="<?php echo date('Y-m-d'); ?>"
              readonly></label>
          <label>Event Type: <input type="text" name="event_type" required></label>
          <label>Function Hall: <input type="text" name="hall" required></label>
          <label>Number of Pax: <input type="number" name="pax" min="1" required></label>
          <label>Time of Event (From): <input type="time" name="time_from" required></label>
          <label>Time of Event (To): <input type="time" name="time_to" required></label>
          <label>Food Provider/Caterer: <input type="text" name="caterer" required></label>
          <label>Contact Person: <input type="text" name="contact_person" required></label>
          <label>Contact Number: <input type="text" name="contact_number" required></label>
          <label>Company Affiliation (optional): <input type="text" name="company"></label>
          <label>Company Number (optional): <input type="text" name="company_number"></label>
          <input type="hidden" name="type" value="pencil">
          <button type="submit" onclick="return pencilReminder()">Submit Pencil Booking</button>
        </form>



        <h2>Bookings</h2>
        <table>
          <tr>
            <th>ID</th>
            <th>Type</th>
            <th>Details</th>
            <th>Date</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>

          <?php
          include __DIR__ . '/database/db_connect.php';

          $result = $conn->query("SELECT * FROM bookings ORDER BY id DESC");
          while ($row = $result->fetch_assoc()):
            $status = $row['status'];
            ?>
            <tr>
              <td><?= htmlspecialchars($row['id']) ?></td>
              <td><?= htmlspecialchars($row['type']) ?></td>
              <td><?= htmlspecialchars($row['details']) ?></td>
              <td><?= htmlspecialchars($row['created_at']) ?></td>
              <td><?= htmlspecialchars($status) ?></td>
              <td>
                <!-- Approve -->
                <form method="POST" action="database/user_auth.php" style="display:inline;">
                  <input type="hidden" name="booking_id" value="<?= $row['id'] ?>">
                  <input type="hidden" name="action" value="admin_update_booking">
                  <input type="hidden" name="admin_action" value="approve">
                  <button type="submit" class="approve" <?= in_array($status, ['confirmed', 'rejected', 'checked_in', 'checked_out', 'cancelled']) ? 'disabled' : '' ?>>
                    Approve
                  </button>
                </form>

                <!-- Reject -->
                <form method="POST" action="database/user_auth.php" style="display:inline;">
                  <input type="hidden" name="booking_id" value="<?= $row['id'] ?>">
                  <input type="hidden" name="action" value="admin_update_booking">
                  <input type="hidden" name="admin_action" value="reject">
                  <button type="submit" class="reject" <?= $status === 'rejected' ? 'disabled' : '' ?>>
                    Reject
                  </button>
                </form>

                <!-- Check In -->
                <form method="POST" action="database/user_auth.php" style="display:inline;">
                  <input type="hidden" name="booking_id" value="<?= $row['id'] ?>">
                  <input type="hidden" name="action" value="admin_update_booking">
                  <input type="hidden" name="admin_action" value="checkin">
                  <button type="submit" class="checkin" <?= in_array($status, ['checked_in', 'checked_out', 'cancelled', 'rejected']) ? 'disabled' : '' ?>>
                    Check In
                  </button>
                </form>

                <!-- Check Out -->
                <form method="POST" action="database/user_auth.php" style="display:inline;">
                  <input type="hidden" name="booking_id" value="<?= $row['id'] ?>">
                  <input type="hidden" name="action" value="admin_update_booking">
                  <input type="hidden" name="admin_action" value="checkout">
                  <button type="submit" class="checkout" <?= in_array($status, ['checked_out', 'cancelled', 'rejected']) ? 'disabled' : '' ?>>
                    Check Out
                  </button>
                </form>

                <!-- Cancel -->
                <form method="POST" action="database/user_auth.php" style="display:inline;">
                  <input type="hidden" name="booking_id" value="<?= $row['id'] ?>">
                  <input type="hidden" name="action" value="admin_update_booking">
                  <input type="hidden" name="admin_action" value="cancel">
                  <button type="submit" class="cancel" <?= in_array($status, ['cancelled', 'rejected', 'checked_out']) ? 'disabled' : '' ?>>
                    Cancel
                  </button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
        </table>

      </section>






      <!-- Users -->
      <section id="users" class="content-section">
        <h2>User Management</h2>

        <!-- ✅ Table of Registered Users -->
        <table>
          <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Created At</th>
            <th>Actions</th>
          </tr>

          <?php

          $sql = "SELECT id, username, email, created_at FROM users ORDER BY created_at DESC";
          $result = $conn->query($sql);

          if ($result->num_rows > 0):
            while ($row = $result->fetch_assoc()):
              ?>
              <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= $row['created_at'] ?></td>
                <td>
                  <!-- ✅ Inline Edit Form -->
                  <form method="post" action="database/user_auth.php" style="display:inline-block;">
                    <input type="hidden" name="action" value="edit_user">
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                    <input type="text" name="username" value="<?= htmlspecialchars($row['username']) ?>" required>
                    <input type="email" name="email" value="<?= htmlspecialchars($row['email']) ?>" required>
                    <button type="submit" class="action-btn edit">Save</button>
                  </form>

                  <!-- ✅ Delete Form -->
                  <form method="post" action="database/user_auth.php" style="display:inline-block;"
                    onsubmit="return confirm('Are you sure you want to delete this user?');">
                    <input type="hidden" name="action" value="admin_delete_user">
                    <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                    <button type="submit" class="action-btn delete">Delete</button>
                  </form>
                </td>
              </tr>
              <?php
            endwhile;
          else:
            ?>
            <tr>
              <td colspan="5">No users found.</td>
            </tr>
          <?php endif; ?>
        </table>
      </section>



      <!-- Communication -->
      <section id="communication" class="content-section">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                  <i class="fas fa-headset me-2 text-primary"></i>Customer Support Chat
                  <small class="text-muted ms-2">- Real-time Guest Support</small>
                </h5>
              </div>
              <div class="card-body p-0">
                <div class="row g-0" style="height: 650px;">

                  <!-- Conversations List -->
                  <div class="col-md-4 border-end">
                    <div class="p-3 border-bottom"
                      style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                      <h6 class="mb-0">
                        <i class="fas fa-users me-2"></i>Active Conversations
                        <span id="total-unread" class="badge bg-danger ms-2" style="display: none;">0</span>
                      </h6>
                      <small class="opacity-75">Manage guest inquiries</small>
                    </div>
                    <div id="conversations-list" class="conversation-list"
                      style="height: 550px; overflow-y: auto; background-color: #f8f9fa;">
                      <div class="text-center text-muted p-4">
                        <i class="fas fa-comment-slash fa-2x mb-3 opacity-50"></i>
                        <p class="mb-2">No conversations yet</p>
                        <small>Guest messages will appear here</small>
                      </div>
                    </div>
                  </div>

                  <!-- Chat Area -->
                  <div class="col-md-8 d-flex flex-column">
                    <div id="chat-header" class="p-3 border-bottom"
                      style="display: none; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                      <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                          <div class="avatar-circle bg-white text-primary me-3"
                            style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                            <i class="fas fa-user"></i>
                          </div>
                          <div>
                            <h6 class="mb-0" id="chat-with-name">Select a conversation</h6>
                            <small class="opacity-75" id="chat-with-email"></small>
                          </div>
                        </div>
                        <div class="d-flex align-items-center">
                          <span class="badge bg-success me-2" id="chat-status">
                            <i class="fas fa-circle me-1" style="font-size: 8px;"></i>Support Active
                          </span>
                          <div class="dropdown">
                            <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button"
                              data-bs-toggle="dropdown">
                              <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu">
                              <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>View Profile</a>
                              </li>
                              <li><a class="dropdown-item" href="#"><i class="fas fa-history me-2"></i>Chat History</a>
                              </li>
                              <li>
                                <hr class="dropdown-divider">
                              </li>
                              <li><a class="dropdown-item text-danger" href="#"><i class="fas fa-ban me-2"></i>Block
                                  User</a></li>
                            </ul>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Messages Area -->
                    <div id="chat-messages" class="flex-grow-1 p-3"
                      style="height: 430px; overflow-y: auto; background: linear-gradient(to bottom, #f8f9fa 0%, #e9ecef 100%);">
                      <div class="text-center text-muted py-5">
                        <i class="fas fa-headset fa-3x mb-3 text-primary opacity-50"></i>
                        <h5 class="text-primary">Customer Support Ready</h5>
                        <p class="mb-0">Select a conversation to assist guests</p>
                        <small class="text-muted">Professional support at your fingertips</small>
                      </div>
                    </div>

                    <!-- Message Input -->
                    <div id="chat-input-area" class="p-3 border-top"
                      style="display: none; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                      <form id="chat-form" class="d-flex">
                        <div class="input-group">
                          <div class="input-group-text bg-white">
                            <i class="fas fa-smile text-muted"></i>
                          </div>
                          <input type="text" id="chat-input" class="form-control border-0"
                            placeholder="Type your support response..." required>
                          <button type="submit" class="btn btn-light">
                            <i class="fas fa-paper-plane text-primary"></i>
                          </button>
                        </div>
                      </form>
                      <div class="mt-2">
                        <small class="text-white opacity-75">
                          <i class="fas fa-info-circle me-1"></i>Quick responses: Press Tab for templates
                        </small>
                      </div>
                    </div>
                  </div>
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















      <!-- Socket.IO for communication features -->
      <script src="/socket.io/socket.io.js"></script>




</body>

</html>