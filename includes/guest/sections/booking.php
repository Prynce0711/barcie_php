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

  <label><input type="radio" name="bookingType" value="reservation" checked onchange="toggleBookingForm()"> Reservation</label>
  <label><input type="radio" name="bookingType" value="pencil" onchange="toggleBookingForm()"> Pencil Booking (Function Hall)</label>

  <form id="reservationForm" method="POST" action="database/user_auth.php" class="compact-form">
    <h3>Reservation Form</h3>
    <input type="hidden" name="action" value="create_booking">
    <input type="hidden" name="booking_type" value="reservation">

    <div class="card mb-3">
      <div class="card-header bg-warning text-dark">
        <strong><i class="fas fa-percent me-2"></i>Apply for Discount</strong>
      </div>
      <div class="card-body">
        <div class="mb-3">
          <label for="discount_type" class="form-label">Discount Type</label>
          <select name="discount_type" id="discount_type" class="form-select">
            <option value="">No Discount</option>
            <option value="pwd_senior">PWD / Senior Citizen (20%)</option>
            <option value="lcuppersonnel">LCUP Personnel (10%)</option>
            <option value="lcupstudent">LCUP Student/Alumni (7%)</option>
          </select>
        </div>
        <div class="mb-3" id="discount_proof_section" style="display:none;">
          <label for="discount_proof" class="form-label">Upload Valid ID/Proof <span class="text-danger">*</span></label>
          <input type="file" name="discount_proof" id="discount_proof" class="form-control" accept="image/*,application/pdf">
          <small class="form-text text-muted">Accepted: ID, certificate, or other proof (image or PDF)</small>
        </div>
        <div class="mb-3" id="discount_details_section" style="display:none;">
          <label for="discount_details" class="form-label">Discount Details</label>
          <input type="text" name="discount_details" id="discount_details" class="form-control" placeholder="ID number, personnel/student number, etc.">
        </div>
        <div class="alert alert-info mb-0" id="discount_info_text" style="display:none;"></div>
      </div>
    </div>

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
        <input type="text" name="guest_name" required>
      </label>

      <label>
        <span class="label-text">Contact Number *</span>
        <input type="text" name="contact_number" required>
      </label>

      <label>
        <span class="label-text">Email Address *</span>
        <input type="email" name="email" title="Only Gmail Address are accepted (@gmail.com)">
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

      <button type="submit" id="reservationSubmitBtn">
        <i class="fas fa-calendar-check me-2"></i>Confirm Reservation
      </button>
    </div>
  </form>

  <form id="pencilForm" method="POST" action="database/user_auth.php" class="compact-form" style="display:none;">
    <h3>Pencil Booking Form (Function Hall)</h3>
    <input type="hidden" name="action" value="create_booking">
    <input type="hidden" name="booking_type" value="pencil">

    <div class="form-grid">
      <label class="full-width">
        <span class="label-text">Date of Pencil Booking:</span>
        <input type="date" name="pencil_date" value="<?php echo date('Y-m-d'); ?>" readonly>
      </label>

      <label>
        <span class="label-text">Event Type *</span>
        <input type="text" name="event_type" required>
      </label>

      <label>
        <span class="label-text">Function Hall/Facility *</span>
        <select name="room_id" required>
          <option value="">Choose a hall or facility...</option>
          <?php
          $facility_stmt = $conn->prepare("SELECT id, name, room_number, capacity, price, room_status FROM items WHERE item_type = 'facility' AND room_status IN ('available', 'clean') ORDER BY name");
          $facility_stmt->execute();
          $facility_result = $facility_stmt->get_result();
          while ($facility = $facility_result->fetch_assoc()) {
            $facility_display = $facility['name'];
            if ($facility['room_number']) $facility_display .= " (Hall #" . $facility['room_number'] . ")";
            $facility_display .= " - " . $facility['capacity'] . " persons";
            if ($facility['price'] > 0) $facility_display .= " - ₱" . number_format($facility['price']) . "/event";

            $status = $facility['room_status'] ?: 'available';
            $status_text = '';
            if ($status === 'clean') $status_text = ' (Ready)';
            elseif ($status === 'available') $status_text = ' (Available)';

            echo "<option value='" . $facility['id'] . "'>" . htmlspecialchars($facility_display . $status_text) . "</option>";
          }
          $facility_stmt->close();
          ?>
        </select>
      </label>

      <label>
        <span class="label-text">Number of Pax *</span>
        <input type="number" name="pax" min="1" required>
      </label>

      <label>
        <span class="label-text">Time From *</span>
        <input type="time" name="time_from" required>
      </label>

      <label>
        <span class="label-text">Time To *</span>
        <input type="time" name="time_to" required>
      </label>

      <label>
        <span class="label-text">Food Provider/Caterer *</span>
        <input type="text" name="caterer" required>
      </label>

      <label>
        <span class="label-text">Contact Person *</span>
        <input type="text" name="contact_person" required>
      </label>

      <label>
        <span class="label-text">Contact Number *</span>
        <input type="text" name="contact_number" required>
      </label>

      <label>
        <span class="label-text">Company Affiliation</span>
        <input type="text" name="company" placeholder="Optional">
      </label>

      <label>
        <span class="label-text">Company Number</span>
        <input type="text" name="company_number" placeholder="Optional">
      </label>

      <button type="submit" id="pencilSubmitBtn" onclick="return pencilReminder()">
        <i class="fas fa-edit me-2"></i>Submit Pencil Booking
      </button>
    </div>
  </form>
</section>
