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

        // Initialize dashboard when document is ready
        document.addEventListener('DOMContentLoaded', function () {
          console.log("Dashboard page initialization starting...");
          
          // Set data for charts (this is needed for dashboard-bootstrap.js)
          setDashboardData(
            window.calendarEvents,
            window.monthlyBookingsData,
            window.statusDistributionData,
            window.dashboardStats
          );
          
          console.log("Dashboard data set, waiting for dashboard-bootstrap.js to initialize...");
        });
      </script>



      <!-- Calendar & Rooms Section -->
      <section id="calendar-section" class="content-section">
        <?php include 'src/components/dashboard/sections/calendar_section.php'; ?>
      </section>

      <section id="rooms" class="content-section">
        <?php include 'src/components/dashboard/sections/rooms_section.php'; ?>
      </section>

      <!-- Bookings Management -->
      <section id="bookings" class="content-section">
        <?php include 'src/components/dashboard/sections/bookings_section.php'; ?>
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
  <script src="src/assets/js/dashboard/dashboard-bootstrap.js"></script>
  <!-- Section-specific JavaScript files -->
  <script src="src/assets/js/dashboard/calendar-section.js"></script>
  <script src="src/assets/js/dashboard/rooms-section.js"></script>
  <script src="src/assets/js/dashboard/bookings-section.js"></script>
  <!-- Verification script for testing -->
  <script src="src/assets/js/verify-structure.js"></script>

      <!-- Additional utility functions -->
      <script>
        // Toggle sidebar on mobile
        function toggleSidebar() {
          const sidebar = document.querySelector('.sidebar');
          if (sidebar) {
            sidebar.classList.toggle('open');
          }
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

        // Make functions globally available
        window.toggleSidebar = toggleSidebar;
        window.toggleDarkMode = toggleDarkMode;
      </script>



</body>

</html>