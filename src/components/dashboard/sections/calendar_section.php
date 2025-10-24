<?php
// Calendar Section Template
// This section displays calendar view and room list management
?>

<!-- Calendar & Rooms Section -->
  <div class="row mb-4">
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
                    <button class="btn btn-outline-primary" onclick="calendarInstance.changeView('dayGridMonth')">Month</button>
                    <button class="btn btn-outline-primary" onclick="calendarInstance.changeView('timeGridWeek')">Week</button>
                    <button class="btn btn-outline-primary" onclick="calendarInstance.changeView('timeGridDay')">Day</button>
                  </div>
                </div>
              </div>
              <div class="mt-2">
                <small class="me-3"><span class="badge bg-success me-1">●</span>Approved/Confirmed</small>
                <small class="me-3"><span class="badge bg-primary me-1">●</span>Checked-in</small>
                <small class="me-3"><span class="badge bg-purple me-1">●</span>Checked-out</small>
                <small class="me-3"><span class="badge bg-warning me-1">●</span>Pending</small>
                <small class="me-3"><span class="badge bg-danger me-1">●</span>Cancelled</small>
                <small class="text-muted">Empty days = No reservations</small>
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
                  <small class="text-muted">Current status and upcoming reservations for all rooms and facilities</small>
                </div>
                <div class="col-md-4 text-end">
                  <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" placeholder="Search rooms & facilities..." id="room-search">
                  </div>
                </div>
              </div>
            </div>
            <div class="room-list-container" style="max-height: 600px; overflow-y: auto;">
              <?php include 'room_list_content.php'; ?>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

      <!-- JS: initialize room calendar and generate window.roomEvents -->
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
          const roomEvents = window.roomEvents || [];

          calendarInstance = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
              left: 'prev,next today',
              center: 'title',
              right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: roomEvents,
            eventDisplay: 'block',
            dayMaxEvents: true,
            height: 'auto',
            aspectRatio: 1.8,
            eventOverlap: false,
            slotEventOverlap: false,
            displayEventTime: true,
            displayEventEnd: true,
            nowIndicator: true,
            businessHours: {
              daysOfWeek: [0,1,2,3,4,5,6],
              startTime: '08:00',
              endTime: '20:00',
            },
            eventClick: function (info) {
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
              console.log('Date clicked:', info.dateStr);
            },
            eventDidMount: function (info) {
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
            $item_name = $booking['item_name'] ? addslashes($booking['item_name']) : 'Unassigned Room/Facility';
            $room_number = $booking['room_number'] ? '#' . $booking['room_number'] : '';
            $item_type = $booking['item_type'] ?: 'room';
            $guest = 'Guest';
            $status = $booking['status'];
            $display_title = $item_name . $room_number . ' - ' . $guest;
            $color = '#28a745';
            if ($status == 'checked_in') $color = '#0d6efd';
            if ($status == 'checked_out') $color = '#6f42c1';
            if ($status == 'pending') $color = '#fd7e14';
        
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
