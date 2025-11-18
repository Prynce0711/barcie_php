<section id="booking" class="content-section">
  <h2>Booking & Reservation</h2>

  <?php
  if (isset($_SESSION['booking_msg'])) {
    $msg = $_SESSION['booking_msg'];
    $alertClass = (strpos($msg, 'Error') !== false || strpos($msg, 'Sorry') !== false) ? 'alert-danger' : 'alert-success';
    echo "<div class='alert $alertClass alert-dismissible fade show' role='alert'>
            <i class='fas fa-" . ($alertClass === 'alert-success' ? 'check-circle' : 'exclamation-circle') . " me-2'></i>
            $msg
            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
          </div>";
    unset($_SESSION['booking_msg']);
  }
  ?>

  <label><input type="radio" name="bookingType" value="reservation" checked onchange="toggleBookingForm()"> Reservation (Confirmed)</label>
  <label><input type="radio" name="bookingType" value="pencil" onchange="toggleBookingForm()"> Pencil Booking (Draft Reservation)</label>

  <form id="reservationForm" method="POST" action="database/user_auth.php" class="compact-form">
    <h3>Reservation Form</h3>
    <input type="hidden" name="action" value="create_booking">
    <input type="hidden" name="booking_type" value="reservation">

    <?php include __DIR__ . '/discount_application.php'; ?>

    <!-- Inline alert area for form-level validation messages -->
    <div class="form-alert mb-2" id="reservation_form_alert" style="display:none;"></div>

    <div class="form-grid">
      <label class="full-width">
        <span class="label-text">Reservation no:</span>
        <input type="text" name="receipt_no" id="receipt_no" readonly>
      </label>

      <label class="full-width">
        <span class="label-text">Select Room/Facility *</span>
        <select name="room_id" id="room_select" required>
          <option value="">Choose a room or facility...</option>
          <?php
          $room_stmt = $conn->prepare("SELECT id, name, item_type, room_number, capacity, price, room_status FROM items WHERE item_type IN ('room', 'facility') AND room_status IN ('available', 'clean') ORDER BY item_type, name");
          $room_stmt->execute();
          $room_result = $room_stmt->get_result();

          $current_type = '';
          while ($room = $room_result->fetch_assoc()) {
            if ($current_type !== $room['item_type']) {
              if ($current_type !== '') echo "</optgroup>";
              $current_type = $room['item_type'];
              echo "<optgroup label='" . ucfirst($current_type) . "s'>";
            }

            $room_display = $room['name'];
            if ($room['room_number']) $room_display .= " (Room #" . $room['room_number'] . ")";
            $room_display .= " - " . $room['capacity'] . " persons";
            if ($room['price'] > 0) $room_display .= " - ₱" . number_format($room['price']) . "/night";

            $status = $room['room_status'] ?: 'available';
            $status_text = '';
            if ($status === 'clean') $status_text = ' (Ready)';
            elseif ($status === 'available') $status_text = ' (Available)';

            echo "<option value='" . $room['id'] . "'>" . htmlspecialchars($room_display . $status_text) . "</option>";
          }
          if ($current_type !== '') echo "</optgroup>";
          $room_stmt->close();
          ?>
        </select>
      </label>

      <label>
        <span class="label-text">Guest Name *</span>
        <input type="text" name="guest_name" required minlength="2" placeholder="Enter your full name">
      </label>

      <label>
        <span class="label-text">Contact Number *</span>
        <input type="tel" name="contact_number" required pattern="^(\+63|0)[0-9]{10}$" placeholder="+63 or 09xxxxxxxxx" title="Enter PH mobile number starting with +63 or 09">
      </label>

      <label>
        <span class="label-text">Email Address *</span>
        <input type="email" name="email" required autocomplete="email" title="Only Gmail Address are accepted (@gmail.com)" placeholder="your.email@gmail.com">
      </label>

      <label>
        <span class="label-text">Check-in Date & Time *</span>
        <input type="datetime-local" name="checkin" required>
      </label>

      <label>
        <span class="label-text">Check-out Date & Time *</span>
        <input type="datetime-local" name="checkout" required>
      </label>

      <label>
        <span class="label-text">Number of Occupants *</span>
        <input type="number" name="occupants" min="1" required>
      </label>

      <label>
        <span class="label-text">Company Affiliation</span>
        <input type="text" name="company" placeholder="Optional">
      </label>

      <label>
        <span class="label-text">Company Contact</span>
        <input type="text" name="company_contact" placeholder="Optional">
      </label>

      <!-- Payment moved into confirm modal -->

      <button type="button" id="reservationSubmitBtn">
        <i class="fas fa-calendar-check me-2"></i>Confirm Reservation
      </button>
    </div>
  </form>

  <?php include __DIR__ . '/pencil_booking.php'; ?>
</section>
<?php
// Include the confirm add-on modal (shows when reservation/pencil form is submitted)
include __DIR__ . '/confirm_addOn.php';
?>

<script>
// Toggle bank details when payment method changes
document.addEventListener('DOMContentLoaded', function () {
  function toggleDetails(selectId, detailsId) {
    const sel = document.getElementById(selectId);
    const details = document.getElementById(detailsId);
    if (!sel || !details) return;
    function update() {
      details.style.display = (sel.value === 'bank') ? 'block' : 'none';
    }
    sel.addEventListener('change', update);
    update();
  }

  toggleDetails('reservation_payment_method', 'reservation_bank_details');
  toggleDetails('pencil_payment_method', 'pencil_bank_details');
});
</script>
