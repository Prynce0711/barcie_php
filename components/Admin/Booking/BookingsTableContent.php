<?php
// Bookings Table Content
// Show reservation bookings that are already out of payment-verification queue.

require_once __DIR__ . '/../Shared/BadgeHelper.php';

// Set timezone to ensure consistent time display
date_default_timezone_set('Asia/Manila');

$bookings = $conn->query("SELECT b.*, i.name as room_name, i.room_number, i.item_type,
                          IFNULL(b.discount_status, 'none') as discount_status
                          FROM bookings b
                          LEFT JOIN items i ON b.room_id = i.id
                          WHERE (b.type = 'reservation' OR b.type IS NULL)
                          ORDER BY b.created_at DESC");

if ($bookings && $bookings->num_rows > 0):
while ($booking = $bookings->fetch_assoc()):
  $badge_color = admin_badge_booking_status_class($booking['status'] ?? '');
  
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
  
  // Extract date from payment_verified_at for filtering (format: YYYY-MM-DD)
  // Use payment_verified_at if available, otherwise fall back to created_at
  $filter_date = !empty($booking['payment_verified_at']) ? $booking['payment_verified_at'] : $booking['created_at'];
  $booking_date = date('Y-m-d', strtotime($filter_date));
  $row_item_type = admin_badge_item_type($booking['item_type'] ?? '');
  $type_badge = admin_badge_booking_type($booking['type'] ?? '');
  ?>
  <tr data-type="<?= htmlspecialchars($row_item_type) ?>" data-status="<?= htmlspecialchars($booking['status'] ?? '') ?>" data-date="<?= htmlspecialchars($booking_date) ?>" data-guest="<?= htmlspecialchars(($guest_name ?? '') . ' ' . ($guest_phone ?? '') . ' ' . ($guest_email ?? '') . ' ' . ($room_facility ?? '') . ' ' . ($booking['details'] ?? '')) ?>">
    <!-- Reservation No. -->
    <td data-label="Reservation No.">
      <strong style="font-size: 0.7rem;"><?= htmlspecialchars($booking['receipt_no'] ?: 'BARCIE-' . date('Ymd', strtotime($booking['created_at'] ?: 'now')) . '-' . str_pad($booking['id'], 4, '0', STR_PAD_LEFT)) ?></strong>
    </td>
    
    <!-- Room/Facility -->
    <td data-label="Room/Facility">
      <div style="line-height: 1.3;">
        <strong style="font-size: 0.75rem;"><?= htmlspecialchars($room_facility ?? 'Unassigned') ?></strong>
      </div>
    </td>
    
    <!-- Type -->
    <td data-label="Type">
      <span class="badge bg-<?= $type_badge['class'] ?>" style="font-size: 0.6rem; padding: 0.25rem 0.4rem; white-space: nowrap;">
        <?= $type_badge['label'] ?>
      </span>
    </td>
    
    <!-- Guest Details -->
    <td data-label="Guest">
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
    <td data-label="Schedule">
      <div style="line-height: 1.3; font-size: 0.7rem;">
        <strong>In:</strong> <?= date('M j, Y', strtotime($booking['checkin'])) ?><br>
        <small class="text-muted" style="font-size: 0.65rem;"><?= date('H:i', strtotime($booking['checkin'])) ?></small><br>
        <strong>Out:</strong> <?= date('M j, Y', strtotime($booking['checkout'])) ?><br>
        <small class="text-muted" style="font-size: 0.65rem;"><?= date('H:i', strtotime($booking['checkout'])) ?></small>
      </div>
    </td>
    
    <!-- Booking Status -->
    <td data-label="Status">
      <span class="badge bg-<?= $badge_color ?>" style="font-size: 0.65rem; padding: 0.35rem 0.6rem;">
        <?= ucfirst(str_replace('_', ' ', $booking['status'])) ?>
      </span>
    </td>
    
    <!-- Discount Status -->
    <td data-label="Discount">
      <?php
      $discount_display = $booking['discount_status'] ? $booking['discount_status'] : 'none';
      $discount_color = admin_badge_discount_status_class($discount_display);
      ?>
      <span class="badge bg-<?= $discount_color ?>" style="font-size: 0.65rem; padding: 0.35rem 0.6rem;">
        <?= ucfirst($discount_display) ?>
      </span>
    </td>
    
    <!-- Approved Date -->
    <td data-label="Approved">
      <?php if (!empty($booking['payment_verified_at'])): ?>
        <div style="font-size: 0.7rem; line-height: 1.3;">
          <?= date('M j, Y', strtotime($booking['payment_verified_at'])) ?><br>
          <small class="text-muted" style="font-size: 0.65rem;"><?= date('H:i', strtotime($booking['payment_verified_at'])) ?></small>
        </div>
      <?php elseif (!empty($booking['approved_at'])): ?>
        <div style="font-size: 0.7rem; line-height: 1.3;">
          <?= date('M j, Y', strtotime($booking['approved_at'])) ?><br>
          <small class="text-muted" style="font-size: 0.65rem;"><?= date('H:i', strtotime($booking['approved_at'])) ?></small>
        </div>
      <?php else: ?>
        <span class="text-muted" style="font-size: 0.65rem;">Pending</span>
      <?php endif; ?>
    </td>
    
    <!-- Actions -->
    <td data-label="Actions">
      <div class="d-flex flex-row flex-wrap" style="gap: 0.25rem;">
        <!-- View Details Button (Always visible) -->
        <button class="btn btn-info btn-sm view-booking-btn" onclick="viewBookingDetails(<?= $booking['id'] ?>)" style="font-size: 0.65rem; padding: 0.3rem 0.5rem;">
          <i class="fas fa-eye"></i> View
        </button>
        
        <?php if ($booking['status'] === 'pending'): ?>
          <button class="btn btn-success btn-sm booking-action-btn" onclick="updateBookingStatus(<?= $booking['id'] ?>, 'approved')" style="font-size: 0.65rem; padding: 0.3rem 0.5rem;">
            <i class="fas fa-check"></i> Approve
          </button>
          <button class="btn btn-danger btn-sm booking-action-btn" onclick="updateBookingStatus(<?= $booking['id'] ?>, 'rejected')" style="font-size: 0.65rem; padding: 0.3rem 0.5rem;">
            <i class="fas fa-times"></i> Reject
          </button>
        <?php elseif ($booking['status'] === 'approved' || $booking['status'] === 'checked_in'): ?>
          <span class="badge bg-info" style="font-size: 0.65rem; padding: 0.35rem 0.6rem;">
            <i class="fas fa-clock"></i> Auto Check-in/out
          </span>
          <button class="btn btn-secondary btn-sm booking-action-btn" onclick="updateBookingStatus(<?= $booking['id'] ?>, 'cancelled')" style="font-size: 0.65rem; padding: 0.3rem 0.5rem;">
            Cancel
          </button>
        <?php endif; ?>
      </div>
    </td>
  </tr>
<?php endwhile; ?>
<?php elseif ($bookings && $bookings->num_rows === 0): ?>
  <tr>
    <td colspan="9" class="text-center text-muted py-4">No bookings found.</td>
  </tr>
<?php else: ?>
  <tr>
    <td colspan="9" class="text-center text-danger py-4">Failed to load bookings data.</td>
  </tr>
<?php endif; ?>
