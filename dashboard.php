<?php
// Include data processing logic
require_once __DIR__ . '/src/components/dashboard/data_processing.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/png" href="src/assets/images/imageBg/barcie_logo.jpg">
  <title>Admin Dashboard</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <!-- Chart.js -->
  <!-- FullCalendar CSS & JS -->
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="src/assets/css/dashboard.css">
  <link rel="stylesheet" href="src/assets/css/dashboard-enhancements.css">
  <link rel="stylesheet" href="src/assets/css/page-state.css">
</head>


<body>




  <!-- Mobile Menu Toggle -->
  <button class="mobile-menu-toggle d-lg-none" onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
  </button>

  <!-- Sidebar -->
  <?php include 'src/components/dashboard/sidebar.php'; ?>



  <!-- Main Content -->
  <div class="main-content">
    <div class="container-fluid px-2" style="max-width: 100%;">
      <div class="row">
        <div class="col-12">
          <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['success_message']) ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
          <?php endif; ?>
          
          <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_SESSION['error_message']) ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- Dashboard Section -->
      <section id="dashboard-section" class="content-section">
  <?php include 'src/components/dashboard/sections/dashboard_section.php'; ?>
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

        // Initialize dashboard when ALL scripts are loaded (not just DOM ready)
        window.addEventListener('load', function () {
          console.log("🚀 Dashboard page initialization starting...");
          console.log("📊 Checking data availability:");
          console.log("  - calendarEvents:", window.calendarEvents ? window.calendarEvents.length + ' events' : 'NOT SET');
          console.log("  - monthlyBookingsData:", window.monthlyBookingsData ? 'SET (' + JSON.stringify(window.monthlyBookingsData).substring(0, 50) + '...)' : 'NOT SET');
          console.log("  - statusDistributionData:", window.statusDistributionData ? 'SET (' + JSON.stringify(window.statusDistributionData).substring(0, 50) + '...)' : 'NOT SET');
          console.log("  - dashboardStats:", window.dashboardStats ? 'SET' : 'NOT SET');
          
          // Wait for dashboard-bootstrap.js to load before calling setDashboardData
          if (typeof setDashboardData === 'function') {
            console.log("✅ setDashboardData function found");
            try {
              setDashboardData(
                window.calendarEvents,
                window.monthlyBookingsData,
                window.statusDistributionData,
                window.dashboardStats
              );
              console.log("✅ Dashboard data set successfully");
            } catch (error) {
              console.error("❌ Error calling setDashboardData:", error);
            }
          } else {
            console.error("❌ setDashboardData function not found - dashboard-bootstrap.js may not be loaded");
            console.log("Available functions:", Object.keys(window).filter(k => typeof window[k] === 'function').slice(0, 20));
          }
        });
      </script>



      <!-- Calendar & Rooms Section -->
      <section id="calendar-section" class="content-section">
        <?php include 'src/components/dashboard/sections/calendar_section.php'; ?>
      </section>

      <section id="rooms-section" class="content-section">
        <?php include 'src/components/dashboard/sections/rooms_section.php'; ?>
      </section>

      <!-- Bookings Management -->
      <section id="bookings-section" class="content-section">
        <?php include 'src/components/dashboard/sections/bookings_section.php'; ?>
      </section>



      <!-- Footer -->
      <div class="footer">
        <p>&copy; <?php echo date("Y"); ?> Hotel Management System</p>
      </div>





      <!-- Generate PHP room events and make them globally available -->
      <script>
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
  <!-- Include Add Item Modal once at page bottom so it's a direct child of body -->
  <?php include 'src/components/dashboard/sections/add_item_modal.php'; ?>

      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
      
      <!-- Page State Manager - Load FIRST -->
      <script src="src/assets/js/page-state-manager.js"></script>
      
      <!-- Dashboard JavaScript files -->
      <script src="src/assets/js/dashboard/dashboard-bootstrap.js" onerror="console.error('❌ Failed to load dashboard-bootstrap.js')"></script>
      <script src="src/assets/js/dashboard/calendar-section.js" onerror="console.error('❌ Failed to load calendar-section.js')"></script>
      <script src="src/assets/js/dashboard/rooms-section.js" onerror="console.error('❌ Failed to load rooms-section.js')"></script>
      <script src="src/assets/js/dashboard/bookings-section.js" onerror="console.error('❌ Failed to load bookings-section.js')"></script>
      <script src="src/assets/js/verify-structure.js" onerror="console.error('❌ Failed to load verify-structure.js')"></script>
      
      <!-- Verify script loading -->
      <script>
        console.log('📦 Checking loaded scripts...');
        console.log('setDashboardData:', typeof setDashboardData);
        console.log('initializeCalendarNavigation:', typeof initializeCalendarNavigation);
        console.log('initializeRoomSearch:', typeof initializeRoomSearch);
        console.log('initializeRoomsFiltering:', typeof initializeRoomsFiltering);
        console.log('FullCalendar:', typeof FullCalendar);
        console.log('Chart:', typeof Chart);
      </script>

      <!-- Additional utility functions -->
      <script>
        // Toggle sidebar on mobile
        function toggleSidebar() {
          const sidebar = document.querySelector('.sidebar');
          if (sidebar) {
            sidebar.classList.toggle('open');
          }
          
          // Add overlay when sidebar is open on mobile
          let overlay = document.querySelector('.sidebar-overlay');
          if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'sidebar-overlay';
            overlay.onclick = toggleSidebar;
            document.body.appendChild(overlay);
          }
          overlay.classList.toggle('active');
        }

        // Dark mode toggle
        function toggleDarkMode() {
          document.body.classList.toggle('dark-mode');
          const icon = document.querySelector('.dark-toggle i');
          if (document.body.classList.contains('dark-mode')) {
            icon.className = 'fas fa-sun';
          } else {
            icon.className = 'fas fa-moon';
          }
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
          const sidebar = document.querySelector('.sidebar');
          const toggleBtn = document.querySelector('.mobile-menu-toggle');
          
          if (window.innerWidth < 992) {
            if (!sidebar.contains(event.target) && !toggleBtn.contains(event.target)) {
              sidebar.classList.remove('open');
              const overlay = document.querySelector('.sidebar-overlay');
              if (overlay) overlay.classList.remove('active');
            }
          }
        });

        // Make functions globally available
        window.toggleSidebar = toggleSidebar;
        window.toggleDarkMode = toggleDarkMode;
      </script>



</body>

</html>