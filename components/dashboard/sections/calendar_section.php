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
                  <!-- FullCalendar will render its own view buttons in the calendar header; removed duplicate custom view buttons -->
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
            <div class="p-3 position-relative">
              <!-- Spinner overlay shown while the calendar initializes -->
              <div id="roomCalendarSpinner" class="spinner-overlay d-flex justify-content-center align-items-center" style="position:absolute; inset:0; background: rgba(255,255,255,0.75); z-index:50;">
                <div class="spinner-border text-primary" role="status" style="width:3rem;height:3rem;"><span class="visually-hidden">Loading calendar...</span></div>
              </div>
              <div id="roomCalendar" style="min-height:200px;"></div>
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
            <div class="room-list-container position-relative" style="max-height: 600px; overflow-y: auto;">
              <!-- Spinner overlay shown while room list is being annotated/refreshed -->
              <div id="roomListSpinner" class="spinner-overlay d-flex justify-content-center align-items-center" style="position:absolute; inset:0; background: rgba(255,255,255,0.6); z-index:40;">
                <div class="spinner-border text-secondary" role="status"><span class="visually-hidden">Loading rooms...</span></div>
              </div>
              <?php include 'room_list_content.php'; ?>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <!-- Room Calendar Modal - outside the card for proper z-index -->
  <div class="modal fade" id="roomCalendarModal" tabindex="-1" aria-labelledby="roomCalendarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header d-flex align-items-center">
          <h5 class="modal-title me-3" id="roomCalendarModalLabel">Room Calendar</h5>
          <div class="me-auto">
            <label class="small text-muted me-2">Range:</label>
            <select id="roomCalendarRange" class="form-select form-select-sm d-inline-block" style="width: auto;">
              <option value="30">30 days</option>
              <option value="90" selected>90 days</option>
              <option value="365">365 days</option>
            </select>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <!-- Color Legend -->
          <div class="alert alert-info py-2 mb-3">
            <div class="d-flex align-items-center justify-content-center gap-4 small">
              <span><span class="badge" style="background-color: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb;">■</span> Free/Available</span>
              <span><span class="badge" style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;">■</span> Reserved/Booked</span>
              <span class="text-muted"><i class="fas fa-info-circle me-1"></i>Click on a booking to see details</span>
            </div>
          </div>
          
          <div class="row">
            <div class="col-lg-8 position-relative">
              <!-- Spinner overlay for modal calendar while it renders -->
              <div id="roomModalSpinner" class="spinner-overlay d-flex justify-content-center align-items-center" style="position:absolute; inset:0; background: rgba(255,255,255,0.8); z-index:60;">
                <div class="spinner-border text-primary" role="status" style="width:2.5rem; height:2.5rem;"><span class="visually-hidden">Loading room calendar...</span></div>
              </div>
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

      <!-- JS: initialize room calendar and generate window.roomEvents -->
      <style>
        /* Spinner overlay styles with fade transition */
        .spinner-overlay {
          transition: opacity 250ms ease-in-out;
          opacity: 0;
          pointer-events: none;
          display: none; /* hidden by default */
        }
        .spinner-overlay.spinner-visible {
          opacity: 1;
          pointer-events: auto;
          display: flex; /* ensure flex layout while visible */
        }
        /* UI polish for calendar and modal */
        /* Design tokens */
        :root{
          --brand-primary: #0d6efd;
          --brand-accent: #6f42c1;
          --status-approved: #28a745;
          --status-checkedin: #0d6efd;
          --status-checkedout: #6f42c1;
          --status-pending: #fd7e14;
          --status-cancelled: #dc3545;
          --muted: #6c757d;
          --card-bg: #ffffff;
        }
        /* Rounded toolbar and modern buttons */
        #roomCalendar .fc-toolbar, #roomModalCalendar .fc-toolbar {
          background: linear-gradient(180deg, rgba(255,255,255,0.95), rgba(248,249,250,0.9));
          border-radius: 8px;
          padding: 0.5rem;
          box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        }
        .fc-button {
          border-radius: 6px;
          border: 1px solid rgba(0,0,0,0.06);
          background: #ffffff;
          color: #0d6efd;
          box-shadow: none;
        }
        .fc-button-primary {
          background: linear-gradient(180deg,#0d6efd,#0b5ed7);
          color: #fff;
          border: none;
        }
        /* Make day cells cleaner and more spacious */
        .fc-daygrid-day-frame { padding: 0.6rem 0.5rem; }
        .fc-daygrid-day-top { padding: 0.2rem 0.5rem; }
        .fc-daygrid-day-number { font-weight:600; color:#0b5ed7; }
  /* Booking details pane */
  #roomBookingDetails { background:var(--card-bg); }
  #roomBookingDetailsContent .detail-row { margin-bottom:0.5rem; }
  #roomBookingDetailsContent .detail-key { color:var(--muted); min-width:100px; display:inline-block; }
  /* Status badges */
  .booking-badge { display:inline-block; padding:0.25rem .5rem; border-radius:0.375rem; color:#fff; font-size:.85rem; }
  .badge-approved { background: var(--status-approved); }
  .badge-checkedin { background: var(--status-checkedin); }
  .badge-checkedout { background: var(--status-checkedout); }
  .badge-pending { background: var(--status-pending); }
  .badge-cancelled { background: var(--status-cancelled); }
  .booking-actions { margin-top:0.75rem; }
  .booking-actions .btn { margin-right:0.4rem; }
        /* Mobile responsive tweak for modal */
        @media (max-width: 991px) {
          #roomBookingDetails { min-height: 200px; }
        }
      </style>
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

          // Show spinner while the calendar is being initialized
          try { showSpinnerById('roomCalendarSpinner'); } catch (e) {}

          // Generate room events based on current booking data
          const roomEvents = window.roomEvents || [];

          calendarInstance = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
              left: 'prev,next today',
              center: 'title',
              // omit built-in view buttons on the right to avoid duplication with top-level controls
              right: ''
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
                // Populate the booking details pane instead of using alerts
                try {
                  showBookingDetailsInPane(info.event);
                  // If modal is open (modal calendar), also highlight the selected event
                  if (modalCalendarInstance && modalCalendarInstance.getEventById(info.event.id)) {
                    // scroll or visually indicate selection if needed
                  }
                } catch (e) { console.warn('eventClick error', e); }
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

          // hide the calendar spinner after render (render is synchronous)
          try { hideSpinnerById('roomCalendarSpinner'); } catch (e) {}
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

        // Spinner helpers that use CSS transitions with a small debounce so quick
        // operations don't flash the spinner. Toggles a 'spinner-visible' class
        // and ensures display is set while fading in/out. `fadeMs` should match CSS transition.
        var _spinnerFadeMs = 250; // milliseconds - keep in sync with CSS transition
        var _spinnerDebounceMs = 150; // only show spinner if operation > 150ms
        // store per-spinner timers: { '<id>_show': timeoutId, '<id>_hide': timeoutId }
        window._spinnerTimers = window._spinnerTimers || {};

        function showSpinnerById(id) {
          try {
            var el = document.getElementById(id);
            if (!el) return;

            // If a hide timer is pending, cancel it (we want to show now)
            var hideKey = id + '_hide';
            if (window._spinnerTimers[hideKey]) {
              clearTimeout(window._spinnerTimers[hideKey]);
              delete window._spinnerTimers[hideKey];
            }

            // If already visible, nothing to do
            if (el.classList.contains('spinner-visible')) return;

            // If a show timer is already scheduled, do nothing
            var showKey = id + '_show';
            if (window._spinnerTimers[showKey]) return;

            // Schedule showing after debounce interval
            window._spinnerTimers[showKey] = setTimeout(function () {
              try {
                // make sure element is in flow and ready to animate
                el.style.display = 'flex';
                // trigger the fade-in via class toggle
                el.classList.add('spinner-visible');
                el.setAttribute('aria-hidden', 'false');
              } catch (e) {
                console.warn('showSpinnerById inner error for', id, e);
              }
              // clear timer handle
              delete window._spinnerTimers[showKey];
            }, _spinnerDebounceMs);
          } catch (e) {
            console.warn('showSpinnerById error for', id, e);
          }
        }

        function hideSpinnerById(id) {
          try {
            var el = document.getElementById(id);
            if (!el) return;

            var showKey = id + '_show';
            var hideKey = id + '_hide';

            // If a show timer is pending (debounce not yet triggered), cancel it and never show
            if (window._spinnerTimers[showKey]) {
              clearTimeout(window._spinnerTimers[showKey]);
              delete window._spinnerTimers[showKey];
              return; // nothing to hide because spinner never showed
            }

            // If spinner is not visible, nothing to do
            if (!el.classList.contains('spinner-visible')) return;

            // Remove visible class to start fade-out
            el.classList.remove('spinner-visible');
            el.setAttribute('aria-hidden', 'true');

            // Clear any previous hide timer
            if (window._spinnerTimers[hideKey]) {
              clearTimeout(window._spinnerTimers[hideKey]);
            }

            // After fade transition, set display none
            window._spinnerTimers[hideKey] = setTimeout(function () {
              try { el.style.display = 'none'; } catch (e) {}
              delete window._spinnerTimers[hideKey];
            }, _spinnerFadeMs + 20);
          } catch (e) {
            console.warn('hideSpinnerById error for', id, e);
          }
        }

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
          console.log('Initializing room search...');
          const searchInput = document.getElementById('room-search');
          const container = document.querySelector('.room-list-container');
          console.log('Container found:', !!container);

          // If the included room list doesn't provide clickable items, build a simple list
          const existingItems = container ? container.querySelectorAll('.room-list-item, .room-item, .list-group-item') : [];
          console.log('Existing room items found in DOM:', existingItems.length);

          // If there are no items at all, build a fallback list from window.roomList
          if (container && existingItems.length === 0) {
            console.log('Building fallback room list from window.roomList:', window.roomList);
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
            console.log('Added', window.roomList.length, 'room items to list');
          } else if (container && existingItems.length > 0) {
            // There are DOM items present but they might lack data-room-id attributes.
            // Try to annotate them by matching their visible text to entries in window.roomList.
            try {
              const lookup = (window.roomList || []).reduce((acc, r) => {
                const key = (r.name + (r.roomNumber ? ' #' + r.roomNumber : '')).trim().toLowerCase();
                acc[key] = r;
                return acc;
              }, {});

              existingItems.forEach(el => {
                // if element already has a room id, skip
                if (el.getAttribute('data-room-id')) {
                  el.style.cursor = el.style.cursor || 'pointer';
                  return;
                }

                const text = (el.textContent || el.innerText || '').trim();
                const key = text.toLowerCase();
                const match = lookup[key];
                if (match) {
                  el.setAttribute('data-room-id', match.id);
                  el.setAttribute('data-room-name', match.name + (match.roomNumber ? ' #' + match.roomNumber : ''));
                  el.classList.add('room-list-item');
                  el.style.cursor = el.style.cursor || 'pointer';
                  console.log('Annotated room list element with data-room-id:', match.id, 'text:', text);
                } else {
                  // no exact match; leave as-is but make clickable to attempt best-effort lookup when clicked
                  el.style.cursor = el.style.cursor || 'pointer';
                }
              });
            } catch (ex) {
              console.warn('Error while annotating existing room list items:', ex);
            }
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

          // Click handler (event delegation) - prefer container delegation but also install a document
          // level fallback so dynamically-inserted items are also handled reliably.
          function handleRoomClickEvent(e) {
            try {
              const el = (e.target && e.target.closest) ? e.target.closest('[data-room-id], .room-item') : null;
              if (!el) return;

              // Prefer explicit attribute, but allow dataset or fallback to text content
              const roomId = el.getAttribute('data-room-id') || (el.dataset && el.dataset.roomId) || null;
              const roomName = el.getAttribute('data-room-name') || (el.dataset && el.dataset.roomName) || el.textContent.trim();

              console.log('Room click detected. id=', roomId, 'name=', roomName);

              if (!roomId) {
                // Try best-effort match from window.roomList using visible text
                const text = (el.textContent || el.innerText || '').trim().toLowerCase();
                const match = (window.roomList || []).find(r => (r.name + (r.roomNumber ? ' #' + r.roomNumber : '')).trim().toLowerCase() === text);
                if (match) {
                  console.log('Matched room by text to id', match.id);
                  showRoomCalendar(match.id, match.name + (match.roomNumber ? ' #' + match.roomNumber : ''));
                  return;
                }
                console.warn('Clicked room element has no data-room-id and no match was found:', text);
                return;
              }

              showRoomCalendar(roomId, roomName);
            } catch (err) {
              console.error('Error handling room click:', err);
            }
          }

          if (container) {
            container.addEventListener('click', handleRoomClickEvent);
          }

          // Document-level fallback in case the container is replaced later or items are injected.
          document.addEventListener('click', function (e) {
            // Avoid double-handling when already handled by container
            const withinContainer = e.target && e.target.closest && e.target.closest('.room-list-container');
            if (withinContainer) return;
            handleRoomClickEvent(e);
          });
        }

        // Populate booking details pane
        function showBookingDetailsInPane(event) {
          const container = document.getElementById('roomBookingDetailsContent');
          if (!container) return;
          const p = event.extendedProps || {};
          const bookingId = (event.id || '').toString();
          // map status to badge class
          const status = (p.status || '').toLowerCase();
          let badgeClass = 'badge-pending';
          if (status === 'approved' || status === 'confirmed') badgeClass = 'badge-approved';
          if (status === 'checked_in') badgeClass = 'badge-checkedin';
          if (status === 'checked_out') badgeClass = 'badge-checkedout';
          if (status === 'pending') badgeClass = 'badge-pending';
          if (status === 'cancelled') badgeClass = 'badge-cancelled';

          const html = [];
          html.push(`<div class="fw-bold mb-2">${(p.itemName || event.title || 'Booking')} <span class="booking-badge ${badgeClass} ms-2">${(p.status || 'Unknown')}</span></div>`);
          if (p.roomNumber) html.push(`<div class="detail-row"><span class="detail-key">Room:</span> ${p.roomNumber}</div>`);
          if (p.guest) html.push(`<div class="detail-row"><span class="detail-key">Guest:</span> ${p.guest}</div>`);
          if (p.checkin) html.push(`<div class="detail-row"><span class="detail-key">Check-in:</span> ${p.checkin}</div>`);
          if (p.checkout) html.push(`<div class="detail-row"><span class="detail-key">Check-out:</span> ${p.checkout}</div>`);
          if (p.details) html.push(`<div class="mt-2 small text-muted">${p.details}</div>`);

          // action buttons
          html.push(`<div class="booking-actions"><button class="btn btn-sm btn-outline-primary booking-action-btn" data-action="view" data-booking-id="${bookingId}">View</button><button class="btn btn-sm btn-outline-secondary booking-action-btn" data-action="edit" data-booking-id="${bookingId}">Edit</button><button class="btn btn-sm btn-outline-danger booking-action-btn" data-action="cancel" data-booking-id="${bookingId}">Cancel</button></div>`);

          container.innerHTML = html.join('\n');
        }

        // Handle action buttons in booking details pane
        document.addEventListener('click', function (e) {
          const btn = e.target && e.target.closest && e.target.closest('.booking-action-btn');
          if (!btn) return;
          const action = btn.getAttribute('data-action');
          const bookingId = btn.getAttribute('data-booking-id');
          if (!action || !bookingId) return;
          handleBookingAction(action, bookingId);
        });

        function handleBookingAction(action, bookingId) {
          // simple action routing. Adjust URLs to actual app routes if available.
          console.log('Booking action', action, 'for', bookingId);
          const details = document.getElementById('roomBookingDetailsContent');
          if (action === 'view') {
            // open booking details API page in new tab (existing api/get_booking_details.php)
            const url = 'api/get_booking_details.php?id=' + encodeURIComponent(bookingId.replace('booking-', ''));
            window.open(url, '_blank');
            return;
          }
          if (action === 'edit') {
            // try to open a dashboard edit route if exists
            const editUrl = 'dashboard.php?booking_id=' + encodeURIComponent(bookingId.replace('booking-', '')) + '&action=edit';
            window.location.href = editUrl;
            return;
          }
          if (action === 'cancel') {
            // optimistic UI: show confirmation inline and then POST to cancel endpoint if exists
            if (!confirm('Are you sure you want to cancel this booking?')) return;
            // call API to cancel (endpoint not defined here) - attempt to hit a sensible endpoint
            fetch('api/cancel_booking.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ id: bookingId.replace('booking-', '') })
            }).then(r => r.json()).then(j => {
              if (j && j.success) {
                if (details) details.innerHTML = '<div class="text-success">Booking cancelled successfully.</div>';
                // refresh modal calendar and main calendar
                if (window.currentModalRoomId) initializeRoomModalCalendar(window.currentModalRoomId);
                try { if (calendarInstance) calendarInstance.refetchEvents(); } catch (e) {}
              } else {
                if (details) details.innerHTML = '<div class="text-danger">Could not cancel booking.</div>';
                console.warn('Cancel response', j);
              }
            }).catch(err => {
              console.error('Cancel request failed', err);
              if (details) details.innerHTML = '<div class="text-danger">Cancel failed (network).</div>';
            });
            return;
          }
        }

        // Show modal with per-room calendar
        function showRoomCalendar(roomId, roomName) {
          console.log('showRoomCalendar called:', roomId, roomName);
          window.currentModalRoomId = roomId;
          const titleEl = document.getElementById('roomCalendarModalLabel');
          if (titleEl) titleEl.textContent = `Room Calendar — ${roomName}`;

          const modalEl = document.getElementById('roomCalendarModal');
          if (!modalEl) {
            console.error('Modal element not found!');
            return;
          }

          // Ensure modal is appended to document.body to avoid z-index/overflow issues from parent containers
          try {
            if (modalEl.parentNode !== document.body) {
              document.body.appendChild(modalEl);
              console.log('Appended modal element to document.body to avoid clipping/overflow.');
            }
          } catch (ex) {
            console.warn('Could not move modal to body:', ex);
          }

          if (typeof bootstrap === 'undefined') {
            console.error('Bootstrap JS is not loaded. Modal will not open.');
            const details = document.getElementById('roomBookingDetailsContent');
            if (details) details.innerHTML = '<div class="text-danger">Bootstrap JS not loaded. Modal cannot open.</div>';
            return;
          }

          const modal = new bootstrap.Modal(modalEl);

          // Initialize calendar after modal is shown (FullCalendar needs visible container)
          modalEl.addEventListener('shown.bs.modal', function onShown() {
            // Check FullCalendar availability
            if (typeof FullCalendar === 'undefined' && typeof FullCalendar !== 'object' && typeof window.FullCalendar === 'undefined') {
              console.error('FullCalendar is not loaded. Calendar cannot render.');
              const details = document.getElementById('roomBookingDetailsContent');
              if (details) details.innerHTML = '<div class="text-danger">FullCalendar JS not loaded. Calendar cannot render.</div>';
            } else {
              initializeRoomModalCalendar(roomId);
            }
            modalEl.removeEventListener('shown.bs.modal', onShown);
          });

          modal.show();
        }

        

        // Initialize or reinitialize the modal calendar for a specific room
        function initializeRoomModalCalendar(roomId) {
          console.log('initializeRoomModalCalendar called for room:', roomId);
          const el = document.getElementById('roomModalCalendar');
          if (!el) {
            console.error('roomModalCalendar element not found!');
            return;
          }

          // show modal spinner while building calendar
          try { showSpinnerById('roomModalSpinner'); } catch (e) {}

          // read selected range (days)
          const rangeSelect = document.getElementById('roomCalendarRange');
          const rangeDays = parseInt(rangeSelect ? rangeSelect.value : 90, 10) || 90;
          console.log('Using range:', rangeDays, 'days');

          // destroy previous instance if exists
          if (modalCalendarInstance) {
            console.log('Destroying previous calendar instance');
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

          // base free-range background (LIGHT BLUE for available dates)
          events.push({
            id: `free-range-${roomId}`,
            start: toISODate(startDate),
            end: toISODate(endDate),
            display: 'background',
            backgroundColor: '#d1ecf1', // Light blue - FREE
            borderColor: 'transparent'
          });

          console.log('Found', bookings.length, 'bookings for room', roomId);

          // reserved background overlays (LIGHT RED for booked dates)
          bookings.forEach(b => {
            console.log('Adding booking event:', b.id, 'from', b.start, 'to', b.end);
            
            // Background color for reserved dates
            events.push({
              id: `reserved-bg-${b.id}`,
              start: b.start,
              end: b.end,
              display: 'background',
              backgroundColor: '#f8d7da', // Light red - RESERVED
              borderColor: 'transparent'
            });
            
            // Visible booking event with details - preserve original colors where possible
            events.push({
              id: b.id,
              title: b.title,
              start: b.start,
              end: b.end,
              backgroundColor: b.backgroundColor || b.background || '#dc3545',
              borderColor: b.borderColor || b.backgroundColor || b.borderColor || (b.backgroundColor || '#dc3545'),
              textColor: b.textColor || '#ffffff',
              extendedProps: b.extendedProps
            });
          });

          // build calendar
          console.log('Building modal calendar with', events.length, 'events (including backgrounds)');
          modalCalendarInstance = new FullCalendar.Calendar(el, {
            initialView: 'dayGridMonth',
            headerToolbar: {
              left: 'prev,next today',
              center: 'title',
              // modal calendar: don't render view buttons (we control views elsewhere)
              right: ''
            },
            events: events,
            height: 'auto',
            contentHeight: 'auto',
            aspectRatio: 1.5,
            eventDisplay: 'block',
            nowIndicator: true,
            displayEventTime: true,
            displayEventEnd: true,
            fixedWeekCount: false,
            showNonCurrentDates: true,
            dateClick: function (info) {
              console.log('Date clicked:', info.dateStr);
              // Could allow booking creation here
            },
            eventClick: function (info) {
              console.log('Event clicked:', info.event.id, info.event.title);
              // show details for booking events
              if (info.event && info.event.id && info.event.id.startsWith('booking-')) {
                showBookingDetailsInPane(info.event);
              }
            },
            eventDidMount: function(info) {
              // Add tooltip on hover
              if (info.event.extendedProps && info.event.extendedProps.guest) {
                info.el.title = `${info.event.title}\nClick for details`;
              }
            }
          });

          console.log('Rendering calendar...');
          modalCalendarInstance.render();
          console.log('Calendar rendered successfully');

          // hide modal spinner now that render is done
          try { hideSpinnerById('roomModalSpinner'); } catch (e) {}
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

        // Fetch latest room list from API and refresh DOM + window.roomList
        async function fetchAndRefreshRoomList() {
          const container = document.querySelector('.room-list-container');
          if (!container) return;

          try {
            // show spinner while fetching/annotating
            try { showSpinnerById('roomListSpinner'); } catch (e) {}
            const res = await fetch('api/items.php', { method: 'GET', credentials: 'same-origin' });
            if (!res.ok) throw new Error('Failed to fetch items: ' + res.status);
            const data = await res.json();
            const items = (data && data.items) ? data.items : [];

            // update global list
            window.roomList = items.map(i => ({ id: i.id, name: i.name, roomNumber: i.room_number || '', itemType: i.item_type || 'room' }));

            console.log('Room list data fetched from API:', items.length, 'items');
            
            // Don't replace the container content - the PHP already rendered proper room cards
            // Just ensure existing items have proper data attributes by annotating them
            const existingItems = container.querySelectorAll('.room-item, .room-card');
            
            if (existingItems.length > 0) {
              console.log('Found', existingItems.length, 'existing room cards, annotating with data attributes...');
              
              const lookup = items.reduce((acc, i) => {
                const key = (i.name + (i.room_number ? ' #' + i.room_number : '')).trim().toLowerCase();
                acc[key] = i;
                return acc;
              }, {});
              
              existingItems.forEach(el => {
                if (el.getAttribute('data-room-id')) return; // Already has ID
                
                const text = (el.textContent || el.innerText || '').trim();
                const key = text.toLowerCase();
                const match = lookup[key];
                
                if (match) {
                  el.setAttribute('data-room-id', match.id);
                  el.setAttribute('data-room-name', match.name + (match.room_number ? ' #' + match.room_number : ''));
                  el.style.cursor = 'pointer';
                  console.log('Annotated room card:', match.name);
                }
              });
              
              console.log('Room cards annotated successfully');
            } else {
              console.log('No existing room cards found, building simple fallback list');
              // Only build fallback if there are no existing cards at all
              const listGroup = document.createElement('div');
              listGroup.className = 'list-group list-group-flush';

              items.forEach(i => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'list-group-item list-group-item-action room-list-item';
                btn.setAttribute('data-room-id', i.id);
                const rn = i.room_number ? (' #' + i.room_number) : '';
                btn.setAttribute('data-room-name', i.name + rn);
                btn.textContent = i.name + rn;
                listGroup.appendChild(btn);
              });

              container.innerHTML = '';
              container.appendChild(listGroup);
              console.log('Fallback list built with', items.length, 'items');
            }
            // hide spinner after DOM annotation or fallback done
            try { hideSpinnerById('roomListSpinner'); } catch (e) {}
          } catch (err) {
            console.warn('Could not refresh room list:', err);
            try { hideSpinnerById('roomListSpinner'); } catch (e) {}
          }
        }

        // Expose a global helper to refresh room list after adding a room
        window.refreshRoomList = fetchAndRefreshRoomList;

        // Optionally auto-refresh on page load in case items were added while user was on the page
        document.addEventListener('DOMContentLoaded', function () {
          // small delay to allow server-side window.roomList initialization to finish
          setTimeout(() => { fetchAndRefreshRoomList(); }, 500);
        });

        // Ensure calendar initializes even if this section is injected after DOMContentLoaded
        (function ensureCalendarInitialization() {
          var _initialized = false;

          function isVisible(el) {
            if (!el) return false;
            // offsetParent is null when display:none; use getClientRects for more robust check
            try {
              return el.getClientRects().length > 0;
            } catch (e) { return false; }
          }

          function tryInit() {
            if (_initialized) return;
            var calendarEl = document.getElementById('roomCalendar');
            if (calendarEl && isVisible(calendarEl)) {
              // Wait for FullCalendar library to be available before initializing
              waitForFullCalendar(function (ready) {
                if (!ready) {
                  console.error('FullCalendar library not available after retries.');
                  // hide spinner so UI isn't blocked
                  try { hideSpinnerById('roomCalendarSpinner'); } catch (e) {}
                  return;
                }
                try {
                  initializeRoomCalendar();
                  _initialized = true;
                } catch (e) {
                  console.warn('initializeRoomCalendar threw, will retry later', e);
                }
              });
              return true;
            }
            return false;
          }

          // Wait for FullCalendar to be defined (with retries up to ~5s)
          function waitForFullCalendar(callback) {
            try {
              // immediate check
              if ((window.FullCalendar && window.FullCalendar.Calendar) || (typeof FullCalendar !== 'undefined' && FullCalendar && FullCalendar.Calendar)) {
                callback(true);
                return;
              }
            } catch (e) {}

            var waited = 0;
            var interval = 150; // ms
            var maxWait = 5000; // ms
            var t = setInterval(function () {
              waited += interval;
              try {
                if ((window.FullCalendar && window.FullCalendar.Calendar) || (typeof FullCalendar !== 'undefined' && FullCalendar && FullCalendar.Calendar)) {
                  clearInterval(t);
                  callback(true);
                  return;
                }
              } catch (e) {}
              if (waited >= maxWait) {
                clearInterval(t);
                callback(false);
              }
            }, interval);
          }

          // Try immediately in case DOMContentLoaded already fired and element exists
          tryInit();

          // Try on load and hashchange (useful if SPA changes location/hash)
          window.addEventListener('load', tryInit);
          window.addEventListener('hashchange', tryInit);

          // Observe DOM additions to detect when the calendar section is injected
          var observer = new MutationObserver(function (mutations, obs) {
            if (tryInit()) {
              obs.disconnect();
            }
          });
          observer.observe(document.documentElement || document.body, { childList: true, subtree: true });

          // As a safeguard, if the calendar doesn't initialize within 5s, hide any lingering spinner
          setTimeout(function () {
            if (!_initialized) {
              try { hideSpinnerById('roomCalendarSpinner'); } catch (e) {}
            }
          }, 5000);
        })();

      </script>
