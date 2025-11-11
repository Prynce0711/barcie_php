<?php
// Room List Content for Calendar Section
$items_query = "SELECT * FROM items 
                WHERE LOWER(TRIM(item_type)) IN ('room', 'facility') 
                ORDER BY 
                  CASE WHEN LOWER(TRIM(item_type))='room' THEN 0 WHEN LOWER(TRIM(item_type))='facility' THEN 1 ELSE 2 END,
                  room_number ASC,
                  name ASC";
$items_result = $conn->query($items_query);

// Debug: Log what we're rendering
error_log("Room List Content: Fetching rooms and facilities");
error_log("Query result rows: " . ($items_result ? $items_result->num_rows : 'null'));

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

    <div class="room-card p-3 border-bottom room-item" data-room-id="<?= htmlspecialchars($item_id) ?>" data-room-name="<?= htmlspecialchars($item_name . ($room_number !== 'N/A' ? ' #' . $room_number : '')) ?>"
      data-room-number="<?= strtolower($room_number) ?>" data-item-type="<?= $item_type ?>" style="cursor: pointer; transition: background-color 0.2s;" onmouseenter="this.style.backgroundColor='#f8f9fa'" onmouseleave="this.style.backgroundColor='transparent'">
      <div class="row align-items-center">
        <div class="col-md-2">
          <?php
          // Build images array and pick first image (support 'images' JSON column, legacy 'image', comma lists)
          $images_list = [];
          if (!empty($item['images'])) {
            $decoded = json_decode($item['images'], true);
            if (is_array($decoded) && !empty($decoded)) {
              $images_list = $decoded;
            }
          }

          // Fallback to 'image' column which may be JSON, comma list or single path
          if (empty($images_list) && !empty($item['image'])) {
            $raw = trim($item['image']);
            if (str_starts_with($raw, '[')) {
              $decoded = json_decode($raw, true);
              if (is_array($decoded) && !empty($decoded)) $images_list = $decoded;
            }
            if (empty($images_list) && strpos($raw, ',') !== false) {
              $parts = array_map('trim', explode(',', $raw));
              foreach ($parts as $p) if ($p !== '') $images_list[] = $p;
            }
            if (empty($images_list) && $raw !== '') $images_list[] = $raw;
          }

          $images_count = count($images_list);
          $firstImage = $images_count > 0 ? $images_list[0] : '';

          // Normalize web path and check file existence
          $projectRoot = realpath(__DIR__ . '/../../..');
          $webImage = '/assets/images/imageBg/barcie_logo.jpg';
          if (!empty($firstImage)) {
            if (str_starts_with($firstImage, 'http')) {
              $webImage = $firstImage;
            } else {
              $candidateFs = $projectRoot . '/' . ltrim($firstImage, '/');
              if (file_exists($candidateFs)) {
                $webImage = '/' . ltrim($firstImage, '/');
              } else {
                $altPaths = [
                  $projectRoot . '/uploads/' . ltrim($firstImage, '/'),
                  $projectRoot . '/assets/images/' . ltrim($firstImage, '/'),
                  $projectRoot . '/' . ltrim($firstImage, '/'),
                ];
                foreach ($altPaths as $ap) {
                  if (file_exists($ap)) {
                    $webImage = '/' . trim(str_replace($projectRoot, '', $ap), '/');
                    break;
                  }
                }
                if ($webImage === '/assets/images/imageBg/barcie_logo.jpg' && !str_contains($firstImage, ' ')) {
                  $webImage = '/' . ltrim($firstImage, '/');
                }
              }
            }
          }
          ?>
          <div class="position-relative thumb">
            <img src="<?= htmlspecialchars($webImage) ?>" class="img-fluid rounded" alt="<?= htmlspecialchars($item['name'] . (isset($room_number) && $room_number !== 'N/A' ? ' #' . $room_number : '')) ?>" title="<?= htmlspecialchars($item['name']) ?>" onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'bg-light rounded d-flex align-items-center justify-content-center\' style=\'width: 100px; height: 75px;\'><i class=\'fas fa-<?= $type_icon ?> text-muted fa-2x\'></i></div>';"></img>
            <?php if ($images_count > 1): ?>
              <span class="img-count-badge position-absolute top-0 start-0 m-1"><?= 1 ?>/<?= $images_count ?></span>
            <?php endif; ?>
          </div>
        </div>
        <div class="col-md-3">
          <h6 class="mb-1">
            <?= htmlspecialchars($item['name']) ?>
            <small class="badge bg-primary ms-1"><?= $type_label ?></small>
          </h6>
          <small class="text-muted">
            <?php if ($item_type == 'room'): ?>
              Room #<?= htmlspecialchars($room_number ?? '') ?> • <?= $item['capacity'] ?> <?= $capacity_label ?>
            <?php else: ?>
              Facility • <?= $item['capacity'] ?> <?= $capacity_label ?>
            <?php endif; ?>
          </small>
          <div class="mt-1">
            <small class="text-success">₱<?= number_format($item['price']) ?><?= $price_label ?></small>
          </div>
        </div>
        <div class="col-md-2">
          <?php if ($status !== 'no-reservation'): ?>
            <span class="badge bg-<?= $status_class ?> px-3 py-2">
              <i class="fas fa-<?= $status_icon ?> me-1"></i><?= $status_text ?>
            </span>
          <?php endif; ?>
        </div>
        <div class="col-md-5 d-flex flex-column align-items-end">
          <div class="booking-info w-100">
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
          <?php endif; ?>
          
          </div>

          <!-- View Calendar Button (aligned right) -->
          <div class="view-actions mt-2">
            <button type="button" class="btn btn-sm btn-outline-primary view-calendar-btn" 
                    onclick="event.stopPropagation(); showRoomCalendar(<?= $item_id ?>, '<?= htmlspecialchars(addslashes($item_name . ($room_number !== 'N/A' ? ' #' . $room_number : ''))) ?>')">
              <i class="fas fa-calendar-alt me-1"></i>View Calendar
            </button>
          </div>
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