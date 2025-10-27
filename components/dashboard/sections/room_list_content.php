<?php
// Room List Content for Calendar Section
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
          <?php 
          $imagePath = $item['image'] ?? '';
          $imageExists = false;
          
          if (!empty($imagePath)) {
            // Get project root from current file location (3 levels up from components/dashboard/sections)
            $projectRoot = realpath(__DIR__ . '/../../..');
            $imageFullPath = $projectRoot . '/' . ltrim($imagePath, '/');
            $imageExists = file_exists($imageFullPath);
            
            // Construct web path for src attribute
            if ($imageExists) {
              // Ensure path starts with / for web access
              if (!str_starts_with($imagePath, '/') && !str_starts_with($imagePath, 'http')) {
                $imagePath = '/' . $imagePath;
              }
            }
          }
          ?>
          <?php
          // Build a web-safe image path (prefer DB value, fall back to logo)
          $webImage = '/assets/images/imageBg/barcie_logo.jpg';
          if (!empty($imagePath)) {
            if (str_starts_with($imagePath, 'http') || str_starts_with($imagePath, '/')) {
              $webImage = $imagePath;
            } else {
              $webImage = '/' . ltrim($imagePath, '/');
            }
          }
          ?>
          <img src="<?= htmlspecialchars($webImage) ?>" class="img-fluid rounded" style="width: 80px; height: 60px; object-fit: cover;" alt="<?= htmlspecialchars($item['name']) ?>" onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'bg-light rounded d-flex align-items-center justify-content-center\' style=\'width: 80px; height: 60px;\'><i class=\'fas fa-<?= $type_icon ?> text-muted fa-2x\'></i></div>';">
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