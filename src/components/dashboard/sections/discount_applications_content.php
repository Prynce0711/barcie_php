<?php
// Discount Applications Content
// Query bookings that have discount requests (where discount_status is not 'none')
$discount_query = "SELECT b.*, i.name as room_name, i.room_number
                   FROM bookings b
                   LEFT JOIN items i ON b.room_id = i.id 
                   WHERE b.discount_status IS NOT NULL AND b.discount_status != 'none'
                   ORDER BY b.created_at DESC";
$discount_result = $conn->query($discount_query);

if ($discount_result && $discount_result->num_rows > 0):
  while ($discount = $discount_result->fetch_assoc()):
    // Extract guest info from details
    $guest_name = 'Guest';
    if (preg_match('/Guest:\s*([^|]+)/', $discount['details'], $matches)) {
      $guest_name = trim($matches[1]);
    }
    
    // Room/Facility name
    $room_facility = $discount['room_name'] ? $discount['room_name'] : 'Unassigned';
    if ($discount['room_number']) {
      $room_facility .= ' #' . $discount['room_number'];
    }
    
    $status_class = $discount['discount_status'] === 'approved' ? 'success' : ($discount['discount_status'] === 'rejected' ? 'danger' : 'warning');
    ?>
    <tr>
      <td>
        <div class="d-flex align-items-center">
          <div class="avatar-circle me-2 bg-secondary text-white">
            <i class="fas fa-user"></i>
          </div>
          <div>
            <div class="fw-bold"><?= htmlspecialchars($guest_name ?? 'Guest') ?></div>
            <small class="text-muted"><?= htmlspecialchars($discount['receipt_no'] ?? '') ?></small>
          </div>
        </div>
      </td>
      <td>
        <span class="badge bg-info px-3 py-2">
          <?= htmlspecialchars($room_facility ?? 'Unassigned') ?>
        </span>
      </td>
      <td>
        <span class="badge bg-<?= $discount['type'] === 'reservation' ? 'primary' : 'warning' ?> px-2 py-1">
          <?= $discount['type'] === 'reservation' ? 'Reservation' : 'Pencil Booking' ?>
        </span>
      </td>
      <td>
        <div class="text-nowrap">
          <strong>In:</strong> <?= date('M j, Y', strtotime($discount['checkin'])) ?><br>
          <strong>Out:</strong> <?= date('M j, Y', strtotime($discount['checkout'])) ?>
        </div>
      </td>
      <td>
        <span class="badge bg-<?= $status_class ?> px-3 py-2">
          <?= ucfirst($discount['discount_status']) ?>
        </span>
      </td>
      <td>
        <div class="text-nowrap">
          <?= date('M j, Y', strtotime($discount['created_at'])) ?>
          <br>
          <small class="text-muted"><?= date('g:i A', strtotime($discount['created_at'])) ?></small>
        </div>
      </td>
      <td>
        <?php if ($discount['discount_status'] === 'pending'): ?>
          <div class="btn-group btn-group-sm">
            <button class="btn btn-success" onclick="updateDiscountStatus(<?= $discount['id'] ?>, 'approved')" title="Approve">
              <i class="fas fa-check"></i> Approve
            </button>
            <button class="btn btn-danger" onclick="updateDiscountStatus(<?= $discount['id'] ?>, 'rejected')" title="Reject">
              <i class="fas fa-times"></i> Reject
            </button>
          </div>
        <?php else: ?>
          <span class="text-muted">Processed</span>
        <?php endif; ?>
      </td>
    </tr>
  <?php endwhile; ?>
<?php else: ?>
  <tr>
    <td colspan="7" class="text-center text-muted py-4">
      <i class="fas fa-percent fa-3x mb-3 opacity-25"></i>
      <h6>No Discount Applications</h6>
      <p class="mb-0">Bookings with discount requests will appear here</p>
    </td>
  </tr>
<?php endif; ?>