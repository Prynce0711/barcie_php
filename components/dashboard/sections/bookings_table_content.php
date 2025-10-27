<?php
// Bookings Table Content
$bookings = $conn->query("SELECT b.*, i.name as room_name, i.room_number,
                          IFNULL(b.discount_status, 'none') as discount_status
                          FROM bookings b 
                          LEFT JOIN items i ON b.room_id = i.id 
                          ORDER BY b.created_at DESC");
while ($booking = $bookings->fetch_assoc()):
  // Determine status badge color
  $status_color = [
    'pending' => 'warning',
    'approved' => 'success',
    'confirmed' => 'info',
    'checked_in' => 'primary',
    'checked_out' => 'secondary',
    'cancelled' => 'danger',
    'rejected' => 'danger'
  ];
  $badge_color = $status_color[$booking['status']] ?? 'secondary';
  
  // Extract guest info from details
  $guest_name = 'Guest';
  $guest_phone = '';
  $guest_email = '';
  if (preg_match('/Guest:\s*([^|]+)/', $booking['details'], $matches)) {
    $guest_name = trim($matches[1]);
  }
  if (preg_match('/(\d{10,11})/', $booking['details'], $matches)) {
    $guest_phone = $matches[1];
  }
  if (preg_match('/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/', $booking['details'], $matches)) {
    $guest_email = $matches[1];
  }
  
  // Room/Facility name
  $room_facility = $booking['room_name'] ? $booking['room_name'] : 'Unassigned';
  if ($booking['room_number']) {
    $room_facility .= ' #' . $booking['room_number'];
  }
  ?>
  <tr>
    <!-- Receipt # -->
    <td>
      <strong style="font-size: 0.7rem;">BARCIE-<?= date('Ymd', strtotime($booking['created_at'])) ?>-<?= str_pad($booking['id'], 4, '0', STR_PAD_LEFT) ?></strong>
    </td>
    
    <!-- Room/Facility -->
    <td>
      <div style="line-height: 1.3;">
        <strong style="font-size: 0.75rem;"><?= htmlspecialchars($room_facility ?? 'Unassigned') ?></strong>
      </div>
    </td>
    
    <!-- Type -->
    <td>
      <span class="badge bg-<?= $booking['type'] === 'reservation' ? 'primary' : 'warning' ?>" style="font-size: 0.65rem; padding: 0.3rem 0.5rem;">
        <?= $booking['type'] === 'reservation' ? 'Reservation' : 'Pencil Booking' ?>
      </span>
    </td>
    
    <!-- Guest Details -->
    <td>
      <div style="line-height: 1.3;">
        <strong style="font-size: 0.75rem;"><?= htmlspecialchars($guest_name ?? 'Guest') ?></strong><br>
        <?php if ($guest_phone): ?>
          <small style="font-size: 0.65rem;"><i class="fas fa-phone"></i> <?= htmlspecialchars($guest_phone ?? '') ?></small><br>
        <?php endif; ?>
        <?php if ($guest_email): ?>
          <small style="font-size: 0.65rem;" class="text-truncate d-inline-block" style="max-width: 150px;"><i class="fas fa-envelope"></i> <?= htmlspecialchars($guest_email ?? '') ?></small>
        <?php endif; ?>
      </div>
    </td>
    
    <!-- Schedule -->
    <td>
      <div style="line-height: 1.3; font-size: 0.7rem;">
        <strong>In:</strong> <?= date('M j, Y', strtotime($booking['checkin'])) ?><br>
        <small class="text-muted" style="font-size: 0.65rem;"><?= date('H:i', strtotime($booking['checkin'])) ?></small><br>
        <strong>Out:</strong> <?= date('M j, Y', strtotime($booking['checkout'])) ?><br>
        <small class="text-muted" style="font-size: 0.65rem;"><?= date('H:i', strtotime($booking['checkout'])) ?></small>
      </div>
    </td>
    
    <!-- Booking Status -->
    <td>
      <span class="badge bg-<?= $badge_color ?>" style="font-size: 0.65rem; padding: 0.35rem 0.6rem;">
        <?= ucfirst(str_replace('_', ' ', $booking['status'])) ?>
      </span>
    </td>
    
    <!-- Discount Status -->
    <td>
      <?php 
      $discount_badge_colors = [
        'pending' => 'warning',
        'approved' => 'success',
        'rejected' => 'danger',
        'none' => 'secondary'
      ];
      $discount_display = $booking['discount_status'] ? $booking['discount_status'] : 'none';
      $discount_color = $discount_badge_colors[$discount_display] ?? 'secondary';
      ?>
      <span class="badge bg-<?= $discount_color ?>" style="font-size: 0.65rem; padding: 0.35rem 0.6rem;">
        <?= ucfirst($discount_display) ?>
      </span>
    </td>
    
    <!-- Created -->
    <td>
      <div style="font-size: 0.7rem; line-height: 1.3;">
        <?= date('M j, Y', strtotime($booking['created_at'])) ?><br>
        <small class="text-muted" style="font-size: 0.65rem;"><?= date('H:i', strtotime($booking['created_at'])) ?></small>
      </div>
    </td>
    
    <!-- Actions -->
    <td>
      <div class="d-flex flex-column" style="gap: 0.25rem;">
        <!-- View Details Button (Always visible) -->
        <button class="btn btn-info btn-sm" onclick="viewBookingDetails(<?= $booking['id'] ?>)" style="font-size: 0.65rem; padding: 0.3rem 0.5rem;">
          <i class="fas fa-eye"></i> View
        </button>
        
        <?php if ($booking['status'] === 'pending'): ?>
          <button class="btn btn-success btn-sm" onclick="updateBookingStatus(<?= $booking['id'] ?>, 'approved')" style="font-size: 0.65rem; padding: 0.3rem 0.5rem;">
            <i class="fas fa-check"></i> Approve
          </button>
          <button class="btn btn-danger btn-sm" onclick="updateBookingStatus(<?= $booking['id'] ?>, 'rejected')" style="font-size: 0.65rem; padding: 0.3rem 0.5rem;">
            <i class="fas fa-times"></i> Reject
          </button>
        <?php elseif ($booking['status'] === 'approved'): ?>
          <button class="btn btn-primary btn-sm" onclick="updateBookingStatus(<?= $booking['id'] ?>, 'checked_in')" style="font-size: 0.65rem; padding: 0.3rem 0.5rem;">
            <i class="fas fa-sign-in-alt"></i> Check In
          </button>
          <button class="btn btn-secondary btn-sm" onclick="updateBookingStatus(<?= $booking['id'] ?>, 'cancelled')" style="font-size: 0.65rem; padding: 0.3rem 0.5rem;">
            Cancel
          </button>
        <?php elseif ($booking['status'] === 'checked_in'): ?>
          <button class="btn btn-info btn-sm" onclick="updateBookingStatus(<?= $booking['id'] ?>, 'checked_out')" style="font-size: 0.65rem; padding: 0.3rem 0.5rem;">
            <i class="fas fa-sign-out-alt"></i> Check Out
          </button>
          <button class="btn btn-secondary btn-sm" onclick="updateBookingStatus(<?= $booking['id'] ?>, 'cancelled')" style="font-size: 0.65rem; padding: 0.3rem 0.5rem;">
            Cancel
          </button>
        <?php endif; ?>
      </div>
    </td>
  </tr>
<?php endwhile; ?>
