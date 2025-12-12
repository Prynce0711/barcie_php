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
              <i class="fas fa-list me-2"></i>Room & Facility Management
            </h5>
            <div class="text-white-50">
              <small>Click on any room to view its availability calendar</small>
            </div>
          </div>
        </div>
        <div class="card-body p-0">
          <!-- Room List View -->
          <div id="room-list-content" class="calendar-content">
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

  <!-- Include Room Calendar Modal Component -->
  <?php include 'room_calendar_modal.php'; ?>

      <!-- JS: initialize room calendar and generate window.roomEvents -->
      <style>
        /* Room List Spinner Overlay */
        .spinner-overlay {
          transition: opacity 250ms ease-in-out;
          opacity: 0;
          pointer-events: none;
          display: none;
        }
        .spinner-overlay.spinner-visible {
          opacity: 1;
          pointer-events: auto;
          display: flex;
        }
      </style>
      <script>
        // Initialize room management when the document is ready
        document.addEventListener('DOMContentLoaded', function () {
          initializeRoomSearch();
        });

        // Global calendar variables
        var modalCalendarInstance = null;

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
            $checkin = $booking['checkin'];
            $checkout = $booking['checkout'];
            $duration_days = ceil((strtotime($checkout) - strtotime($checkin)) / 86400);
            $duration_text = $duration_days > 1 ? "({$duration_days} days)" : '(1 day)';
            $display_title = $item_name . $room_number . ' - Booked ' . $duration_text;
            // Unique colors for each status
            $color = '#dc3545';  // Red for default/booked
            if ($status == 'pending') $color = '#ffc107';      // Yellow
            if ($status == 'approved') $color = '#28a745';     // Green
            if ($status == 'confirmed') $color = '#17a2b8';    // Cyan
            if ($status == 'checked_in') $color = '#0d6efd';   // Blue
            if ($status == 'checked_out') $color = '#6c757d';  // Gray
            if ($status == 'cancelled') $color = '#f39c12';    // Orange-Yellow
            if ($status == 'rejected') $color = '#dc3545';     // Red

            // Emit booking as an event
            echo "window.roomEvents.push({\n";
            echo "  id: 'booking-{$booking['id']}',\n";
            echo "  title: '{$display_title}',\n";
            echo "  start: '{$checkin}',\n";
            echo "  end: '" . date('Y-m-d', strtotime($checkout . ' +1 day')) . "',\n";
            echo "  backgroundColor: '{$color}',\n";
            echo "  borderColor: '{$color}',\n";
            echo "  textColor: '#ffffff',\n";
            echo "  extendedProps: {\n";
            echo "    itemName: '{$item_name}',\n";
            echo "    roomNumber: '" . ($booking['room_number'] ?: '') . "',\n";
            echo "    itemType: '{$item_type}',\n";
            echo "    guest: '{$guest}',\n";
            echo "    status: '{$status}',\n";
            echo "    bookingType: 'regular',\n";
            echo "    checkin: '{$checkin}',\n";
            echo "    checkout: '{$checkout}',\n";
            echo "    durationDays: {$duration_days},\n";
            echo "    roomId: " . ($booking['room_id'] ?: 'null') . "\n";
            echo "  }\n";
            echo "});\n";
          }
        }

        // Fetch pencil bookings if table exists
        $table_check = $conn->query("SHOW TABLES LIKE 'pencil_bookings'");
        if ($table_check && $table_check->num_rows > 0) {
          $pencil_query = "SELECT pb.*, i.name as item_name, i.item_type, i.room_number
                           FROM pencil_bookings pb
                           LEFT JOIN items i ON pb.room_id = i.id
                           WHERE pb.status IN ('approved', 'pending', 'confirmed')
                           AND pb.token_expires_at >= NOW()
                           AND pb.checkin >= CURDATE() - INTERVAL 7 DAY
                           AND pb.checkin <= CURDATE() + INTERVAL 30 DAY
                           ORDER BY pb.checkin ASC";
          $pencil_result = $conn->query($pencil_query);

          if ($pencil_result && $pencil_result->num_rows > 0) {
            while ($pencil = $pencil_result->fetch_assoc()) {
              $item_name = $pencil['item_name'] ? addslashes($pencil['item_name']) : 'Unassigned Room/Facility';
              $room_number = $pencil['room_number'] ? '#' . $pencil['room_number'] : '';
              $item_type = $pencil['item_type'] ?: 'room';
              $guest = $pencil['guest_name'] ? addslashes($pencil['guest_name']) : 'Guest';
              $checkin = $pencil['checkin'];
              $checkout = $pencil['checkout'];
              $duration_days = ceil((strtotime($checkout) - strtotime($checkin)) / 86400);
              $duration_text = $duration_days > 1 ? "({$duration_days} days)" : '(1 day)';
              $display_title = $item_name . $room_number . ' - Pencil ' . $duration_text;
              $color = '#fd7e14'; // Orange for pencil bookings

              echo "window.roomEvents.push({\n";
              echo "  id: 'pencil-{$pencil['id']}',\n";
              echo "  title: '{$display_title}',\n";
              echo "  start: '{$checkin}',\n";
              echo "  end: '" . date('Y-m-d', strtotime($checkout . ' +1 day')) . "',\n";
              echo "  backgroundColor: '{$color}',\n";
              echo "  borderColor: '{$color}',\n";
              echo "  textColor: '#ffffff',\n";
              echo "  extendedProps: {\n";
              echo "    itemName: '{$item_name}',\n";
              echo "    roomNumber: '" . ($pencil['room_number'] ?: '') . "',\n";
              echo "    itemType: '{$item_type}',\n";
              echo "    guest: '{$guest}',\n";
              echo "    status: 'pencil',\n";
              echo "    bookingType: 'pencil',\n";
              echo "    checkin: '{$checkin}',\n";
              echo "    checkout: '{$checkout}',\n";
              echo "    durationDays: {$duration_days},\n";
              echo "    roomId: " . ($pencil['room_id'] ?: 'null') . "\n";
              echo "  }\n";
              echo "});\n";
            }
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

        // Populate booking details pane with comprehensive information
        function showBookingDetailsInPane(event) {
          const container = document.getElementById('roomBookingDetailsContent');
          if (!container) return;
          const p = event.extendedProps || {};
          const bookingId = (event.id || '').toString();
          const bookingNumericId = bookingId.replace('booking-', '').replace('pencil-', '');
          
          // map status to badge class
          const status = (p.status || '').toLowerCase();
          let badgeClass = 'badge-pending';
          if (status === 'approved' || status === 'confirmed') badgeClass = 'badge-approved';
          if (status === 'checked_in') badgeClass = 'badge-checkedin';
          if (status === 'checked_out') badgeClass = 'badge-checkedout';
          if (status === 'pending') badgeClass = 'badge-pending';
          if (status === 'cancelled') badgeClass = 'badge-cancelled';
          if (status === 'pencil') badgeClass = 'badge-warning';

          const html = [];
          
          // Header
          html.push(`<div class="card border-0 shadow-sm">`);
          html.push(`<div class="card-header bg-primary text-white">`);
          html.push(`<h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Booking Details</h6>`);
          html.push(`</div>`);
          html.push(`<div class="card-body">`);
          
          // Booking Title and Status
          html.push(`<div class="d-flex justify-content-between align-items-start mb-3">`);
          html.push(`<div class="fw-bold fs-5">${(p.itemName || event.title || 'Booking')}</div>`);
          html.push(`<span class="booking-badge ${badgeClass} ms-2">${(p.status || 'Unknown').toUpperCase()}</span>`);
          html.push(`</div>`);
          
          // Booking Type
          if (p.bookingType) {
            const typeIcon = p.bookingType === 'pencil' ? 'fa-pencil-alt' : 'fa-calendar-check';
            const typeClass = p.bookingType === 'pencil' ? 'text-warning' : 'text-primary';
            html.push(`<div class="detail-row mb-2"><span class="detail-key"><i class="fas ${typeIcon} ${typeClass} me-2"></i>Type:</span> <strong>${p.bookingType === 'pencil' ? 'Pencil Booking (Draft)' : 'Confirmed Booking'}</strong></div>`);
          }
          
          // Room/Facility Details
          if (p.roomNumber) {
            html.push(`<div class="detail-row mb-2"><span class="detail-key"><i class="fas fa-door-open text-info me-2"></i>Room:</span> <strong>#${p.roomNumber}</strong></div>`);
          }
          if (p.itemType) {
            html.push(`<div class="detail-row mb-2"><span class="detail-key"><i class="fas fa-building text-secondary me-2"></i>Type:</span> ${p.itemType.charAt(0).toUpperCase() + p.itemType.slice(1)}</div>`);
          }
          
          // Guest Information
          if (p.guest) {
            html.push(`<div class="detail-row mb-2"><span class="detail-key"><i class="fas fa-user text-success me-2"></i>Guest:</span> <strong>${p.guest}</strong></div>`);
          }
          
          // Date Information
          html.push(`<hr class="my-3">`);
          html.push(`<h6 class="mb-2"><i class="fas fa-calendar-alt text-primary me-2"></i>Stay Duration</h6>`);
          if (p.checkin) {
            html.push(`<div class="detail-row mb-2"><span class="detail-key"><i class="fas fa-sign-in-alt text-success me-1"></i>Check-in:</span> <strong>${formatDate(p.checkin)}</strong></div>`);
          }
          if (p.checkout) {
            html.push(`<div class="detail-row mb-2"><span class="detail-key"><i class="fas fa-sign-out-alt text-danger me-1"></i>Check-out:</span> <strong>${formatDate(p.checkout)}</strong></div>`);
          }
          if (p.durationDays) {
            html.push(`<div class="detail-row mb-2"><span class="detail-key"><i class="fas fa-clock text-info me-1"></i>Duration:</span> <strong>${p.durationDays} ${p.durationDays === 1 ? 'day' : 'days'}</strong></div>`);
          }
          
          // Additional Details
          if (p.details) {
            html.push(`<hr class="my-3">`);
            html.push(`<div class="small text-muted"><i class="fas fa-sticky-note me-2"></i>${p.details}</div>`);
          }

          // Action buttons
          html.push(`<hr class="my-3">`);
          html.push(`<div class="booking-actions d-grid gap-2">`);
          html.push(`<button class="btn btn-sm btn-primary booking-action-btn" data-action="view" data-booking-id="${bookingId}"><i class="fas fa-eye me-2"></i>View Full Details</button>`);
          html.push(`<div class="d-flex gap-2">`);
          html.push(`<button class="btn btn-sm btn-outline-secondary flex-fill booking-action-btn" data-action="edit" data-booking-id="${bookingId}"><i class="fas fa-edit me-2"></i>Edit</button>`);
          html.push(`<button class="btn btn-sm btn-outline-danger flex-fill booking-action-btn" data-action="cancel" data-booking-id="${bookingId}"><i class="fas fa-times-circle me-2"></i>Cancel</button>`);
          html.push(`</div>`);
          html.push(`</div>`);
          
          html.push(`</div>`); // card-body
          html.push(`</div>`); // card

          container.innerHTML = html.join('\n');
        }
        
        // Helper function to format dates nicely
        function formatDate(dateStr) {
          if (!dateStr) return '';
          const date = new Date(dateStr);
          const options = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' };
          return date.toLocaleDateString('en-US', options);
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

        async function handleBookingAction(action, bookingId) {
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
            const confirmed = await showConfirm(
              'Are you sure you want to cancel this booking?',
              { title: 'Cancel Booking', confirmText: 'Yes, Cancel', confirmClass: 'btn-danger' }
            );
            if (!confirmed) return;
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
            contentHeight: 280,
            aspectRatio: 3.0,
            eventDisplay: 'block',
            nowIndicator: true,
            displayEventTime: true,
            displayEventEnd: true,
            fixedWeekCount: true,
            showNonCurrentDates: false,
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



      </script>
