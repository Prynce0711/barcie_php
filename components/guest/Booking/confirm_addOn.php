<?php
// Modal and client-side logic to preview booking details and select add-ons
// This file is intended to be included on the Guest booking page (booking.php)
?>

<!-- QRCode.js Library for generating QR codes -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"
  integrity="sha512-CNgIRecGo7nphbeZ04Sc13ka07paqdeTu0WR1IM4kNcpmBAUSHSQX0FslNhTDadL4O5SAGapGt4FodqL8My0mA=="
  crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<!-- Confirm Add-ons & Booking Preview Modal -->
<div class="modal fade" id="confirmAddOnModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-list-check me-2"></i>Confirm Booking & Add-ons</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="confirmPreview">
          <h6>Booking Summary</h6>
          <div id="previewDetails" style="margin-bottom: 12px;">Loading...</div>

          <h6>Add-ons</h6>
          <div id="addonsList" class="mb-3">
            <!-- Add-on checkboxes inserted by JS -->
          </div>

          <h6 class="mt-3">Payment</h6>
          <div class="mb-2">
            <label class="form-label">Payment Method</label>
            <select id="modal_payment_method" class="form-select">
              <option value="cash">Cash Payment</option>
              <option value="bank">Bank Transfer</option>
            </select>
          </div>
          <div id="modal_bank_details" style="display:none;" class="card p-3 mb-2">
            <div class="row">
              <div class="col-md-7">
                <div style="margin-bottom: 8px;"><strong>Account Name:</strong> La Consolacion University Philippines
                </div>
                <div style="margin-bottom: 8px;"><strong>Account Number:</strong> 575-7-575007089</div>
                <div style="margin-bottom: 8px;"><strong>Branch:</strong> Malolos Mc Arthur</div>
                <div
                  style="margin-top: 12px; padding: 10px; background-color: #fff3cd; border-radius: 5px; font-size: 13px;">
                  <strong>📱 Scan the QR Code →</strong><br>
                  <small class="text-muted">Use your banking app to scan and transfer payment quickly</small>
                </div>
              </div>
              <div class="col-md-5 text-center">
                <div
                  style="padding: 10px; background: white; border: 2px solid #ddd; border-radius: 8px; display: inline-block;">
                  <div id="bank_qr_code" style="width: 180px; height: 180px;"></div>
                </div>
                <div style="margin-top: 8px; font-size: 12px; color: #666;">Scan to Pay</div>
              </div>
            </div>
          </div>

          <div id="modal_payment_proof_wrap" style="display:none;" class="mb-2">
            <label class="form-label">Upload Proof of Payment (Bank Transfer)</label>
            <input type="file" id="modal_payment_proof" accept="image/*,application/pdf" class="form-control">
            <small class="text-muted">Accepted: images or PDF. Max recommended size: 5MB.</small>
          </div>


          <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
              <small class="text-muted">Note: Add-ons are optional and will be added to your booking total.</small>
            </div>
            <div>
              <strong>Original Total: ₱<span id="originalTotal">0</span></strong><br>
              <strong>Discount (<span id="discountPercent">0</span>%): -₱<span id="discountAmount">0</span></strong><br>
              <strong>Final Total: ₱<span id="previewTotal">0</span></strong>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer flex-column align-items-stretch">
        <div class="border rounded p-3 mb-3" style="background-color: #fff3cd;" id="policyAgreementSection">
          <div class="form-check text-start">
            <input class="form-check-input" type="checkbox" id="policyAgreementCheckbox" required
              style="width: 20px; height: 20px; margin-top: 0.15rem;">
            <label class="form-check-label" for="policyAgreementCheckbox" style="margin-left: 8px;">
              <strong><i class="fas fa-exclamation-triangle text-warning me-2"></i>Non-Refundable Payment Policy
                (v1.0)</strong><br>
              <small>
                I acknowledge and agree that BarCIE's payment policy is <strong class="text-danger">strictly
                  non-refundable</strong>.
                I understand that all payments made are considered <strong>final</strong> and are <strong>not eligible
                  for
                  cancellation, reimbursement, or credit</strong>, in accordance with BarCIE's established
                financial and reservation policies.
                <a href="#" id="readMorePolicyLink" class="ms-2" style="text-decoration: underline;">Read Full Terms &
                  Conditions</a>
              </small>
            </label>
          </div>
          <div class="form-check mt-2 text-start">
            <input class="form-check-input" type="checkbox" id="doubleConfirmCheckbox" required
              style="width: 20px; height: 20px; margin-top: 0.15rem;">
            <label class="form-check-label" for="doubleConfirmCheckbox" style="margin-left: 8px;">
              <small><strong>I understand and confirm</strong> that I have read and fully comprehend the non-refundable
                policy.</small>
            </label>
          </div>
        </div>
        <div class="d-flex justify-content-between">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Back</button>
          <button type="button" id="confirmBookingBtn" class="btn btn-primary" disabled>Confirm & Proceed</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Full Terms & Conditions Modal -->
<div class="modal fade" id="fullPolicyModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-warning bg-opacity-10">
        <h5 class="modal-title">
          <i class="fas fa-file-contract me-2"></i>
          Terms and Conditions - House Rules & Regulations
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
          id="closePolicyModal"></button>
      </div>
      <div class="modal-body">
        <div class="text-center mb-4">
          <div class="policy-header-image mb-3">
            <img src="public/images/imageBg/barcie_logo.jpg" alt="BarCIE Logo" class="img-fluid"
              style="max-width: 200px; height: auto;" onerror="this.style.display='none'">
          </div>
          <h4 class="text-primary fw-bold">
            <i class="fas fa-building me-2"></i>
            Welcome to BarCIE International Center!
          </h4>
          <hr class="my-3">
        </div>

        <div class="policy-section mb-4">
          <p class="text-justify" style="line-height: 1.8;">
            The BarCIE International Center was built in 2000 as a training center for the Hotel and Restaurant
            Management students of the La Consolacion University Philippines in Malolos City. As Part of the LCUP, it
            promotes the Vision, Mission, Goals and Core Values of the University. It upholds a
            Catholic-Augustinian-Filipino education and promotes gender consciousness, patriotism, creation
            spirituality, justice and peace.
          </p>
          <p class="text-justify" style="line-height: 1.8;">
            The Center has become a conducive place for conventions, seminars and conferences, recollections and
            retreats, as well as a pleasant place for wedding celebrations, birthday parties and the like. It is our
            desire to provide quality service and to keep the center safe, clean and a convenient place for you to stay.
          </p>
        </div>

        <div class="alert alert-primary mb-4">
          <h5 class="fw-bold mb-3">
            <i class="fas fa-clipboard-list me-2"></i>
            For the pleasure of all our guests, kindly observe the Center's House Rules and Regulations:
          </h5>
        </div>

        <div class="policy-section mb-3">
          <p class="ms-3"><strong>1.</strong> Check out time is 12:00 noon, regardless of the number of hours of stay
            within the day prior to check-out. If the guest intend to stay beyond the reservation period, kindly notify
            the Front Desk at least a day before the intended extension.</p>
        </div>

        <div class="policy-section mb-3">
          <p class="ms-3"><strong>2.</strong> Remember to turn off lights and air-conditioning units whenever leaving
            the Center for a long period of time.</p>
        </div>

        <div class="policy-section mb-3">
          <p class="ms-3"><strong>3.</strong> Observe silence at all times. Unattended children are not allowed to play
            or loiter around the lobby, function areas, pool side, staircases and parking lot for safety reasons.</p>
        </div>

        <div class="policy-section mb-3">
          <p class="ms-3"><strong>4.</strong> Report breakage or damage inside the room.</p>
        </div>

        <div class="policy-section mb-3">
          <p class="ms-3"><strong>5.</strong> Turn off faucets, shower and electrical appliances/devices after use.</p>
        </div>

        <div class="policy-section mb-3">
          <p class="ms-3"><strong>6.</strong> Breakfast and lunch are available on personal account at the Café Barcelo
            from Monday till Saturday.</p>
        </div>

        <div class="policy-section mb-3">
          <p class="ms-3"><strong>7.</strong> Cooking and bringing of food inside the room is not allowed.</p>
        </div>

        <div class="policy-section mb-3">
          <p class="ms-3"><strong>8.</strong> Drinking alcoholic liquor or any intoxicating materials/drugs inside the
            room is strictly prohibited.</p>
        </div>

        <div class="policy-section mb-3">
          <p class="ms-3"><strong>9.</strong> Bringing in firearms, deadly weapons and gambling devices is not allowed
            in the Center.</p>
        </div>

        <div class="policy-section mb-3">
          <p class="ms-3"><strong>10.</strong> Keeping of inflammable materials and any kind of explosive inside the
            room is prohibited.</p>
        </div>

        <div class="policy-section mb-3">
          <p class="ms-3"><strong>11.</strong> Unauthorized/Unregistered guests are not allowed to sleep/stay in the
            room. Entertainment of unregistered guests may be done in the ground floor lobby.</p>
        </div>

        <div class="policy-section mb-3">
          <p class="ms-3"><strong>12.</strong> Guests should deposit room key to the Front Desk Receptionist when
            leaving the Center's premises. In case of lost key, kindly report it to the Receptionist for replacement.
          </p>
        </div>

        <div class="policy-section mb-3">
          <p class="ms-3"><strong>13.</strong> Pets are not allowed inside the Center.</p>
        </div>

        <div class="policy-section mb-3">
          <p class="ms-3"><strong>14.</strong> Windows and doors should be closed anytime while the air-conditioning
            unit is on.</p>
        </div>

        <div class="policy-section mb-3">
          <p class="ms-3"><strong>15.</strong> Laundry services are provided. Washing of clothes inside the room is not
            allowed.</p>
        </div>

        <div class="policy-section mb-3">
          <p class="ms-3"><strong>16.</strong> Do not leave valuables such as cash, jewelries, cell phone, camera,
            passport, ticket, etc., for safety reasons. The management will not be responsible for the loss of
            personal/valuable items unless surrendered for safekeeping.</p>
        </div>

        <div class="policy-section mb-3">
          <p class="ms-3"><strong>17.</strong> A clinic is available within the campus, hence; should our guests feel
            sick, kindly notify the Front Desk for assistance.</p>
        </div>

        <div class="policy-section mb-3">
          <p class="ms-3"><strong>18.</strong> Settle bills one (1) hour before departure at the Front Desk in order to
            get assistance during check-out.</p>
        </div>

        <div class="policy-section mb-3">
          <p class="ms-3"><strong>19.</strong> Please wear proper attire suited for in different areas of the Center's
            facilities.</p>
        </div>

        <div class="policy-section mb-3">
          <p class="ms-3"><strong>20.</strong> Pool users are requested not to go through the lobby when going up the
            rooms. Kindly use the back door near the shower area.</p>
        </div>

        <div class="policy-section mb-3">
          <p class="ms-3"><strong>21.</strong> All items inside the room are inventoried. We will let our guests sign
            the checklist upon check-in.</p>
        </div>

        <div class="policy-section mb-4">
          <p class="ms-3"><strong>22.</strong> The Center has the right to inspect guests' luggage if the Center deems
            it necessary due to missing items inside the room.</p>
        </div>

        <div class="alert alert-warning mt-4">
          <p class="mb-2"><strong>Any violation of the above House Rules and Regulations, a fine will be charged to your
              account.</strong></p>
          <p class="mb-0 small text-muted">(Please seek information from the Front Desk Officers)</p>
        </div>

        <div class="alert alert-info mt-3">
          <p class="mb-2"><strong>Thank you for having chosen BarCIE International Center as your home away from
              home.</strong></p>
          <p class="mb-0 small"><strong>N.B.</strong> The Center is aspiring to improve its services and upgrade its
            facilities for the convenience of our clients. Help us meet the standard service by writing down your
            comments and suggestions. Thank you very much!</p>
        </div>

        <div class="text-end mt-3">
          <p class="text-muted mb-0"><em>BarCIE Management</em></p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="backToPolicyBtn">
          <i class="fas fa-arrow-left me-2"></i>Back to Booking Confirmation
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Booking Success Modal -->
<div class="modal fade" id="bookingSuccessModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
  data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">
          <i class="fas fa-check-circle me-2"></i>
          Booking Successful!
        </h5>
      </div>
      <div class="modal-body text-center py-4">
        <div class="success-icon mb-3">
          <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
        </div>
        <h4 class="text-success mb-3">Your reservation has been successfully submitted!</h4>
        <p class="text-muted mb-3" id="successMessage">Thank you for choosing BarCIE International Center.</p>

        <!-- Booking Details for Print -->
        <div id="bookingDetailsForPrint" class="text-start border rounded p-3 mb-3" style="display: none;">
          <h6 class="fw-bold mb-3">Booking Details</h6>
          <div id="printBookingDetails"></div>
        </div>

        <div class="alert alert-warning mb-3">
          <i class="fas fa-clock me-2"></i>
          <strong>⏳ Awaiting Payment Verification</strong>
          <p class="mb-0 mt-2 small">Your reservation is currently pending admin approval. Please wait while our team
            verifies your payment. You will receive a confirmation email once approved (usually within 24 hours).</p>
        </div>

        <div class="alert alert-info mb-0">
          <i class="fas fa-envelope me-2"></i>
          <small>A confirmation email has been sent with detailed instructions.</small>
        </div>
      </div>
      <div class="modal-footer justify-content-center print-buttons-container">
        <button type="button" class="btn btn-primary" onclick="printBookingSimple()" title="Download your receipt">
          <i class="fas fa-download me-2"></i>Download Your Receipt
        </button>
        <button type="button" class="btn btn-success" id="doneBookingBtn" disabled>
          <i class="fas fa-spinner fa-spin me-2" id="doneSpinner"></i>
          <span id="doneButtonText">Done (<span id="doneTimer">15</span>)</span>
        </button>
      </div>
    </div>
  </div>
</div>

<script>
  /*
   * Booking preview & add-on modal script
   * - Intercepts reservation/pencil booking form submissions
   * - Shows preview with add-on choices and calculated total
   * - On confirm, appends add-on fields to the original form and submits
   */
  (function () {
    const BASE_PATH =
      typeof window.APP_BASE_PATH === 'string' && window.APP_BASE_PATH.trim() !== ''
        ? window.APP_BASE_PATH.replace(/\/+$/, '')
        : '';
    const MAX_UPLOAD_MB = 10;
    const MAX_UPLOAD_BYTES = MAX_UPLOAD_MB * 1024 * 1024;

    // Notification helper: prefer popup notice; fallback to alert only when needed
    function notify(message, type = 'info') {
      try {
        if (typeof window.showToast === 'function') {
          return window.showToast(message, type);
        }
      } catch (e) { }

      try {
        if (typeof window.alert === 'function') {
          window.alert(String(message || 'Notification'));
        }
      } catch (e) { /* ignore */ }

      return null;
    }

    // Clear all form data
    function clearFormData() {
      try {
        if (currentForm) {
          // Reset the form
          currentForm.reset();

          // Clear all input fields
          currentForm.querySelectorAll('input, textarea, select').forEach(field => {
            if (field.type === 'checkbox' || field.type === 'radio') {
              field.checked = false;
            } else if (field.type === 'file') {
              field.value = '';
            } else if (field.tagName === 'SELECT') {
              field.selectedIndex = 0;
            } else {
              field.value = '';
            }
          });

          // Clear any validation states
          currentForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
          currentForm.querySelectorAll('.inline-validation-msg').forEach(el => el.remove());

          // Hide any alert messages
          const formAlert = currentForm.querySelector('.form-alert');
          if (formAlert) {
            formAlert.innerHTML = '';
            formAlert.style.display = 'none';
          }
        }

        // Reset current booking variables
        currentBooking = null;
        currentItem = null;

        // Scroll to top of booking section
        try {
          const bookingSection = document.getElementById('booking') || document.querySelector('[name="booking"]');
          if (bookingSection) bookingSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } catch (e) { }

      } catch (e) {
        console.warn('Error clearing form:', e);
      }
    }

    // Print booking details with elegant PDF
    window.printBookingDetails = function () {
      try {
        let receiptNumber = '';
        let bookingType = 'reservation';

        // First, try to get from stored global variables (most reliable)
        if (window.lastBookingReceiptNumber) {
          receiptNumber = window.lastBookingReceiptNumber;
          bookingType = window.lastBookingType || 'reservation';
        }

        // Fallback: try to extract from DOM elements
        if (!receiptNumber) {
          const receiptElement = document.querySelector('#printBookingDetails .receipt-number, [data-receipt]');
          if (receiptElement) {
            receiptNumber = receiptElement.textContent || receiptElement.getAttribute('data-receipt') || '';
            receiptNumber = receiptNumber.replace(/[^A-Z0-9\-]/g, ''); // Clean receipt number
          }
        }

        // Another fallback: try to get from form inputs
        if (!receiptNumber) {
          const regularReceiptInput = document.getElementById('receipt_no');
          const pencilReceiptInput = document.getElementById('pencil_receipt_no');

          if (regularReceiptInput && regularReceiptInput.value) {
            receiptNumber = regularReceiptInput.value;
            bookingType = 'reservation';
          } else if (pencilReceiptInput && pencilReceiptInput.value) {
            receiptNumber = pencilReceiptInput.value;
            bookingType = 'pencil_booking';
          }
        }

        // Determine booking type if not already set
        if (receiptNumber && !window.lastBookingType) {
          if (receiptNumber.includes('PENCIL') ||
            document.querySelector('#pencilForm:not([style*="display: none"])') ||
            document.querySelector('.pencil-booking-indicator')) {
            bookingType = 'pencil_booking';
          }
        }

        if (!receiptNumber) {
          showToast('❌ Unable to identify booking receipt number for PDF generation. Please ensure your booking was submitted successfully.', 'error');
          return;
        }

        // Show loading notification
        const loadingToast = showToast(`
        <div class="d-flex align-items-center">
          <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <div>
            <strong>🎨 Generating Elegant PDF Receipt</strong><br>
            <small>Creating professional document with BarCIE logo watermark...</small>
          </div>
        </div>
      `, 'info', 0);

        // Generate PDF URL
        const pdfUrl = `api/GenerateBookingPdf.php?receipt_number=${encodeURIComponent(receiptNumber)}&type=${encodeURIComponent(bookingType)}`;

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
            <i class="fas fa-file-pdf text-primary me-2 fs-4"></i>
            <div>
              <strong>🎊 PDF Receipt Generated Successfully!</strong><br>
              <small>✨ Professional booking confirmation with BarCIE logo watermark</small>
            </div>
          </div>
        `;
          showToast(successHTML, 'success', 5000);

          // Clean up
          document.body.removeChild(link);
        }, 2000);

      } catch (e) {
        console.error('PDF generation error:', e);
        showToast('❌ Unable to generate PDF. Please try the simple print option.', 'error');

        // Fallback to simple print
        try {
          const printContent = document.getElementById('bookingDetailsForPrint').innerHTML;
          const printWindow = window.open('', '', 'height=600,width=800');
          printWindow.document.write('<html><head><title>Booking Confirmation</title>');
          printWindow.document.write('<style>');
          printWindow.document.write('body { font-family: Arial, sans-serif; padding: 20px; line-height: 1.6; }');
          printWindow.document.write('h6 { color: #333; margin-bottom: 15px; font-size: 16px; }');
          printWindow.document.write('p { margin: 8px 0; }');
          printWindow.document.write('.text-muted { color: #666; }');
          printWindow.document.write('strong { color: #000; font-weight: 600; }');
          printWindow.document.write('@media print { body { padding: 10px; } }');
          printWindow.document.write('</style></head><body>');
          printWindow.document.write('<div style="text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px;">');
          printWindow.document.write('<h1 style="color: #1e3a8a; margin: 0;">BarCIE International Center</h1>');
          printWindow.document.write('<h2 style="color: #666; margin: 5px 0;">Booking Confirmation</h2>');
          printWindow.document.write('<p style="font-style: italic; color: #888; margin: 0;">Tempora Mutantur, Nos Et Mutamur In Illis</p>');
          printWindow.document.write('</div>');
          printWindow.document.write(printContent);
          printWindow.document.write('<div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #666; font-size: 12px;">');
          printWindow.document.write('<p>BarCIE International Center - Barangay Center for Innovative Education © 2000</p>');
          printWindow.document.write('<p>Generated on ' + new Date().toLocaleString() + '</p>');
          printWindow.document.write('</div>');
          printWindow.document.write('</body></html>');
          printWindow.document.close();
          printWindow.focus();

          setTimeout(() => {
            printWindow.print();
            printWindow.close();
          }, 250);

          showToast('📄 Simple print version opened as fallback', 'info');
        } catch (fallbackError) {
          console.error('Fallback print error:', fallbackError);
          showToast('❌ Both PDF and print options failed. Please contact support.', 'error');
        }
      }
    };

    // Simple print function (fallback)
    window.printBookingSimple = function () {
      try {
        const printContent = document.getElementById('bookingDetailsForPrint').innerHTML;
        const printWindow = window.open('', '', 'height=600,width=800');

        printWindow.document.write('<html><head><title>Booking Confirmation - BarCIE</title>');
        printWindow.document.write('<style>');
        printWindow.document.write('body { font-family: Arial, sans-serif; padding: 20px; line-height: 1.6; max-width: 800px; margin: 0 auto; }');
        printWindow.document.write('.header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #1e3a8a; padding-bottom: 20px; }');
        printWindow.document.write('.header h1 { color: #1e3a8a; margin: 0; font-size: 28px; }');
        printWindow.document.write('.header h2 { color: #666; margin: 5px 0 10px 0; font-size: 18px; }');
        printWindow.document.write('.header .subtitle { font-style: italic; color: #888; margin: 0; }');
        printWindow.document.write('h6 { color: #333; margin: 20px 0 10px 0; font-size: 16px; padding-bottom: 5px; border-bottom: 1px solid #ddd; }');
        printWindow.document.write('p { margin: 8px 0; }');
        printWindow.document.write('.text-muted { color: #666; }');
        printWindow.document.write('strong { color: #000; font-weight: 600; }');
        printWindow.document.write('.footer { margin-top: 40px; padding-top: 20px; border-top: 2px solid #ddd; text-align: center; color: #666; font-size: 12px; }');
        printWindow.document.write('@media print { body { padding: 15px; } .no-print { display: none; } }');
        printWindow.document.write('</style></head><body>');

        printWindow.document.write('<div class="header">');
        printWindow.document.write('<h1>BarCIE International Center</h1>');
        printWindow.document.write('<h2>Booking Confirmation</h2>');
        printWindow.document.write('<p class="subtitle">Tempora Mutantur, Nos Et Mutamur In Illis</p>');
        printWindow.document.write('</div>');

        printWindow.document.write(printContent);

        printWindow.document.write('<div class="footer">');
        printWindow.document.write('<p><strong>BarCIE International Center</strong></p>');
        printWindow.document.write('<p>Barangay Center for Innovative Education © 2000</p>');
        printWindow.document.write('<p>Generated on ' + new Date().toLocaleString() + '</p>');
        printWindow.document.write('<p style="margin-top: 15px; font-size: 11px;">For inquiries and assistance, please contact our front desk.</p>');
        printWindow.document.write('</div>');

        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.focus();

        setTimeout(() => {
          printWindow.print();
          printWindow.close();
        }, 250);

        showToast('🖨️ Simple print dialog opened', 'success');

      } catch (e) {
        console.error('Simple print error:', e);
        showToast('❌ Unable to open print dialog. Please try again.', 'error');
      }
    };

    // Show success modal with timer
    function showSuccessModal(message, bookingDetails) {
      try {
        // Set success message
        const successMsg = document.getElementById('successMessage');
        if (successMsg) successMsg.textContent = message || 'Thank you for choosing BarCIE International Center.';

        // Populate booking details for print
        const printDetails = document.getElementById('printBookingDetails');
        if (printDetails && bookingDetails) {
          printDetails.innerHTML = bookingDetails;
          document.getElementById('bookingDetailsForPrint').style.display = 'block';
        }

        // Show the modal
        const successModal = new bootstrap.Modal(document.getElementById('bookingSuccessModal'));
        successModal.show();

        // Start 15 second countdown
        let countdown = 15;
        const doneBtn = document.getElementById('doneBookingBtn');
        const timerSpan = document.getElementById('doneTimer');
        const doneSpinner = document.getElementById('doneSpinner');

        const countdownInterval = setInterval(() => {
          countdown--;
          if (timerSpan) timerSpan.textContent = countdown;

          if (countdown <= 0) {
            clearInterval(countdownInterval);
            if (doneBtn) {
              doneBtn.disabled = false;
              doneBtn.innerHTML = '<i class="fas fa-check me-2"></i>Done';
            }
          }
        }, 1000);

        // Handle done button click
        if (doneBtn) {
          doneBtn.onclick = function () {
            clearInterval(countdownInterval);
            successModal.hide();
            clearFormData();

            // Auto-reload the page to refresh data
            setTimeout(() => {
              window.location.reload();
            }, 300);
          };
        }

      } catch (e) {
        console.error('Error showing success modal:', e);
        // Fallback to alert
        showToast('Booking successful! ' + (message || ''), 'success');
        clearFormData();
      }
    }

    // Show booking confirmation without reloading the page: update URL, scroll to booking section and show a small banner
    function showBookingConfirmation(msg) {
      try {
        // Update URL without reload
        if (history && history.pushState) {
          history.pushState(null, '', 'index.php?view=guest#booking');
        } else {
          location.hash = 'booking';
        }
      } catch (e) { /* ignore */ }

      // Scroll to booking anchor if present
      try {
        const anchor = document.getElementById('booking') || document.querySelector('[name="booking"]') || document.querySelector('#booking');
        if (anchor) anchor.scrollIntoView({ behavior: 'smooth', block: 'start' });
      } catch (e) { /* ignore */ }
    }
    // Default add-on catalogue - admin can override per-item via DB
    const DEFAULT_ADDONS = [
      { id: 'addon_breakfast', label: 'Breakfast (per person / per night)', price: 150, pricing: 'per_person' },
      { id: 'addon_extra_bed', label: 'Extra Bed (per night)', price: 500, pricing: 'per_night' },
      { id: 'addon_airport', label: 'Airport Transfer (one-way)', price: 800, pricing: 'per_event' }
    ];
    // will be populated from item data when previewed
    let AVAILABLE_ADDONS = [];

    const modalEl = document.getElementById('confirmAddOnModal');
    const previewDetails = document.getElementById('previewDetails');
    const addonsList = document.getElementById('addonsList');
    const previewTotal = document.getElementById('previewTotal');
    const confirmBtn = document.getElementById('confirmBookingBtn');

    let currentForm = null; // will hold reservationForm or pencilForm
    let currentBooking = null; // object with booking data
    let currentItem = null; // room/facility data from server

    // Inline validation helpers
    function getFieldLabel(field) {
      try {
        // Match surrounding label text if present
        const lab = field.closest('label') || document.querySelector('label[for="' + (field.id || '') + '"]');
        if (lab) {
          const span = lab.querySelector('.label-text');
          if (span && span.textContent) return span.textContent.replace('*', '').trim();
          // fallback to label text
          return lab.textContent.trim();
        }
      } catch (e) { }
      // fallback from name
      return (field.name || 'This field').replace(/_/g, ' ');
    }

    function clearInlineAlerts(form) {
      if (!form) return;
      form.querySelectorAll('.inline-validation-msg').forEach(n => n.remove());
      form.querySelectorAll('.is-invalid').forEach(n => n.classList.remove('is-invalid'));
      // NOTE: keep top-level form alert visible until the user fixes inputs
      // This function only clears per-field inline messages and invalid classes.
    }

    // Small persistent banner at top of page for validation errors
    function showValidationBanner(message) {
      try {
        let banner = document.getElementById('booking_validation_banner');
        if (!banner) {
          banner = document.createElement('div');
          banner.id = 'booking_validation_banner';
          banner.style.position = 'fixed';
          banner.style.top = '12px';
          banner.style.left = '50%';
          banner.style.transform = 'translateX(-50%)';
          banner.style.zIndex = 2147483647;
          banner.style.maxWidth = '900px';
          banner.style.width = 'calc(100% - 48px)';
          banner.style.pointerEvents = 'auto';
          banner.innerHTML = '';
          document.body.appendChild(banner);
        }
        banner.innerHTML = '<div class="alert alert-danger d-flex justify-content-between align-items-center mb-0">' +
          '<div style="flex:1; padding-right:12px;">' + escapeHtml(message) + '</div>' +
          '<button type="button" aria-label="Dismiss" class="btn-close" style="margin-left:12px;" onclick="(function(){var b=document.getElementById(\'booking_validation_banner\'); if(b) b.style.display=\'none\';})()"></button>' +
          '</div>';
        banner.style.display = 'block';
        try { banner.setAttribute('role', 'alert'); banner.setAttribute('aria-live', 'assertive'); } catch (e) { }
      } catch (e) { console.warn('showValidationBanner error', e); }
    }

    function hideValidationBanner() {
      try {
        const banner = document.getElementById('booking_validation_banner');
        if (banner) banner.style.display = 'none';
      } catch (e) { }
    }

    // Attach listeners to form fields so errors clear as soon as user types/corrects
    function attachFieldListeners(form) {
      if (!form) return;
      const inputs = Array.from(form.querySelectorAll('input, textarea, select'));
      inputs.forEach(field => {
        // skip hidden fields
        if (field.type === 'hidden') return;
        const handler = function () {
          try {
            // Remove per-field inline message(s) adjacent to this field
            try {
              const parent = field.parentNode;
              if (parent) {
                parent.querySelectorAll('.inline-validation-msg').forEach(n => n.remove());
              }
            } catch (e) { }

            // Remove invalid class if field is now valid
            if (field.checkValidity && field.checkValidity()) {
              field.classList.remove('is-invalid');
            }

            // If no remaining invalid fields in this form, hide top-level alert and banner
            const stillInvalid = form.querySelectorAll('.is-invalid, .inline-validation-msg');
            if (!stillInvalid || stillInvalid.length === 0) {
              try {
                const top = form.querySelector('.form-alert');
                if (top) { top.innerHTML = ''; top.style.display = 'none'; }
              } catch (e) { }
              hideValidationBanner();
            }
          } catch (e) { /* ignore */ }
        };

        // Use input and change to catch typing and selection changes
        field.addEventListener('input', handler);
        field.addEventListener('change', handler);
      });
    }

    function showInlineAlert(field, message) {
      try {
        const form = field.form || document;
        // Do not remove existing form-level alerts (we want the user to see them until they fix inputs)
        clearInlineAlerts(form);
        field.classList.add('is-invalid');
        const err = document.createElement('div');
        err.className = 'invalid-feedback d-block inline-validation-msg';
        err.style.marginTop = '6px';
        err.textContent = message;
        // Insert after field (prefer placing inside the field's label/container)
        try {
          if (field.parentNode) field.parentNode.appendChild(err);
          else field.insertAdjacentElement('afterend', err);
        } catch (e) {
          // fallback: append to form alert area
          try {
            const top = form.querySelector('.form-alert');
            if (top) { top.innerHTML = '<div class="alert alert-danger mb-0">' + message + '</div>'; top.style.display = 'block'; }
          } catch (ee) { }
        }

        // Also show a persistent form-level summary at the top of the form
        try {
          const top = form.querySelector('.form-alert');
          if (top) {
            top.innerHTML = '<div class="alert alert-danger mb-0">Please fill: <strong>' + escapeHtml(getFieldLabel(field)) + '</strong>. ' + escapeHtml(message) + '</div>';
            top.style.display = 'block';
            // Accessibility: mark the alert for screen readers and ensure it's visible
            try { top.setAttribute('role', 'alert'); top.setAttribute('aria-live', 'assertive'); } catch (e) { }
            try { top.scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch (e) { }
          }
        } catch (e) { }

        try { field.focus(); } catch (e) { }
        try { field.scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch (e) { }
        // Keep the per-field inline message until the user interacts and clears it via validate flow
      } catch (e) { console.warn('showInlineAlert error', e); }
    }

    // Validate form and return first invalid field info or null when OK
    function validateFormInline(form) {
      if (!form) return null;
      clearInlineAlerts(form);

      // Check ID upload requirement first
      const formId = form.id;
      const isReservation = formId === 'reservationForm';
      const isPencil = formId === 'pencilForm';

      if (isReservation || isPencil) {
        const discountTypeSelect = document.getElementById('discount_type');
        const hasDiscount = !!(discountTypeSelect && discountTypeSelect.value && discountTypeSelect.value !== '' && discountTypeSelect.value !== 'none');
        const discountProof = document.getElementById('discount_proof');
        const idUpload = document.getElementById(isReservation ? 'reservation_id_upload' : 'pencil_id_upload');
        const idValidated = document.getElementById(isReservation ? 'reservation_id_upload_validated' : 'pencil_id_upload_validated');

        // Check if either discount proof OR ID upload is provided
        const hasDiscountProof = hasDiscount && discountProof && discountProof.files && discountProof.files.length > 0;
        const hasIdUpload = idUpload && idUpload.files && idUpload.files.length > 0;
        const isIdValidated = idValidated && idValidated.value === '1';

        if (hasDiscount && !hasDiscountProof && !hasIdUpload) {
          return {
            field: idUpload || discountProof,
            message: 'Please upload a valid ID. If you have a discount with ID proof, that can be used instead.'
          };
        }

        // Check if ID was uploaded but not validated
        if (hasIdUpload && !hasDiscountProof && !isIdValidated) {
          return {
            field: idUpload,
            message: 'The uploaded file does not appear to be a valid ID document. Please upload a clear photo of a government-issued ID.'
          };
        }
      }

      // HTML5 validation first: find required fields missing or pattern mismatch
      const requiredFields = Array.from(form.querySelectorAll('[required]'));
      for (const f of requiredFields) {
        // skip hidden inputs and ID upload if discount is selected
        if (f.type === 'hidden' || f.offsetParent === null && f.type !== 'date') continue;

        // Skip ID upload validation if discount with proof exists
        if ((f.id === 'reservation_id_upload' || f.id === 'pencil_id_upload')) {
          const discountTypeSelect = document.getElementById('discount_type');
          const hasDiscount = !!(discountTypeSelect && discountTypeSelect.value && discountTypeSelect.value !== '' && discountTypeSelect.value !== 'none');
          const discountProof = document.getElementById('discount_proof');
          const hasDiscountProof = hasDiscount && discountProof && discountProof.files && discountProof.files.length > 0;
          if (hasDiscountProof) continue; // Skip ID validation, discount proof is sufficient
        }

        if (!f.checkValidity()) {
          // Build friendly message
          let msg = '';
          if (f.validity.valueMissing) msg = getFieldLabel(f) + ' is required.';
          else if (f.validity.typeMismatch) msg = 'Please enter a valid ' + getFieldLabel(f) + '.';
          else if (f.validity.patternMismatch) msg = 'Please enter a valid ' + getFieldLabel(f) + '.';
          else msg = 'Please correct the ' + getFieldLabel(f) + ' field.';
          return { field: f, message: msg };
        }
      }

      // Additional custom checks
      const email = form.querySelector('[name="email"]');
      if (email) {
        const val = (email.value || '').trim();
        if (val && !/[@].+/.test(val)) {
          return { field: email, message: 'Please enter a valid email address.' };
        }
        // Enforce allowed email domains
        const allowedDomains = ['@gmail.com', '@email.lcup.edu.ph', '@yahoo.com', '@icloud.com'];
        const hasAllowedDomain = allowedDomains.some(domain => val.toLowerCase().endsWith(domain));
        if (val && !hasAllowedDomain) {
          return { field: email, message: 'Please use an accepted email domain: @gmail.com, @email.lcup.edu.ph, @yahoo.com, or @icloud.com' };
        }
      }

      // Age validation - must be 18 or older
      const age = form.querySelector('[name="age"]');
      if (age) {
        const ageValue = parseInt(age.value);
        if (ageValue && ageValue < 18) {
          return { field: age, message: 'You must be at least 18 years old to make a booking.' };
        }
        if (ageValue && (ageValue < 1 || ageValue > 120)) {
          return { field: age, message: 'Please enter a valid age.' };
        }
      }

      return null;
    }
    // Build add-ons UI
    function renderAddons() {
      addonsList.innerHTML = '';
      const list = (currentItem && Array.isArray(currentItem.addons) && currentItem.addons.length) ? currentItem.addons : DEFAULT_ADDONS;
      AVAILABLE_ADDONS = list;

      list.forEach((addon, idx) => {
        const aid = addon.id ? addon.id : ('addon_' + idx + '_' + addon.label.replace(/\s+/g, '_').toLowerCase());
        const price = Number(addon.price || 0);
        const pricing = addon.pricing || 'per_event';
        const wrapper = document.createElement('div');
        wrapper.className = 'form-check';
        wrapper.innerHTML = `
        <input class="form-check-input addon-checkbox" type="checkbox" value="${aid}" id="${aid}" data-price="${price}" data-pricing="${pricing}">
        <label class="form-check-label" for="${aid}">${escapeHtml(addon.label || addon.name || 'Add-on')} — ₱${price}</label>
        <div class="mt-1" style="display:none;"><small class="text-muted addon-qty-text">Quantity: <input type="number" min="1" value="1" class="addon-qty form-control form-control-sm" style="width:80px; display:inline-block; margin-left:8px;"></small></div>
      `;
        addonsList.appendChild(wrapper);
      });

      // Show qty input when checkbox toggled
      document.querySelectorAll('.addon-checkbox').forEach(cb => {
        cb.addEventListener('change', e => {
          const container = e.target.closest('.form-check');
          const qtyDiv = container.querySelector('.addon-qty-text');
          if (e.target.checked) qtyDiv.style.display = 'block'; else qtyDiv.style.display = 'none';
          recalcTotal();
        });
      });

      // qty change
      addonsList.addEventListener('input', e => {
        if (e.target && e.target.classList.contains('addon-qty')) recalcTotal();
      });
    }

    // Calculate nights between two date inputs (reservation)
    function calcNights(checkin, checkout) {
      try {
        const diff = Math.max(0, new Date(checkout) - new Date(checkin));
        return Math.ceil(diff / (1000 * 60 * 60 * 24));
      } catch (err) { return 1; }
    }

    // Update recalcTotal to include detailed breakdown
    function recalcTotal() {
      let base = 0;
      let nights = 1;
      // reservation has checkin/checkout
      if (currentBooking && currentItem) {
        if (currentBooking.type === 'reservation') {
          nights = calcNights(currentBooking.checkin, currentBooking.checkout) || 1;
          base = Number(currentItem.price || 0) * nights;
        } else {
          // pencil / facility - base fee = item price (per event)
          base = Number(currentItem.price || 0);
        }
      }

      let addonsTotal = 0;
      document.querySelectorAll('.addon-checkbox').forEach(cb => {
        if (cb.checked) {
          const price = Number(cb.dataset.price || 0);
          const pricing = cb.dataset.pricing || 'per_event';
          const qtyInput = cb.closest('.form-check').querySelector('.addon-qty');
          const qty = qtyInput ? Math.max(1, parseInt(qtyInput.value || '1')) : 1;

          if (pricing === 'per_person' && currentBooking && currentBooking.type === 'reservation') {
            const occupants = Number(currentBooking.occupants || 1);
            addonsTotal += price * occupants * nights * qty;
          } else if (pricing === 'per_night' && currentBooking && currentBooking.type === 'reservation') {
            addonsTotal += price * qty * nights;
          } else {
            // per_event or fallback
            addonsTotal += price * qty;
          }
        }
      });

      const discountType = document.getElementById('discount_type');
      const discountPercent = discountType && discountType.value ? getDiscountPercent(discountType.value) : 0;
      const total = base + addonsTotal;
      const discountAmount = total * (discountPercent / 100);
      const finalTotal = total - discountAmount;

      document.getElementById('originalTotal').textContent = total.toLocaleString();
      document.getElementById('discountPercent').textContent = discountPercent;
      document.getElementById('discountAmount').textContent = discountAmount.toLocaleString();
      previewTotal.textContent = finalTotal.toLocaleString();

      return finalTotal;
    }

    // Helper function to get discount percentage based on type
    function getDiscountPercent(type) {
      switch (type) {
        case 'pwd_senior':
          return 20;
        case 'lcuppersonnel':
          return 10;
        case 'lcupstudent':
          return 7;
        default:
          return 0;
      }
    }

    // Helper: find room data from server
    async function fetchItemById(id) {
      try {
        // Use the project-relative fetch path to be consistent with dashboard script
        const res = await fetch('database/index.php?endpoint=fetch_items');
        if (!res.ok) return null;
        const items = await res.json();
        return items.find(it => Number(it.id) === Number(id)) || null;
      } catch (err) {
        console.error('Failed to fetch items', err);
        return null;
      }
    }

    // Build preview HTML
    function buildPreviewHtml() {
      if (!currentBooking || !currentItem) return '—';
      let html = '<div class="row">';
      html += '<div class="col-8">';
      html += `<p><strong>Room/Facility:</strong> ${escapeHtml(currentItem.name)} ${currentItem.room_number ? ' (Room #' + escapeHtml(currentItem.room_number) + ')' : ''}</p>`;
      if (currentBooking.type === 'reservation') {
        html += `<p><strong>Check-in:</strong> ${escapeHtml(currentBooking.checkin)}</p>`;
        html += `<p><strong>Check-out:</strong> ${escapeHtml(currentBooking.checkout)}</p>`;
        html += `<p><strong>Occupants:</strong> ${escapeHtml(currentBooking.occupants)}</p>`;
        const nights = calcNights(currentBooking.checkin, currentBooking.checkout) || 1;
        html += `<p><strong>Nights:</strong> ${nights}</p>`;
        html += `<p><strong>Rate (per night):</strong> ₱${Number(currentItem.price).toLocaleString()}</p>`;
      } else {
        html += `<p><strong>Event / Pencil booking</strong></p>`;
        html += `<p><strong>Date:</strong> ${escapeHtml(currentBooking.pencil_date || '')}</p>`;
        html += `<p><strong>Time:</strong> ${escapeHtml((currentBooking.time_from || '') + ' - ' + (currentBooking.time_to || ''))}</p>`;
        html += `<p><strong>Base Fee:</strong> ₱${Number(currentItem.price).toLocaleString()}</p>`;
      }

      // Add discount preview
      const discountType = document.getElementById('discount_type');
      const discountDetails = document.getElementById('discount_details');
      if (discountType && discountType.value) {
        html += `<p><strong>Discount:</strong> ${escapeHtml(discountType.options[discountType.selectedIndex].text)}</p>`;
        if (discountDetails && discountDetails.value) {
          html += `<p><strong>Details:</strong> ${escapeHtml(discountDetails.value)}</p>`;
        }
      }

      html += '</div>';
      html += '<div class="col-4 text-end">';
      if (currentItem.image) {
        let imgSrc = currentItem.image;
        if (!imgSrc.startsWith('http') && !imgSrc.startsWith('/')) {
          imgSrc = '/' + imgSrc;
        }
        html += `<img src="${escapeHtml(imgSrc)}" style="max-width:120px;border-radius:6px;object-fit:cover;">`;
      }
      html += '</div>';
      html += '</div>';
      return html;
    }

    function escapeHtml(s) {
      if (!s && s !== 0) return '';
      return String(s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": "&#39;" }[c]; });
    }

    // Build printable booking details HTML
    function buildPrintableBookingDetails() {
      if (!currentBooking || !currentItem) return '';

      let html = '';
      // Add receipt number at the top for PDF generation reference
      if (window.lastBookingReceiptNumber) {
        html += `<p class="receipt-number" data-receipt="${escapeHtml(window.lastBookingReceiptNumber)}"><strong>Receipt Number:</strong> ${escapeHtml(window.lastBookingReceiptNumber)}</p>`;
      }
      html += `<p><strong>Room/Facility:</strong> ${escapeHtml(currentItem.name)}`;
      if (currentItem.room_number) html += ` (Room #${escapeHtml(currentItem.room_number)})`;
      html += '</p>';

      if (currentBooking.type === 'reservation') {
        html += `<p><strong>Check-in:</strong> ${escapeHtml(currentBooking.checkin)}</p>`;
        html += `<p><strong>Check-out:</strong> ${escapeHtml(currentBooking.checkout)}</p>`;
        html += `<p><strong>Occupants:</strong> ${escapeHtml(currentBooking.occupants)}</p>`;
        const nights = calcNights(currentBooking.checkin, currentBooking.checkout) || 1;
        html += `<p><strong>Nights:</strong> ${nights}</p>`;
      }

      html += `<p><strong>Guest Name:</strong> ${escapeHtml(currentBooking.guest_name)}</p>`;
      html += `<p><strong>Contact:</strong> ${escapeHtml(currentBooking.contact)}</p>`;
      html += `<p><strong>Email:</strong> ${escapeHtml(currentBooking.email)}</p>`;

      // Add selected add-ons
      const selectedAddons = [];
      document.querySelectorAll('.addon-checkbox:checked').forEach(cb => {
        const label = document.querySelector(`label[for="${cb.id}"]`);
        if (label) selectedAddons.push(label.textContent.trim());
      });

      if (selectedAddons.length > 0) {
        html += '<p><strong>Add-ons:</strong></p><ul style="margin-left: 20px;">';
        selectedAddons.forEach(addon => {
          html += `<li>${escapeHtml(addon)}</li>`;
        });
        html += '</ul>';
      }

      html += `<p><strong>Total Amount:</strong> ₱${recalcTotal().toLocaleString()}</p>`;
      html += `<p class="text-muted" style="margin-top: 15px;"><small>Booking Date: ${new Date().toLocaleString()}</small></p>`;

      return html;
    }

    // Generate QR Code for bank transfer details
    function generateBankQRCode() {
      try {
        const qrContainer = document.getElementById('bank_qr_code');
        if (!qrContainer) return;

        // Clear any existing QR code
        qrContainer.innerHTML = '';

        // Bank transfer details to encode
        const bankDetails = {
          accountName: 'La Consolacion University Philippines',
          accountNumber: '575-7-575007089',
          branch: 'Malolos Mc Arthur',
          bank: 'BDO/BPI/GCash'
        };

        // Create formatted text for QR code
        const qrText = `Bank Transfer Payment\n\nAccount Name: ${bankDetails.accountName}\nAccount Number: ${bankDetails.accountNumber}\nBranch: ${bankDetails.branch}\nBank: ${bankDetails.bank}\n\nBarCIE International Center`;

        // Check if QRCode library is available
        if (typeof QRCode !== 'undefined') {
          new QRCode(qrContainer, {
            text: qrText,
            width: 180,
            height: 180,
            colorDark: '#000000',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.H
          });
        } else {
          // Fallback: use API to generate QR code image
          const qrApiUrl = `https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=${encodeURIComponent(qrText)}`;
          const img = document.createElement('img');
          img.src = qrApiUrl;
          img.alt = 'Bank Transfer QR Code';
          img.style.width = '180px';
          img.style.height = '180px';
          qrContainer.appendChild(img);
        }
      } catch (err) {
        console.error('Failed to generate QR code:', err);
      }
    }

    // Show modal, render preview & addons
    async function showPreviewModal(form, bookingData) {
      currentForm = form;
      currentBooking = bookingData;

      // If a discount type is selected but no proof file is attached, do not show the modal.
      try {
        const discountTypeField = currentForm.querySelector('[name="discount_type"]');
        const proofField = currentForm.querySelector('[name="discount_proof"]');
        const hasDiscountSelected = !!(discountTypeField && discountTypeField.value && discountTypeField.value !== '' && discountTypeField.value !== 'none');
        if (hasDiscountSelected) {
          const hasFile = proofField && proofField.files && proofField.files.length > 0;
          if (!hasFile) {
            // Show inline alert in the discount card (if available) instead of native alert()
            const msg = 'You selected a discount but did not attach a proof image/file. Please attach your ID/proof or deselect the discount to proceed.';
            // Prefer showing the alert right after the proof asterisk if available
            let proofAlert = null;
            try { proofAlert = (currentForm && currentForm.querySelector('#discount_proof_alert')) || document.getElementById('discount_proof_alert'); } catch (e) { proofAlert = null; }
            let discountInfo = proofAlert || document.getElementById('discount_info_text') || (currentForm && currentForm.querySelector('#discount_info_text'));
            if (discountInfo) {
              // If using the small inline spot (proofAlert), wrap message in small markup to avoid large blocks
              if (proofAlert === discountInfo) {
                discountInfo.innerHTML = '<small class="text-danger fw-semibold" style="display:inline-block; margin-left:6px;">' + msg + '</small>';
                try { discountInfo.scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch (e) { /* ignore */ }
                setTimeout(() => { try { discountInfo.innerHTML = ''; } catch (e) { } }, 8000);
              } else {
                discountInfo.innerHTML = '<div class="alert alert-danger mb-0">' + msg + '</div>';
                discountInfo.style.display = 'block';
                try { discountInfo.scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch (e) { /* ignore */ }
                setTimeout(() => { try { discountInfo.style.display = 'none'; } catch (e) { } }, 8000);
              }
            } else {
              try { notify(msg, 'error'); } catch (e) { /* ignore */ }
            }
            // Focus the proof input if available
            try { if (proofField) proofField.focus(); } catch (e) { /* ignore */ }
            return;
          }
        }
      } catch (e) { /* ignore any DOM errors and proceed */ }

      // fetch item data
      currentItem = await fetchItemById(bookingData.room_id);
      if (!currentItem) {
        previewDetails.innerHTML = '<div class="alert alert-danger">Unable to load room/facility details. Please try again.</div>';
        return;
      }

      previewDetails.innerHTML = buildPreviewHtml();
      renderAddons();
      recalcTotal();

      // initialize modal payment method from the original form if present
      try {
        const modalPay = document.getElementById('modal_payment_method');
        const modalBank = document.getElementById('modal_bank_details');
        const modalPaymentProof = document.getElementById('modal_payment_proof');
        if (modalPay) {
          const orig = currentForm.querySelector('[name="payment_method"]');
          if (orig) modalPay.value = orig.value || 'cash';
          modalPay.addEventListener('change', () => {
            if (modalBank) {
              modalBank.style.display = (modalPay.value === 'bank') ? 'block' : 'none';
              // Generate QR code when bank transfer is selected
              if (modalPay.value === 'bank') {
                generateBankQRCode();
              }
            }
            const proofWrap = document.getElementById('modal_payment_proof_wrap');
            if (proofWrap) proofWrap.style.display = (modalPay.value === 'bank') ? 'block' : 'none';
            if (modalPaymentProof) {
              modalPaymentProof.required = (modalPay.value === 'bank');
              if (modalPay.value !== 'bank') {
                modalPaymentProof.classList.remove('is-invalid');
              }
            }
          });
          // set initial visibility
          if (modalBank) modalBank.style.display = (modalPay.value === 'bank') ? 'block' : 'none';
          const proofWrapInit = document.getElementById('modal_payment_proof_wrap');
          if (proofWrapInit) proofWrapInit.style.display = (modalPay.value === 'bank') ? 'block' : 'none';
          if (modalPaymentProof) {
            modalPaymentProof.required = (modalPay.value === 'bank');
            if (modalPay.value !== 'bank') {
              modalPaymentProof.classList.remove('is-invalid');
            }
          }

          // Generate QR code if bank is initially selected
          if (modalPay.value === 'bank') {
            generateBankQRCode();
          }
        }
      } catch (err) { /* ignore */ }

      // show bootstrap modal
      const modal = new bootstrap.Modal(modalEl);
      modal.show();
    }

    function collectReservationBookingData(form, options = {}) {
      const bookingData = {
        type: options.type || 'reservation',
        room_id: form.querySelector('[name="room_id"]')?.value || '',
        guest_name: form.querySelector('[name="guest_name"]')?.value || '',
        contact: form.querySelector('[name="contact_number"]')?.value || '',
        email: form.querySelector('[name="email"]')?.value || '',
        checkin: form.querySelector('[name="checkin"]')?.value || '',
        checkout: form.querySelector('[name="checkout"]')?.value || '',
        occupants: form.querySelector('[name="occupants"]')?.value || ''
      };

      if (options.originalType) {
        bookingData._originalType = options.originalType;
      }

      return bookingData;
    }

    function openPreviewFromForm(form, options = {}) {
      if (!form) return;

      const invalid = validateFormInline(form);
      if (invalid) {
        showInlineAlert(invalid.field, invalid.message);
        return;
      }

      const bookingData = collectReservationBookingData(form, options);
      showPreviewModal(form, bookingData);
    }

    // Attach to reservation & pencil form submit
    function attachInterceptors() {
      const reservationForm = document.getElementById('reservationForm');
      const pencilForm = document.getElementById('pencilForm');

      if (reservationForm) {
        reservationForm.addEventListener('submit', function (e) {
          e.preventDefault();
          openPreviewFromForm(e.target);
        });
      }

      // Attach field listeners so inline errors clear as user types
      try { attachFieldListeners(reservationForm); } catch (e) { }

      // Also attach to the review button (button is type=button and does not submit by default)
      const reviewBookingBtn = document.getElementById('reviewBookingBtn') || document.getElementById('reservationSubmitBtn');
      if (reviewBookingBtn) {
        reviewBookingBtn.addEventListener('click', function (e) {
          e.preventDefault();
          openPreviewFromForm(document.getElementById('reservationForm'));
        });
      }

      if (pencilForm) {
        pencilForm.addEventListener('submit', function (e) {
          e.preventDefault();
          openPreviewFromForm(e.target, {
            type: 'reservation',
            originalType: 'pencil'
          });
        });
      }

      try { attachFieldListeners(pencilForm); } catch (e) { }

      // Pencil booking direct submission (bypasses modal)
      const pencilBtn = document.getElementById('pencilSubmitBtn');
      if (pencilBtn) {
        pencilBtn.addEventListener('click', async function (e) {
          e.preventDefault();
          const form = document.getElementById('pencilForm');
          if (!form) return;

          // Validate form first
          const invalid = validateFormInline(form);
          if (invalid) {
            showInlineAlert(invalid.field, invalid.message);
            return;
          }

          // Disable button and show loading
          pencilBtn.disabled = true;
          pencilBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';

          try {
            const formData = new FormData(form);

            // Send the form data directly - use dynamic base path
            const response = await fetch((BASE_PATH || '') + '/database/index.php?endpoint=user_auth', {
              method: 'POST',
              body: formData,
              credentials: 'same-origin',
              headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
              }
            });

            const result = await response.json();

            if (result.success) {
              // Store receipt number for PDF generation
              if (result.receipt_no) {
                window.lastBookingReceiptNumber = result.receipt_no;
                window.lastBookingType = 'pencil_booking';

                // Update the form input with actual receipt number
                const pencilReceiptInput = document.getElementById('pencil_receipt_no');
                if (pencilReceiptInput) {
                  pencilReceiptInput.value = result.receipt_no;
                }
              }

              // Show success modal with receipt number
              showPencilSuccessModal(result.message || 'Draft reservation submitted successfully!', result.receipt_no);

              // Clear the form but preserve receipt number
              const currentReceiptNo = result.receipt_no;
              form.reset();
              if (currentReceiptNo) {
                const pencilReceiptInput = document.getElementById('pencil_receipt_no');
                if (pencilReceiptInput) {
                  pencilReceiptInput.value = currentReceiptNo;
                }
              }

              // Hide the form and show reservation form
              setTimeout(() => {
                document.getElementById('pencilForm').style.display = 'none';
                document.getElementById('reservationForm').style.display = 'block';
                const reservationToggle = document.getElementById('reservationToggle');
                if (reservationToggle) reservationToggle.checked = true;
              }, 2000);
            } else {
              notify(result.message || 'Failed to submit draft reservation. Please try again.', 'error');
            }
          } catch (error) {
            console.error('Submission error:', error);
            notify('An error occurred. Please try again.', 'error');
          } finally {
            // Re-enable button
            pencilBtn.disabled = false;
            pencilBtn.innerHTML = '<i class="fas fa-edit me-2"></i>Submit Draft Reservation';
            // Re-check terms checkbox state
            const termsCheckbox = document.getElementById('pencil_terms_checkbox');
            if (termsCheckbox && !termsCheckbox.checked) {
              pencilBtn.disabled = true;
            }
          }
        });
      }

      // NOTE: The pencil success modal implementation has been moved to
      // `components/guest/sections/pencil_booking.php` to centralize the UI.
      // Call `showPencilSuccessModal(message)` here; implementation lives in pencil_booking.php.
    }

    // Enable/disable confirm button based on both policy checkboxes
    const policyCheckbox = document.getElementById('policyAgreementCheckbox');
    const doubleConfirmCheckbox = document.getElementById('doubleConfirmCheckbox');
    const policySection = document.getElementById('policyAgreementSection');

    function updateConfirmButtonState() {
      if (confirmBtn && policyCheckbox && doubleConfirmCheckbox) {
        confirmBtn.disabled = !(policyCheckbox.checked && doubleConfirmCheckbox.checked);
      }
    }

    if (policyCheckbox && confirmBtn) {
      policyCheckbox.addEventListener('change', updateConfirmButtonState);
    }
    if (doubleConfirmCheckbox && confirmBtn) {
      doubleConfirmCheckbox.addEventListener('change', updateConfirmButtonState);
    }

    // Handle "Read More" policy link - show full terms in a modal
    const readMoreLink = document.getElementById('readMorePolicyLink');
    if (readMoreLink) {
      readMoreLink.addEventListener('click', function (e) {
        e.preventDefault();

        // Hide the main confirmation modal
        const mainModal = bootstrap.Modal.getInstance(modalEl);
        if (mainModal) mainModal.hide();

        // Show the policy terms modal
        const policyModal = new bootstrap.Modal(document.getElementById('fullPolicyModal'));
        policyModal.show();
      });
    }

    // Handle return from policy modal
    const backToPolicyBtn = document.getElementById('backToPolicyBtn');
    if (backToPolicyBtn) {
      backToPolicyBtn.addEventListener('click', function () {
        // Hide policy modal
        const policyModal = bootstrap.Modal.getInstance(document.getElementById('fullPolicyModal'));
        if (policyModal) policyModal.hide();

        // Show main confirmation modal again
        const mainModal = new bootstrap.Modal(modalEl);
        mainModal.show();
      });
    }

    // Shake animation for validation failure
    function shakeElement(element) {
      if (!element) return;
      element.style.animation = 'shake 0.5s';
      setTimeout(() => { element.style.animation = ''; }, 500);
    }

    // On confirm, append addon fields to currentForm then submit
    if (confirmBtn) {
      confirmBtn.addEventListener('click', function () {
        // Double-check both policy checkboxes before proceeding
        if (policyCheckbox && !policyCheckbox.checked) {
          notify('Please agree to the non-refundable payment policy to proceed.', 'warning');
          shakeElement(policySection);
          try { policyCheckbox.focus(); policySection.scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch (e) { }
          return;
        }
        if (doubleConfirmCheckbox && !doubleConfirmCheckbox.checked) {
          notify('Please confirm that you understand the non-refundable policy.', 'warning');
          shakeElement(policySection);
          try { doubleConfirmCheckbox.focus(); policySection.scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch (e) { }
          return;
        }
        if (!currentForm) return;

        // Bank transfer requires uploaded payment receipt before proceeding.
        const modalPayMethod = document.getElementById('modal_payment_method');
        const modalPaymentProof = document.getElementById('modal_payment_proof');
        if (modalPaymentProof) {
          modalPaymentProof.classList.remove('is-invalid');
        }

        if (modalPayMethod && modalPayMethod.value === 'bank') {
          const proofFile = (modalPaymentProof && modalPaymentProof.files && modalPaymentProof.files.length > 0)
            ? modalPaymentProof.files[0]
            : null;

          if (!proofFile) {
            notify('Please upload proof of payment for Bank Transfer before confirming.', 'warning');
            if (modalPaymentProof) {
              modalPaymentProof.classList.add('is-invalid');
              try { modalPaymentProof.focus(); } catch (e) { }
            }
            return;
          }

          if (proofFile.size > MAX_UPLOAD_BYTES) {
            notify('Payment receipt exceeds ' + MAX_UPLOAD_MB + 'MB. Please upload a smaller file.', 'warning');
            if (modalPaymentProof) {
              modalPaymentProof.classList.add('is-invalid');
              try { modalPaymentProof.focus(); } catch (e) { }
            }
            return;
          }
        }

        // remove previous addon inputs
        currentForm.querySelectorAll('[data-addon-input]').forEach(n => n.remove());

        // add selected addons as hidden inputs
        document.querySelectorAll('.addon-checkbox').forEach(cb => {
          if (cb.checked) {
            const qtyInput = cb.closest('.form-check').querySelector('.addon-qty');
            const qty = qtyInput ? Math.max(1, parseInt(qtyInput.value || '1')) : 1;
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'addons[' + cb.value + ']';
            input.value = qty;
            input.setAttribute('data-addon-input', '1');
            currentForm.appendChild(input);
          }
        });

        // Also add computed total for convenience
        const totalInput = document.createElement('input');
        totalInput.type = 'hidden';
        totalInput.name = 'computed_total';
        totalInput.value = recalcTotal();
        totalInput.setAttribute('data-addon-input', '1');
        currentForm.appendChild(totalInput);

        // Add policy agreement timestamp and version for legal records
        const policyTimestamp = document.createElement('input');
        policyTimestamp.type = 'hidden';
        policyTimestamp.name = 'policy_agreed_at';
        policyTimestamp.value = new Date().toISOString();
        policyTimestamp.setAttribute('data-addon-input', '1');
        currentForm.appendChild(policyTimestamp);

        const policyVersion = document.createElement('input');
        policyVersion.type = 'hidden';
        policyVersion.name = 'policy_version';
        policyVersion.value = 'v1.0';
        policyVersion.setAttribute('data-addon-input', '1');
        currentForm.appendChild(policyVersion);

        // Copy payment selection from modal into form as a hidden input
        try {
          const modalPay = document.getElementById('modal_payment_method');
          if (modalPay) {
            // remove old if present
            currentForm.querySelectorAll('[name="payment_method"]').forEach(n => n.remove());
            const payInput = document.createElement('input');
            payInput.type = 'hidden';
            payInput.name = 'payment_method';
            payInput.value = modalPay.value || 'cash';
            payInput.setAttribute('data-addon-input', '1');
            currentForm.appendChild(payInput);
          }
        } catch (err) { /* ignore */ }

        // CRITICAL: Remove focus from modal elements BEFORE closing to prevent aria-hidden warning
        try {
          const active = document.activeElement;
          if (active && modalEl.contains(active)) {
            active.blur();
            // Move focus to safe element outside modal
            document.body.focus();
          }
        } catch (err) { /* ignore */ }

        // Close modal and submit
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();

        // Submit via fetch so we can control post-submit behavior and redirect back to booking
        (async function submitForm() {
          try {
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = 'Processing...';

            // If user selected a discount but did not attach a proof image/file,
            // automatically clear the discount selection so the booking can proceed.
            try {
              const discountTypeField = currentForm.querySelector('[name="discount_type"]');
              const discountDetailsField = currentForm.querySelector('[name="discount_details"]');
              const proofField = currentForm.querySelector('[name="discount_proof"]');
              if (discountTypeField && discountTypeField.value) {
                const hasFile = proofField && proofField.files && proofField.files.length > 0;
                if (!hasFile) {
                  // Clear discount selection and details so server treats it as no discount
                  discountTypeField.value = '';
                  if (discountDetailsField) discountDetailsField.value = '';
                  // If proofField exists, clear it to avoid sending empty file
                  if (proofField) try { proofField.value = ''; proofField.required = false; } catch (e) { /* ignore */ }
                  // Inform user briefly using inline alert if available
                  const infoMsg = 'No discount proof attached — your booking will proceed without a discount.';
                  try {
                    // Prefer the compact inline spot next to the asterisk if available
                    let proofAlert = null;
                    try { proofAlert = (currentForm && currentForm.querySelector('#discount_proof_alert')) || document.getElementById('discount_proof_alert'); } catch (e) { proofAlert = null; }
                    let discountInfo = proofAlert || document.getElementById('discount_info_text') || (currentForm && currentForm.querySelector('#discount_info_text'));
                    if (discountInfo) {
                      if (proofAlert === discountInfo) {
                        discountInfo.innerHTML = '<small class="text-info" style="display:inline-block; margin-left:6px;">' + infoMsg + '</small>';
                        setTimeout(() => { try { discountInfo.innerHTML = ''; } catch (e) { } }, 6000);
                      } else {
                        discountInfo.innerHTML = '<div class="alert alert-info mb-0">' + infoMsg + '</div>';
                        discountInfo.style.display = 'block';
                        setTimeout(() => { try { discountInfo.style.display = 'none'; } catch (e) { } }, 6000);
                      }
                    } else {
                      try { notify(infoMsg, 'info'); } catch (e) { /* ignore */ }
                    }
                  } catch (e) { /* ignore */ }
                }
              }
            } catch (e) { /* ignore errors */ }

            const fd = new FormData(currentForm);
            // If user attached a payment proof in the modal, append it to the form data
            try {
              const modalPaymentProof = document.getElementById('modal_payment_proof');
              if (modalPaymentProof && modalPaymentProof.files && modalPaymentProof.files.length > 0) {
                fd.append('payment_proof', modalPaymentProof.files[0]);
              }
            } catch (e) { /* ignore */ }

            // Collect selected add-ons data and append as JSON
            try {
              const selectedAddons = [];
              document.querySelectorAll('.addon-checkbox:checked').forEach(cb => {
                const label = document.querySelector(`label[for="${cb.id}"]`);
                const priceElement = cb.closest('.form-check')?.querySelector('.text-success, .fw-bold');
                if (label) {
                  const addonName = label.textContent.trim();
                  const priceMatch = priceElement?.textContent.match(/[\d,]+(?:\.\d{2})?/);
                  const price = priceMatch ? parseFloat(priceMatch[0].replace(/,/g, '')) : 0;
                  selectedAddons.push({ name: addonName, price: price });
                }
              });
              if (selectedAddons.length > 0) {
                fd.append('add_ons', JSON.stringify(selectedAddons));
              }
            } catch (e) { console.error('Error collecting add-ons:', e); }

            // CRITICAL: Add the action field if not present (required by user_auth.php)
            if (!fd.has('action')) {
              fd.append('action', 'create_booking');
            }

            // Get the form action URL properly - use dynamic base path
            const actionAttr = currentForm.getAttribute('action');
            // Use form action if specified, otherwise default to dynamic path
            const targetUrl = actionAttr || ((BASE_PATH || '') + '/database/index.php?endpoint=user_auth');

            console.debug('Submitting booking to', targetUrl);

            // DEBUG: Log FormData to see what's being sent
            console.group('📝 Booking Submission Debug');
            console.log('Form ID:', currentForm.id);
            console.log('Target URL:', targetUrl);
            console.log('FormData contents:');
            for (let [key, value] of fd.entries()) {
              if (value instanceof File) {
                console.log(`  ${key}: [File] ${value.name} (${value.size} bytes)`);
              } else {
                console.log(`  ${key}:`, value);
              }
            }
            const roomIdElement = currentForm.querySelector('[name="room_id"]');
            console.log('room_id element:', roomIdElement);
            console.log('room_id value:', roomIdElement?.value);
            console.groupEnd();

            // If the form includes an uploaded discount proof file, prefer XHR so we can show upload progress
            const proofInput = currentForm.querySelector('#discount_proof');
            const discountProof = fd.get('discount_proof');
            const paymentProof = fd.get('payment_proof');
            const hasProof = (discountProof && typeof discountProof.size !== 'undefined' && discountProof.size > 0) || (paymentProof && typeof paymentProof.size !== 'undefined' && paymentProof.size > 0);

            if (hasProof) {
              // If discount proof exists, validate it using existing dataset flags
              if (discountProof && discountProof.size > 0) {
                if (proofInput && proofInput.dataset && proofInput.dataset.validProof !== '1') {
                  notify('Uploaded proof did not pass preliminary validation: ' + (proofInput.dataset.validReason || 'Please upload a valid ID/proof for the selected discount.'), 'error');
                  confirmBtn.disabled = false;
                  confirmBtn.innerHTML = 'Confirm & Proceed';
                  return;
                }
              }

              // Use XMLHttpRequest to get upload progress events
              await new Promise((resolve) => {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', targetUrl, true);
                xhr.withCredentials = true;
                // Indicate AJAX so server-side can detect if needed
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.setRequestHeader('Accept', 'application/json');

                // Find progress UI elements in the page (guest form preview area)
                const progressWrap = document.getElementById('discount_upload_progress');
                const progressBar = progressWrap ? progressWrap.querySelector('.progress-bar') : null;
                const cancelBtn = document.getElementById('discount_upload_cancel');
                const uploadControls = document.getElementById('discount_upload_controls');

                xhr.upload.onprogress = function (e) {
                  if (progressWrap) progressWrap.style.display = 'block';
                  if (uploadControls) uploadControls.style.display = 'block';
                  if (cancelBtn) cancelBtn.style.display = 'inline-block';
                  if (e.lengthComputable && progressBar) {
                    const pct = Math.round((e.loaded / e.total) * 100);
                    progressBar.style.width = pct + '%';
                    progressBar.textContent = pct + '%';
                    confirmBtn.innerHTML = 'Uploading... ' + pct + '%';
                  } else if (progressBar) {
                    progressBar.style.width = '10%';
                    progressBar.textContent = 'Uploading...';
                    confirmBtn.innerHTML = 'Uploading...';
                  }
                };

                // Expose current upload XHR so cancel button can abort
                window.currentBookingUploadXhr = xhr;

                // Wire cancel button if available
                if (cancelBtn) {
                  cancelBtn.onclick = function () {
                    if (window.currentBookingUploadXhr) {
                      window.currentBookingUploadXhr.abort();
                      window.currentBookingUploadXhr = null;
                      if (progressBar) { progressBar.style.width = '0%'; progressBar.textContent = 'Cancelled'; }
                      if (confirmBtn) { confirmBtn.disabled = false; confirmBtn.innerHTML = 'Confirm & Proceed'; }
                      if (uploadControls) uploadControls.style.display = 'none';
                      notify('Upload cancelled. You can choose a different file or try again.', 'info');
                    }
                  };
                }

                xhr.onreadystatechange = function () {
                  if (xhr.readyState !== 4) return;
                  // Hide cancel once done
                  if (cancelBtn) cancelBtn.style.display = 'none';
                  if (progressWrap) progressWrap.style.display = 'none';
                  if (uploadControls) uploadControls.style.display = 'none';
                  window.currentBookingUploadXhr = null;

                  let jsonResponse = null;
                  try {
                    const contentType = (xhr.getResponseHeader('Content-Type') || '').toLowerCase();
                    if (contentType.includes('application/json')) jsonResponse = JSON.parse(xhr.responseText);
                    else jsonResponse = { success: false, message: 'Server returned non-JSON response', _raw: xhr.responseText };
                  } catch (e) {
                    jsonResponse = null;
                  }

                  if (xhr.status >= 200 && xhr.status < 300 && jsonResponse && jsonResponse.success) {
                    // Extract receipt number from response message
                    const receiptMatch = (jsonResponse.message || '').match(/receipt number:?\s*([A-Z0-9\-]+)/i);
                    if (receiptMatch) {
                      window.lastBookingReceiptNumber = receiptMatch[1];
                      window.lastBookingType = currentBooking.type || 'reservation';
                    }

                    // Build booking details HTML for print
                    let bookingDetailsHtml = buildPrintableBookingDetails();

                    // Show success modal instead of toast
                    showSuccessModal(jsonResponse.message || 'Booking submitted successfully!', bookingDetailsHtml);
                    showBookingConfirmation(jsonResponse.message || 'Booking submitted successfully!');
                    resolve();
                    return;
                  }

                  const errorMsg = (jsonResponse && (jsonResponse.message || jsonResponse.error)) || 'Booking submission failed. Please try again.';
                  notify(errorMsg, 'error');
                  confirmBtn.disabled = false;
                  confirmBtn.innerHTML = 'Confirm & Proceed';
                  resolve();
                };

                xhr.onerror = function () {
                  if (progressBar) { progressBar.style.width = '0%'; progressBar.textContent = 'Error'; }
                  notify('An error occurred uploading the file. Please check your connection and try again.', 'error');
                  confirmBtn.disabled = false;
                  confirmBtn.innerHTML = 'Confirm & Proceed';
                  window.currentBookingUploadXhr = null;
                  if (uploadControls) uploadControls.style.display = 'none';
                  resolve();
                };

                xhr.send(fd);
              });

              return;
            }

            // No file upload or proof - use fetch as before
            const res = await fetch(targetUrl, {
              method: 'POST',
              body: fd,
              credentials: 'same-origin',
              headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest' // Ensure server detects this as AJAX
              }
            });

            console.debug('Response status:', res.status, 'Content-Type:', res.headers.get('content-type'));

            // Parse response safely. If response is not JSON, capture body text for debugging.
            let jsonResponse = null;
            try {
              const contentType = (res.headers.get('content-type') || '').toLowerCase();
              if (contentType.includes('application/json')) {
                jsonResponse = await res.json();
                console.debug('JSON response:', jsonResponse);
              } else {
                // Non-JSON response (likely HTML error page). Read text for diagnostics.
                const text = await res.text();
                console.warn('Non-JSON response body:', text);
                // Attach the raw text to a simple object so downstream code can show it
                jsonResponse = { success: false, message: 'Server returned non-JSON response', _raw: text };
              }
            } catch (e) {
              console.warn('Failed to parse response:', e);
              jsonResponse = null;
            }

            // Check if submission was successful
            if (res.ok && jsonResponse && jsonResponse.success) {
              // Success - show message and redirect
              console.log('Booking successful:', jsonResponse.message);

              // Build booking details HTML for print
              let bookingDetailsHtml = buildPrintableBookingDetails();

              // Show success modal instead of toast
              showSuccessModal(jsonResponse.message || 'Booking submitted successfully!', bookingDetailsHtml);
              showBookingConfirmation(jsonResponse.message || 'Booking submitted successfully!');
              return;
            }

            // Handle error response
            const errorMsg = jsonResponse?.message || jsonResponse?.error || 'Booking submission failed. Please try again.';
            console.error('Booking failed:', errorMsg);
            notify(errorMsg, 'error');
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = 'Confirm & Proceed';
            return;

          } catch (err) {
            console.error('Submit error', err);
            notify('An error occurred while submitting your booking. Please open the browser console for details.', 'error');
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = 'Confirm & Proceed';
          }
        })();
      });
    }

    // initialize: attach immediately if DOM already loaded, otherwise wait
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', attachInterceptors);
    } else {
      // already loaded
      attachInterceptors();
    }

  })();
</script>

<!-- Minimal styles for modal elements -->
<style>
  #previewDetails p {
    margin: 4px 0;
  }

  @keyframes shake {

    0%,
    100% {
      transform: translateX(0);
    }

    10%,
    30%,
    50%,
    70%,
    90% {
      transform: translateX(-5px);
    }

    20%,
    40%,
    60%,
    80% {
      transform: translateX(5px);
    }
  }

  #policyAgreementSection {
    transition: all 0.3s ease;
  }

  #policyAgreementSection:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  }

  #readMorePolicyLink {
    color: #0066cc;
    font-weight: 600;
    text-decoration: underline;
    transition: all 0.2s ease;
  }

  #readMorePolicyLink:hover {
    color: #0052a3;
    text-decoration: none;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
  }

  #readMorePolicyLink:active {
    transform: scale(0.98);
  }

  /* Full Policy Modal Styles */
  #fullPolicyModal .modal-dialog {
    max-width: 800px;
  }

  #fullPolicyModal .modal-body {
    max-height: 75vh;
    padding: 2rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
  }

  #fullPolicyModal .modal-header {
    border-bottom: 3px solid #ffc107;
    padding: 1.5rem;
  }

  #fullPolicyModal .modal-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #333;
  }

  #fullPolicyModal .policy-header-image {
    animation: fadeInDown 0.5s ease-in-out;
  }

  @keyframes fadeInDown {
    from {
      opacity: 0;
      transform: translateY(-20px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  #fullPolicyModal .policy-section {
    padding: 20px 25px;
    border-left: 5px solid #e0e0e0;
    background: white;
    border-radius: 8px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    animation: fadeInUp 0.4s ease-in-out;
    animation-fill-mode: both;
  }

  @keyframes fadeInUp {
    from {
      opacity: 0;
      transform: translateY(20px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  /* Stagger animation for each section */
  #fullPolicyModal .policy-section:nth-child(2) {
    animation-delay: 0.05s;
  }

  #fullPolicyModal .policy-section:nth-child(3) {
    animation-delay: 0.1s;
  }

  #fullPolicyModal .policy-section:nth-child(4) {
    animation-delay: 0.15s;
  }

  #fullPolicyModal .policy-section:nth-child(5) {
    animation-delay: 0.2s;
  }

  #fullPolicyModal .policy-section:nth-child(6) {
    animation-delay: 0.25s;
  }

  #fullPolicyModal .policy-section:nth-child(7) {
    animation-delay: 0.3s;
  }

  #fullPolicyModal .policy-section:nth-child(8) {
    animation-delay: 0.35s;
  }

  #fullPolicyModal .policy-section:nth-child(9) {
    animation-delay: 0.4s;
  }

  #fullPolicyModal .policy-section:hover {
    border-left-color: #0066cc;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateX(5px);
  }

  #fullPolicyModal .policy-section h5 {
    margin-bottom: 12px;
    font-size: 1.15rem;
    font-weight: 700;
    display: flex;
    align-items: center;
  }

  #fullPolicyModal .policy-section h5 i {
    font-size: 1.3rem;
    width: 30px;
  }

  #fullPolicyModal .policy-section p {
    margin-bottom: 0;
    line-height: 1.7;
    font-size: 0.95rem;
    color: #555;
  }

  #fullPolicyModal .alert-warning {
    border-left: 5px solid #ffc107;
    background-color: #fff3cd;
    border-radius: 8px;
    padding: 1rem 1.25rem;
    animation: fadeInUp 0.5s ease-in-out 0.45s both;
  }

  #fullPolicyModal .modal-footer {
    border-top: 2px solid #e9ecef;
    padding: 1.25rem 1.5rem;
  }

  #fullPolicyModal #backToPolicyBtn {
    padding: 0.625rem 1.5rem;
    font-weight: 600;
    font-size: 1rem;
    border-radius: 6px;
    transition: all 0.2s ease;
  }

  #fullPolicyModal #backToPolicyBtn:hover {
    transform: translateX(-3px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
  }

  /* Mobile responsive adjustments */
  @media (max-width: 768px) {
    #fullPolicyModal .modal-body {
      padding: 1.25rem;
    }

    #fullPolicyModal .policy-section {
      padding: 15px 18px;
    }

    #fullPolicyModal .policy-section h5 {
      font-size: 1rem;
    }
  }

  /* Success Modal Styles */
  #bookingSuccessModal .success-icon {
    animation: successPulse 1s ease-in-out;
  }

  @keyframes successPulse {
    0% {
      transform: scale(0);
      opacity: 0;
    }

    50% {
      transform: scale(1.1);
    }

    100% {
      transform: scale(1);
      opacity: 1;
    }
  }

  #bookingSuccessModal .modal-content {
    border-radius: 12px;
    overflow: hidden;
  }

  #bookingSuccessModal .modal-header {
    border-bottom: none;
  }

  #bookingSuccessModal h4 {
    font-weight: 600;
    animation: fadeInUp 0.5s ease-in-out 0.3s both;
  }

  #bookingSuccessModal .alert-info {
    animation: fadeInUp 0.5s ease-in-out 0.5s both;
  }

  #bookingDetailsForPrint {
    background-color: #f8f9fa;
    animation: fadeInUp 0.5s ease-in-out 0.4s both;
  }

  #bookingDetailsForPrint p {
    margin: 8px 0;
    line-height: 1.6;
  }

  #doneBookingBtn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
  }

  #printBookingBtn {
    transition: all 0.3s ease;
  }

  #printBookingBtn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
  }

  #doneBookingBtn {
    min-width: 120px;
    transition: all 0.3s ease;
  }

  #doneBookingBtn:not(:disabled):hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
  }

  /* Enhanced Print Buttons Styling */
  .print-buttons-container {
    gap: 12px;
    flex-wrap: wrap;
  }

  .elegant-pdf-btn {
    position: relative;
    background: linear-gradient(135deg, #1e40af, #3730a3);
    border-color: #1e40af;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-size: 0.875rem;
    padding: 10px 20px;
    transition: all 0.3s ease;
    overflow: hidden;
  }

  .elegant-pdf-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s ease;
  }

  .elegant-pdf-btn:hover {
    background: linear-gradient(135deg, #1e3a8a, #312e81);
    border-color: #1e3a8a;
    transform: translateY(-3px);
    box-shadow: 0 12px 25px rgba(30, 64, 175, 0.4);
  }

  .elegant-pdf-btn:hover::before {
    left: 100%;
  }

  .elegant-pdf-btn:active {
    transform: translateY(-1px);
    box-shadow: 0 8px 15px rgba(30, 64, 175, 0.3);
  }

  .simple-print-btn {
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-size: 0.875rem;
    padding: 10px 20px;
    transition: all 0.3s ease;
    border-width: 2px;
  }

  .simple-print-btn:hover {
    background: #6b7280;
    border-color: #6b7280;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 8px 15px rgba(107, 114, 128, 0.3);
  }

  .simple-print-btn:active {
    transform: translateY(-1px);
  }

  /* Loading state for PDF button */
  .elegant-pdf-btn.loading {
    pointer-events: none;
    opacity: 0.8;
  }

  .elegant-pdf-btn.loading i {
    animation: spin 1s linear infinite;
  }

  @keyframes spin {
    0% {
      transform: rotate(0deg);
    }

    100% {
      transform: rotate(360deg);
    }
  }

  /* Responsive adjustments */
  @media (max-width: 576px) {
    .print-buttons-container {
      flex-direction: column;
      align-items: center;
    }

    .elegant-pdf-btn,
    .simple-print-btn {
      width: 100%;
      max-width: 250px;
      justify-content: center;
    }
  }
</style>
