<!-- Pencil Booking (Draft Reservation Form) -->
<form id="pencilForm" method="POST" action="database/user_auth.php" class="compact-form" style="display:none;">
  <h3>Pencil Booking Form (Draft Reservation)</h3>
  <input type="hidden" name="action" value="create_booking">
  <input type="hidden" name="booking_type" value="pencil_booking">

  <?php include __DIR__ . '/../Discount/discount_application.php'; ?>

  <!-- ID Upload Section -->
  <div class="card mb-3" id="pencilIdUploadCard">
    <div class="card-header bg-info text-white">
      <strong><i class="fas fa-id-card me-2"></i>Valid ID Upload</strong>
    </div>
    <div class="card-body">
      <div class="mb-3">
        <label for="pencil_id_type" class="form-label">ID Type (for discount only)</label>
        <select name="id_type" id="pencil_id_type" class="form-control">
          <option value="">-- Select ID Type (Optional) --</option>
          <option value="national_id">National ID (PhilSys ID / ePhilID)</option>
          <option value="passport">Passport (Philippine or foreign, if applicable)</option>
          <option value="drivers_license">Driver's License (LTO)</option>
          <option value="umid">UMID Card (SSS / GSIS)</option>
          <option value="prc_id">PRC ID (Professional Regulation Commission)</option>
          <option value="voters_id">Voter's ID/Certification (COMELEC)</option>
          <option value="postal_id">Postal ID</option>
          <option value="philhealth_id">PhilHealth ID</option>
          <option value="tin_id">TIN ID (BIR)</option>
          <option value="school_id">School ID</option>
          <option value="alumni_id">Alumni ID</option>
          <option value="personnel_id">Personnel ID</option>
        </select>
        <small class="form-text text-muted">Optional. Select this only when applying a discount.</small>
      </div>
      <div class="mb-2">
        <label for="pencil_id_upload" class="form-label">Upload Valid ID <span class="text-danger"
            id="pencil_id_required"></span></label>
        <input type="file" name="id_upload" id="pencil_id_upload" class="form-control" accept="image/*">
        <input type="hidden" name="id_upload_cropped" id="pencil_id_upload_cropped">
        <input type="hidden" name="id_upload_validated" id="pencil_id_upload_validated" value="0">
        <small class="form-text text-muted">Optional: Upload ID if you want discount validation support.</small>

        <!-- Validation status -->
        <div id="pencil_id_validation" style="margin-top:8px;display:none;"></div>

        <!-- Preview area -->
        <div id="pencil_id_preview" style="margin-top:10px;display:none;">
          <div id="pencil_id_thumb" style="margin-top:8px;max-width:160px;"></div>
        </div>
      </div>
      <div class="alert alert-info mb-0" style="font-size: 0.9rem;">
        <i class="fas fa-info-circle me-2"></i>If you apply for a discount above, the discount ID proof will be used and
        this upload becomes optional.
      </div>
    </div>
  </div>

  <!-- Inline alert area for form-level validation messages -->
  <div class="form-alert mb-2" id="pencil_form_alert" style="display:none;"></div>

  <!-- ID Required Notice -->
  <div class="alert alert-info" id="pencil_id_notice" style="display:none;">
    <i class="fas fa-info-circle me-2"></i><strong>Optional:</strong> ID upload is only used for discount validation.
  </div>

  <div class="form-grid" id="pencil_form_fields">
    <label class="full-width">
      <span class="label-text">Pencil Booking no:</span>
      <input type="text" name="receipt_no" id="pencil_receipt_no" readonly placeholder="Auto-generated on submit"
        style="background-color: #f8f9fa; font-weight: 600; color: #856404;">
    </label>

    <label class="full-width">
      <span class="label-text">Select Room/Facility *</span>
      <select name="room_id" id="pencil_room_select" required>
        <option value="">Choose a room or facility...</option>
        <?php
        $pencil_room_stmt = $conn->prepare("SELECT id, name, item_type, room_number, capacity, price, room_status FROM items WHERE item_type IN ('room', 'facility') ORDER BY item_type, name");
        $pencil_room_stmt->execute();
        $pencil_room_result = $pencil_room_stmt->get_result();

        $pencil_current_type = '';
        while ($pencil_room = $pencil_room_result->fetch_assoc()) {
          if ($pencil_current_type !== $pencil_room['item_type']) {
            if ($pencil_current_type !== '')
              echo "</optgroup>";
            $pencil_current_type = $pencil_room['item_type'];
            $pencil_label = ($pencil_current_type === 'facility') ? 'Facilities' : ucfirst($pencil_current_type) . 's';
            echo "<optgroup label='$pencil_label'>";
          }

          $pencil_room_display = $pencil_room['name'];
          if ($pencil_room['room_number'])
            $pencil_room_display .= " (Room #" . $pencil_room['room_number'] . ")";
          $pencil_room_display .= " - " . $pencil_room['capacity'] . " persons";
          if ($pencil_room['price'] > 0)
            $pencil_room_display .= " - ₱" . number_format($pencil_room['price']) . "/night";

          $pencil_status = $pencil_room['room_status'] ?: 'available';
          $pencil_status_text = '';
          if ($pencil_status === 'clean')
            $pencil_status_text = ' (Ready)';
          elseif ($pencil_status === 'available')
            $pencil_status_text = ' (Available)';

          echo "<option value='" . $pencil_room['id'] . "'>" . htmlspecialchars($pencil_room_display . $pencil_status_text) . "</option>";
        }
        if ($pencil_current_type !== '')
          echo "</optgroup>";
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
      <input type="number" name="age" id="pencil_age" required min="18" max="120" placeholder="Enter your age">
      <small class="form-text text-danger" id="pencil_age_error" style="display:none;">You must be at least 18 years old
        to make a booking.</small>
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

    <!-- Two-Week Policy Acknowledgment -->
    <div class="full-width"
      style="margin-top: 1rem; padding: 1rem; background-color: #fff3cd; border: 1px solid #ffc107; border-radius: 5px;">
      <label style="display: flex; align-items: start; cursor: pointer; margin-bottom: 0;">
        <input type="checkbox" name="terms_acknowledged" id="pencil_terms_checkbox" required
          style="margin-right: 10px; margin-top: 4px; width: 18px; height: 18px; cursor: pointer;">
        <span style="font-size: 0.9rem; line-height: 1.5; color: #856404;">
          <strong>Two-Week Policy Acknowledgment:</strong> I acknowledge and agree that BarCIE implements a two-week
          allowance for pencil bookings.
          During this period, the reservation will be held temporarily pending formal confirmation.
          I understand that if confirmation and payment are not received within the two-week timeframe,
          the reserved slot may be released in accordance with BarCIE's booking policies. *
        </span>
      </label>
    </div>

    <!-- Payment moved into confirm modal -->

    <button type="button" id="pencilSubmitBtn" disabled>
      <i class="fas fa-edit me-2"></i>Submit Draft Reservation
    </button>
  </div>
</form>

<script>
  // Enable submit button only when terms are acknowledged
  document.addEventListener('DOMContentLoaded', function () {
    const termsCheckbox = document.getElementById('pencil_terms_checkbox');
    const submitBtn = document.getElementById('pencilSubmitBtn');
    const receiptInput = document.getElementById('pencil_receipt_no');

    // Generate preview pencil booking number
    function generatePencilBookingNumber() {
      const today = new Date();
      const year = today.getFullYear();
      const month = String(today.getMonth() + 1).padStart(2, '0');
      const day = String(today.getDate()).padStart(2, '0');
      const dateStr = `${year}${month}${day}`;
      return `PENCIL-${dateStr}-0001`;
    }

    // Set preview receipt number when form is shown
    if (receiptInput) {
      receiptInput.value = generatePencilBookingNumber();
    }

    if (termsCheckbox && submitBtn) {
      termsCheckbox.addEventListener('change', function () {
        if (this.checked) {
          submitBtn.removeAttribute('disabled');
          submitBtn.style.opacity = '1';
          submitBtn.style.cursor = 'pointer';
        } else {
          submitBtn.setAttribute('disabled', 'disabled');
          submitBtn.style.opacity = '0.6';
          submitBtn.style.cursor = 'not-allowed';
        }
      });

      // Initial state - will be updated by checkAndEnableFormFields
      submitBtn.setAttribute('disabled', 'disabled');
      submitBtn.style.opacity = '0.6';
      submitBtn.style.cursor = 'not-allowed';
    }

    // Regenerate receipt number when pencil form is displayed
    const pencilForm = document.getElementById('pencilForm');
    if (pencilForm) {
      const observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
          if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
            if (pencilForm.style.display !== 'none' && receiptInput) {
              receiptInput.value = generatePencilBookingNumber();
            }
          }
        });
      });
      observer.observe(pencilForm, { attributes: true });
    }

    // Setup availability checking for pencil booking form
    const pencilRoomSelect = document.getElementById('pencil_room_select');
    const pencilCheckinInput = document.querySelector('#pencilForm input[name="checkin"]');
    const pencilCheckoutInput = document.querySelector('#pencilForm input[name="checkout"]');

    // Add info elements after date inputs
    if (pencilCheckinInput && !document.getElementById('pencil_checkin_info')) {
      const infoDiv = document.createElement('div');
      infoDiv.id = 'pencil_checkin_info';
      infoDiv.className = 'availability-info';
      pencilCheckinInput.parentNode.appendChild(infoDiv);
    }

    if (pencilCheckoutInput && !document.getElementById('pencil_checkout_info')) {
      const infoDiv = document.createElement('div');
      infoDiv.id = 'pencil_checkout_info';
      infoDiv.className = 'availability-info';
      pencilCheckoutInput.parentNode.appendChild(infoDiv);
    }

    if (pencilRoomSelect) {
      pencilRoomSelect.addEventListener('change', function () {
        const roomId = this.value;
        if (typeof occupiedDatesCache !== 'undefined') {
          occupiedDatesCache = {}; // Clear cache when room changes
        }
        if (roomId && pencilCheckinInput && typeof checkRoomAvailability === 'function') {
          checkRoomAvailability(roomId, pencilCheckinInput, document.getElementById('pencil_checkin_info'));
        }
        if (roomId && pencilCheckoutInput && typeof checkRoomAvailability === 'function') {
          checkRoomAvailability(roomId, pencilCheckoutInput, document.getElementById('pencil_checkout_info'));
        }
      });
    }

    if (pencilCheckinInput) {
      pencilCheckinInput.addEventListener('change', function () {
        const roomId = pencilRoomSelect ? pencilRoomSelect.value : null;
        if (roomId && typeof checkRoomAvailability === 'function') {
          checkRoomAvailability(roomId, this, document.getElementById('pencil_checkin_info'));
          if (typeof validateDateRange === 'function') {
            validateDateRange(pencilCheckinInput, pencilCheckoutInput, roomId, document.getElementById('pencil_checkout_info'));
          }
        }
      });
    }

    if (pencilCheckoutInput) {
      pencilCheckoutInput.addEventListener('change', function () {
        const roomId = pencilRoomSelect ? pencilRoomSelect.value : null;
        if (roomId && typeof checkRoomAvailability === 'function') {
          checkRoomAvailability(roomId, this, document.getElementById('pencil_checkout_info'));
          if (typeof validateDateRange === 'function') {
            validateDateRange(pencilCheckinInput, pencilCheckoutInput, roomId, document.getElementById('pencil_checkout_info'));
          }
        }
      });
    }
  });
</script>
<script>
  /* Centralized pencil success modal and soft-refresh behavior.
     Placing this in `pencil_booking.php` so confirm_addOn and other code
     can call `showPencilSuccessModal(message)` without duplicating the implementation.
  */
  window.showPencilSuccessModal = function (message, receiptNumber = null) {
    try {
      const existing = document.getElementById('pencilSuccessModal');
      if (existing) existing.remove();

      // Try to extract receipt number from message if not provided
      if (!receiptNumber && message) {
        const receiptMatch = message.match(/receipt number:?\s*([A-Z0-9\-]+)/i);
        if (receiptMatch) {
          receiptNumber = receiptMatch[1];
        }
      }

      // Use global variable as fallback
      if (!receiptNumber && window.lastBookingReceiptNumber && window.lastBookingType === 'pencil_booking') {
        receiptNumber = window.lastBookingReceiptNumber;
      }

      const modalHtml = `
      <div class="modal fade" id="pencilSuccessModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" ${receiptNumber ? `data-receipt="${receiptNumber}"` : ''}>
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header bg-success text-white">
              <h5 class="modal-title"><i class="fas fa-check-circle me-2"></i>Draft Reservation Submitted!</h5>
            </div>
            <div class="modal-body text-center py-4">
              <div class="mb-3"><i class="fas fa-check-circle text-success" style="font-size: 3.5rem;"></i></div>
              <h4 class="text-success mb-2">Draft Reservation Submitted!</h4>
              <p class="mb-3">${(typeof escapeHtml === 'function') ? escapeHtml(message) : String(message)}</p>
              ${receiptNumber ? `<p class="receipt-number text-muted small">Receipt: <strong>${receiptNumber}</strong></p>` : ''}
              <div class="alert alert-warning mt-3 mb-3 text-start">
                <p class="mb-2"><strong>⚠️ Important:</strong> This is a <strong>draft reservation</strong> only.</p>
                <p class="mb-0 small">To secure your booking, you must confirm and complete payment within <strong>14 days</strong>. Check your email for the conversion link and payment instructions.</p>
              </div>
              <p class="small text-muted">Click <strong>Done</strong> to refresh the page.</p>
            </div>
            <div class="modal-footer justify-content-center pencil-buttons-container">
              <button type="button" class="btn btn-primary" onclick="printPencilSimple()" title="Download your receipt">
                <i class="fas fa-download me-2"></i>Download Your Receipt
              </button>
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="button" id="pencilDoneBtn" class="btn btn-success">Done</button>
            </div>
          </div>
        </div>
      </div>
    `;

      document.body.insertAdjacentHTML('beforeend', modalHtml);
      const modalEl = document.getElementById('pencilSuccessModal');
      const bsModal = new bootstrap.Modal(modalEl);

      // Done: auto-reload the page
      const doneBtn = modalEl.querySelector('#pencilDoneBtn');
      if (doneBtn) {
        doneBtn.addEventListener('click', function () {
          try { bsModal.hide(); } catch (e) { }
          setTimeout(() => {
            window.location.reload();
          }, 300);
        });
      }

      modalEl.addEventListener('hidden.bs.modal', function () {
        setTimeout(() => { try { modalEl.remove(); } catch (e) { } }, 200);
      });

      bsModal.show();
    } catch (e) {
      console.error('showPencilSuccessModal error', e);
      try { showToast(message || 'Draft reservation submitted successfully!', 'success'); } catch (e) { }
    }
  };

  // PDF download function for pencil bookings
  window.downloadPencilPDF = function () {
    try {
      let receiptNumber = '';

      // First, try to get from stored global variables (most reliable)
      if (window.lastBookingReceiptNumber && window.lastBookingType === 'pencil_booking') {
        receiptNumber = window.lastBookingReceiptNumber;
      }

      // Fallback: get from form input
      if (!receiptNumber) {
        const receiptInput = document.getElementById('pencil_receipt_no');
        if (receiptInput && receiptInput.value) {
          receiptNumber = receiptInput.value.replace(/[^A-Z0-9\-]/g, '');
        }
      }

      // Another fallback: try to extract from modal or success message
      if (!receiptNumber) {
        const modalElement = document.getElementById('pencilSuccessModal');
        if (modalElement) {
          const receiptElement = modalElement.querySelector('[data-receipt], .receipt-number');
          if (receiptElement) {
            receiptNumber = receiptElement.textContent || receiptElement.getAttribute('data-receipt') || '';
            receiptNumber = receiptNumber.replace(/[^A-Z0-9\-]/g, '');
          }
        }
      }

      if (!receiptNumber) {
        showToast('❌ Unable to find pencil booking receipt number for PDF generation. Please ensure your pencil booking was submitted successfully.', 'error');
        return;
      }

      // Show loading notification
      const loadingToast = showToast(`
      <div class="d-flex align-items-center">
        <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <div>
          <strong>🎨 Generating Pencil Booking PDF</strong><br>
          <small>Creating draft reservation receipt with BarCIE logo...</small>
        </div>
      </div>
    `, 'info', 0);

      // Generate PDF URL for pencil booking
      const pdfUrl = `api/GenerateBookingPdf.php?receipt_number=${encodeURIComponent(receiptNumber)}&type=pencil_booking`;

      // Create download link
      const link = document.createElement('a');
      link.href = pdfUrl;
      link.target = '_blank';
      link.style.display = 'none';
      document.body.appendChild(link);

      // Trigger download
      link.click();

      // Remove loading toast and show success
      setTimeout(() => {
        if (loadingToast && loadingToast.remove) {
          loadingToast.remove();
        }

        const successHTML = `
        <div class="d-flex align-items-center">
          <i class="fas fa-file-pdf text-warning me-2 fs-4"></i>
          <div>
            <strong>📋 Pencil Booking PDF Generated!</strong><br>
            <small>✨ Draft reservation receipt with BarCIE logo watermark</small>
          </div>
        </div>
      `;
        showToast(successHTML, 'success', 5000);

        // Clean up
        document.body.removeChild(link);
      }, 2000);

    } catch (e) {
      console.error('Pencil PDF generation error:', e);
      showToast('❌ Unable to generate pencil booking PDF. Please try the simple print option.', 'error');
    }
  };

  // Simple print function for pencil bookings
  window.printPencilSimple = function () {
    try {
      const receiptInput = document.getElementById('pencil_receipt_no');
      const guestNameInput = document.querySelector('#pencilForm input[name="guest_name"]');
      const roomSelect = document.getElementById('pencil_room_select');
      const checkinInput = document.querySelector('#pencilForm input[name="checkin"]');
      const checkoutInput = document.querySelector('#pencilForm input[name="checkout"]');

      const receiptNumber = receiptInput ? receiptInput.value : 'PENCIL-BOOKING';
      const guestName = guestNameInput ? guestNameInput.value : 'Guest';
      const roomName = roomSelect ? roomSelect.options[roomSelect.selectedIndex]?.text || 'Selected Room' : 'Room';
      const checkin = checkinInput ? new Date(checkinInput.value).toLocaleDateString() : '';
      const checkout = checkoutInput ? new Date(checkoutInput.value).toLocaleDateString() : '';

      const printWindow = window.open('', '', 'height=600,width=800');

      printWindow.document.write('<html><head><title>Pencil Booking - BarCIE</title>');
      printWindow.document.write('<style>');
      printWindow.document.write('body { font-family: Arial, sans-serif; padding: 20px; line-height: 1.6; max-width: 800px; margin: 0 auto; }');
      printWindow.document.write('.header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #f59e0b; padding-bottom: 20px; }');
      printWindow.document.write('.header h1 { color: #1e3a8a; margin: 0; font-size: 28px; }');
      printWindow.document.write('.header h2 { color: #d97706; margin: 5px 0 10px 0; font-size: 18px; }');
      printWindow.document.write('.header .subtitle { font-style: italic; color: #888; margin: 0; }');
      printWindow.document.write('.pencil-badge { background: #fef3c7; color: #92400e; padding: 8px 16px; border-radius: 20px; font-weight: bold; margin-top: 10px; display: inline-block; }');
      printWindow.document.write('.details { margin: 20px 0; padding: 20px; border: 2px solid #fbbf24; border-radius: 10px; background: #fffbeb; }');
      printWindow.document.write('.detail-item { margin: 10px 0; padding: 8px 0; border-bottom: 1px solid #fed7aa; }');
      printWindow.document.write('.detail-label { font-weight: bold; color: #92400e; display: inline-block; width: 150px; }');
      printWindow.document.write('.detail-value { color: #1f2937; }');
      printWindow.document.write('.terms { background: #fef7cd; border: 2px solid #fbbf24; border-radius: 8px; padding: 15px; margin: 20px 0; }');
      printWindow.document.write('.terms h3 { color: #92400e; margin-top: 0; }');
      printWindow.document.write('.footer { margin-top: 40px; padding-top: 20px; border-top: 2px solid #ddd; text-align: center; color: #666; font-size: 12px; }');
      printWindow.document.write('@media print { body { padding: 15px; } }');
      printWindow.document.write('</style></head><body>');

      printWindow.document.write('<div class="header">');
      printWindow.document.write('<h1>BarCIE International Center</h1>');
      printWindow.document.write('<h2>Draft Reservation (Pencil Booking)</h2>');
      printWindow.document.write('<p class="subtitle">Tempora Mutantur, Nos Et Mutamur In Illis</p>');
      printWindow.document.write('<div class="pencil-badge">DRAFT RESERVATION - PENDING CONFIRMATION</div>');
      printWindow.document.write('</div>');

      printWindow.document.write('<div class="details">');
      printWindow.document.write('<div class="detail-item"><span class="detail-label">Receipt Number:</span><span class="detail-value">' + receiptNumber + '</span></div>');
      printWindow.document.write('<div class="detail-item"><span class="detail-label">Guest Name:</span><span class="detail-value">' + guestName + '</span></div>');
      printWindow.document.write('<div class="detail-item"><span class="detail-label">Room/Facility:</span><span class="detail-value">' + roomName + '</span></div>');
      if (checkin) printWindow.document.write('<div class="detail-item"><span class="detail-label">Check-in:</span><span class="detail-value">' + checkin + '</span></div>');
      if (checkout) printWindow.document.write('<div class="detail-item"><span class="detail-label">Check-out:</span><span class="detail-value">' + checkout + '</span></div>');
      printWindow.document.write('<div class="detail-item"><span class="detail-label">Status:</span><span class="detail-value">DRAFT (Pending Confirmation)</span></div>');
      printWindow.document.write('<div class="detail-item"><span class="detail-label">Generated:</span><span class="detail-value">' + new Date().toLocaleString() + '</span></div>');
      printWindow.document.write('</div>');

      printWindow.document.write('<div class="terms">');
      printWindow.document.write('<h3>📋 Pencil Booking Terms & Conditions</h3>');
      printWindow.document.write('<ul>');
      printWindow.document.write('<li>This is a <strong>draft reservation</strong> and requires confirmation within 2 weeks</li>');
      printWindow.document.write('<li>Payment must be completed to confirm your reservation</li>');
      printWindow.document.write('<li>Reserved slot may be released if not confirmed within the timeframe</li>');
      printWindow.document.write('<li>Please contact BarCIE for confirmation and payment arrangements</li>');
      printWindow.document.write('</ul>');
      printWindow.document.write('</div>');

      printWindow.document.write('<div class="footer">');
      printWindow.document.write('<p><strong>BarCIE International Center</strong></p>');
      printWindow.document.write('<p>Barangay Center for Innovative Education © 2000</p>');
      printWindow.document.write('<p>Generated on ' + new Date().toLocaleString() + '</p>');
      printWindow.document.write('<p style="margin-top: 15px; font-size: 11px;">For confirmation and inquiries, please contact our front desk.</p>');
      printWindow.document.write('</div>');

      printWindow.document.write('</body></html>');
      printWindow.document.close();
      printWindow.focus();

      setTimeout(() => {
        printWindow.print();
        printWindow.close();
      }, 250);

      showToast('🖨️ Pencil booking print dialog opened', 'success');

    } catch (e) {
      console.error('Pencil simple print error:', e);
      showToast('❌ Unable to open print dialog. Please try again.', 'error');
    }
  };
</script>

<style>
  /* Enhanced Print Buttons Styling for Pencil Bookings */
  .pencil-buttons-container {
    gap: 10px;
    flex-wrap: wrap;
  }

  .pencil-buttons-container .elegant-pdf-btn {
    background: linear-gradient(135deg, #1e40af, #3730a3);
    border-color: #1e40af;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-size: 0.8rem;
    padding: 8px 16px;
    transition: all 0.3s ease;
  }

  .pencil-buttons-container .elegant-pdf-btn:hover {
    background: linear-gradient(135deg, #1e3a8a, #312e81);
    transform: translateY(-2px);
    box-shadow: 0 8px 15px rgba(30, 64, 175, 0.3);
  }

  .pencil-buttons-container .simple-print-btn {
    font-weight: 500;
    font-size: 0.8rem;
    padding: 8px 16px;
    transition: all 0.3s ease;
  }

  .pencil-buttons-container .simple-print-btn:hover {
    background: #6b7280;
    color: white;
    transform: translateY(-2px);
  }

  @media (max-width: 576px) {
    .pencil-buttons-container {
      flex-direction: column;
      align-items: center;
    }

    .pencil-buttons-container .btn {
      width: 100%;
      max-width: 200px;
    }
  }
</style>