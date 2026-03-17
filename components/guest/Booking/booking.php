<style>
  /* Booking form scoped styles */
  #booking>label {
    display: inline-block;
    margin: 10px 15px 20px 0;
    padding: 12px 20px;
    background: rgba(255, 255, 255, 0.9);
    border: 2px solid rgba(52, 152, 219, 0.4);
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 500;
    font-size: 1rem;
  }

  #booking>label:hover {
    background: rgba(52, 152, 219, 0.1);
    border-color: #3498db;
  }

  #booking>label input[type="radio"] {
    width: auto;
    margin: 0 8px 0 0;
    accent-color: #3498db;
  }

  #booking>label:has(input[type="radio"]:checked) {
    background: #3498db;
    color: white;
    border-color: #2980b9;
  }

  form {
    background: rgba(255, 255, 255, 0.95);
    padding: 25px;
    border-radius: 12px;
    border: 1px solid rgba(52, 152, 219, 0.3);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    margin: 20px 0;
    max-width: 100%;
    overflow: hidden;
  }

  form h3 {
    color: #2c3e50;
    margin-bottom: 25px;
    text-align: center;
    font-size: 1.4rem;
    font-weight: 600;
    padding-bottom: 15px;
    border-bottom: 2px solid rgba(52, 152, 219, 0.2);
  }

  .form-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-top: 20px;
  }

  .form-grid.two-column {
    grid-template-columns: repeat(2, 1fr);
  }

  .form-grid.single-column {
    grid-template-columns: 1fr;
  }

  form label {
    display: flex;
    flex-direction: column;
    font-weight: 600;
    color: #34495e;
    font-size: 1rem;
    margin-bottom: 15px;
  }

  form label .label-text {
    margin-bottom: 8px;
    font-size: 0.95rem;
  }

  form input,
  form select,
  form textarea {
    width: 100%;
    padding: 12px 15px;
    border-radius: 6px;
    border: 2px solid rgba(52, 152, 219, 0.3);
    font-size: 1rem;
    background: rgba(255, 255, 255, 0.95);
    transition: all 0.3s ease;
    font-family: inherit;
  }

  form input:focus,
  form select:focus,
  form textarea:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 8px rgba(52, 152, 219, 0.3);
    background: rgba(255, 255, 255, 1);
  }

  form input[readonly] {
    background: rgba(248, 249, 250, 0.9);
    border-color: rgba(108, 117, 125, 0.4);
    color: #6c757d;
    cursor: not-allowed;
  }

  .full-width {
    grid-column: 1 / -1;
  }

  .half-width {
    grid-column: span 1;
  }

  .two-thirds {
    grid-column: span 2;
  }

  .form-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin: 15px 0;
  }

  .form-row.two-column {
    grid-template-columns: repeat(2, 1fr);
  }

  .form-row.single {
    grid-template-columns: 1fr;
  }

  form button {
    width: 100%;
    background: linear-gradient(135deg, #3498db, #2980b9);
    color: #fff;
    font-weight: 600;
    cursor: pointer;
    border: none;
    margin-top: 25px;
    padding: 14px 20px;
    border-radius: 6px;
    font-size: 1.1rem;
    transition: all 0.3s ease;
    grid-column: 1 / -1;
  }

  form button:hover {
    background: linear-gradient(135deg, #2980b9, #1f618d);
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(52, 152, 219, 0.3);
  }

  form button:active {
    transform: translateY(0);
  }

  .compact-form {
    padding: 25px;
    margin-bottom: 30px;
  }

  .compact-form label {
    margin-bottom: 12px;
  }

  .compact-form .form-grid {
    gap: 15px;
  }

  #booking form {
    margin-bottom: 40px;
  }

  .booking-form-highlight {
    animation: tw-pulse 2s infinite;
  }

  @keyframes tw-pulse {
    0% {
      box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
    }

    70% {
      box-shadow: 0 0 0 10px rgba(40, 167, 69, 0);
    }

    100% {
      box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
    }
  }

  @media (max-width: 1200px) {

    .form-grid,
    .form-row {
      grid-template-columns: repeat(2, 1fr);
      gap: 18px;
    }
  }

  @media (max-width: 768px) {

    .form-grid,
    .form-row {
      grid-template-columns: 1fr;
      gap: 15px;
    }

    form {
      padding: 25px 20px;
    }

    #booking>label {
      display: block;
      margin: 8px 0;
      text-align: center;
    }

    .full-width,
    .half-width,
    .two-thirds {
      grid-column: 1;
    }
  }

  @media (max-width: 480px) {
    form {
      padding: 20px 15px;
    }

    .form-grid {
      gap: 12px;
    }
  }
</style>

<section id="booking"
  class="content-section bg-white/95 border-2 border-[rgba(52,152,219,0.2)] p-[30px] mb-[30px] rounded-xl shadow-[0_4px_15px_rgba(0,0,0,0.1)] relative z-[1]">
  <h2 class="mb-6 text-3xl text-[#2c3e50] font-semibold pb-2.5 border-b-2 border-[rgba(52,152,219,0.3)]">Booking &
    Reservation</h2>

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

  <label><input type="radio" name="bookingType" value="reservation" checked onchange="toggleBookingForm()"> Reservation
    (Confirmed)</label>
  <label><input type="radio" name="bookingType" value="pencil" onchange="toggleBookingForm()"> Pencil Booking (Draft
    Reservation)</label>

  <form id="reservationForm" method="POST" action="database/user_auth.php" class="compact-form">
    <h3>Reservation Form</h3>
    <input type="hidden" name="action" value="create_booking">
    <input type="hidden" name="booking_type" value="reservation">

    <?php include __DIR__ . '/discount_application.php'; ?>

    <!-- ID Upload Section -->
    <div class="card mb-3" id="reservationIdUploadCard">
      <div class="card-header bg-info text-white">
        <strong><i class="fas fa-id-card me-2"></i>Valid ID Upload</strong>
      </div>
      <div class="card-body">
        <div class="mb-3">
          <label for="reservation_id_type" class="form-label">ID Type <span class="text-danger">*</span></label>
          <select name="id_type" id="reservation_id_type" class="form-control" required>
            <option value="">-- Select ID Type --</option>
            <option value="national_id">National ID (PhilSys ID / ePhilID)</option>
            <option value="passport">Passport (Philippine or foreign, if applicable)</option>
            <option value="drivers_license">Driver's License (LTO)</option>
            <option value="umid">UMID Card (SSS / GSIS)</option>
            <option value="prc_id">PRC ID (Professional Regulation Commission)</option>
            <option value="voters_id">Voter's ID/Certification (COMELEC)</option>
            <option value="postal_id">Postal ID</option>
            <option value="philhealth_id">PhilHealth ID</option>
            <option value="tin_id">TIN ID (BIR)</option>
          </select>
          <small class="form-text text-muted">Select the type of valid ID you will upload.</small>
        </div>
        <div class="mb-2">
          <label for="reservation_id_upload" class="form-label">Upload Valid ID <span class="text-danger"
              id="reservation_id_required">*</span></label>
          <input type="file" name="id_upload" id="reservation_id_upload" class="form-control" accept="image/*" disabled>
          <input type="hidden" name="id_upload_cropped" id="reservation_id_upload_cropped">
          <input type="hidden" name="id_upload_validated" id="reservation_id_upload_validated" value="0">
          <small class="form-text text-muted">Required: Clear photo of your government-issued ID. Not needed if discount
            with ID is applied.</small>

          <!-- Validation status -->
          <div id="reservation_id_validation" style="margin-top:8px;display:none;"></div>

          <!-- Preview area -->
          <div id="reservation_id_preview" style="margin-top:10px;display:none;">
            <div id="reservation_id_thumb" style="margin-top:8px;max-width:160px;"></div>
          </div>
        </div>
        <div class="alert alert-info mb-0" style="font-size: 0.9rem;">
          <i class="fas fa-info-circle me-2"></i>If you apply for a discount above, the discount ID proof will be used
          and this upload becomes optional.
        </div>
      </div>
    </div>

    <!-- Inline alert area for form-level validation messages -->
    <div class="form-alert mb-2" id="reservation_form_alert" style="display:none;"></div>

    <!-- ID Required Notice -->
    <div class="alert alert-warning" id="reservation_id_notice" style="display:block;">
      <i class="fas fa-lock me-2"></i><strong>ID Upload Required:</strong> Please upload a valid ID above to unlock and
      fill out the booking form.
    </div>

    <div class="form-grid" id="reservation_form_fields">
      <label class="full-width">
        <span class="label-text">Reservation no:</span>
        <input type="text" name="receipt_no" id="receipt_no" readonly>
      </label>

      <label class="full-width">
        <span class="label-text">Select Room/Facility *</span>
        <select name="room_id" id="room_select" required>
          <option value="">Choose a room or facility...</option>
          <?php
          $room_stmt = $conn->prepare("SELECT id, name, item_type, room_number, capacity, price, room_status FROM items WHERE item_type IN ('room', 'facility') ORDER BY item_type, name");
          $room_stmt->execute();
          $room_result = $room_stmt->get_result();

          $current_type = '';
          while ($room = $room_result->fetch_assoc()) {
            if ($current_type !== $room['item_type']) {
              if ($current_type !== '')
                echo "</optgroup>";
              $current_type = $room['item_type'];
              $label = ($current_type === 'facility') ? 'Facilities' : ucfirst($current_type) . 's';
              echo "<optgroup label='$label'>";
            }

            $room_display = $room['name'];
            if ($room['room_number'])
              $room_display .= " (Room #" . $room['room_number'] . ")";
            $room_display .= " - " . $room['capacity'] . " persons";
            if ($room['price'] > 0)
              $room_display .= " - ₱" . number_format($room['price']) . "/night";

            $status = $room['room_status'] ?: 'available';
            $status_text = '';
            if ($status === 'clean')
              $status_text = ' (Ready)';
            elseif ($status === 'available')
              $status_text = ' (Available)';

            echo "<option value='" . $room['id'] . "'>" . htmlspecialchars($room_display . $status_text) . "</option>";
          }
          if ($current_type !== '')
            echo "</optgroup>";
          $room_stmt->close();
          ?>
        </select>
      </label>

      <label>
        <span class="label-text">Guest Name *</span>
        <input type="text" name="guest_name" id="reservation_guest_name" required minlength="2"
          placeholder="Enter your full name">
      </label>

      <label>
        <span class="label-text">Contact Number *</span>
        <input type="tel" name="contact_number" required pattern="^(\+63|0)[0-9]{10}$" placeholder="+63 or 09xxxxxxxxx"
          title="Enter PH mobile number starting with +63 or 09">
      </label>

      <label>
        <span class="label-text">Email Address *</span>
        <input type="email" name="email" required autocomplete="email"
          title="Accepted email domains: @gmail.com, @email.lcup.edu.ph, @yahoo.com, @icloud.com"
          placeholder="your.email@example.com">
      </label>

      <label>
        <span class="label-text">Age *</span>
        <input type="number" name="age" required min="18" max="120" placeholder="Enter your age">
      </label>

      <!-- Booking Time Notice -->
      <div class="full-width"
        style="margin: 0.5rem 0; padding: 0.75rem; background-color: #e7f3ff; border-left: 4px solid #2196F3; border-radius: 4px;">
        <div style="display: flex; align-items: center;">
          <i class="fas fa-clock" style="color: #2196F3; font-size: 1.2rem; margin-right: 10px;"></i>
          <div>
            <strong style="color: #1976D2;">Standard Booking Hours:</strong>
            <div style="font-size: 0.9rem; color: #424242; margin-top: 2px;">
              Check-in: <strong>2:00 PM</strong> | Check-out: <strong>12:00 Noon</strong>
            </div>
          </div>
        </div>
      </div>

      <label>
        <span class="label-text">Check-in Date *</span>
        <input type="date" name="checkin" required>
      </label>

      <label>
        <span class="label-text">Check-out Date *</span>
        <input type="date" name="checkout" required>
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

<style>
  /* Date availability styling */
  input[type="datetime-local"].date-occupied {
    border: 2px solid #dc3545 !important;
    background-color: #ffe5e5 !important;
  }

  input[type="datetime-local"].date-available {
    border: 2px solid #28a745 !important;
    background-color: #e8f5e9 !important;
  }

  .availability-info {
    font-size: 0.85rem;
    margin-top: 0.25rem;
    padding: 0.5rem;
    border-radius: 4px;
    display: none;
  }

  .availability-info.occupied {
    background-color: #ffe5e5;
    color: #dc3545;
    border: 1px solid #dc3 545;
    display: block;
  }

  .availability-info.available {
    background-color: #e8f5e9;
    color: #28a745;
    border: 1px solid #28a745;
    display: block;
  }
</style>

<script>
  // Room availability checker
  let occupiedDatesCache = {};

  // Make form lock functions globally accessible for form toggle functionality
  window.checkAndEnableFormFields = null;
  window.updateIdUploadRequirement = null;

  async function checkRoomAvailability(roomId, dateInput, infoElement) {
    if (!roomId || !dateInput) return;

    // If the reservation form was pre-filled from a pencil booking conversion,
    // the form contains a hidden `converted_from_pencil_id` field. When present
    // we should tell the API to exclude that pencil booking from the occupied
    // dates so the user can convert their own pencil into a confirmed reservation.
    const excludeEl = document.querySelector('input[name="converted_from_pencil_id"]');
    const excludePencilId = excludeEl && excludeEl.value ? String(excludeEl.value) : '';

    // Use a cache key that includes the exclude id so cached results are correct
    const cacheKey = excludePencilId ? `${roomId}:${excludePencilId}` : String(roomId);

    // Fetch occupied dates if not cached
    if (!occupiedDatesCache[cacheKey]) {
      try {
        let url = `api/RoomAvailability.php?room_id=${roomId}`;

        // If this date input belongs to the pencil booking form, include other pencil bookings
        // in availability checks (so pencil-to-pencil conflicts are detected). For regular
        // reservation checks, pencil bookings are excluded by default.
        if (dateInput && dateInput.closest && dateInput.closest('#pencilForm')) {
          url += `&include_pencil=1`;
        }

        if (excludePencilId) url += `&exclude_pencil_id=${encodeURIComponent(excludePencilId)}`;

        const response = await fetch(url);
        const data = await response.json();
        if (data.success) {
          occupiedDatesCache[cacheKey] = data.occupied_dates || [];
        } else {
          occupiedDatesCache[cacheKey] = [];
        }
      } catch (error) {
        console.error('Error fetching room availability:', error);
        occupiedDatesCache[cacheKey] = [];
      }
    }

    const selectedDate = dateInput.value;
    if (!selectedDate) {
      dateInput.classList.remove('date-occupied', 'date-available');
      if (infoElement) infoElement.className = 'availability-info';
      return;
    }

    const dateOnly = selectedDate.split('T')[0];
    const isOccupied = (occupiedDatesCache[cacheKey] || []).includes(dateOnly);

    if (isOccupied) {
      dateInput.classList.remove('date-available');
      dateInput.classList.add('date-occupied');
      if (infoElement) {
        infoElement.className = 'availability-info occupied';
        infoElement.innerHTML = '<i class="fas fa-exclamation-circle"></i> This date is already occupied for this room';
      }
    } else {
      dateInput.classList.remove('date-occupied');
      dateInput.classList.add('date-available');
      if (infoElement) {
        infoElement.className = 'availability-info available';
        infoElement.innerHTML = '<i class="fas fa-check-circle"></i> This date is available';
      }
    }
  }

  function validateDateRange(checkinInput, checkoutInput, roomId, infoElement) {
    const checkin = checkinInput.value;
    const checkout = checkoutInput.value;

    if (!checkin || !checkout || !roomId) return true;

    const checkinDate = new Date(checkin);
    const checkoutDate = new Date(checkout);

    if (checkoutDate <= checkinDate) {
      if (infoElement) {
        infoElement.className = 'availability-info occupied';
        infoElement.innerHTML = '<i class="fas fa-exclamation-circle"></i> Check-out must be after check-in';
      }
      return false;
    }

    // Check if any date in range is occupied (excluding checkout date - guests can check out and new guests check in same day)
    const occupiedDates = occupiedDatesCache[roomId] || [];
    let currentDate = new Date(checkinDate);

    while (currentDate < checkoutDate) {
      const dateStr = currentDate.toISOString().split('T')[0];
      if (occupiedDates.includes(dateStr)) {
        if (infoElement) {
          infoElement.className = 'availability-info occupied';
          infoElement.innerHTML = '<i class="fas fa-exclamation-circle"></i> Your selected date range includes occupied dates';
        }
        return false;
      }
      currentDate.setDate(currentDate.getDate() + 1);
    }

    if (infoElement) {
      infoElement.className = 'availability-info available';
      infoElement.innerHTML = '<i class="fas fa-check-circle"></i> Date range is available';
    }
    return true;
  }

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

    // Setup availability checking for reservation form
    const roomSelect = document.getElementById('room_select');
    const checkinInput = document.querySelector('#reservationForm input[name="checkin"]');
    const checkoutInput = document.querySelector('#reservationForm input[name="checkout"]');

    // Add info elements after date inputs
    if (checkinInput && !document.getElementById('reservation_checkin_info')) {
      const infoDiv = document.createElement('div');
      infoDiv.id = 'reservation_checkin_info';
      infoDiv.className = 'availability-info';
      checkinInput.parentNode.appendChild(infoDiv);
    }

    if (checkoutInput && !document.getElementById('reservation_checkout_info')) {
      const infoDiv = document.createElement('div');
      infoDiv.id = 'reservation_checkout_info';
      infoDiv.className = 'availability-info';
      checkoutInput.parentNode.appendChild(infoDiv);
    }

    if (roomSelect) {
      roomSelect.addEventListener('change', function () {
        const roomId = this.value;
        occupiedDatesCache = {}; // Clear cache when room changes
        if (roomId && checkinInput) {
          checkRoomAvailability(roomId, checkinInput, document.getElementById('reservation_checkin_info'));
        }
        if (roomId && checkoutInput) {
          checkRoomAvailability(roomId, checkoutInput, document.getElementById('reservation_checkout_info'));
        }
      });
    }

    if (checkinInput) {
      checkinInput.addEventListener('change', function () {
        const roomId = roomSelect ? roomSelect.value : null;
        if (roomId) {
          checkRoomAvailability(roomId, this, document.getElementById('reservation_checkin_info'));
          validateDateRange(checkinInput, checkoutInput, roomId, document.getElementById('reservation_checkout_info'));
        }
      });
    }

    if (checkoutInput) {
      checkoutInput.addEventListener('change', function () {
        const roomId = roomSelect ? roomSelect.value : null;
        if (roomId) {
          checkRoomAvailability(roomId, this, document.getElementById('reservation_checkout_info'));
          validateDateRange(checkinInput, checkoutInput, roomId, document.getElementById('reservation_checkout_info'));
        }
      });
    }

    // ID Upload Management - Update requirement based on discount selection
    function updateIdUploadRequirement(formType) {
      const isReservation = formType === 'reservation';
      const discountTypeSelect = document.getElementById('discount_type');
      const discountProof = document.getElementById('discount_proof');
      const idUpload = document.getElementById(isReservation ? 'reservation_id_upload' : 'pencil_id_upload');
      const idRequired = document.getElementById(isReservation ? 'reservation_id_required' : 'pencil_id_required');

      if (!discountTypeSelect || !idUpload || !idRequired) return;

      // If discount is selected, ID upload becomes optional (discount proof is used)
      const hasDiscount = discountTypeSelect.value && discountTypeSelect.value !== '';

      if (hasDiscount) {
        // Discount selected - ID upload is optional
        idUpload.removeAttribute('required');
        idRequired.style.display = 'none';
        idRequired.textContent = '';
      } else {
        // No discount - ID upload is required
        idUpload.setAttribute('required', 'required');
        idRequired.style.display = 'inline';
        idRequired.textContent = '*';
      }

      // Check if form fields should be enabled
      checkAndEnableFormFields(formType);
    }

    // Make globally accessible
    window.updateIdUploadRequirement = updateIdUploadRequirement;

    // Enable or disable form fields based on ID upload status
    function checkAndEnableFormFields(formType) {
      const isReservation = formType === 'reservation';
      const discountTypeSelect = document.getElementById('discount_type');
      const discountProof = document.getElementById('discount_proof');
      const idUpload = document.getElementById(isReservation ? 'reservation_id_upload' : 'pencil_id_upload');
      const idValidated = document.getElementById(isReservation ? 'reservation_id_upload_validated' : 'pencil_id_upload_validated');
      const formFields = document.getElementById(isReservation ? 'reservation_form_fields' : 'pencil_form_fields');
      const idNotice = document.getElementById(isReservation ? 'reservation_id_notice' : 'pencil_id_notice');
      const submitBtn = document.getElementById(isReservation ? 'reservationSubmitBtn' : 'pencilSubmitBtn');

      if (!formFields) return;

      // Check if either discount proof OR ID upload is provided AND validated
      const hasDiscount = discountTypeSelect && discountTypeSelect.value && discountTypeSelect.value !== '';
      const hasDiscountProofFile = hasDiscount && discountProof && discountProof.files && discountProof.files.length > 0;
      const isDiscountProofValid = hasDiscountProofFile && discountProof.dataset.validProof === '1';
      const hasIdUpload = idUpload && idUpload.files && idUpload.files.length > 0;
      const isIdValidated = idValidated && idValidated.value === '1';

      // Form is unlocked only if: (discount proof uploaded AND validated) OR (ID uploaded AND validated)
      const hasValidId = isDiscountProofValid || (hasIdUpload && isIdValidated);

      // Get all input, select, and button elements within the form fields
      const allInputs = formFields.querySelectorAll('input:not([type=\"hidden\"]):not([readonly]), select, textarea, button');

      if (hasValidId) {
        // Enable all fields
        allInputs.forEach(field => {
          field.removeAttribute('disabled');
          field.style.opacity = '1';
          field.style.cursor = 'auto';
          if (field.tagName === 'SELECT') {
            field.style.cursor = 'pointer';
          }
          if (field.tagName === 'BUTTON') {
            field.style.cursor = 'pointer';
          }
        });

        // Hide notice
        if (idNotice) idNotice.style.display = 'none';

        // Enable submit button (for reservation) or check terms for pencil
        if (submitBtn) {
          if (isReservation) {
            submitBtn.removeAttribute('disabled');
            submitBtn.style.opacity = '1';
            submitBtn.style.cursor = 'pointer';
          } else {
            // For pencil, check terms checkbox requirement
            const termsCheckbox = document.getElementById('pencil_terms_checkbox');
            if (termsCheckbox && termsCheckbox.checked) {
              submitBtn.removeAttribute('disabled');
              submitBtn.style.opacity = '1';
              submitBtn.style.cursor = 'pointer';
            } else {
              // Keep button disabled until terms are checked
              submitBtn.setAttribute('disabled', 'disabled');
              submitBtn.style.opacity = '0.6';
              submitBtn.style.cursor = 'not-allowed';
            }
          }
        }
      } else {
        // Disable all fields (including terms checkbox for pencil)
        allInputs.forEach(field => {
          field.setAttribute('disabled', 'disabled');
          field.style.opacity = '0.5';
          field.style.cursor = 'not-allowed';
        });

        // Show notice
        if (idNotice) idNotice.style.display = 'block';

        // Disable submit button
        if (submitBtn) {
          submitBtn.setAttribute('disabled', 'disabled');
          submitBtn.style.opacity = '0.6';
          submitBtn.style.cursor = 'not-allowed';
        }
      }
    }

    // Make globally accessible
    window.checkAndEnableFormFields = checkAndEnableFormFields;

    // Initialize form fields as disabled on page load
    function initializeFormLock() {
      checkAndEnableFormFields('reservation');
      checkAndEnableFormFields('pencil');
    }

    // Extract name from OCR text
    function extractNameFromOCR(text, ocrData) {
      const lines = text.split('\n').map(l => l.trim()).filter(l => l.length > 0);
      let extractedName = '';

      console.log('OCR Full Text for Extraction:', text);

      // Name extraction patterns
      const namePatterns = [
        /(?:full\s*)?name[:\s]*([a-z][a-z\s,.'-]+)/i,
        /surname[:\s]*([a-z][a-z\s,.'-]+)/i,
        /(?:given|first)\s*name[:\s]*([a-z][a-z\s,.'-]+)/i,
        /^([A-Z][a-z]+(?:\s+[A-Z][a-z]+){1,3})$/m
      ];

      for (const pattern of namePatterns) {
        const match = text.match(pattern);
        if (match && match[1]) {
          let name = match[1].trim();
          name = name.replace(/\s+/g, ' ').trim();
          name = name.replace(/[,.:;\d]+$/, '').trim();
          if (name.length >= 3 && name.length <= 50) {
            extractedName = name;
            console.log('Name extracted:', name);
            break;
          }
        }
      }

      return { name: extractedName };
    }

    // ID Validation Functions with OCR for Philippine IDs
    async function validateIDDocument(file, validationElement, validatedInput, idType) {
      if (!file) return false;

      // Get ID type name for display
      let idTypeName = 'government-issued ID';
      let expectedTexts = [];

      if (idType) {
        const idTypeMap = {
          'national_id': {
            name: 'National ID (PhilSys ID / ePhilID)',
            keywords: ['IDENTIFICATION', 'PHILSYS', 'PSA', 'PAMBANSANG', 'PAGKAKAKILANLAN', 'PCN']
          },
          'passport': {
            name: 'Passport',
            keywords: ['PASSPORT', 'FOREIGN AFFAIRS', 'DFA', 'REPUBLIC', 'PHILIPPINES']
          },
          'drivers_license': {
            name: 'Driver\'s License (LTO)',
            keywords: ['DRIVER', 'LICENSE', 'LTO', 'LAND', 'TRANSPORTATION', 'REPUBLIC']
          },
          'umid': {
            name: 'UMID Card (SSS / GSIS)',
            keywords: ['UMID', 'SSS', 'GSIS', 'UNIFIED', 'MULTI', 'PURPOSE', 'SECURITY']
          },
          'prc_id': {
            name: 'PRC ID (Professional Regulation Commission)',
            keywords: ['PRC', 'PROFESSIONAL', 'REGULATION', 'COMMISSION', 'LICENSE']
          },
          'voters_id': {
            name: 'Voter\'s ID/Certification (COMELEC)',
            keywords: ['VOTER', 'COMELEC', 'COMMISSION', 'ELECTIONS']
          },
          'postal_id': {
            name: 'Postal ID',
            keywords: ['POSTAL', 'PHILPOST', 'POST', 'CORPORATION']
          },
          'philhealth_id': {
            name: 'PhilHealth ID',
            keywords: ['PHILHEALTH', 'HEALTH', 'INSURANCE', 'CORPORATION', 'MEMBER']
          },
          'tin_id': {
            name: 'TIN ID (BIR)',
            keywords: ['TIN', 'TAXPAYER', 'IDENTIFICATION', 'NUMBER', 'BIR', 'INTERNAL', 'REVENUE', 'FINANCE']
          }
        };

        if (idTypeMap[idType]) {
          idTypeName = idTypeMap[idType].name;
          expectedTexts = idTypeMap[idType].keywords;
        }
      }

      // Show validating message
      if (validationElement) {
        validationElement.style.display = 'block';
        validationElement.innerHTML = `<span class="text-info"><i class="fas fa-spinner fa-spin me-2"></i>Reading and validating ${idTypeName}... This may take 10-20 seconds.</span>`;
      }

      // Only accept image files - perform OCR validation
      if (file.type.startsWith('image/')) {
        try {
          // First do basic image quality checks
          const imageCheck = await performBasicImageChecks(file);

          if (!imageCheck.isValid) {
            if (validationElement) {
              validationElement.innerHTML = `<span class="text-danger"><i class="fas fa-times-circle me-2"></i>${imageCheck.reason}</span>`;
            }
            if (validatedInput) validatedInput.value = '0';
            return false;
          }

          // Perform OCR text extraction
          const ocrResult = await performOCRValidation(file, expectedTexts, idTypeName);

          if (ocrResult.isValid) {
            if (validationElement) {
              validationElement.innerHTML = `<span class="text-success"><i class="fas fa-check-circle me-2"></i>${ocrResult.message}</span>`;
            }
            if (validatedInput) validatedInput.value = '1';
            return true;
          } else {
            if (validationElement) {
              validationElement.innerHTML = `<span class="text-danger"><i class="fas fa-times-circle me-2"></i>${ocrResult.message}</span>`;
            }
            if (validatedInput) validatedInput.value = '0';
            return false;
          }
        } catch (error) {
          console.error('ID validation error:', error);
          if (validationElement) {
            validationElement.innerHTML = `<span class="text-warning"><i class="fas fa-exclamation-triangle me-2"></i>Could not validate ${idTypeName}. Please ensure image is clear. Admin will verify. Fake IDs will result in cancellation.</span>`;
          }
          if (validatedInput) validatedInput.value = '1';
          return true; // Allow submission but admin will verify
        }
      }

      return false;
    }

    // Perform OCR validation on ID image
    async function performOCRValidation(file, expectedTexts, idTypeName) {
      return new Promise((resolve) => {
        const reader = new FileReader();
        reader.onload = async function (e) {
          try {
            // Use Tesseract.js to extract text
            const result = await Tesseract.recognize(
              e.target.result,
              'eng',
              {
                logger: m => {
                  if (m.status === 'recognizing text') {
                    console.log(`OCR Progress: ${Math.round(m.progress * 100)}%`);
                  }
                }
              }
            );

            const extractedText = result.data.text.toUpperCase();
            console.log('Extracted text:', extractedText);

            // Remove extra spaces and normalize text for better matching
            const normalizedText = extractedText.replace(/\s+/g, ' ').trim();

            // Check if extracted text is sufficient (more lenient)
            if (normalizedText.length < 10) {
              resolve({
                isValid: false,
                message: `Cannot read enough text from image (found ${normalizedText.length} characters). Please upload a clearer, well-lit photo of your ${idTypeName}. Try rotating or improving lighting.`
              });
              return;
            }

            // Check for expected keywords with VERY flexible matching
            let matchedKeywords = 0;
            let matchedTexts = [];

            for (const keyword of expectedTexts) {
              const keywordUpper = keyword.toUpperCase();
              // Check if the keyword appears anywhere in the text (case-insensitive)
              if (normalizedText.includes(keywordUpper)) {
                matchedKeywords++;
                matchedTexts.push(keyword);
              }
            }

            // Show what was extracted for debugging
            const textPreview = normalizedText.substring(0, 150) + (normalizedText.length > 150 ? '...' : '');
            console.log(`Matched ${matchedKeywords} keywords:`, matchedTexts);
            console.log('Text preview:', textPreview);

            
            if (matchedKeywords >= 2) {
              resolve({
                isValid: true,
                message: `✓ Valid ${idTypeName} detected and verified.`
              });
            } else if (matchedKeywords === 1) {
              // If only 1 keyword matched, still accept
              resolve({
                isValid: true,
                message: `✓ Valid ${idTypeName} detected and verified.`
              });
            } else {
              resolve({
                isValid: false,
                message: `✗ Cannot verify this as ${idTypeName}. Please upload a clearer, well-lit photo of your ID.`
              });
            }
          } catch (error) {
            console.error('OCR error:', error);
            resolve({
              isValid: false,
              message: `Could not read text from image. Error: ${error.message}. Please upload a clearer photo.`
            });
          }
        };
        reader.onerror = function () {
          resolve({
            isValid: false,
            message: 'Could not read file. Please try again.'
          });
        };
        reader.readAsDataURL(file);
      });
    }

   
    async function performBasicImageChecks(file) {
      return new Promise((resolve) => {
        const reader = new FileReader();
        reader.onload = function (e) {
          const img = new Image();
          img.onload = function () {
         
            if (img.width < 300 || img.height < 200) {
              resolve({
                isValid: false,
                reason: `Image resolution too low (${img.width}x${img.height}). Minimum 300x200 required. Take a clear photo.`
              });
              return;
            }

           
            const fileSizeKB = file.size / 1024;
            if (fileSizeKB < 20) {
              resolve({
                isValid: false,
                reason: `File too small (${fileSizeKB.toFixed(0)}KB). Upload original photo, not a thumbnail.`
              });
              return;
            }

            if (fileSizeKB > 20000) {
              resolve({
                isValid: false,
                reason: `File too large (${(fileSizeKB / 1024).toFixed(1)}MB). Maximum 20MB.`
              });
              return;
            }

          
            resolve({ isValid: true });
          };
          img.onerror = function () {
            resolve({ isValid: false, reason: 'Could not load image. File may be corrupted.' });
          };
          img.src = e.target.result;
        };
        reader.onerror = function () {
          resolve({ isValid: false, reason: 'Could not read file.' });
        };
        reader.readAsDataURL(file);
      });
    }

  
    const reservationIdTypeSelect = document.getElementById('reservation_id_type');
    const reservationIdUpload = document.getElementById('reservation_id_upload');

    if (reservationIdTypeSelect && reservationIdUpload) {
      reservationIdTypeSelect.addEventListener('change', function () {
        if (this.value) {
         
          reservationIdUpload.removeAttribute('disabled');
          reservationIdUpload.style.opacity = '1';
          reservationIdUpload.style.cursor = 'pointer';

         
          const idTypeText = this.options[this.selectedIndex].text;
          const helpText = reservationIdUpload.parentElement.querySelector('.form-text');
          if (helpText) {
            helpText.textContent = `Upload your ${idTypeText} (image or PDF format).`;
          }
        } else {
          
          reservationIdUpload.setAttribute('disabled', 'disabled');
          reservationIdUpload.style.opacity = '0.5';
          reservationIdUpload.style.cursor = 'not-allowed';
          reservationIdUpload.value = '';

        
          const validatedInput = document.getElementById('reservation_id_upload_validated');
          if (validatedInput) validatedInput.value = '0';

         
          const preview = document.getElementById('reservation_id_preview');
          if (preview) preview.style.display = 'none';

         
          const validationElement = document.getElementById('reservation_id_validation');
          if (validationElement) validationElement.style.display = 'none';

          checkAndEnableFormFields('reservation');
        }
      });
    }

 
    if (reservationIdUpload) {
      reservationIdUpload.addEventListener('change', async function (e) {
        const file = e.target.files[0];
        const preview = document.getElementById('reservation_id_preview');
        const thumb = document.getElementById('reservation_id_thumb');
        const validationElement = document.getElementById('reservation_id_validation');
        const validatedInput = document.getElementById('reservation_id_upload_validated');
        const idTypeSelect = document.getElementById('reservation_id_type');

        if (!file) {
          if (validatedInput) validatedInput.value = '0';
          checkAndEnableFormFields('reservation');
          return;
        }

       
        const idType = idTypeSelect ? idTypeSelect.value : null;

        if (!preview || !thumb) return;

       
        if (file.type.startsWith('image/')) {
          const reader = new FileReader();
          reader.onload = function (e) {
            thumb.innerHTML = '<img src="' + e.target.result + '" style="max-width: 100%; border-radius: 4px; border: 1px solid #ddd;">';
            preview.style.display = 'block';
          };
          reader.readAsDataURL(file);
        } else if (file.type === 'application/pdf') {
          thumb.innerHTML = '<div style="padding: 10px; background: #f0f0f0; border-radius: 4px;"><i class="fas fa-file-pdf" style="font-size: 32px; color: #dc3545;"></i><br><small>' + file.name + '</small></div>';
          preview.style.display = 'block';
        }

        const isValid = await validateIDDocument(file, validationElement, validatedInput, idType);

        if (isValid) {
          checkAndEnableFormFields('reservation');
        } else {
          const formFields = document.getElementById('reservation_form_fields');
          if (formFields) {
            const allInputs = formFields.querySelectorAll('input:not([type="hidden"]):not([readonly]), select, textarea, button');
            allInputs.forEach(field => {
              field.setAttribute('disabled', 'disabled');
              field.style.opacity = '0.5';
              field.style.cursor = 'not-allowed';
            });
          }
          this.value = '';
        }
      });
    }

    const pencilIdTypeSelect = document.getElementById('pencil_id_type');
    const pencilIdUpload = document.getElementById('pencil_id_upload');

    if (pencilIdTypeSelect && pencilIdUpload) {
      pencilIdTypeSelect.addEventListener('change', function () {
        if (this.value) {
        
          pencilIdUpload.removeAttribute('disabled');
          pencilIdUpload.style.opacity = '1';
          pencilIdUpload.style.cursor = 'pointer';

       
          const idTypeText = this.options[this.selectedIndex].text;
          const helpText = pencilIdUpload.parentElement.querySelector('.form-text');
          if (helpText) {
            helpText.textContent = `Upload your ${idTypeText} (image or PDF format).`;
          }
        } else {
       
          pencilIdUpload.setAttribute('disabled', 'disabled');
          pencilIdUpload.style.opacity = '0.5';
          pencilIdUpload.style.cursor = 'not-allowed';
          pencilIdUpload.value = '';

    
          const validatedInput = document.getElementById('pencil_id_upload_validated');
          if (validatedInput) validatedInput.value = '0';


          const preview = document.getElementById('pencil_id_preview');
          if (preview) preview.style.display = 'none';


          const validationElement = document.getElementById('pencil_id_validation');
          if (validationElement) validationElement.style.display = 'none';

          checkAndEnableFormFields('pencil');
        }
      });
    }


    if (pencilIdUpload) {
      pencilIdUpload.addEventListener('change', async function (e) {
        const file = e.target.files[0];
        const preview = document.getElementById('pencil_id_preview');
        const thumb = document.getElementById('pencil_id_thumb');
        const validationElement = document.getElementById('pencil_id_validation');
        const validatedInput = document.getElementById('pencil_id_upload_validated');
        const idTypeSelect = document.getElementById('pencil_id_type');

        if (!file) {
          if (validatedInput) validatedInput.value = '0';
          checkAndEnableFormFields('pencil');
          return;
        }


        const idType = idTypeSelect ? idTypeSelect.value : null;

        if (!preview || !thumb) return;


        if (file.type.startsWith('image/')) {
          const reader = new FileReader();
          reader.onload = function (e) {
            thumb.innerHTML = '<img src="' + e.target.result + '" style="max-width: 100%; border-radius: 4px; border: 1px solid #ddd;">';
            preview.style.display = 'block';
          };
          reader.readAsDataURL(file);
        } else if (file.type === 'application/pdf') {
          thumb.innerHTML = '<div style="padding: 10px; background: #f0f0f0; border-radius: 4px;"><i class="fas fa-file-pdf" style="font-size: 32px; color: #dc3545;"></i><br><small>' + file.name + '</small></div>';
          preview.style.display = 'block';
        }


        const isValid = await validateIDDocument(file, validationElement, validatedInput, idType);

        if (isValid) {
          checkAndEnableFormFields('pencil');
        } else {

          const formFields = document.getElementById('pencil_form_fields');
          if (formFields) {
            const allInputs = formFields.querySelectorAll('input:not([type="hidden"]):not([readonly]), select, textarea, button');
            allInputs.forEach(field => {
              field.setAttribute('disabled', 'disabled');
              field.style.opacity = '0.5';
              field.style.cursor = 'not-allowed';
            });
          }
         
          this.value = '';
        }
      });
    }

    // Watch for discount proof upload to enable form
    const discountProofUpload = document.getElementById('discount_proof');
    if (discountProofUpload) {
      discountProofUpload.addEventListener('change', function () {
        // Update both forms in case they're affected
        checkAndEnableFormFields('reservation');
        checkAndEnableFormFields('pencil');
      });
    }

    // Watch for discount type changes to update ID requirement
    const discountTypeSelect = document.getElementById('discount_type');
    if (discountTypeSelect) {
      discountTypeSelect.addEventListener('change', function () {
        // Update for currently visible form
        const reservationForm = document.getElementById('reservationForm');
        const pencilForm = document.getElementById('pencilForm');

        if (reservationForm && reservationForm.style.display !== 'none') {
          updateIdUploadRequirement('reservation');
        }
        if (pencilForm && pencilForm.style.display !== 'none') {
          updateIdUploadRequirement('pencil');
        }
      });

      // Initialize on page load
      updateIdUploadRequirement('reservation');
      updateIdUploadRequirement('pencil');
    }

    // Age validation for 18+ requirement
    function validateAge(ageInput, errorElement) {
      const age = parseInt(ageInput.value);
      if (age && age < 18) {
        ageInput.setCustomValidity('You must be at least 18 years old to make a booking.');
        if (errorElement) {
          errorElement.style.display = 'block';
        }
        return false;
      } else {
        ageInput.setCustomValidity('');
        if (errorElement) {
          errorElement.style.display = 'none';
        }
        return true;
      }
    }

    // Add age validation listeners for reservation form
    const reservationAgeInput = document.getElementById('reservation_age');
    const reservationAgeError = document.getElementById('reservation_age_error');
    if (reservationAgeInput) {
      reservationAgeInput.addEventListener('input', function () {
        validateAge(this, reservationAgeError);
      });
      reservationAgeInput.addEventListener('change', function () {
        validateAge(this, reservationAgeError);
      });
      reservationAgeInput.addEventListener('blur', function () {
        validateAge(this, reservationAgeError);
      });
    }

    // Add age validation listeners for pencil form
    const pencilAgeInput = document.getElementById('pencil_age');
    const pencilAgeError = document.getElementById('pencil_age_error');
    if (pencilAgeInput) {
      pencilAgeInput.addEventListener('input', function () {
        validateAge(this, pencilAgeError);
      });
      pencilAgeInput.addEventListener('change', function () {
        validateAge(this, pencilAgeError);
      });
      pencilAgeInput.addEventListener('blur', function () {
        validateAge(this, pencilAgeError);
      });
    }

    // Initialize form lock on page load
    initializeFormLock();
  });
</script>