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

    <!-- ID Upload Section -->
    <div class="card mb-3" id="reservationIdUploadCard">
      <div class="card-header bg-info text-white">
        <strong><i class="fas fa-id-card me-2"></i>Valid ID Upload</strong>
      </div>
      <div class="card-body">
        <div class="mb-2">
          <label for="reservation_id_upload" class="form-label">Upload Valid ID <span class="text-danger" id="reservation_id_required">*</span></label>
          <input type="file" name="id_upload" id="reservation_id_upload" class="form-control" accept="image/*,application/pdf">
          <input type="hidden" name="id_upload_cropped" id="reservation_id_upload_cropped">
          <input type="hidden" name="id_upload_validated" id="reservation_id_upload_validated" value="0">
          <small class="form-text text-muted">Required: Government-issued ID (image or PDF). Not needed if discount with ID is applied.</small>
          
          <!-- Validation status -->
          <div id="reservation_id_validation" style="margin-top:8px;display:none;"></div>
          
          <!-- Preview area -->
          <div id="reservation_id_preview" style="margin-top:10px;display:none;">
            <div id="reservation_id_thumb" style="margin-top:8px;max-width:160px;"></div>
          </div>
        </div>
        <div class="alert alert-info mb-0" style="font-size: 0.9rem;">
          <i class="fas fa-info-circle me-2"></i>If you apply for a discount above, the discount ID proof will be used and this upload becomes optional.
        </div>
      </div>
    </div>

    <!-- Inline alert area for form-level validation messages -->
    <div class="form-alert mb-2" id="reservation_form_alert" style="display:none;"></div>

    <!-- ID Required Notice -->
    <div class="alert alert-warning" id="reservation_id_notice" style="display:block;">
      <i class="fas fa-lock me-2"></i><strong>ID Upload Required:</strong> Please upload a valid ID above to unlock and fill out the booking form.
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
        <span class="label-text">Age *</span>
        <input type="number" name="age" id="reservation_age" required min="18" max="120" placeholder="Enter your age">
        <small class="form-text text-danger" id="reservation_age_error" style="display:none;">You must be at least 18 years old to make a booking.</small>
      </label>

      <!-- Booking Time Notice -->
      <div class="full-width" style="margin: 0.5rem 0; padding: 0.75rem; background-color: #e7f3ff; border-left: 4px solid #2196F3; border-radius: 4px;">
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
      let url = `api/room_availability.php?room_id=${roomId}`;

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
    const hasDiscountProof = hasDiscount && discountProof && discountProof.files && discountProof.files.length > 0;
    const hasIdUpload = idUpload && idUpload.files && idUpload.files.length > 0;
    const isIdValidated = idValidated && idValidated.value === '1';
    
    // Form is unlocked only if: discount proof exists OR (ID uploaded AND validated)
    const hasValidId = hasDiscountProof || (hasIdUpload && isIdValidated);
    
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
  
  // ID Validation Functions
  async function validateIDDocument(file, validationElement, validatedInput) {
    if (!file) return false;
    
    // Show validating message
    if (validationElement) {
      validationElement.style.display = 'block';
      validationElement.innerHTML = '<span class="text-info"><i class="fas fa-spinner fa-spin me-2"></i>Validating ID document...</span>';
    }
    
    // PDF files - basic validation only
    if (file.type === 'application/pdf') {
      if (validationElement) {
        validationElement.innerHTML = '<span class="text-success"><i class="fas fa-check-circle me-2"></i>PDF accepted. Please ensure it contains a valid government-issued ID.</span>';
      }
      if (validatedInput) validatedInput.value = '1';
      return true;
    }
    
    // Image files - perform visual validation
    if (file.type.startsWith('image/')) {
      try {
        const result = await analyzeIDImage(file);
        
        if (result.isValid) {
          if (validationElement) {
            validationElement.innerHTML = '<span class="text-success"><i class="fas fa-check-circle me-2"></i>Valid ID detected</span>';
          }
          if (validatedInput) validatedInput.value = '1';
          return true;
        } else {
          if (validationElement) {
            validationElement.innerHTML = `<span class="text-danger"><i class="fas fa-times-circle me-2"></i>Invalid ID: ${result.reason}. Please upload a clear photo of a government-issued ID.</span>`;
          }
          if (validatedInput) validatedInput.value = '0';
          return false;
        }
      } catch (error) {
        console.error('ID validation error:', error);
        if (validationElement) {
          validationElement.innerHTML = '<span class="text-warning"><i class="fas fa-exclamation-triangle me-2"></i>Could not validate ID. Please ensure image is clear and try again.</span>';
        }
        if (validatedInput) validatedInput.value = '0';
        return false;
      }
    }
    
    return false;
  }
  
  // Analyze ID image for validation
  async function analyzeIDImage(file) {
    return new Promise((resolve) => {
      const reader = new FileReader();
      reader.onload = function(e) {
        const img = new Image();
        img.onload = function() {
          const canvas = document.createElement('canvas');
          const ctx = canvas.getContext('2d');
          canvas.width = img.width;
          canvas.height = img.height;
          ctx.drawImage(img, 0, 0);
          
          // Get image data
          const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
          const data = imageData.data;
          
          // Check 1: Image dimensions (IDs are typically rectangular)
          const aspectRatio = img.width / img.height;
          const isRectangular = (aspectRatio >= 1.4 && aspectRatio <= 1.8) || (aspectRatio >= 0.55 && aspectRatio <= 0.72);
          
          // Check 2: File size (too small might be a screenshot or low quality)
          const fileSizeKB = file.size / 1024;
          const hasReasonableSize = fileSizeKB >= 50 && fileSizeKB <= 10000;
          
          // Check 3: Color variance (IDs have text, photos, and varied colors)
          let colorVariance = 0;
          const sampleSize = Math.min(1000, data.length / 4);
          const step = Math.floor((data.length / 4) / sampleSize);
          let prevR = data[0], prevG = data[1], prevB = data[2];
          
          for (let i = 0; i < data.length; i += step * 4) {
            const r = data[i];
            const g = data[i + 1];
            const b = data[i + 2];
            colorVariance += Math.abs(r - prevR) + Math.abs(g - prevG) + Math.abs(b - prevB);
            prevR = r; prevG = g; prevB = b;
          }
          colorVariance = colorVariance / sampleSize;
          
          // Check 4: Edge detection (IDs have defined borders and text)
          let edgeCount = 0;
          const edgeThreshold = 30;
          for (let i = 0; i < data.length - img.width * 4; i += 4) {
            const currentPixel = (data[i] + data[i + 1] + data[i + 2]) / 3;
            const bottomPixel = (data[i + img.width * 4] + data[i + img.width * 4 + 1] + data[i + img.width * 4 + 2]) / 3;
            if (Math.abs(currentPixel - bottomPixel) > edgeThreshold) {
              edgeCount++;
            }
          }
          const edgeDensity = (edgeCount / (data.length / 4)) * 100;
          
          // Check 5: Brightness (not too dark, not overexposed)
          let totalBrightness = 0;
          for (let i = 0; i < data.length; i += 4) {
            totalBrightness += (data[i] + data[i + 1] + data[i + 2]) / 3;
          }
          const avgBrightness = totalBrightness / (data.length / 4);
          const hasGoodBrightness = avgBrightness >= 40 && avgBrightness <= 220;
          
          // Calculate confidence score
          let confidence = 0;
          let failReasons = [];
          
          if (isRectangular) confidence += 25;
          else failReasons.push('Image is not ID-shaped');
          
          if (hasReasonableSize) confidence += 20;
          else failReasons.push('File size is too small or too large');
          
          if (colorVariance > 15) confidence += 20;
          else failReasons.push('Insufficient color variation for an ID');
          
          if (edgeDensity >= 2 && edgeDensity <= 20) confidence += 20;
          else failReasons.push('Missing text or border patterns');
          
          if (hasGoodBrightness) confidence += 15;
          else failReasons.push('Image is too dark or overexposed');
          
          // Determine if valid
          const isValid = confidence >= 60;
          
          resolve({
            isValid: isValid,
            confidence: Math.min(confidence, 95),
            reason: isValid ? 'Valid ID detected' : failReasons.join('; '),
            details: {
              aspectRatio: aspectRatio.toFixed(2),
              fileSizeKB: fileSizeKB.toFixed(0),
              colorVariance: colorVariance.toFixed(1),
              edgeDensity: edgeDensity.toFixed(1),
              avgBrightness: avgBrightness.toFixed(0)
            }
          });
        };
        img.onerror = function() {
          resolve({ isValid: false, confidence: 0, reason: 'Could not load image' });
        };
        img.src = e.target.result;
      };
      reader.onerror = function() {
        resolve({ isValid: false, confidence: 0, reason: 'Could not read file' });
      };
      reader.readAsDataURL(file);
    });
  }
  
  // Handle ID upload preview for reservation form
  const reservationIdUpload = document.getElementById('reservation_id_upload');
  if (reservationIdUpload) {
    reservationIdUpload.addEventListener('change', async function(e) {
      const file = e.target.files[0];
      const preview = document.getElementById('reservation_id_preview');
      const thumb = document.getElementById('reservation_id_thumb');
      const validationElement = document.getElementById('reservation_id_validation');
      const validatedInput = document.getElementById('reservation_id_upload_validated');
      
      if (!file) {
        if (validatedInput) validatedInput.value = '0';
        checkAndEnableFormFields('reservation');
        return;
      }
      
      // Validate the ID
      const isValid = await validateIDDocument(file, validationElement, validatedInput);
      
      if (!file || !preview || !thumb) return;
      
      if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
          thumb.innerHTML = '<img src="' + e.target.result + '" style="max-width: 100%; border-radius: 4px; border: 1px solid #ddd;">';
          preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
      } else if (file.type === 'application/pdf') {
        thumb.innerHTML = '<div style="padding: 10px; background: #f0f0f0; border-radius: 4px;"><i class="fas fa-file-pdf" style="font-size: 32px; color: #dc3545;"></i><br><small>' + file.name + '</small></div>';
        preview.style.display = 'block';
      }
      
      // Enable form fields only if validation passed
      if (isValid) {
        checkAndEnableFormFields('reservation');
      } else {
        // Keep form locked if ID is invalid
        const formFields = document.getElementById('reservation_form_fields');
        if (formFields) {
          const allInputs = formFields.querySelectorAll('input:not([type="hidden"]):not([readonly]), select, textarea, button');
          allInputs.forEach(field => {
            field.setAttribute('disabled', 'disabled');
            field.style.opacity = '0.5';
            field.style.cursor = 'not-allowed';
          });
        }
        // Clear the file input
        this.value = '';
      }
    });
  }
  
  // Handle ID upload preview for pencil form
  const pencilIdUpload = document.getElementById('pencil_id_upload');
  if (pencilIdUpload) {
    pencilIdUpload.addEventListener('change', async function(e) {
      const file = e.target.files[0];
      const preview = document.getElementById('pencil_id_preview');
      const thumb = document.getElementById('pencil_id_thumb');
      const validationElement = document.getElementById('pencil_id_validation');
      const validatedInput = document.getElementById('pencil_id_upload_validated');
      
      if (!file) {
        if (validatedInput) validatedInput.value = '0';
        checkAndEnableFormFields('pencil');
        return;
      }
      
      // Validate the ID
      const isValid = await validateIDDocument(file, validationElement, validatedInput);
      
      if (!file || !preview || !thumb) return;
      
      if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
          thumb.innerHTML = '<img src="' + e.target.result + '" style="max-width: 100%; border-radius: 4px; border: 1px solid #ddd;">';
          preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
      } else if (file.type === 'application/pdf') {
        thumb.innerHTML = '<div style="padding: 10px; background: #f0f0f0; border-radius: 4px;"><i class="fas fa-file-pdf" style="font-size: 32px; color: #dc3545;"></i><br><small>' + file.name + '</small></div>';
        preview.style.display = 'block';
      }
      
      // Enable form fields only if validation passed
      if (isValid) {
        checkAndEnableFormFields('pencil');
      } else {
        // Keep form locked if ID is invalid
        const formFields = document.getElementById('pencil_form_fields');
        if (formFields) {
          const allInputs = formFields.querySelectorAll('input:not([type="hidden"]):not([readonly]), select, textarea, button');
          allInputs.forEach(field => {
            field.setAttribute('disabled', 'disabled');
            field.style.opacity = '0.5';
            field.style.cursor = 'not-allowed';
          });
        }
        // Clear the file input
        this.value = '';
      }
    });
  }
  
  // Watch for discount proof upload to enable form
  const discountProofUpload = document.getElementById('discount_proof');
  if (discountProofUpload) {
    discountProofUpload.addEventListener('change', function() {
      // Update both forms in case they're affected
      checkAndEnableFormFields('reservation');
      checkAndEnableFormFields('pencil');
    });
  }
  
  // Watch for discount type changes to update ID requirement
  const discountTypeSelect = document.getElementById('discount_type');
  if (discountTypeSelect) {
    discountTypeSelect.addEventListener('change', function() {
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
    reservationAgeInput.addEventListener('input', function() {
      validateAge(this, reservationAgeError);
    });
    reservationAgeInput.addEventListener('change', function() {
      validateAge(this, reservationAgeError);
    });
    reservationAgeInput.addEventListener('blur', function() {
      validateAge(this, reservationAgeError);
    });
  }
  
  // Add age validation listeners for pencil form
  const pencilAgeInput = document.getElementById('pencil_age');
  const pencilAgeError = document.getElementById('pencil_age_error');
  if (pencilAgeInput) {
    pencilAgeInput.addEventListener('input', function() {
      validateAge(this, pencilAgeError);
    });
    pencilAgeInput.addEventListener('change', function() {
      validateAge(this, pencilAgeError);
    });
    pencilAgeInput.addEventListener('blur', function() {
      validateAge(this, pencilAgeError);
    });
  }
  
  // Initialize form lock on page load
  initializeFormLock();
});
</script>
