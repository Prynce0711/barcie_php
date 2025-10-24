<?php
// Bookings Table Content
$bookings = $conn->query("SELECT * FROM bookings ORDER BY created_at DESC");
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
  ?>
  <tr>
    <td>
      <strong class="text-primary">#<?= $booking['id'] ?></strong>
    </td>
    <td>
      <div class="d-flex align-items-center">
        <div class="avatar-circle me-2 bg-primary text-white">
          <i class="fas fa-user"></i>
        </div>
        <div>
          <div class="fw-bold">Guest</div>
          <small class="text-muted">Guest User</small>
        </div>
      </div>
    </td>
    <td>
      <span class="badge bg-<?= $booking['type'] === 'room' ? 'primary' : 'success' ?> px-3 py-2">
        <i class="fas fa-<?= $booking['type'] === 'room' ? 'bed' : 'building' ?> me-1"></i>
        <?= ucfirst($booking['type']) ?>
      </span>
    </td>
    <td>
      <div class="booking-details">
        <div class="text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($booking['details']) ?>">
          <?= htmlspecialchars($booking['details']) ?>
        </div>
      </div>
    </td>
    <td>
      <div class="text-nowrap">
        <i class="fas fa-sign-in-alt text-success me-1"></i>
        <?= date('M j, Y', strtotime($booking['checkin'])) ?>
        <br>
        <small class="text-muted"><?= date('g:i A', strtotime($booking['checkin'])) ?></small>
      </div>
    </td>
    <td>
      <div class="text-nowrap">
        <i class="fas fa-sign-out-alt text-danger me-1"></i>
        <?= date('M j, Y', strtotime($booking['checkout'])) ?>
        <br>
        <small class="text-muted"><?= date('g:i A', strtotime($booking['checkout'])) ?></small>
      </div>
    </td>
    <td>
      <span class="badge bg-<?= $badge_color ?> px-3 py-2">
        <?= ucfirst(str_replace('_', ' ', $booking['status'])) ?>
      </span>
    </td>
    <td>
      <div class="text-nowrap">
        <i class="fas fa-calendar text-primary me-1"></i>
        <?= date('M j, Y', strtotime($booking['created_at'])) ?>
        <br>
        <small class="text-muted"><?= date('g:i A', strtotime($booking['created_at'])) ?></small>
      </div>
    </td>
    <td>
      <div class="btn-group btn-group-sm" role="group">
        <!-- Status Update Dropdown -->
        <div class="dropdown">
          <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
            <i class="fas fa-cog"></i>
          </button>
          <ul class="dropdown-menu">
            <?php if ($booking['status'] === 'pending'): ?>
              <li><a class="dropdown-item text-success" href="#" onclick="updateBookingStatus(<?= $booking['id'] ?>, 'approved')">
                  <i class="fas fa-check me-1"></i>Approve
                </a></li>
              <li><a class="dropdown-item text-danger" href="#" onclick="updateBookingStatus(<?= $booking['id'] ?>, 'rejected')">
                  <i class="fas fa-times me-1"></i>Reject
                </a></li>
            <?php elseif ($booking['status'] === 'approved'): ?>
              <li><a class="dropdown-item text-info" href="#" onclick="updateBookingStatus(<?= $booking['id'] ?>, 'confirmed')">
                  <i class="fas fa-handshake me-1"></i>Confirm
                </a></li>
              <li><a class="dropdown-item text-warning" href="#" onclick="updateBookingStatus(<?= $booking['id'] ?>, 'cancelled')">
                  <i class="fas fa-ban me-1"></i>Cancel
                </a></li>
            <?php elseif ($booking['status'] === 'confirmed'): ?>
              <li><a class="dropdown-item text-primary" href="#" onclick="updateBookingStatus(<?= $booking['id'] ?>, 'checked_in')">
                  <i class="fas fa-sign-in-alt me-1"></i>Check In
                </a></li>
              <li><a class="dropdown-item text-warning" href="#" onclick="updateBookingStatus(<?= $booking['id'] ?>, 'cancelled')">
                  <i class="fas fa-ban me-1"></i>Cancel
                </a></li>
            <?php elseif ($booking['status'] === 'checked_in'): ?>
              <li><a class="dropdown-item text-secondary" href="#" onclick="updateBookingStatus(<?= $booking['id'] ?>, 'checked_out')">
                  <i class="fas fa-sign-out-alt me-1"></i>Check Out
                </a></li>
            <?php endif; ?>
            <li>
              <hr class="dropdown-divider">
            </li>
            <li><a class="dropdown-item text-muted" href="#" onclick="viewBookingDetails(<?= $booking['id'] ?>)">
                <i class="fas fa-eye me-1"></i>View Details
              </a></li>
          </ul>
        </div>

        <!-- Quick Actions -->
        <button class="btn btn-outline-info" onclick="viewBookingDetails(<?= $booking['id'] ?>)" title="View Details">
          <i class="fas fa-eye"></i>
        </button>

        <?php if (in_array($booking['status'], ['pending', 'approved', 'confirmed'])): ?>
          <button class="btn btn-outline-danger" onclick="deleteBooking(<?= $booking['id'] ?>)" title="Delete Booking">
            <i class="fas fa-trash"></i>
          </button>
        <?php endif; ?>
      </div>
    </td>
  </tr>
<?php endwhile; ?>