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
                        <h3 class="text-white mb-1 fw-bold">Admin Dashboard</h3>
                        <p class="text-white-50 mb-0 small">BarCIE International Center Management</p>
                        <?php if ($admin_display_name): ?>
                          <div class="d-flex align-items-center mt-2" style="gap:12px;">
                            <div style="width:44px; height:44px; border-radius:50%; background:rgba(255,255,255,0.12); display:flex; align-items:center; justify-content:center;">
                              <i class="fas fa-user-shield text-white" style="font-size:1.1rem;"></i>
                            </div>
                            <div>
                              <div style="color:#ffffff; font-size:1.05rem; font-weight:700; line-height:1;"><?php echo htmlspecialchars($greeting . ', ' . $admin_display_name); ?></div>
                              <?php if ($admin_role): ?><small class="text-white-50" style="opacity:0.85;"><?php echo htmlspecialchars(ucfirst($admin_role)); ?></small><?php endif; ?>
                            </div>
                          </div>
                        <?php endif; ?>
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
                  <div class="col-md-4 text-center d-none d-md-block">
                    <div class="position-relative" style="opacity: 0.15;">
                      <i class="fas fa-hotel" style="font-size: 8rem; color: white;"></i>
                    </div>
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

