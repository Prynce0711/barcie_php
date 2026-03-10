<?php
// Include data processing logic
require_once __DIR__ . '/Components/Admin/data_processing.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport"
    content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="format-detection" content="telephone=yes">
  <meta name="theme-color" content="#3b82f6">
  <link rel="icon" type="image/png" href="public/images/imageBg/barcie_logo.jpg">
  <title>Admin Dashboard</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <script
    src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
  <!-- FullCalendar CSS & JS -->
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="Components/Admin/dashboard.css">
  <link rel="stylesheet" href="Components/Admin/dashboard-enhancements.css">
  <link rel="stylesheet" href="assets/css/mobile-responsive.css">
  <link rel="stylesheet" href="assets/css/page-state.css">
  <link rel="stylesheet" href="Components/Admin/News/news.css">
  <link rel="stylesheet" href="Components/Admin/Reports/reports.css">
  <!-- Enhanced Admin Management Styles -->
  <link rel="stylesheet" href="Components/Admin/AccountManagement/admin-online-status.css">
</head>


<body>




  <!-- Mobile Menu Toggle -->
  <button class="mobile-menu-toggle d-lg-none" onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
  </button>

  <!-- Sidebar -->
  <?php include __DIR__ . '/Components/Admin/sidebar.php'; ?>



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
      <section id="dashboard-section" class="content-section active d-block">
        <?php include __DIR__ . '/Components/Admin/Dashboard/dashboard_section.php'; ?>
      </section>

      <?php
      // Load booking events for calendar
      $events = [];

      // Safe query - no user input (status is hardcoded)
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

        console.log("📊 Dashboard data initialized");
        // Current admin info exposed to frontend
        window.currentAdmin = <?php echo json_encode([
          'id' => $_SESSION['admin_id'] ?? null,
          'username' => $_SESSION['admin_username'] ?? null,
          'role' => $_SESSION['admin_role'] ?? 'staff'
        ]); ?>;
      </script>



      <!-- Calendar & Rooms Section -->
      <section id="calendar-section" class="content-section">
        <?php include __DIR__ . '/Components/Admin/Calendar/calendar_section.php'; ?>
      </section>

      <section id="rooms-section" class="content-section">
        <?php include __DIR__ . '/Components/Admin/RoomsAndFacilities/rooms_section.php'; ?>
      </section>

      <!-- Bookings Management -->
      <section id="bookings-section" class="content-section">
        <?php include __DIR__ . '/Components/Admin/Booking/bookings_section.php'; ?>
      </section>

      <!-- Pencil Bookings Management (independent from bookings) -->
      <section id="pencil-bookings-section" class="content-section">
        <?php include __DIR__ . '/Components/Admin/Booking/pencil_book_management.php'; ?>
      </section>


      <!-- Feedback Section -->
      <section id="feedback-section" class="content-section">
        <?php include __DIR__ . '/Components/Admin/Feedback/feedback_section.php'; ?>
      </section>

      <!-- News & Updates Section -->
      <section id="news-section" class="content-section">
        <?php include __DIR__ . '/Components/Admin/News/news_section.php'; ?>
      </section>

      <!-- Payment Verification Section -->
      <section id="payment-verification-section" class="content-section">
        <?php include __DIR__ . '/Components/Admin/Booking/payment_verification.php'; ?>
      </section>

      <!-- Reports & Analytics Section -->
      <section id="reports-section" class="content-section">
        <?php include __DIR__ . '/Components/Admin/Reports/reports_section.php'; ?>
      </section>

      <!-- Admin Management Section (Manage Roles) -->
      <section id="admin-management-section" class="content-section">
        <?php include __DIR__ . '/Components/Admin/AccountManagement/admin_management_enhanced.php'; ?>
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
      <?php include __DIR__ . '/Components/Admin/RoomsAndFacilities/add_item_modal.php'; ?>
      <?php include __DIR__ . '/Components/Admin/RoomsAndFacilities/edit_item_modal.php'; ?>

      <!-- Include Admin Management Modals at page bottom so they're always accessible -->
      <?php include __DIR__ . '/Components/Admin/AccountManagement/admin_auth_modal.php'; ?>
      <?php include __DIR__ . '/Components/Admin/AccountManagement/add_admin_modal.php'; ?>
      <?php include __DIR__ . '/Components/Admin/AccountManagement/edit_admin_modal.php'; ?>

      <!-- Delete Admin Confirmation Modal (header styled blue for modal theme consistency) -->
      <div class="modal fade" id="deleteAdminModal" tabindex="-1" aria-labelledby="deleteAdminModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header bg-primary text-white">
              <h5 class="modal-title" id="deleteAdminModalLabel">
                <i class="fas fa-exclamation-triangle me-2"></i>Delete Administrator
              </h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <p>Are you sure you want to delete admin <strong id="delete-admin-username"></strong>?</p>
              <p class="text-danger"><i class="fas fa-exclamation-circle me-2"></i>This action cannot be undone!</p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="button" class="btn btn-danger" id="confirmDeleteAdmin">
                <i class="fas fa-trash me-2"></i>Delete Admin
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Delete Admin Modal JavaScript -->
      <script>
        (function () {
          let deleteAdminModal;
          let adminToDelete = null;

          document.addEventListener('DOMContentLoaded', function () {
            const modalElement = document.getElementById('deleteAdminModal');
            if (modalElement) {
              deleteAdminModal = new bootstrap.Modal(modalElement);
            }

            // Confirm delete button
            const confirmBtn = document.getElementById('confirmDeleteAdmin');
            if (confirmBtn) {
              confirmBtn.addEventListener('click', function () {
                if (!adminToDelete) return;

                const originalHtml = this.innerHTML;
                this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';
                this.disabled = true;

                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('admin_id', adminToDelete.id);

                fetch('api/admin_management_enhanced.php', {
                  method: 'POST',
                  body: formData
                })
                  .then(response => response.json())
                  .then(data => {
                    if (data.success) {
                      if (typeof window.showAdminAlert === 'function') {
                        window.showAdminAlert('success', 'Admin deleted successfully!');
                      }
                      deleteAdminModal.hide();
                      if (typeof window.loadAdmins === 'function') {
                        window.loadAdmins();
                      }
                    } else {
                      if (typeof window.showAdminAlert === 'function') {
                        window.showAdminAlert('danger', data.message || 'Failed to delete admin');
                      } else {
                        window.showToast(data.message || 'Failed to delete admin', 'error');
                      }
                    }
                  })
                  .catch(error => {
                    console.error('Error:', error);
                    if (typeof window.showAdminAlert === 'function') {
                      window.showAdminAlert('danger', 'Error deleting admin');
                    } else {
                      window.showToast('Error deleting admin', 'error');
                    }
                  })
                  .finally(() => {
                    this.innerHTML = originalHtml;
                    this.disabled = false;
                    adminToDelete = null;
                  });
              });
            }
          });

          // Global function to open delete modal
          window.deleteAdmin = function (adminId, username) {
            adminToDelete = { id: adminId, username: username };
            document.getElementById('delete-admin-username').textContent = username;
            deleteAdminModal.show();
          };
        })();
      </script>

      <?php include __DIR__ . '/Components/Popup/ConfirmPopup.php'; ?>
      <?php include __DIR__ . '/Components/Popup/ErrorPopup.php'; ?>
      <?php include __DIR__ . '/Components/Popup/LoadingPopup.php'; ?>
      <?php include __DIR__ . '/Components/Popup/SuccessPopup.php'; ?>

      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
      <script src="Components/Popup/popup-manager.js"></script>

      <!-- Page State Manager - Load FIRST -->
      <script src="assets/js/page-state-manager.js"></script>

      <!-- Dashboard JavaScript files -->
      <script src="Components/Admin/Dashboard/dashboard-bootstrap.js"
        onerror="console.error('❌ Failed to load dashboard-bootstrap.js')"></script>
      <script src="Components/Admin/Calendar/calendar-section.js"
        onerror="console.error('❌ Failed to load calendar-section.js')"></script>
      <script src="Components/Admin/RoomsAndFacilities/rooms-section.js"
        onerror="console.error('❌ Failed to load rooms-section.js')"></script>
      <script src="Components/Admin/Booking/bookings-section.js"
        onerror="console.error('❌ Failed to load bookings-section.js')"></script>
      <script src="Components/Admin/News/news-section.js"
        onerror="console.error('❌ Failed to load news-section.js')"></script>
      <script src="Components/Admin/mobile-enhancements.js"
        onerror="console.error('❌ Failed to load mobile-enhancements.js')"></script>

      <!-- Initialize dashboard with data after all scripts are loaded -->
      <script>
        console.log('📦 All scripts loaded, checking functions...');
        console.log('  - setDashboardData:', typeof setDashboardData);
        console.log('  - initializeCalendarNavigation:', typeof initializeCalendarNavigation);
        console.log('  - initializeRoomSearch:', typeof initializeRoomSearch);
        console.log('  - initializeRoomsFiltering:', typeof initializeRoomsFiltering);
        console.log('  - FullCalendar:', typeof FullCalendar);
        console.log('  - Chart:', typeof Chart);

        // Call setDashboardData if available
        if (typeof setDashboardData === 'function') {
          console.log("✅ setDashboardData function found - initializing dashboard data");
          try {
            setDashboardData(
              window.calendarEvents,
              window.monthlyBookingsData,
              window.statusDistributionData,
              window.dashboardStats
            );
            console.log("✅ Dashboard data initialized successfully");
          } catch (error) {
            console.error("❌ Error calling setDashboardData:", error);
          }
        } else {
          console.error("❌ setDashboardData function not found");
          console.log("Available window functions:", Object.keys(window).filter(k => typeof window[k] === 'function').slice(0, 30));
        }
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
        document.addEventListener('click', function (event) {
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

      <!-- Enhanced Admin Management JavaScript -->
      <script src="Components/Admin/AccountManagement/admin-management-enhanced.js"></script>

      <!-- Reports & Analytics JavaScript -->
      <script src="Components/Admin/Reports/js/state.js"></script>
      <script src="Components/Admin/Reports/js/utils.js"></script>
      <script src="Components/Admin/Reports/js/charts.js"></script>
      <script src="Components/Admin/Reports/js/updaters.js"></script>
      <script src="Components/Admin/Reports/js/actions.js"></script>
      <script src="Components/Admin/Reports/reports.js"></script>

</body>

</html>