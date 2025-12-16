<?php
// Dashboard Section Template
// This section displays key performance metrics, analytics, and quick actions

// Ensure DB connection is available
@include_once __DIR__ . '/../../../database/db_connect.php';

// Initialize defaults
$occupied_rooms = 0;
$available_rooms = 0;
$today_checkins = 0;
$today_checkouts = 0;
$total_bookings = 0;
$cancelled_bookings = 0;
$today_revenue = 0.00;
$recent_bookings = [];

// Resolve admin display name (prefer full_name from DB, fallback to username)
$admin_id = isset($_SESSION['admin_id']) ? (int)$_SESSION['admin_id'] : 0;
$admin_display_name = null;
$admin_role = null;
$greeting = 'Hello';
if ($admin_id && isset($conn) && $conn instanceof mysqli) {
    $stmt = $conn->prepare("SELECT username, full_name, role FROM admins WHERE id = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('i', $admin_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $row = $res->fetch_assoc()) {
            $admin_display_name = $row['full_name'] ?: $row['username'];
            $admin_role = $row['role'] ?: null;
        }
        $stmt->close();
    }
}
if (!$admin_display_name) {
    $admin_display_name = $_SESSION['admin_username'] ?? null;
}
if ($admin_display_name) {
  $hour = (int)date('H');
  if ($hour < 12) $greeting = 'Good morning';
  elseif ($hour < 18) $greeting = 'Good afternoon';
  else $greeting = 'Good evening';
}

if (isset($conn) && $conn instanceof mysqli) {
  // Room status
  $r = $conn->query("SELECT
    SUM(CASE WHEN item_type = 'room' AND room_status = 'occupied' THEN 1 ELSE 0 END) AS occupied,
    SUM(CASE WHEN item_type = 'room' AND room_status = 'available' THEN 1 ELSE 0 END) AS available
    FROM items");
  if ($r) {
    $row = $r->fetch_assoc();
    $occupied_rooms = intval($row['occupied'] ?? 0);
    $available_rooms = intval($row['available'] ?? 0);
  }

  // Today check-ins and check-outs
  $r = $conn->query("SELECT
    SUM(CASE WHEN DATE(checkin) = CURDATE() THEN 1 ELSE 0 END) AS today_checkins,
    SUM(CASE WHEN DATE(checkout) = CURDATE() THEN 1 ELSE 0 END) AS today_checkouts
    FROM bookings");
  if ($r) {
    $row = $r->fetch_assoc();
    $today_checkins = intval($row['today_checkins'] ?? 0);
    $today_checkouts = intval($row['today_checkouts'] ?? 0);
  }

  // Booking counts
  $r = $conn->query("SELECT
    COUNT(*) AS total,
    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled
    FROM bookings");
  if ($r) {
    $row = $r->fetch_assoc();
    $total_bookings = intval($row['total'] ?? 0);
    $cancelled_bookings = intval($row['cancelled'] ?? 0);
  }

  // Today's revenue (sum of amount for verified payments today, excluding cancelled/rejected)
  $r = $conn->query("SELECT COALESCE(SUM(COALESCE(amount,0)),0) AS total_today FROM bookings WHERE DATE(payment_verified_at) = CURDATE() AND payment_status = 'verified' AND status NOT IN ('cancelled', 'rejected')");
  if ($r) {
    $row = $r->fetch_assoc();
    $today_revenue = floatval($row['total_today'] ?? 0.00);
  }

  // Recent bookings (limit 6)
  $res = $conn->query("SELECT b.id, b.receipt_no, b.checkin, b.checkout, b.status, b.created_at, i.name as room_name FROM bookings b LEFT JOIN items i ON b.room_id = i.id ORDER BY b.created_at DESC LIMIT 6");
  if ($res) {
    while ($rb = $res->fetch_assoc()) {
      $recent_bookings[] = $rb;
    }
  }

  // Sparkline data: last 7 days revenue (verified payments, excluding cancelled/rejected)
  $spark_data = [];
  $spark_res = $conn->query("SELECT DATE(payment_date) as d, COALESCE(SUM(COALESCE(amount,0)),0) as total FROM bookings WHERE payment_status = 'verified' AND status NOT IN ('cancelled', 'rejected') AND payment_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY DATE(payment_date) ORDER BY DATE(payment_date) ASC");
  if ($spark_res) {
      $tmp = [];
      while ($sr = $spark_res->fetch_assoc()) {
          $tmp[$sr['d']] = floatval($sr['total']);
      }
      // build 7 days array
      for ($i = 6; $i >= 0; $i--) {
          $d = date('Y-m-d', strtotime("-{$i} days"));
          $spark_data[] = isset($tmp[$d]) ? $tmp[$d] : 0.0;
      }
  }
}
?>

<!-- Dashboard Section Content -->

        <!-- Welcome Header -->
        <div class="row mb-4">
          <div class="col-12">
            <div class="card border-0 shadow-lg overflow-hidden" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
              <div class="card-body p-4">
                <div class="row align-items-center">
                  <div class="col-md-8">
                    <div class="d-flex align-items-center mb-3">
                      <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                           style="width: 60px; height: 60px; background: rgba(255,255,255,0.2); backdrop-filter: blur(10px);">
                        <i class="fas fa-tachometer-alt fa-2x text-white"></i>
                      </div>
                      <div>
                        <h3 class="text-white mb-1 fw-bold">Admin Dashboard testttt</h3>
                        <p class="text-white-50 mb-0 small">BarCIE International Center Management</p>
                      </div>
                    </div>
                    <p class="text-white mb-2" style="font-size: 0.95rem;">
                      <i class="fas fa-chart-line me-2"></i>Real-time overview of your hotel operations and performance metrics
                    </p>
                    <div class="d-flex align-items-center text-white-50">
                      <i class="fas fa-clock me-2"></i>
                      <small>Last updated: <?php echo date('M d, Y - H:i'); ?></small>
                    </div>
                  </div>
                  <div class="col-md-4 text-end">
                    <?php if ($admin_display_name): ?>
                      <div class="d-inline-flex align-items-center" style="gap:12px; background:rgba(255,255,255,0.1); padding:12px 20px; border-radius:12px; backdrop-filter: blur(10px);">
                        <div style="width:44px; height:44px; border-radius:50%; background:rgba(255,255,255,0.15); display:flex; align-items:center; justify-content:center;">
                          <i class="fas fa-user-shield text-white" style="font-size:1.1rem;"></i>
                        </div>
                        <div class="text-start">
                          <div style="color:#ffffff; font-size:1.05rem; font-weight:700; line-height:1.2;"><?php echo htmlspecialchars($greeting . ', ' . $admin_display_name); ?></div>
                          <?php if ($admin_role): ?><small class="text-white-50" style="opacity:0.9; text-transform: uppercase; font-size: 0.75rem; font-weight: 600; letter-spacing: 0.5px;"><?php echo htmlspecialchars($admin_role); ?></small><?php endif; ?>
                        </div>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Dashboard Metrics Grid -->
        <div class="row g-3 mb-4">
          <!-- Room Status Section -->
          <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100" style="border-left: 3px solid #2a5298 !important;">
              <div class="card-body p-3">
                <h6 class="fw-bold mb-3" style="color: #2a5298; font-size: 0.9rem;">
                  <i class="fas fa-door-open me-2"></i>ROOM STATUS
                </h6>
                <div class="row g-2">
                  <div class="col-6">
                    <div class="p-3 rounded" style="background-color: #fff5f5; border: 1px solid #ffe0e0;">
                      <div class="d-flex align-items-center mb-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-2" 
                             style="width: 36px; height: 36px; background-color: #dc3545;">
                          <i class="fas fa-door-closed text-white" style="font-size: 0.85rem;"></i>
                        </div>
                        <div>
                          <small class="text-muted d-block" style="font-size: 0.7rem;">OCCUPIED</small>
                          <h4 class="mb-0 fw-bold" style="color: #dc3545;"><?php echo $occupied_rooms; ?></h4>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="p-3 rounded" style="background-color: #f0fdf4; border: 1px solid #dcfce7;">
                      <div class="d-flex align-items-center mb-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-2" 
                             style="width: 36px; height: 36px; background-color: #28a745;">
                          <i class="fas fa-door-open text-white" style="font-size: 0.85rem;"></i>
                        </div>
                        <div>
                          <small class="text-muted d-block" style="font-size: 0.7rem;">AVAILABLE</small>
                          <h4 class="mb-0 fw-bold" style="color: #28a745;"><?php echo $available_rooms; ?></h4>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>  

          <!-- Today Section -->
          <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100" style="border-left: 3px solid #17a2b8 !important;">
              <div class="card-body p-3">
                <h6 class="fw-bold mb-3" style="color: #17a2b8; font-size: 0.9rem;">
                  <i class="fas fa-calendar-day me-2"></i>TODAY
                </h6>
                <div class="row g-2">
                  <div class="col-6">
                    <div class="p-3 rounded" style="background-color: #f0f9ff; border: 1px solid #e0f2fe;">
                      <div class="d-flex align-items-center mb-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-2" 
                             style="width: 36px; height: 36px; background-color: #17a2b8;">
                          <i class="fas fa-sign-in-alt text-white" style="font-size: 0.85rem;"></i>
                        </div>
                        <div>
                          <small class="text-muted d-block" style="font-size: 0.7rem;">CHECK-INS</small>
                          <h4 class="mb-0 fw-bold" style="color: #17a2b8;"><?php echo $today_checkins; ?></h4>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="p-3 rounded" style="background-color: #f8f9fa; border: 1px solid #e9ecef;">
                      <div class="d-flex align-items-center mb-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-2" 
                              style="width: 36px; height: 36px; background-color: #6c757d;">
                          <i class="fas fa-sign-out-alt text-white" style="font-size: 0.85rem;"></i>
                        </div>
                        <div>
                          <small class="text-muted d-block" style="font-size: 0.7rem;">CHECK-OUTS</small>
                          <h4 class="mb-0 fw-bold" style="color: #6c757d;"><?php echo $today_checkouts; ?></h4>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Bookings Section -->
          <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100" style="border-left: 3px solid #007bff !important;">
              <div class="card-body p-3">
                <h6 class="fw-bold mb-3" style="color: #007bff; font-size: 0.9rem;">
                  <i class="fas fa-clipboard-list me-2"></i>BOOKINGS
                </h6>
                <div class="row g-2">
                  <div class="col-6">
                    <div class="p-3 rounded" style="background-color: #eff6ff; border: 1px solid #dbeafe;">
                      <div class="d-flex align-items-center mb-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-2" 
                             style="width: 36px; height: 36px; background-color: #007bff;">
                          <i class="fas fa-list-alt text-white" style="font-size: 0.85rem;"></i>
                        </div>
                        <div>
                          <small class="text-muted d-block" style="font-size: 0.7rem;">TOTAL</small>
                          <h4 class="mb-0 fw-bold" style="color: #007bff;"><?php echo $total_bookings; ?></h4>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="p-3 rounded" style="background-color: #fffbeb; border: 1px solid #fef3c7;">
                      <div class="d-flex align-items-center mb-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-2" 
                             style="width: 36px; height: 36px; background-color: #ffc107;">
                          <i class="fas fa-times-circle text-white" style="font-size: 0.85rem;"></i>
                        </div>
                        <div>
                          <small class="text-muted d-block" style="font-size: 0.7rem;">CANCELLED</small>
                          <h4 class="mb-0 fw-bold" style="color: #ffc107;"><?php echo $cancelled_bookings; ?></h4>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Revenue Section -->
          <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100" style="border-left: 3px solid #20c997 !important;">
              <div class="card-body p-3">
                <h6 class="fw-bold mb-3" style="color: #20c997; font-size: 0.9rem;">
                  <i class="fas fa-money-bill-wave me-2"></i>REVENUE
                </h6>
                <div class="p-3 rounded text-center" style="background: linear-gradient(135deg, #20c997 0%, #17a085 100%);">
                  <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="fas fa-peso-sign text-white me-2" style="font-size: 1.5rem;"></i>
                    <h2 class="mb-0 fw-bold text-white" style="font-size: 2rem;">₱<?php echo number_format($today_revenue, 2); ?></h2>
                  </div>
                  <small class="text-white" style="opacity: 0.9; font-size: 0.75rem;">TODAY'S REVENUE</small>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Activities Section -->
        <div class="row mt-4">
          <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-left: 3px solid #6f42c1 !important;">
              <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-4">
                  <div class="d-flex align-items-center">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                         style="width: 48px; height: 48px; background-color: #6f42c1;">
                      <i class="fas fa-history text-white" style="font-size: 1.2rem;"></i>
                    </div>
                    <div>
                      <h5 class="mb-0 fw-bold" style="color: #2d3748;">Recent Activities</h5>
                      <small class="text-muted">Latest system events and actions</small>
                    </div>
                  </div>
                  <span class="badge bg-primary px-3 py-2" style="font-size: 0.75rem; font-weight: 600;">LIVE</span>
                </div>

                <!-- Activities List -->
                <div id="recentActivitiesList" class="activity-timeline">
                  <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                      <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted mt-2 mb-0">Loading recent activities...</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <script>
        // Fetch and display recent activities
        let cachedActivities = [];
        let lastUpdateTime = null;

        function loadRecentActivities() {
          fetch('api/recent_activities.php?limit=8', {
            credentials: 'same-origin'
          })
            .then(response => {
              if (!response.ok) {
                return response.json().then(err => {
                  throw new Error(err.error || 'Network response was not ok');
                });
              }
              return response.json();
            })
            .then(data => {
              if (data.success && data.activities) {
                cachedActivities = data.activities;
                lastUpdateTime = new Date();
                displayActivities(cachedActivities);
              } else {
                showActivitiesError('No activities available');
              }
            })
            .catch(error => {
              console.error('Error loading activities:', error);
              showActivitiesError('Failed to load activities');
            });
        }

        function displayActivities(activities) {
          const container = document.getElementById('recentActivitiesList');
          if (!activities || activities.length === 0) {
            container.innerHTML = '<div class="text-center py-4"><p class="text-muted mb-0">No recent activities found</p></div>';
            return;
          }

          let html = '';
          activities.forEach((activity, index) => {
            const { icon, color, bgColor, text } = getActivityDisplay(activity);
            const timeAgo = formatTimeAgo(activity.activity_date);
            
            html += `
              <div class="activity-item d-flex align-items-start p-3 ${index !== activities.length - 1 ? 'border-bottom' : ''}" style="transition: background 0.2s;" data-timestamp="${activity.activity_date}">
                <div class="activity-icon me-3">
                  <div class="icon-circle rounded-circle d-flex align-items-center justify-content-center" 
                       style="width: 42px; height: 42px; background-color: ${bgColor}; flex-shrink: 0;">
                    <i class="${icon}" style="color: ${color}; font-size: 1rem;"></i>
                  </div>
                </div>
                <div class="flex-grow-1" style="min-width: 0;">
                  <div class="d-flex align-items-start justify-content-between">
                    <p class="mb-1" style="color: #4a5568; font-size: 0.95rem; line-height: 1.5;">
                      ${text}
                      ${activity.receipt_no ? `<span class="badge bg-secondary ms-2" style="font-size: 0.7rem; padding: 3px 8px;">${activity.receipt_no}</span>` : ''}
                    </p>
                    <small class="activity-time text-muted text-nowrap ms-3" style="font-size: 0.8rem; font-weight: 600;">${timeAgo}</small>
                  </div>
                </div>
              </div>
            `;
          });

          container.innerHTML = html;
        }

        function updateTimeAgo() {
          // Update time ago labels without re-fetching data
          const activityItems = document.querySelectorAll('.activity-item');
          activityItems.forEach(item => {
            const timestamp = item.getAttribute('data-timestamp');
            const timeElement = item.querySelector('.activity-time');
            if (timestamp && timeElement) {
              timeElement.textContent = formatTimeAgo(timestamp);
            }
          });
        }

        function getActivityDisplay(activity) {
          const guestName = activity.guest_name || 'Guest';
          const roomName = activity.room_name || 'Room';
          const adminName = activity.admin_name || 'Admin';

          switch (activity.activity_type) {
            case 'guest_checkin':
              return {
                icon: 'fas fa-sign-in-alt',
                color: '#3b82f6',
                bgColor: '#dbeafe',
                text: `<strong>${guestName}</strong> checked in to <strong>${roomName}</strong>`
              };
            case 'guest_checkout':
              return {
                icon: 'fas fa-sign-out-alt',
                color: '#6b7280',
                bgColor: '#f3f4f6',
                text: `<strong>${guestName}</strong> checked out from <strong>${roomName}</strong>`
              };
            case 'booking_approved':
              return {
                icon: 'fas fa-check-circle',
                color: '#10b981',
                bgColor: '#d1fae5',
                text: `Booking for <strong>${guestName}</strong> was approved by <strong>${adminName}</strong>`
              };
            case 'payment_approved':
              return {
                icon: 'fas fa-credit-card',
                color: '#10b981',
                bgColor: '#d1fae5',
                text: `Payment for <strong>${guestName}</strong> was approved by <strong>${adminName}</strong>`
              };
            case 'booking_cancelled':
              return {
                icon: 'fas fa-times-circle',
                color: '#ef4444',
                bgColor: '#fee2e2',
                text: `Booking for <strong>${guestName}</strong> was cancelled`
              };
            case 'pencil_created':
              return {
                icon: 'fas fa-pencil-alt',
                color: '#8b5cf6',
                bgColor: '#ede9fe',
                text: `Pencil booking created for <strong>${guestName}</strong>`
              };
            case 'pencil_approved':
              return {
                icon: 'fas fa-user-check',
                color: '#10b981',
                bgColor: '#d1fae5',
                text: `Booking for <strong>${guestName}</strong> was approved by <strong>${adminName}</strong>`
              };
            case 'pencil_cancelled':
              return {
                icon: 'fas fa-ban',
                color: '#ef4444',
                bgColor: '#fee2e2',
                text: `Booking for <strong>${guestName}</strong> was cancelled`
              };
            case 'feedback_submitted':
              const stars = '⭐'.repeat(activity.rating || 0);
              return {
                icon: 'fas fa-comment-dots',
                color: '#f59e0b',
                bgColor: '#fef3c7',
                text: `<strong>${guestName}</strong> submitted ${stars} feedback`
              };
            default:
              return {
                icon: 'fas fa-info-circle',
                color: '#6b7280',
                bgColor: '#f3f4f6',
                text: `Activity: ${activity.activity_type}`
              };
          }
        }

        function formatTimeAgo(dateString) {
          const date = new Date(dateString);
          const now = new Date();
          const diffMs = now - date;
          const diffSecs = Math.floor(diffMs / 1000);
          const diffMins = Math.floor(diffSecs / 60);
          const diffHours = Math.floor(diffMins / 60);
          const diffDays = Math.floor(diffHours / 24);

          if (diffSecs < 60) return 'JUST NOW';
          if (diffMins < 60) return diffMins + 'M AGO';
          if (diffHours < 24) return diffHours + 'H AGO';
          if (diffDays === 1) return '1D AGO';
          if (diffDays < 7) return diffDays + 'D AGO';
          return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        }

        function showActivitiesError(message) {
          const container = document.getElementById('recentActivitiesList');
          container.innerHTML = `
            <div class="text-center py-4">
              <i class="fas fa-exclamation-circle text-warning mb-2" style="font-size: 2rem;"></i>
              <p class="text-muted mb-0">${message || 'Failed to load recent activities'}</p>
              <button class="btn btn-sm btn-primary mt-2" onclick="loadRecentActivities()">
                <i class="fas fa-refresh me-1"></i>Retry
              </button>
            </div>
          `;
        }

        // Load activities on page load
        document.addEventListener('DOMContentLoaded', function() {
          // Load immediately
          loadRecentActivities();
          // Update time ago every 1 second for real-time counting
          setInterval(updateTimeAgo, 1000);
          // Refresh activities every 15 seconds for faster updates
          setInterval(loadRecentActivities, 15000);
        });
        </script>

