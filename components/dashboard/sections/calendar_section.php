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
        <div class="modal fade" id="roomCalendarModal" tabindex="-1" aria-labelledby="roomCalendarModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header d-flex align-items-center">
                <h5 class="modal-title me-3" id="roomCalendarModalLabel">Room Calendar</h5>
                <div class="me-auto">
                  <label class="small text-muted me-2">Range:</label>
                  <select id="roomCalendarRange" class="form-select form-select-sm d-inline-block" style="width: auto;">
                    <option value="30">30 days</option>
                    <option value="90">90 days</option>
                    <option value="365">365 days</option>
                  </select>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <div class="row">
                  <div class="col-lg-8">
                    <div id="roomModalCalendar"></div>
                  </div>
                  <div class="col-lg-4">
                    <div id="roomBookingDetails" class="border rounded p-3" style="min-height:320px;">
                      <h6 class="mb-2">Booking Details</h6>
                      <div id="roomBookingDetailsContent" class="small text-muted">Select a booking event to see details here.</div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              </div>
            </div>
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

        // Global calendar variables
        window.calendarInstance = window.calendarInstance || null;
        var modalCalendarInstance = null;

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
        // Generate JavaScript events using proper room_id relationship (bookings)
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

            // Emit booking as an event
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

        // Also generate a room list to ensure clickable items exist for the UI
        echo "window.roomList = window.roomList || [];\n";
        $items_query = "SELECT id, name, room_number, item_type FROM items ORDER BY name ASC";
        $items_result = $conn->query($items_query);
        if ($items_result && $items_result->num_rows > 0) {
          while ($item = $items_result->fetch_assoc()) {
            $iname = $item['name'] ? addslashes($item['name']) : 'Unnamed';
            $rnum = $item['room_number'] ? addslashes($item['room_number']) : '';
            echo "window.roomList.push({ id: {$item['id']}, name: '{$iname}', roomNumber: '{$rnum}', itemType: '" . addslashes($item['item_type'] ?: 'room') . "' });\n";
          }
        }
        ?>

        // ensure roomList exists even if empty
        if (!window.roomList) window.roomList = [];
        // keep track of current modal room id
        window.currentModalRoomId = null;

        // Navigation between calendar view and room list
        function initializeCalendarNavigation() {
          const calendarBtn = document.getElementById('calendar-view-btn');
          const listBtn = document.getElementById('room-list-btn');
          const calendarContent = document.getElementById('calendar-view-content');
          const listContent = document.getElementById('room-list-content');

          if (calendarBtn) calendarBtn.addEventListener('click', function () {
            calendarBtn.classList.add('active');
            if (listBtn) listBtn.classList.remove('active');
            if (calendarContent) calendarContent.style.display = '';
            if (listContent) listContent.style.display = 'none';
          });
          if (listBtn) listBtn.addEventListener('click', function () {
            listBtn.classList.add('active');
            if (calendarBtn) calendarBtn.classList.remove('active');
            if (calendarContent) calendarContent.style.display = 'none';
            if (listContent) listContent.style.display = '';
          });
        }

        // Room search and click wiring
        function initializeRoomSearch() {
          const searchInput = document.getElementById('room-search');
          const container = document.querySelector('.room-list-container');

          // If the included room list doesn't provide clickable items, build a simple list
          if (container && container.querySelectorAll('.room-list-item, .room-item, .list-group-item').length === 0) {
            const listGroup = document.createElement('div');
            listGroup.className = 'list-group list-group-flush';
            (window.roomList || []).forEach(r => {
              const btn = document.createElement('button');
              btn.type = 'button';
              btn.className = 'list-group-item list-group-item-action room-list-item';
              btn.setAttribute('data-room-id', r.id);
              btn.setAttribute('data-room-name', r.name + (r.roomNumber ? ' #' + r.roomNumber : ''));
              btn.textContent = r.name + (r.roomNumber ? ' #' + r.roomNumber : '');
              listGroup.appendChild(btn);
            });
            container.appendChild(listGroup);
          }

          // Search filter
          if (searchInput && container) {
            searchInput.addEventListener('input', function (e) {
              const term = e.target.value.toLowerCase();
              Array.from(container.querySelectorAll('.room-list-item, .room-item, .list-group-item')).forEach(el => {
                const text = (el.textContent || el.innerText || '').toLowerCase();
                el.style.display = text.indexOf(term) === -1 ? 'none' : '';
              });
            });
          }

          // Click handler (event delegation)
          if (container) {
            container.addEventListener('click', function (e) {
              const el = e.target.closest('[data-room-id]');
              if (!el) return;
              const roomId = el.getAttribute('data-room-id');
              const roomName = el.getAttribute('data-room-name') || el.textContent.trim();
              if (roomId) showRoomCalendar(roomId, roomName);
            });
          }
        }

        // Populate booking details pane
        function showBookingDetailsInPane(event) {
          const container = document.getElementById('roomBookingDetailsContent');
          if (!container) return;
          const p = event.extendedProps || {};
          const html = [];
          html.push(`<div class="fw-bold mb-2">${(p.itemName || event.title || 'Booking')}</div>`);
          if (p.roomNumber) html.push(`<div><strong>Room:</strong> ${p.roomNumber}</div>`);
          if (p.guest) html.push(`<div><strong>Guest:</strong> ${p.guest}</div>`);
          if (p.status) html.push(`<div><strong>Status:</strong> ${p.status}</div>`);
          if (p.checkin) html.push(`<div><strong>Check-in:</strong> ${p.checkin}</div>`);
          if (p.checkout) html.push(`<div><strong>Check-out:</strong> ${p.checkout}</div>`);
          if (p.details) html.push(`<div class="mt-2 small text-muted">${p.details}</div>`);
          container.innerHTML = html.join('\n');
        }

        // Show modal with per-room calendar
        function showRoomCalendar(roomId, roomName) {
          window.currentModalRoomId = roomId;
          const titleEl = document.getElementById('roomCalendarModalLabel');
          if (titleEl) titleEl.textContent = `Room Calendar — ${roomName}`;
          initializeRoomModalCalendar(roomId);
          const modalEl = document.getElementById('roomCalendarModal');
          const modal = new bootstrap.Modal(modalEl);
          modal.show();
        }

        // Initialize or reinitialize the modal calendar for a specific room
        function initializeRoomModalCalendar(roomId) {
          const el = document.getElementById('roomModalCalendar');
          if (!el) return;

          // read selected range (days)
          const rangeSelect = document.getElementById('roomCalendarRange');
          const rangeDays = parseInt(rangeSelect ? rangeSelect.value : 60, 10) || 60;

          // destroy previous instance if exists
          if (modalCalendarInstance) {
            modalCalendarInstance.destroy();
            modalCalendarInstance = null;
            el.innerHTML = '';
          }

          const allEvents = window.roomEvents || [];
          // bookings for this room (regular events)
          const bookings = allEvents.filter(e => e.extendedProps && String(e.extendedProps.roomId) === String(roomId));

          // date range for background free area
          const startDate = new Date();
          const endDate = new Date();
          endDate.setDate(endDate.getDate() + rangeDays);

          function toISODate(d) {
            const yyyy = d.getFullYear();
            const mm = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(d.getDate()).padStart(2, '0');
            return `${yyyy}-${mm}-${dd}`;
          }

          const events = [];

          // base free-range background (blue-ish)
          events.push({
            id: `free-range-${roomId}`,
            start: toISODate(startDate),
            end: toISODate(endDate),
            display: 'background',
            backgroundColor: '#cfe2ff' // light blue
          });

          // reserved background overlays (red) for each booking
          bookings.forEach(b => {
            events.push({
              id: `reserved-${b.id}`,
              start: b.start,
              end: b.end,
              display: 'background',
              backgroundColor: '#dc3545'
            });
            // also include the visible event (title)
            events.push(Object.assign({}, b));
          });

          // build calendar
          modalCalendarInstance = new FullCalendar.Calendar(el, {
            initialView: 'dayGridMonth',
            headerToolbar: {
              left: 'prev,next today',
              center: 'title',
              right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: events,
            height: 'auto',
            eventDisplay: 'block',
            nowIndicator: true,
            displayEventTime: false,
            dateClick: function (info) {
              // No-op or optional: could open booking creation
            },
            eventClick: function (info) {
              // show details for booking events
              if (info.event && info.event.id && info.event.id.startsWith('booking-')) {
                showBookingDetailsInPane(info.event);
              }
            }
          });

          modalCalendarInstance.render();
        }

        // re-render modal calendar when range selector changes (if modal open)
        document.addEventListener('DOMContentLoaded', function () {
          const rangeSelect = document.getElementById('roomCalendarRange');
          if (rangeSelect) {
            rangeSelect.addEventListener('change', function () {
              if (window.currentModalRoomId) initializeRoomModalCalendar(window.currentModalRoomId);
            });
          }
        });

      </script>
