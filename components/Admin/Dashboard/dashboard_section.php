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

<!-- ═══════════════════════════════════════════════
     Dashboard Section
═══════════════════════════════════════════════ -->

<!-- Welcome Bar -->
<div class="row mb-4">
  <div class="col-12">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 px-4 py-3"
         style="background:#fff;border-radius:.875rem;
                border-left:4px solid #2563eb;
                box-shadow:0 1px 3px rgba(0,0,0,.07),0 4px 16px rgba(37,99,235,.1);">
      <!-- Left: page title -->
      <div class="d-flex align-items-center gap-3">
        <div class="d-flex align-items-center justify-content-center flex-shrink-0"
             style="width:46px;height:46px;background:linear-gradient(135deg,#1e3a8a,#2563eb);
                    border-radius:12px;box-shadow:0 4px 12px rgba(37,99,235,.35);">
          <i class="fas fa-tachometer-alt text-white" style="font-size:1.1rem;"></i>
        </div>
        <div>
          <h5 class="mb-0 fw-bold" style="color:#0f172a;letter-spacing:-.02em;">Admin Dashboard</h5>
          <small style="color:#64748b;">BarCIE International Center &nbsp;·&nbsp;
            <i class="fas fa-clock me-1" style="font-size:.65rem;"></i><?php echo date('M d, Y — H:i'); ?>
          </small>
        </div>
      </div>
      <!-- Right: greeting -->
      <?php if ($admin_display_name): ?>
      <div class="d-flex align-items-center gap-2">
        <div class="d-flex align-items-center justify-content-center flex-shrink-0"
             style="width:38px;height:38px;border-radius:50%;background:#eff6ff;border:2px solid #bfdbfe;">
          <i class="fas fa-user-shield" style="color:#2563eb;font-size:.9rem;"></i>
        </div>
        <div>
          <div class="fw-semibold" style="color:#0f172a;font-size:.9rem;line-height:1.2;">
            <?php echo htmlspecialchars($greeting . ', ' . $admin_display_name); ?>
          </div>
          <?php if ($admin_role): ?>
          <small style="color:#64748b;text-transform:uppercase;font-size:.65rem;letter-spacing:.07em;font-weight:600;">
            <?php echo htmlspecialchars($admin_role); ?>
          </small>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">

  <!-- Room Status -->
  <div class="col-xl-3 col-md-6">
    <div class="h-100" style="background:#fff;border-radius:.875rem;
                border-top:3px solid #2563eb;
                box-shadow:0 1px 3px rgba(0,0,0,.07),0 4px 16px rgba(0,0,0,.06);">
      <div class="px-3 pt-3 pb-1 d-flex align-items-center justify-content-between">
        <span style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#64748b;">
          Room Status
        </span>
        <div style="width:32px;height:32px;border-radius:8px;background:#eff6ff;
                    display:flex;align-items:center;justify-content:center;">
          <i class="fas fa-door-open" style="color:#2563eb;font-size:.8rem;"></i>
        </div>
      </div>
      <div class="px-3 pb-3">
        <div class="row g-2 mt-1">
          <div class="col-6">
            <div style="background:#fef2f2;border-radius:.5rem;padding:.75rem .875rem;">
              <div class="d-flex align-items-center gap-2 mb-1">
                <span style="width:8px;height:8px;border-radius:50%;background:#dc2626;display:inline-block;flex-shrink:0;"></span>
                <span style="font-size:.65rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;">Occupied</span>
              </div>
              <span style="font-size:1.75rem;font-weight:800;color:#dc2626;line-height:1;letter-spacing:-.03em;"><?php echo $occupied_rooms; ?></span>
            </div>
          </div>
          <div class="col-6">
            <div style="background:#f0fdf4;border-radius:.5rem;padding:.75rem .875rem;">
              <div class="d-flex align-items-center gap-2 mb-1">
                <span style="width:8px;height:8px;border-radius:50%;background:#16a34a;display:inline-block;flex-shrink:0;"></span>
                <span style="font-size:.65rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;">Available</span>
              </div>
              <span style="font-size:1.75rem;font-weight:800;color:#16a34a;line-height:1;letter-spacing:-.03em;"><?php echo $available_rooms; ?></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Today -->
  <div class="col-xl-3 col-md-6">
    <div class="h-100" style="background:#fff;border-radius:.875rem;
                border-top:3px solid #0891b2;
                box-shadow:0 1px 3px rgba(0,0,0,.07),0 4px 16px rgba(0,0,0,.06);">
      <div class="px-3 pt-3 pb-1 d-flex align-items-center justify-content-between">
        <span style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#64748b;">
          Today
        </span>
        <div style="width:32px;height:32px;border-radius:8px;background:#ecfeff;
                    display:flex;align-items:center;justify-content:center;">
          <i class="fas fa-calendar-day" style="color:#0891b2;font-size:.8rem;"></i>
        </div>
      </div>
      <div class="px-3 pb-3">
        <div class="row g-2 mt-1">
          <div class="col-6">
            <div style="background:#f0f9ff;border-radius:.5rem;padding:.75rem .875rem;">
              <div class="d-flex align-items-center gap-2 mb-1">
                <span style="width:8px;height:8px;border-radius:50%;background:#0891b2;display:inline-block;flex-shrink:0;"></span>
                <span style="font-size:.65rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;">Check-ins</span>
              </div>
              <span style="font-size:1.75rem;font-weight:800;color:#0891b2;line-height:1;letter-spacing:-.03em;"><?php echo $today_checkins; ?></span>
            </div>
          </div>
          <div class="col-6">
            <div style="background:#f8fafc;border-radius:.5rem;padding:.75rem .875rem;">
              <div class="d-flex align-items-center gap-2 mb-1">
                <span style="width:8px;height:8px;border-radius:50%;background:#64748b;display:inline-block;flex-shrink:0;"></span>
                <span style="font-size:.65rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;">Check-outs</span>
              </div>
              <span style="font-size:1.75rem;font-weight:800;color:#475569;line-height:1;letter-spacing:-.03em;"><?php echo $today_checkouts; ?></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bookings -->
  <div class="col-xl-3 col-md-6">
    <div class="h-100" style="background:#fff;border-radius:.875rem;
                border-top:3px solid #4f46e5;
                box-shadow:0 1px 3px rgba(0,0,0,.07),0 4px 16px rgba(0,0,0,.06);">
      <div class="px-3 pt-3 pb-1 d-flex align-items-center justify-content-between">
        <span style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#64748b;">
          Bookings
        </span>
        <div style="width:32px;height:32px;border-radius:8px;background:#eef2ff;
                    display:flex;align-items:center;justify-content:center;">
          <i class="fas fa-clipboard-list" style="color:#4f46e5;font-size:.8rem;"></i>
        </div>
      </div>
      <div class="px-3 pb-3">
        <div class="row g-2 mt-1">
          <div class="col-6">
            <div style="background:#eef2ff;border-radius:.5rem;padding:.75rem .875rem;">
              <div class="d-flex align-items-center gap-2 mb-1">
                <span style="width:8px;height:8px;border-radius:50%;background:#4f46e5;display:inline-block;flex-shrink:0;"></span>
                <span style="font-size:.65rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;">Total</span>
              </div>
              <span style="font-size:1.75rem;font-weight:800;color:#4f46e5;line-height:1;letter-spacing:-.03em;"><?php echo $total_bookings; ?></span>
            </div>
          </div>
          <div class="col-6">
            <div style="background:#fffbeb;border-radius:.5rem;padding:.75rem .875rem;">
              <div class="d-flex align-items-center gap-2 mb-1">
                <span style="width:8px;height:8px;border-radius:50%;background:#d97706;display:inline-block;flex-shrink:0;"></span>
                <span style="font-size:.65rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;">Cancelled</span>
              </div>
              <span style="font-size:1.75rem;font-weight:800;color:#d97706;line-height:1;letter-spacing:-.03em;"><?php echo $cancelled_bookings; ?></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Revenue -->
  <div class="col-xl-3 col-md-6">
    <div class="h-100 d-flex flex-column" style="background:#fff;border-radius:.875rem;
                border-top:3px solid #059669;
                box-shadow:0 1px 3px rgba(0,0,0,.07),0 4px 16px rgba(0,0,0,.06);">
      <div class="px-3 pt-3 pb-1 d-flex align-items-center justify-content-between">
        <span style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#64748b;">
          Today's Revenue
        </span>
        <div style="width:32px;height:32px;border-radius:8px;background:#ecfdf5;
                    display:flex;align-items:center;justify-content:center;">
          <i class="fas fa-money-bill-wave" style="color:#059669;font-size:.8rem;"></i>
        </div>
      </div>
      <div class="px-3 pb-3 flex-grow-1 d-flex align-items-center">
        <div class="w-100 mt-2" style="background:linear-gradient(135deg,#047857,#059669,#10b981);
                    border-radius:.625rem;padding:1.1rem 1.25rem;
                    box-shadow:0 4px 14px rgba(4,120,87,.25);">
          <div class="d-flex align-items-baseline gap-1">
            <span class="text-white fw-bold" style="font-size:.85rem;opacity:.85;">₱</span>
            <span class="text-white fw-black" style="font-size:1.6rem;letter-spacing:-.03em;line-height:1;">
              <?php echo number_format($today_revenue, 2); ?>
            </span>
          </div>
          <p class="mb-0 text-white mt-1" style="font-size:.68rem;opacity:.75;text-transform:uppercase;letter-spacing:.07em;font-weight:600;">
            Verified payments
          </p>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- Recent Activities -->
<div class="row mb-4">
  <div class="col-12">
    <div style="background:#fff;border-radius:.875rem;
                box-shadow:0 1px 3px rgba(0,0,0,.07),0 4px 16px rgba(0,0,0,.06);overflow:hidden;">

      <!-- Card header -->
      <div class="d-flex align-items-center justify-content-between px-4 py-3"
           style="border-bottom:1px solid #f1f5f9;">
        <div class="d-flex align-items-center gap-3">
          <div style="width:38px;height:38px;border-radius:10px;background:#f5f3ff;
                      display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="fas fa-history" style="color:#7c3aed;font-size:.95rem;"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold" style="color:#0f172a;letter-spacing:-.01em;">Recent Activities</h6>
            <small style="color:#94a3b8;">Latest system events and actions</small>
          </div>
        </div>
        <span style="display:inline-flex;align-items:center;gap:6px;
                     background:#f0fdf4;color:#16a34a;font-size:.68rem;font-weight:700;
                     text-transform:uppercase;letter-spacing:.08em;
                     padding:.3rem .75rem;border-radius:2rem;border:1px solid #bbf7d0;">
          <span style="width:7px;height:7px;border-radius:50%;background:#22c55e;
                       box-shadow:0 0 0 2px rgba(34,197,94,.3);display:inline-block;"></span>
          Live
        </span>
      </div>

      <!-- Filters -->
      <div class="px-4 py-2" style="background:#f8fafc;border-bottom:1px solid #f1f5f9;">
        <div class="d-flex align-items-center gap-2 flex-wrap">
          <select id="activityTypeFilter" class="form-select form-select-sm" style="width:auto;min-width:160px;">
            <option value="all">All Activities</option>
            <option value="booking_approved">Bookings Approved</option>
            <option value="payment_approved">Payments Approved</option>
            <option value="booking_cancelled">Bookings Cancelled</option>
            <option value="guest_checkin">Check-ins</option>
            <option value="guest_checkout">Check-outs</option>
            <option value="feedback_submitted">Feedback</option>
            <option value="pencil_created">Pencil Bookings</option>
          </select>
          <div class="vr d-none d-md-block" style="height:26px;"></div>
          <?php $searchScope = 'activities'; $searchPlaceholder = 'Search guest or booking ID...'; include __DIR__ . '/../../Filter/Searchbar.php'; ?>
          <div class="ms-auto d-flex align-items-center gap-2">
            <button class="btn btn-sm btn-outline-secondary" onclick="loadRecentActivities()" style="font-size:.78rem;">
              <i class="fas fa-sync-alt me-1"></i>Refresh
            </button>
            <?php $resetScope = 'activities'; include __DIR__ . '/../../Filter/ResetFilter.php'; ?>
          </div>
        </div>
      </div>

      <!-- Bridge script -->
      <script>
      (function(){
        document.addEventListener('search-changed', function(e){
          if(e.detail.scope!=='activities') return;
          var el=document.getElementById('activitySearchInput');
          if(!el){el=document.createElement('input');el.type='hidden';el.id='activitySearchInput';document.body.appendChild(el);}
          el.value=e.detail.value||'';
          if(typeof applyFilters==='function') applyFilters();
        });
        document.addEventListener('filters-reset', function(e){
          if(e.detail&&e.detail.scope&&e.detail.scope!=='activities') return;
          var t=document.getElementById('activityTypeFilter');if(t)t.value='all';
          if(typeof clearActivityFilters==='function') clearActivityFilters();
        });
      })();
      </script>

      <!-- Activity feed -->
      <div id="recentActivitiesList" style="max-height:560px;overflow-y:auto;">
        <div class="d-flex flex-column align-items-center justify-content-center py-5">
          <div class="spinner-border" role="status"
               style="width:1.8rem;height:1.8rem;color:#2563eb;border-width:2px;">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="text-muted mt-3 mb-0" style="font-size:.85rem;">Loading activities…</p>
        </div>
      </div>

      <!-- Pagination footer -->
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 px-4 py-2"
           style="border-top:1px solid #f1f5f9;background:#f8fafc;">
        <small style="color:#94a3b8;font-size:.78rem;">
          Showing <strong id="activityCount" style="color:#374151;">0</strong>
          of <strong id="totalActivityCount" style="color:#374151;">0</strong> activities
        </small>
        <div class="d-flex align-items-center gap-1">
          <button class="btn btn-sm btn-outline-secondary" id="prevPageBtn" disabled style="font-size:.78rem;padding:.25rem .6rem;">
            <i class="fas fa-chevron-left"></i>
          </button>
          <span id="pageInfo"
                style="font-size:.78rem;font-weight:600;color:#374151;padding:.25rem .75rem;
                       background:#fff;border:1px solid #e2e8f0;border-radius:.375rem;min-width:90px;text-align:center;">
            Page 1
          </span>
          <button class="btn btn-sm btn-outline-secondary" id="nextPageBtn" disabled style="font-size:.78rem;padding:.25rem .6rem;">
            <i class="fas fa-chevron-right"></i>
          </button>
        </div>
      </div>

    </div>
  </div>
</div>

        <script>
        // Fetch and display recent activities
        let allActivities = [];
        let filteredActivities = [];
        let currentPage = 1;
        const activitiesPerPage = 10;
        let lastUpdateTime = null;
        let activitiesRequestInFlight = false;
        let activitiesAbortController = null;

        function ensureActivitySearchInput() {
          let input = document.getElementById('activitySearchInput');
          if (!input) {
            input = document.createElement('input');
            input.type = 'hidden';
            input.id = 'activitySearchInput';
            document.body.appendChild(input);
          }
          return input;
        }

        function loadRecentActivities() {
          // Prevent overlapping requests from piling up and keeping the page busy.
          if (activitiesRequestInFlight) {
            return;
          }

          activitiesRequestInFlight = true;
          activitiesAbortController = new AbortController();
          const timeoutId = setTimeout(() => {
            if (activitiesAbortController) {
              activitiesAbortController.abort();
            }
          }, 12000);

          fetch('api/RecentActivities.php?limit=100', {
            credentials: 'same-origin',
            cache: 'no-store',
            signal: activitiesAbortController.signal
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
                allActivities = data.activities;
                lastUpdateTime = new Date();
                applyFilters();
              } else {
                showActivitiesError('No activities available');
              }
            })
            .catch(error => {
              console.error('Error loading activities:', error);
              if (error && error.name === 'AbortError') {
                showActivitiesError('Loading activities timed out. Please retry.');
              } else {
                showActivitiesError('Failed to load activities');
              }
            })
            .finally(() => {
              clearTimeout(timeoutId);
              activitiesRequestInFlight = false;
              activitiesAbortController = null;
            });
        }

        function applyFilters() {
          const typeFilterEl = document.getElementById('activityTypeFilter');
          const searchInputEl = ensureActivitySearchInput();
          const typeFilter = typeFilterEl ? typeFilterEl.value : 'all';
          const searchTerm = (searchInputEl && searchInputEl.value ? searchInputEl.value : '').toLowerCase();

          filteredActivities = allActivities.filter(activity => {
            const typeMatch = typeFilter === 'all' || activity.activity_type === typeFilter;
            const searchMatch = searchTerm === '' ||
              (activity.guest_name && activity.guest_name.toLowerCase().includes(searchTerm)) ||
              (activity.receipt_no && activity.receipt_no.toLowerCase().includes(searchTerm));

            return typeMatch && searchMatch;
          });

          currentPage = 1;
          displayActivities();
        }

        function clearActivityFilters() {
          const typeFilterEl = document.getElementById('activityTypeFilter');
          if (typeFilterEl) typeFilterEl.value = 'all';
          ensureActivitySearchInput().value = '';
          applyFilters();
        }

        function changePage(direction) {
          console.log('changePage called with direction:', direction);
          console.log('Current page:', currentPage);
          console.log('Filtered activities:', filteredActivities.length);

          const totalPages = Math.ceil(filteredActivities.length / activitiesPerPage);
          const newPage = currentPage + direction;

          console.log('Total pages:', totalPages);
          console.log('New page would be:', newPage);

          // Validate page bounds
          if (newPage < 1 || newPage > totalPages) {
            console.log('Page out of bounds, returning');
            return;
          }

          currentPage = newPage;
          console.log('Setting current page to:', currentPage);
          displayActivities();

          // Scroll to top of activities list
          const container = document.getElementById('recentActivitiesList');
          if (container) {
            container.scrollTop = 0;
          }
        }

        function displayActivities() {
          const container = document.getElementById('recentActivitiesList');

          if (!filteredActivities || filteredActivities.length === 0) {
            container.innerHTML = `
              <div class="d-flex flex-column align-items-center justify-content-center py-5">
                <i class="fas fa-inbox" style="font-size:2rem;color:#cbd5e1;"></i>
                <p class="text-muted mt-3 mb-0" style="font-size:.875rem;">No activities match your filters</p>
              </div>`;
            updatePaginationInfo(0, 0);
            return;
          }

          const startIndex = (currentPage - 1) * activitiesPerPage;
          const endIndex = startIndex + activitiesPerPage;
          const pageActivities = filteredActivities.slice(startIndex, endIndex);

          let html = '';
          pageActivities.forEach((activity, index) => {
            const { icon, color, bgColor, text } = getActivityDisplay(activity);
            const timeAgo = formatTimeAgo(activity.activity_date);
            const isLast = index === pageActivities.length - 1;

            html += `
              <div class="activity-item d-flex align-items-start px-4 py-3"
                   style="${!isLast ? 'border-bottom:1px solid #f1f5f9;' : ''}transition:background .15s;"
                   onmouseenter="this.style.background='#f8fafc'" onmouseleave="this.style.background=''"
                   data-timestamp="${activity.activity_date}">
                <div class="flex-shrink-0 me-3">
                  <div class="d-flex align-items-center justify-content-center"
                       style="width:38px;height:38px;border-radius:50%;background-color:${bgColor};">
                    <i class="${icon}" style="color:${color};font-size:.875rem;"></i>
                  </div>
                </div>
                <div class="flex-grow-1 min-width-0">
                  <p class="mb-1" style="color:#374151;font-size:.875rem;line-height:1.5;">
                    ${text}
                    ${activity.receipt_no ? `<span class="badge bg-light text-secondary ms-1" style="font-size:.65rem;font-weight:600;border:1px solid #e2e8f0;">${activity.receipt_no}</span>` : ''}
                  </p>
                </div>
                <small class="activity-time flex-shrink-0 ms-3"
                       style="color:#94a3b8;font-size:.75rem;font-weight:600;white-space:nowrap;">
                  ${timeAgo}
                </small>
              </div>
            `;
          });

          container.innerHTML = html;
          updatePaginationInfo(pageActivities.length, filteredActivities.length);
        }

        function updatePaginationInfo(showing, total) {
          const totalPages = Math.max(1, Math.ceil(total / activitiesPerPage));

          document.getElementById('activityCount').textContent = showing;
          document.getElementById('totalActivityCount').textContent = total;
          document.getElementById('pageInfo').textContent = `Page ${currentPage} of ${totalPages}`;

          // Update button states
          const prevBtn = document.getElementById('prevPageBtn');
          const nextBtn = document.getElementById('nextPageBtn');

          if (prevBtn && nextBtn) {
            prevBtn.disabled = currentPage <= 1;
            nextBtn.disabled = currentPage >= totalPages || total === 0;
          }
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

          if (diffSecs < 60) return 'Just now';
          if (diffMins < 60) return diffMins + 'm ago';
          if (diffHours < 24) return diffHours + 'h ago';
          if (diffDays === 1) return '1d ago';
          if (diffDays < 7) return diffDays + 'd ago';
          return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        }

        function showActivitiesError(message) {
          const container = document.getElementById('recentActivitiesList');
          container.innerHTML = `
            <div class="d-flex flex-column align-items-center justify-content-center py-5">
              <i class="fas fa-exclamation-circle" style="font-size:2rem;color:#fbbf24;"></i>
              <p class="text-muted mt-3 mb-2" style="font-size:.875rem;">${message || 'Failed to load recent activities'}</p>
              <button class="btn btn-sm btn-outline-primary" onclick="loadRecentActivities()" style="font-size:.8rem;">
                <i class="fas fa-redo me-1"></i>Retry
              </button>
            </div>
          `;
        }

        document.addEventListener('DOMContentLoaded', function() {
          ensureActivitySearchInput();

          loadRecentActivities();

          const activityTypeFilter = document.getElementById('activityTypeFilter');
          const activitySearchInput = document.getElementById('activitySearchInput');
          const prevPageBtn = document.getElementById('prevPageBtn');
          const nextPageBtn = document.getElementById('nextPageBtn');

          if (activityTypeFilter) activityTypeFilter.addEventListener('change', applyFilters);
          if (activitySearchInput) activitySearchInput.addEventListener('input', debounce(applyFilters, 300));

          if (prevPageBtn) {
            prevPageBtn.addEventListener('click', function() {
              changePage(-1);
            });
          }

          if (nextPageBtn) {
            nextPageBtn.addEventListener('click', function() {
              changePage(1);
            });
          }

          setInterval(updateTimeAgo, 1000);
          setInterval(loadRecentActivities, 30000);
        });

        function debounce(func, wait) {
          let timeout;
          return function executedFunction(...args) {
            const later = () => {
              clearTimeout(timeout);
              func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
          };
        }
        </script>
