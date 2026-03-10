<?php
// Calendar Section Template
// Displays room & facility cards with per-room availability calendar (guest-style)
?>

<!-- Calendar & Rooms Section -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="fas fa-calendar-alt me-2 text-primary"></i>Room &amp; Facility Availability</h4>
        <div class="d-flex gap-2 align-items-center">
          <div class="btn-group btn-group-sm" role="group" aria-label="Filter">
            <button type="button" class="btn btn-outline-secondary admin-room-filter-btn active" data-filter="all">All</button>
            <button type="button" class="btn btn-outline-secondary admin-room-filter-btn" data-filter="room">Rooms</button>
            <button type="button" class="btn btn-outline-secondary admin-room-filter-btn" data-filter="facility">Facilities</button>
          </div>
          <div class="input-group input-group-sm" style="max-width: 220px;">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
            <input type="text" class="form-control" placeholder="Search..." id="room-search">
          </div>
        </div>
      </div>

      <!-- Room/Facility Card List (JS-rendered like guest AvailabilityCalendar) -->
      <div class="room-list-container position-relative">
        <div id="adminRoomListContainer" class="row">
          <div class="col-12 text-center py-5">
            <i class="fas fa-spinner fa-spin fa-2x text-muted mb-3"></i>
            <p class="text-muted">Loading rooms and facilities...</p>
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
        // Initialize room card rendering when DOM is ready
        document.addEventListener('DOMContentLoaded', function () {
          renderAdminRoomCards();
          initAdminRoomFilters();
          initAdminRoomSearch();
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

        // Generate room list with full data for card rendering (images, price, capacity)
        echo "window.roomList = window.roomList || [];\n";
        $items_query = "SELECT id, name, room_number, item_type, capacity, price, images, image FROM items ORDER BY 
                        CASE WHEN LOWER(TRIM(item_type))='room' THEN 0 WHEN LOWER(TRIM(item_type))='facility' THEN 1 ELSE 2 END,
                        room_number ASC, name ASC";
        $items_result = $conn->query($items_query);
        if ($items_result && $items_result->num_rows > 0) {
          while ($item = $items_result->fetch_assoc()) {
            $iname = $item['name'] ? addslashes($item['name']) : 'Unnamed';
            $rnum = $item['room_number'] ? addslashes($item['room_number']) : '';
            $itype = addslashes($item['item_type'] ?: 'room');
            $capacity = (int)($item['capacity'] ?: 0);
            $price = (int)($item['price'] ?: 0);
            // Pass raw images/image fields for JS-side image resolution
            $images_raw = $item['images'] ? addslashes($item['images']) : '';
            $image_raw = $item['image'] ? addslashes($item['image']) : '';
            echo "window.roomList.push({ id: {$item['id']}, name: '{$iname}', roomNumber: '{$rnum}', itemType: '{$itype}', capacity: {$capacity}, price: {$price}, images: '{$images_raw}', image: '{$image_raw}' });\n";
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



        // Choose best preview image for a room/facility item
        function chooseAdminPreviewImage(item) {
          var defaultImg = 'public/images/imageBg/barcie_logo.jpg';
          function normalize(path) {
            if (!path || typeof path !== 'string') return null;
            path = path.trim();
            if (!path) return null;
            if (path.startsWith('http://') || path.startsWith('https://')) return path;
            return path.replace(/^\/+/, '');
          }
          try {
            if (item.images) {
              var imgs = item.images;
              if (typeof imgs === 'string') {
                try { imgs = JSON.parse(imgs); } catch(e) { imgs = [item.images]; }
              }
              if (Array.isArray(imgs) && imgs.length) {
                var first = imgs[0];
                if (typeof first === 'object' && first !== null) first = first.url || first.src || first.path || null;
                var n = normalize(first);
                if (n) return n;
              }
            }
            var candidates = [item.image, item.preview, item.thumbnail];
            for (var i = 0; i < candidates.length; i++) {
              var n = normalize(candidates[i]);
              if (n) return n;
            }
          } catch(e) {}
          return defaultImg;
        }

        // Current filter state
        var _adminRoomFilter = 'all';

        // Render room/facility cards in guest AvailabilityCalendar style
        function renderAdminRoomCards(filterType) {
          var container = document.getElementById('adminRoomListContainer');
          if (!container) return;

          var filter = (typeof filterType === 'string' && filterType) ? filterType : _adminRoomFilter;
          _adminRoomFilter = filter;

          var items = window.roomList || [];
          if (!items.length) {
            container.innerHTML = '<div class="col-12 text-center py-5"><i class="fas fa-building fa-2x text-muted mb-3"></i><p class="text-muted">No rooms or facilities found.</p></div>';
            return;
          }

          // Apply filter
          var filtered = items;
          if (filter === 'room') {
            filtered = items.filter(function(i) { return (i.itemType || '').toLowerCase() === 'room'; });
          } else if (filter === 'facility') {
            filtered = items.filter(function(i) { return (i.itemType || '').toLowerCase() === 'facility'; });
          }

          // Apply search term if present
          var searchInput = document.getElementById('room-search');
          var searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
          if (searchTerm) {
            filtered = filtered.filter(function(i) {
              var text = (i.name + ' ' + (i.roomNumber || '') + ' ' + (i.itemType || '')).toLowerCase();
              return text.indexOf(searchTerm) !== -1;
            });
          }

          if (!filtered.length) {
            container.innerHTML = '<div class="col-12 text-center py-5"><i class="fas fa-exclamation-circle fa-2x text-muted mb-3"></i><p class="text-muted">No ' + (filter === 'room' ? 'rooms' : (filter === 'facility' ? 'facilities' : 'items')) + ' found.</p></div>';
            return;
          }

          // Animate out existing content then rebuild
          if (container.children.length > 0 && typeof container.animate === 'function') {
            var outAnim = container.animate(
              [{ opacity: 1, transform: 'translateY(0)' }, { opacity: 0, transform: 'translateY(-8px)' }],
              { duration: 180, easing: 'ease-in', fill: 'forwards' }
            );
            outAnim.onfinish = function() { buildCards(container, filtered); };
          } else {
            buildCards(container, filtered);
          }
        }

        function buildCards(container, filtered) {
          container.innerHTML = '';
          filtered.forEach(function(item) {
            var preview = chooseAdminPreviewImage(item);
            var isRoom = (item.itemType || '').toLowerCase() === 'room';
            var badgeClass = isRoom ? 'bg-primary' : 'bg-info';
            var badgeText = isRoom ? 'ROOM' : 'FACILITY';
            var capacityLabel = isRoom ? 'guests' : 'people';
            var priceLabel = isRoom ? '/night' : '/day';
            var roomInfo = isRoom && item.roomNumber ? ('Room #' + item.roomNumber + ' &middot; ') : (isRoom ? '' : 'Facility &middot; ');

            var col = document.createElement('div');
            col.className = 'col-12 mb-3';
            col.innerHTML =
              '<div class="card shadow-sm" style="transition: box-shadow 0.2s, transform 0.2s; cursor: default;">' +
              '  <div class="card-body">' +
              '    <div class="row align-items-center">' +
              '      <div class="col-auto">' +
              '        <img src="' + preview + '" alt="' + item.name + '" ' +
              '             style="width:120px;height:90px;object-fit:cover;border-radius:8px;" ' +
              '             onerror="this.src=\'public/images/imageBg/barcie_logo.jpg\';">' +
              '      </div>' +
              '      <div class="col">' +
              '        <div class="d-flex justify-content-between align-items-start">' +
              '          <div>' +
              '            <h5 class="mb-1 fw-bold">' + item.name.toUpperCase() + '</h5>' +
              '            <span class="badge ' + badgeClass + ' mb-2">' + badgeText + '</span>' +
              '            <p class="text-muted mb-1">' + roomInfo + (item.capacity || 0) + ' ' + capacityLabel + '</p>' +
              '          </div>' +
              '          <div class="text-end">' +
              '            <h4 class="mb-0 text-primary">&#8369;' + (item.price || 0).toLocaleString() + '</h4>' +
              '            <small class="text-muted">' + priceLabel + '</small>' +
              '          </div>' +
              '        </div>' +
              '        <div class="mt-3">' +
              '          <button class="btn btn-outline-primary btn-sm view-cal-btn" data-room-id="' + item.id + '" data-room-name="' + item.name + (item.roomNumber ? ' #' + item.roomNumber : '') + '">' +
              '            <i class="fas fa-calendar-alt me-1"></i>View Calendar' +
              '          </button>' +
              '        </div>' +
              '      </div>' +
              '    </div>' +
              '  </div>' +
              '</div>';

            container.appendChild(col);
          });

          // Wire up View Calendar buttons
          container.querySelectorAll('.view-cal-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
              e.stopPropagation();
              var roomId = this.getAttribute('data-room-id');
              var roomName = this.getAttribute('data-room-name') || 'Room';
              showRoomCalendar(roomId, roomName);
            });
          });

          // Animate in
          if (typeof container.animate === 'function') {
            container.animate(
              [{ opacity: 0, transform: 'translateY(10px)' }, { opacity: 1, transform: 'translateY(0)' }],
              { duration: 280, easing: 'cubic-bezier(0.16, 1, 0.3, 1)', fill: 'both' }
            );
          }
        }

        // Filter button handlers
        function initAdminRoomFilters() {
          var btns = document.querySelectorAll('.admin-room-filter-btn');
          btns.forEach(function(btn) {
            btn.addEventListener('click', function() {
              btns.forEach(function(b) { b.classList.remove('active'); });
              this.classList.add('active');
              renderAdminRoomCards(this.getAttribute('data-filter'));
            });
          });
        }

        // Search handler
        function initAdminRoomSearch() {
          var searchInput = document.getElementById('room-search');
          if (searchInput) {
            searchInput.addEventListener('input', function() {
              renderAdminRoomCards(_adminRoomFilter);
            });
          }
        }

        // Populate booking details pane with comprehensive information
        function showBookingDetailsInPane(event) {
          console.log('showBookingDetailsInPane called with event:', event);
          
          const container = document.getElementById('roomBookingDetailsContent');
          const wrapper = document.getElementById('roomBookingDetails');
          
          if (!container) {
            console.error('roomBookingDetailsContent container not found!');
            return;
          }
          
          const p = event.extendedProps || {};
          const bookingId = (event.id || '').toString();
          const bookingNumericId = bookingId.replace('booking-', '').replace('pencil-', '');
          
          console.log('Building booking details for:', bookingId, 'Status:', p.status);
          
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
          
          html.push(`</div>`); // card-body
          html.push(`</div>`); // card

          container.innerHTML = html.join('\n');
          
          // Show the booking details wrapper
          if (wrapper) {
            wrapper.style.display = 'block';
            console.log('Booking details displayed successfully');
          } else {
            console.warn('roomBookingDetails wrapper not found');
          }
          
          // Scroll to the details
          setTimeout(() => {
            if (wrapper) {
              wrapper.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
          }, 100);
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
          var spinnerEl = document.getElementById('roomModalSpinner');
          if (spinnerEl) spinnerEl.style.display = 'flex';

          // Use fixed 90-day range
          const rangeDays = 90;
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
            headerToolbar: false,
            timeZone: 'Asia/Manila',
            events: events,
            height: calcModalCalendarHeight(),
            eventDisplay: 'block',
            nowIndicator: true,
            locale: 'en',
            firstDay: 1,
            fixedWeekCount: true,
            showNonCurrentDates: false,
            dateClick: function (info) {
              console.log('Date clicked:', info.dateStr);
              // Could allow booking creation here
            },
            eventClick: function (info) {
              console.log('Event clicked:', info.event.id, info.event.title);
              console.log('Event details:', info.event);
              console.log('Extended props:', info.event.extendedProps);
              
              // show details for booking events (skip background events)
              if (info.event && info.event.id && !info.event.id.toString().startsWith('reserved-bg-') && !info.event.id.toString().startsWith('free-range-')) {
                console.log('Calling showBookingDetailsInPane...');
                showBookingDetailsInPane(info.event);
              } else {
                console.log('Event is a background event, skipping details display');
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
          if (spinnerEl) spinnerEl.style.display = 'none';

          // Update the title display in modal header
          try {
            var titleDisplay = document.getElementById('roomCalendarMonthTitle');
            if (titleDisplay && modalCalendarInstance) {
              titleDisplay.textContent = modalCalendarInstance.view.title;
            }
          } catch(e) {}
        }

        // Responsive calendar height (like guest AvailabilityCalendar)
        function calcModalCalendarHeight() {
          var vh = Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0);
          return Math.max(240, Math.min(520, Math.floor(vh * 0.5)));
        }

        // Custom nav button handlers for the modal calendar (matching guest style)
        window.adminCalPrev = function() {
          if (modalCalendarInstance) {
            modalCalendarInstance.prev();
            var t = document.getElementById('roomCalendarMonthTitle');
            if (t) t.textContent = modalCalendarInstance.view.title;
          }
        };
        window.adminCalToday = function() {
          if (modalCalendarInstance) {
            modalCalendarInstance.today();
            var t = document.getElementById('roomCalendarMonthTitle');
            if (t) t.textContent = modalCalendarInstance.view.title;
          }
        };
        window.adminCalNext = function() {
          if (modalCalendarInstance) {
            modalCalendarInstance.next();
            var t = document.getElementById('roomCalendarMonthTitle');
            if (t) t.textContent = modalCalendarInstance.view.title;
          }
        };



        // Fetch latest room list from API and re-render cards
        async function fetchAndRefreshRoomList() {
          try {
            const res = await fetch('api/items.php', { method: 'GET', credentials: 'same-origin' });
            if (!res.ok) throw new Error('Failed to fetch items: ' + res.status);
            const data = await res.json();
            const items = (data && data.items) ? data.items : [];

            // Update global list with full data
            window.roomList = items.map(i => ({
              id: i.id,
              name: i.name,
              roomNumber: i.room_number || '',
              itemType: i.item_type || 'room',
              capacity: parseInt(i.capacity) || 0,
              price: parseInt(i.price) || 0,
              images: i.images || '',
              image: i.image || ''
            }));

            // Re-render cards
            renderAdminRoomCards(_adminRoomFilter);
          } catch (err) {
            console.warn('Could not refresh room list:', err);
          }
        }

        // Expose a global helper to refresh room list after adding a room
        window.refreshRoomList = fetchAndRefreshRoomList;



      </script>
