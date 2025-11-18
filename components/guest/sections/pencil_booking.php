<!-- Pencil Booking (Draft Reservation Form) -->
<form id="pencilForm" method="POST" action="database/user_auth.php" class="compact-form" style="display:none;">
  <h3>Pencil Booking Form (Draft Reservation)</h3>
  <input type="hidden" name="action" value="create_booking">
  <input type="hidden" name="booking_type" value="pencil">

  <?php include __DIR__ . '/discount_application.php'; ?>

  <!-- Inline alert area for form-level validation messages -->
  <div class="form-alert mb-2" id="pencil_form_alert" style="display:none;"></div>

  <div class="form-grid">
    <label class="full-width">
      <span class="label-text">Pencil Booking no:</span>
      <input type="text" name="receipt_no" id="pencil_receipt_no" readonly>
    </label>

    <label class="full-width">
      <span class="label-text">Select Room/Facility *</span>
      <select name="room_id" id="pencil_room_select" required>
        <option value="">Choose a room or facility...</option>
        <?php
        $pencil_room_stmt = $conn->prepare("SELECT id, name, item_type, room_number, capacity, price, room_status FROM items WHERE item_type IN ('room', 'facility') AND room_status IN ('available', 'clean') ORDER BY item_type, name");
        $pencil_room_stmt->execute();
        $pencil_room_result = $pencil_room_stmt->get_result();

        $pencil_current_type = '';
        while ($pencil_room = $pencil_room_result->fetch_assoc()) {
          if ($pencil_current_type !== $pencil_room['item_type']) {
            if ($pencil_current_type !== '') echo "</optgroup>";
            $pencil_current_type = $pencil_room['item_type'];
            echo "<optgroup label='" . ucfirst($pencil_current_type) . "s'>";
          }

          $pencil_room_display = $pencil_room['name'];
          if ($pencil_room['room_number']) $pencil_room_display .= " (Room #" . $pencil_room['room_number'] . ")";
          $pencil_room_display .= " - " . $pencil_room['capacity'] . " persons";
          if ($pencil_room['price'] > 0) $pencil_room_display .= " - ₱" . number_format($pencil_room['price']) . "/night";

          $pencil_status = $pencil_room['room_status'] ?: 'available';
          $pencil_status_text = '';
          if ($pencil_status === 'clean') $pencil_status_text = ' (Ready)';
          elseif ($pencil_status === 'available') $pencil_status_text = ' (Available)';

          echo "<option value='" . $pencil_room['id'] . "'>" . htmlspecialchars($pencil_room_display . $pencil_status_text) . "</option>";
        }
        if ($pencil_current_type !== '') echo "</optgroup>";
        $pencil_room_stmt->close();
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

    <button type="button" id="pencilSubmitBtn">
      <i class="fas fa-edit me-2"></i>Confirm Draft Reservation
    </button>
  </div>
</form>
