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
          $room_stmt = $conn->prepare("SELECT id, name, item_type, room_number, capacity, price, room_status FROM items WHERE item_type IN ('room', 'facility') ORDER BY item_type, name");
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
  border: 1px solid #dc3545;
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

async function checkRoomAvailability(roomId, dateInput, infoElement) {
  if (!roomId || !dateInput) return;
  
  // Fetch occupied dates if not cached
  if (!occupiedDatesCache[roomId]) {
    try {
      const response = await fetch(`api/room_availability.php?room_id=${roomId}`);
      const data = await response.json();
      if (data.success) {
        occupiedDatesCache[roomId] = data.occupied_dates || [];
      } else {
        occupiedDatesCache[roomId] = [];
      }
    } catch (error) {
      console.error('Error fetching room availability:', error);
      occupiedDatesCache[roomId] = [];
    }
  }
  
  const selectedDate = dateInput.value;
  if (!selectedDate) {
    dateInput.classList.remove('date-occupied', 'date-available');
    if (infoElement) infoElement.className = 'availability-info';
    return;
  }
  
  const dateOnly = selectedDate.split('T')[0];
  const isOccupied = occupiedDatesCache[roomId].includes(dateOnly);
  
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
    roomSelect.addEventListener('change', function() {
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
    checkinInput.addEventListener('change', function() {
      const roomId = roomSelect ? roomSelect.value : null;
      if (roomId) {
        checkRoomAvailability(roomId, this, document.getElementById('reservation_checkin_info'));
        validateDateRange(checkinInput, checkoutInput, roomId, document.getElementById('reservation_checkout_info'));
      }
    });
  }
  
  if (checkoutInput) {
    checkoutInput.addEventListener('change', function() {
      const roomId = roomSelect ? roomSelect.value : null;
      if (roomId) {
        checkRoomAvailability(roomId, this, document.getElementById('reservation_checkout_info'));
        validateDateRange(checkinInput, checkoutInput, roomId, document.getElementById('reservation_checkout_info'));
      }
    });
  }
});
</script>
