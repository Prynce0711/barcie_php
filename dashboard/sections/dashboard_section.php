<?php
// sections/dashboard_section.php
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
                        <i class="fas fa-star <?php echo $i <= round($feedback_stats['avg_rating']) ? '' : 'opacity-50'; ?>"></i>
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
                      <i class="fas fa-star <?php echo $i <= round($avg_rating) ? 'text-warning' : 'text-muted'; ?> fa-lg"></i>
                    <?php endfor; ?>
                  </div>
                  <h3 class="text-primary mb-1"><?php echo number_format($feedback_stats['avg_rating'], 1); ?>/5.0</h3>
                  <p class="text-muted small">Based on <?php echo $feedback_stats['total_feedback']; ?> guest reviews</p>
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
                        <div class="progress-bar bg-<?php echo $data['color']; ?>" style="width: <?php echo $percentage; ?>%"></div>
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
                      <div class="activity-item d-flex p-3 <?php echo $index < count($recent_activities) - 1 ? 'border-bottom' : ''; ?>">
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
                              <i class="fas fa-clock me-1"></i><?php echo date('M d, H:i', strtotime($activity['created_at'])); ?>
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