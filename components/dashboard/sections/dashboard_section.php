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

  // Today's revenue (sum of amount for verified payments today)
  $r = $conn->query("SELECT COALESCE(SUM(COALESCE(amount,0)),0) AS total_today FROM bookings WHERE DATE(payment_date) = CURDATE() AND payment_status = 'verified'");
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

  // Sparkline data: last 7 days revenue (verified payments)
  $spark_data = [];
  $spark_res = $conn->query("SELECT DATE(payment_date) as d, COALESCE(SUM(COALESCE(amount,0)),0) as total FROM bookings WHERE payment_status = 'verified' AND payment_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY DATE(payment_date) ORDER BY DATE(payment_date) ASC");
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
            <div class="card border-0 overflow-hidden" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%); box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);">
              <div class="card-body p-4">
                <div class="row align-items-center">
                  <div class="col-md-7">
                    <div class="d-flex align-items-center mb-3">
                      <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                           style="width: 64px; height: 64px; background: rgba(255,255,255,0.25); backdrop-filter: blur(10px); box-shadow: 0 4px 20px rgba(0,0,0,0.15);">
                        <i class="fas fa-tachometer-alt text-white" style="font-size: 1.6rem;"></i>
                      </div>
                      <div>
                        <h2 class="text-white mb-1 fw-bold" style="font-size: 1.8rem; letter-spacing: -0.5px;">Admin Dashboard</h2>
                        <p class="text-white mb-0" style="opacity: 0.9; font-size: 0.95rem;">BarCIE International Center Management</p>
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
                  <div class="col-md-5 d-flex justify-content-end">
                    <?php if ($admin_display_name): ?>
                      <div class="d-flex align-items-center p-3 rounded" style="gap:14px; background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); box-shadow: 0 4px 20px rgba(0,0,0,0.15);">
                        <div style="width:56px; height:56px; border-radius:50%; background:rgba(255,255,255,0.25); display:flex; align-items:center; justify-content:center; box-shadow: 0 3px 10px rgba(0,0,0,0.15);">
                          <i class="fas fa-user-shield text-white" style="font-size:1.4rem;"></i>
                        </div>
                        <div>
                          <div style="color:#ffffff; font-size:1.15rem; font-weight:700; line-height:1.2;"><?php echo htmlspecialchars($greeting . ', ' . $admin_display_name); ?></div>
                          <?php if ($admin_role): ?><span class="badge" style="background:rgba(255,255,255,0.3); color:#fff; margin-top:4px; font-size:0.75rem; font-weight:600; padding:4px 12px; text-transform: uppercase; letter-spacing: 0.5px;"><?php echo htmlspecialchars(str_replace('_', ' ', $admin_role)); ?></span><?php endif; ?>
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
        <div class="row g-4 mb-4">
          <!-- Room Status Section -->
          <div class="col-lg-6">
            <div class="card border-0 h-100" style="border-left: 4px solid #3b82f6 !important; box-shadow: 0 4px 15px rgba(59, 130, 246, 0.15);">
              <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                  <div class="rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 38px; height: 38px; background: linear-gradient(135deg, #3b82f6, #2563eb); box-shadow: 0 3px 10px rgba(59, 130, 246, 0.3);">
                    <i class="fas fa-door-open text-white" style="font-size: 0.9rem;"></i>
                  </div>
                  <h6 class="fw-bold mb-0" style="color: #1e40af; font-size: 1rem; letter-spacing: -0.3px;">Room Status</h6>
                </div>
                <div class="row g-3">
                  <div class="col-6">
                    <div class="p-3 rounded-3" style="background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border: 1px solid #fca5a5; box-shadow: 0 2px 8px rgba(220, 38, 38, 0.1);">
                      <div class="d-flex align-items-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                             style="width: 42px; height: 42px; background-color: #dc2626; box-shadow: 0 3px 10px rgba(220, 38, 38, 0.3);">
                          <i class="fas fa-door-closed text-white" style="font-size: 0.95rem;"></i>
                        </div>
                        <div>
                          <small class="text-muted d-block fw-semibold" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Occupied</small>
                          <h3 class="mb-0 fw-bold" style="color: #dc2626; font-size: 1.8rem;"><?php echo $occupied_rooms; ?></h3>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="p-3 rounded-3" style="background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); border: 1px solid #86efac; box-shadow: 0 2px 8px rgba(34, 197, 94, 0.1);">
                      <div class="d-flex align-items-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                             style="width: 42px; height: 42px; background-color: #16a34a; box-shadow: 0 3px 10px rgba(22, 163, 74, 0.3);">
                          <i class="fas fa-door-open text-white" style="font-size: 0.95rem;"></i>
                        </div>
                        <div>
                          <small class="text-muted d-block fw-semibold" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Available</small>
                          <h3 class="mb-0 fw-bold" style="color: #16a34a; font-size: 1.8rem;"><?php echo $available_rooms; ?></h3>
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
            <div class="card border-0 h-100" style="border-left: 4px solid #06b6d4 !important; box-shadow: 0 4px 15px rgba(6, 182, 212, 0.15);">
              <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                  <div class="rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 38px; height: 38px; background: linear-gradient(135deg, #06b6d4, #0891b2); box-shadow: 0 3px 10px rgba(6, 182, 212, 0.3);">
                    <i class="fas fa-calendar-day text-white" style="font-size: 0.9rem;"></i>
                  </div>
                  <h6 class="fw-bold mb-0" style="color: #0e7490; font-size: 1rem; letter-spacing: -0.3px;">Today's Activity</h6>
                </div>
                <div class="row g-3">
                  <div class="col-6">
                    <div class="p-3 rounded-3" style="background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); border: 1px solid #93c5fd; box-shadow: 0 2px 8px rgba(59, 130, 246, 0.1);">
                      <div class="d-flex align-items-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                             style="width: 42px; height: 42px; background-color: #2563eb; box-shadow: 0 3px 10px rgba(37, 99, 235, 0.3);">
                          <i class="fas fa-sign-in-alt text-white" style="font-size: 0.95rem;"></i>
                        </div>
                        <div>
                          <small class="text-muted d-block fw-semibold" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Check-ins</small>
                          <h3 class="mb-0 fw-bold" style="color: #2563eb; font-size: 1.8rem;"><?php echo $today_checkins; ?></h3>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="p-3 rounded-3" style="background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%); border: 1px solid #d1d5db; box-shadow: 0 2px 8px rgba(107, 114, 128, 0.1);">
                      <div class="d-flex align-items-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                             style="width: 42px; height: 42px; background-color: #6b7280; box-shadow: 0 3px 10px rgba(107, 114, 128, 0.3);">
                          <i class="fas fa-sign-out-alt text-white" style="font-size: 0.95rem;"></i>
                        </div>
                        <div>
                          <small class="text-muted d-block fw-semibold" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Check-outs</small>
                          <h3 class="mb-0 fw-bold" style="color: #6b7280; font-size: 1.8rem;"><?php echo $today_checkouts; ?></h3>
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
            <div class="card border-0 h-100" style="border-left: 4px solid #8b5cf6 !important; box-shadow: 0 4px 15px rgba(139, 92, 246, 0.15);">
              <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                  <div class="rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 38px; height: 38px; background: linear-gradient(135deg, #8b5cf6, #7c3aed); box-shadow: 0 3px 10px rgba(139, 92, 246, 0.3);">
                    <i class="fas fa-clipboard-list text-white" style="font-size: 0.9rem;"></i>
                  </div>
                  <h6 class="fw-bold mb-0" style="color: #6d28d9; font-size: 1rem; letter-spacing: -0.3px;">Bookings</h6>
                </div>
                <div class="row g-3">
                  <div class="col-6">
                    <div class="p-3 rounded-3" style="background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%); border: 1px solid #a5b4fc; box-shadow: 0 2px 8px rgba(99, 102, 241, 0.1);">
                      <div class="d-flex align-items-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                             style="width: 42px; height: 42px; background-color: #6366f1; box-shadow: 0 3px 10px rgba(99, 102, 241, 0.3);">
                          <i class="fas fa-list-alt text-white" style="font-size: 0.95rem;"></i>
                        </div>
                        <div>
                          <small class="text-muted d-block fw-semibold" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Total</small>
                          <h3 class="mb-0 fw-bold" style="color: #6366f1; font-size: 1.8rem;"><?php echo $total_bookings; ?></h3>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="p-3 rounded-3" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border: 1px solid #fcd34d; box-shadow: 0 2px 8px rgba(245, 158, 11, 0.1);">
                      <div class="d-flex align-items-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                             style="width: 42px; height: 42px; background-color: #f59e0b; box-shadow: 0 3px 10px rgba(245, 158, 11, 0.3);">
                          <i class="fas fa-times-circle text-white" style="font-size: 0.95rem;"></i>
                        </div>
                        <div>
                          <small class="text-muted d-block fw-semibold" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Cancelled</small>
                          <h3 class="mb-0 fw-bold" style="color: #f59e0b; font-size: 1.8rem;"><?php echo $cancelled_bookings; ?></h3>
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
            <div class="card border-0 h-100" style="border-left: 4px solid #10b981 !important; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.15);">
              <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                  <div class="rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 38px; height: 38px; background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 3px 10px rgba(16, 185, 129, 0.3);">
                    <i class="fas fa-money-bill-wave text-white" style="font-size: 0.9rem;"></i>
                  </div>
                  <h6 class="fw-bold mb-0" style="color: #047857; font-size: 1rem; letter-spacing: -0.3px;">Today's Revenue</h6>
                </div>
                <div class="p-4 rounded-3 text-center" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); box-shadow: 0 4px 15px rgba(16, 185, 129, 0.25);">
                  <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="fas fa-peso-sign text-white me-2" style="font-size: 1.5rem; opacity: 0.9;"></i>
                    <h2 class="mb-0 fw-bold text-white" style="font-size: 2.2rem; letter-spacing: -1px;">₱<?php echo number_format($today_revenue, 2); ?></h2>
                  </div>
                  <div class="badge" style="background: rgba(255,255,255,0.25); color: #fff; font-size: 0.75rem; font-weight: 600; padding: 6px 14px;">Verified Payments</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Activities Section -->
        <div class="row mb-4">
          <div class="col-12">
            <div class="card border-0" style="border-left: 4px solid #8b5cf6 !important; box-shadow: 0 4px 15px rgba(139, 92, 246, 0.15);">
              <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-4">
                  <div class="d-flex align-items-center">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 42px; height: 42px; background: linear-gradient(135deg, #8b5cf6, #6d28d9); box-shadow: 0 3px 10px rgba(139, 92, 246, 0.3);">
                      <i class="fas fa-history text-white" style="font-size: 1rem;"></i>
                    </div>
                    <div>
                      <h5 class="fw-bold mb-0" style="color: #6d28d9; font-size: 1.1rem; letter-spacing: -0.3px;">Recent Activities</h5>
                      <small class="text-muted" style="font-size: 0.8rem;">Latest system events and actions</small>
                    </div>
                  </div>
                  <span class="badge rounded-pill" style="background: linear-gradient(135deg, #8b5cf6, #6d28d9); padding: 6px 14px; font-size: 0.75rem;">Live</span>
                </div>
                <div id="recentActivitiesContainer">
                  <div class="text-center py-5">
                    <div class="spinner-border text-primary mb-3" role="status" style="width: 2.5rem; height: 2.5rem; border-width: 3px;">
                      <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted mb-0" style="font-size: 0.9rem; font-weight: 500;">Loading activities...</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <script>
        (function(){
          try {
            var data = <?php echo json_encode(array_map('floatval', $spark_data ?? [])); ?> || [];
            var c = document.getElementById('revenueSparkline');
            if (!c || !c.getContext) return;
            var ctx = c.getContext('2d');
            var w = c.width, h = c.height; ctx.clearRect(0,0,w,h);
            if (data.length === 0) {
              // draw placeholder
              ctx.fillStyle = 'rgba(255,255,255,0.05)';
              ctx.fillRect(0,0,w,h);
              ctx.fillStyle = '#ffffff'; ctx.font = '10px sans-serif'; ctx.fillText('No data', w/2 - 16, h/2 + 4);
              return;
            }
            var max = Math.max.apply(null, data.concat([1]));
            var min = Math.min.apply(null, data.concat([0]));
            var pad = 6;
            var len = data.length;
            var stepX = (w - pad*2) / Math.max(1, len-1);
            ctx.beginPath();
            for (var i=0;i<len;i++){
              var x = pad + i*stepX;
              var y = pad + (1 - ((data[i]-min)/(max-min || 1))) * (h - pad*2);
              if (i===0) ctx.moveTo(x,y); else ctx.lineTo(x,y);
            }
            ctx.strokeStyle = 'rgba(255,255,255,0.95)'; ctx.lineWidth = 2; ctx.stroke();
            // fill under curve
            ctx.lineTo(pad + (len-1)*stepX, h-pad);
            ctx.lineTo(pad, h-pad);
            ctx.closePath();
            ctx.fillStyle = 'rgba(255,255,255,0.08)'; ctx.fill();
          } catch(e) { console && console.warn && console.warn('sparkline', e); }
        })();
        </script>

        <script>
        // Load Recent Activities
        (function() {
          let cachedActivities = [];
          
          function loadRecentActivities() {
            fetch('api/recent_activities.php?limit=8')
              .then(response => {
                if (!response.ok) {
                  // if unauthorized, try again asking for sample activities for demo
                  if (response.status === 401) {
                    return fetch('api/recent_activities.php?limit=8&allow_sample=1').then(r => r.json());
                  }
                  throw new Error('Network response was not ok');
                }
                return response.json();
              })
              .then(data => {
                if (data.success && data.activities && data.activities.length > 0) {
                  cachedActivities = data.activities;
                  renderActivities();
                } else {
                  const container = document.getElementById('recentActivitiesContainer');
                  if (container) {
                    container.innerHTML = `
                      <div class="text-center py-5">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px; background: linear-gradient(135deg, #e5e7eb, #f3f4f6);">
                          <i class="fas fa-inbox" style="font-size: 1.8rem; color: #9ca3af;"></i>
                        </div>
                        <p class="text-muted mb-0" style="font-size: 0.95rem; font-weight: 500;">No recent activities</p>
                        <small class="text-muted" style="font-size: 0.8rem;">Activity feed will appear here when actions occur</small>
                      </div>
                    `;
                  }
                }
              })
              .catch(error => {
                console.error('Failed to load activities:', error);
                const container = document.getElementById('recentActivitiesContainer');
                if (container) {
                  container.innerHTML = `
                    <div class="text-center py-5">
                      <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px; background: linear-gradient(135deg, #fee2e2, #fecaca);">
                        <i class="fas fa-exclamation-triangle" style="font-size: 1.8rem; color: #dc2626;"></i>
                      </div>
                      <p class="text-danger mb-0" style="font-size: 0.95rem; font-weight: 500;">Failed to load activities</p>
                      <small class="text-muted" style="font-size: 0.8rem;">Please refresh the page to try again</small>
                    </div>
                  `;
                }
              });
          }
          
          function renderActivities() {
            const container = document.getElementById('recentActivitiesContainer');
            if (!container || cachedActivities.length === 0) return;
            
            let html = '<div class="list-group list-group-flush">';
            
            cachedActivities.forEach((activity, index) => {
              const icon = getActivityIcon(activity.activity_type);
              const color = getActivityColor(activity.activity_type);
              const message = getActivityMessage(activity);
              const timeAgo = formatTimeAgo(activity.activity_date);
              
              html += `
                <div class="list-group-item border-0 px-0 py-3 activity-item" style="border-bottom: 1px solid #f1f5f9 !important; transition: all 0.2s ease;" data-activity-date="${escapeHtml(activity.activity_date)}">
                  <div class="d-flex align-items-start">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                         style="width: 44px; height: 44px; min-width: 44px; background: linear-gradient(135deg, ${color}20, ${color}10); border: 2px solid ${color}; box-shadow: 0 2px 8px ${color}25;">
                      <i class="${icon}" style="color: ${color}; font-size: 0.95rem;"></i>
                    </div>
                    <div class="flex-grow-1">
                      <div class="d-flex justify-content-between align-items-start mb-1">
                        <p class="mb-0 activity-message" style="font-size: 0.9rem; line-height: 1.5; color: #334155;">
                          ${message}
                        </p>
                        <span class="badge bg-light text-muted ms-3 activity-time" style="font-size: 0.7rem; white-space: nowrap; font-weight: 500; padding: 4px 8px;">${timeAgo}</span>
                      </div>
                    </div>
                  </div>
                </div>
              `;
            });
            
            html += '</div>';
            
            // Add hover styles
            html += `
            <style>
              .activity-item:hover {
                background-color: #f8fafc !important;
                transform: translateX(4px);
                cursor: pointer;
              }
              .activity-item:last-child {
                border-bottom: none !important;
              }
              .activity-message strong {
                color: #1e293b;
                font-weight: 600;
              }
            </style>
            `;
            
            container.innerHTML = html;
          }
          
          function updateTimestamps() {
            // Update all timestamp badges without re-fetching data
            const activityItems = document.querySelectorAll('.activity-item');
            activityItems.forEach(item => {
              const activityDate = item.getAttribute('data-activity-date');
              if (activityDate) {
                const timeBadge = item.querySelector('.activity-time');
                if (timeBadge) {
                  timeBadge.textContent = formatTimeAgo(activityDate);
                }
              }
            });
          }
          
          function getActivityIcon(type) {
            const icons = {
              'pencil_created': 'fas fa-pencil-alt',
              'pencil_approved': 'fas fa-check-circle',
              'pencil_converted': 'fas fa-exchange-alt',
              'pencil_cancelled': 'fas fa-times-circle',
              'booking_reserved': 'fas fa-calendar-plus',
              'payment_approved': 'fas fa-money-check-alt',
              'booking_approved': 'fas fa-user-check',
              'guest_checkin': 'fas fa-sign-in-alt',
              'guest_checkout': 'fas fa-sign-out-alt',
              'booking_cancelled': 'fas fa-ban'
            };
            return icons[type] || 'fas fa-info-circle';
          }
          
          function getActivityColor(type) {
            const colors = {
              'pencil_created': '#ffc107',
              'pencil_approved': '#28a745',
              'booking_approved': '#28a745',
              'pencil_converted': '#17a2b8',
              'pencil_cancelled': '#dc3545',
              'booking_reserved': '#007bff',
              'payment_approved': '#20c997',
              'guest_checkin': '#007bff',
              'guest_checkout': '#6c757d',
              'booking_cancelled': '#dc3545'
            };
            return colors[type] || '#6c757d';
          }
          
          function getActivityMessage(activity) {
            const guestName = activity.guest_name || 'Guest';
            const roomName = activity.room_name || 'Room';
            const receiptNo = activity.receipt_no || '';
            const adminName = activity.admin_name || '';
            
            const messages = {
              'pencil_created': `<strong>${escapeHtml(guestName)}</strong> created a draft booking for <strong>${escapeHtml(roomName)}</strong> <span class="badge bg-secondary" style="font-size: 0.65rem;">${escapeHtml(receiptNo)}</span>`,
              'pencil_approved': `Draft booking for <strong>${escapeHtml(guestName)}</strong> was approved${adminName ? ' by <strong>' + escapeHtml(adminName) + '</strong>' : ''} <span class="badge bg-secondary" style="font-size: 0.65rem;">${escapeHtml(receiptNo)}</span>`,
              'pencil_converted': `Draft booking for <strong>${escapeHtml(guestName)}</strong> was converted to full booking <span class="badge bg-secondary" style="font-size: 0.65rem;">${escapeHtml(receiptNo)}</span>`,
              'pencil_cancelled': `Draft booking for <strong>${escapeHtml(guestName)}</strong> was cancelled <span class="badge bg-secondary" style="font-size: 0.65rem;">${escapeHtml(receiptNo)}</span>`,
              'booking_reserved': `<strong>${escapeHtml(guestName)}</strong> submitted payment for <strong>${escapeHtml(roomName)}</strong> <span class="badge bg-secondary" style="font-size: 0.65rem;">${escapeHtml(receiptNo)}</span>`,
              'payment_approved': `Payment for <strong>${escapeHtml(guestName)}</strong> was approved${adminName ? ' by <strong>' + escapeHtml(adminName) + '</strong>' : ''} <span class="badge bg-secondary" style="font-size: 0.65rem;">${escapeHtml(receiptNo)}</span>`,
              'booking_approved': `Booking for <strong>${escapeHtml(guestName)}</strong> was approved${adminName ? ' by <strong>' + escapeHtml(adminName) + '</strong>' : ''} <span class="badge bg-secondary" style="font-size: 0.65rem;">${escapeHtml(receiptNo)}</span>`,
              'guest_checkin': `<strong>${escapeHtml(guestName)}</strong> checked in to <strong>${escapeHtml(roomName)}</strong> <span class="badge bg-secondary" style="font-size: 0.65rem;">${escapeHtml(receiptNo)}</span>`,
              'guest_checkout': `<strong>${escapeHtml(guestName)}</strong> checked out from <strong>${escapeHtml(roomName)}</strong> <span class="badge bg-secondary" style="font-size: 0.65rem;">${escapeHtml(receiptNo)}</span>`,
              'booking_cancelled': `Booking for <strong>${escapeHtml(guestName)}</strong> was cancelled <span class="badge bg-secondary" style="font-size: 0.65rem;">${escapeHtml(receiptNo)}</span>`
            };
            
            return messages[activity.activity_type] || 'Activity recorded';
          }
          
          function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
          }
          
          function formatTimeAgo(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const seconds = Math.floor((now - date) / 1000);
            
            if (seconds < 60) return 'Just now';
            if (seconds < 3600) return Math.floor(seconds / 60) + 'm ago';
            if (seconds < 86400) return Math.floor(seconds / 3600) + 'h ago';
            if (seconds < 2592000) return Math.floor(seconds / 86400) + 'd ago';
            return date.toLocaleDateString();
          }
          
          // Load on page load
          if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', loadRecentActivities);
          } else {
            loadRecentActivities();
          }
          
          // Refresh data every 30 seconds
          setInterval(loadRecentActivities, 30000);
          
          // Update timestamps every 10 seconds (without refetching data)
          setInterval(updateTimestamps, 10000);
        })();
        </script>

