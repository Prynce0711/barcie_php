<!-- Calendar & Rooms Section -->
      <section id="calendar-section" class="content-section">
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
                    <?php
                    // Fetch all rooms AND facilities with their current booking status
                    $items_query = "SELECT * FROM items WHERE item_type IN ('room', 'facility') ORDER BY item_type DESC, room_number ASC, name ASC";
                    $items_result = $conn->query($items_query);

                    if ($items_result && $items_result->num_rows > 0) {
                      while ($item = $items_result->fetch_assoc()) {
                        // Get current reservation for this item
                        $today = date('Y-m-d');
                        $item_id = $item['id'];
                        $item_name = $item['name'];
                        $item_type = $item['item_type'];
                        $room_number = $item['room_number'] ?: 'N/A';

                        // Check for active bookings (today or ongoing)
                        $booking_query = "SELECT b.* 
                                            FROM bookings b 
                                            WHERE b.details LIKE '%$item_name%' 
                                            AND b.status IN ('approved', 'confirmed', 'checked_in') 
                                            AND DATE(b.checkin) <= '$today' 
                                            AND DATE(b.checkout) >= '$today'
                                            ORDER BY b.checkin ASC LIMIT 1";
                        $booking_result = $conn->query($booking_query);
                        $current_booking = $booking_result ? $booking_result->fetch_assoc() : null;

                        // Get next upcoming booking
                        $next_booking_query = "SELECT b.* 
                                                 FROM bookings b 
                                                 WHERE b.details LIKE '%$item_name%' 
                                                 AND b.status IN ('approved', 'confirmed', 'pending') 
                                                 AND DATE(b.checkin) > '$today'
                                                 ORDER BY b.checkin ASC LIMIT 1";
                        $next_booking_result = $conn->query($next_booking_query);
                        $next_booking = $next_booking_result ? $next_booking_result->fetch_assoc() : null;

                        // Determine status
                        $status = 'available';
                        $status_class = 'success';
                        $status_text = 'Available';
                        $status_icon = 'check-circle';

                        if ($current_booking) {
                          if ($current_booking['status'] == 'checked_in') {
                            $status = 'occupied';
                            $status_class = 'info';
                            $status_text = $item_type == 'room' ? 'Occupied' : 'In Use';
                            $status_icon = $item_type == 'room' ? 'user' : 'cog';
                          } else {
                            $status = 'reserved';
                            $status_class = 'warning';
                            $status_text = 'Reserved';
                            $status_icon = 'calendar-check';
                          }
                        } elseif (!$next_booking) {
                          $status = 'no-reservation';
                          $status_class = 'secondary';
                          $status_text = 'No Reservations';
                          $status_icon = 'calendar-times';
                        }

                        // Different icons for different types
                        $type_icon = $item_type == 'room' ? 'door-open' : 'building';
                        $type_label = ucfirst($item_type);
                        $capacity_label = $item_type == 'room' ? 'guests' : 'people';
                        $price_label = $item_type == 'room' ? '/night' : '/day';
                        ?>

                        <div class="room-card p-3 border-bottom room-item" data-room-name="<?= strtolower($item_name) ?>"
                          data-room-number="<?= strtolower($room_number) ?>" data-item-type="<?= $item_type ?>">
                          <div class="row align-items-center">
                            <div class="col-md-2">
                              <?php if ($item['image'] && file_exists($item['image'])): ?>
                                <img src="<?= htmlspecialchars($item['image']) ?>" class="img-fluid rounded" style="width: 80px; height: 60px; object-fit: cover;" alt="<?= htmlspecialchars($item['name']) ?>">
                              <?php else: ?>
                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 80px; height: 60px;">
                                  <i class="fas fa-<?= $type_icon ?> text-muted fa-2x"></i>
                                </div>
                              <?php endif; ?>
                            </div>
                            <div class="col-md-3">
                              <h6 class="mb-1">
                                <?= htmlspecialchars($item['name']) ?>
                                <small class="badge bg-primary ms-1"><?= $type_label ?></small>
                              </h6>
                              <small class="text-muted">
                                <?php if ($item_type == 'room'): ?>
                                  Room #<?= htmlspecialchars($room_number) ?> • <?= $item['capacity'] ?> <?= $capacity_label ?>
                                <?php else: ?>
                                  Facility • <?= $item['capacity'] ?> <?= $capacity_label ?>
                                <?php endif; ?>
                              </small>
                              <div class="mt-1">
                                <small class="text-success">₱<?= number_format($item['price']) ?><?= $price_label ?></small>
                              </div>
                            </div>
                            <div class="col-md-2">
                              <span class="badge bg-<?= $status_class ?> px-3 py-2">
                                <i class="fas fa-<?= $status_icon ?> me-1"></i><?= $status_text ?>
                              </span>
                            </div>
                            <div class="col-md-5">
                              <?php if ($current_booking): ?>
                                <div class="current-booking mb-2">
                                  <strong class="text-<?= $status_class ?>">Current <?= $item_type == 'room' ? 'Guest' : 'User' ?>:</strong>
                                  <div class="small">
                                    Guest
                                    <span class="text-muted">
                                      • <?= date('M j', strtotime($current_booking['checkin'])) ?> - <?= date('M j', strtotime($current_booking['checkout'])) ?>
                                    </span>
                                  </div>
                                </div>
                              <?php endif; ?>

                              <?php if ($next_booking): ?>
                                <div class="next-booking">
                                  <strong class="text-primary">Next Reservation:</strong>
                                  <div class="small">
                                    Guest
                                    <span class="text-muted">
                                      • <?= date('M j', strtotime($next_booking['checkin'])) ?> - <?= date('M j', strtotime($next_booking['checkout'])) ?>
                                    </span>
                                  </div>
                                </div>
                              <?php elseif (!$current_booking): ?>
                                <div class="text-muted small">
                                  <i class="fas fa-calendar-times me-1"></i>No upcoming reservations
                                </div>
                              <?php endif; ?>
                            </div>
                          </div>
                        </div>

                        <?php
                      }
                    } else {
                      echo '<div class="text-center text-muted p-4">
                                <i class="fas fa-building fa-3x mb-3 opacity-50"></i>
                                <p>No rooms or facilities found</p>
                                <small>Add rooms and facilities in the Rooms & Facilities section</small>
                              </div>';
                    }
                    ?>
                  </div>
                </div>

              </div>
            </div>
          </div>
        </div>
      </section>

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
