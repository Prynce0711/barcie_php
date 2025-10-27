// cSpell:ignore barcie checkin lcuppersonnel lcupstudent LCUP
// Handle missing images gracefully
window.addEventListener('load', function () {
  document.querySelectorAll('img').forEach(img => {
    img.addEventListener('error', function () {
      if (!this.classList.contains('error-handled')) {
        this.classList.add('error-handled');
        this.src = '/assets/images/imageBg/barcie_logo.jpg';
        this.alt = 'Image not available';
        this.style.opacity = '0.7';
        console.warn('Image failed to load, using fallback:', this.dataset.originalSrc || 'unknown');
      }
    });
    if (img.src) img.dataset.originalSrc = img.src;
  });
});

// Booking & feedback form logic
(function(){
  document.addEventListener('DOMContentLoaded', function () {
    const roomSelect = document.getElementById('room_select');
    const checkinInput = document.querySelector('input[name="checkin"]');
    const checkoutInput = document.querySelector('input[name="checkout"]');
    const occupantsInput = document.querySelector('input[name="occupants"]');

    function checkAvailability() {
      if (!roomSelect || !checkinInput || !checkoutInput) return;
      const roomId = roomSelect.value;
      const checkin = checkinInput.value;
      const checkout = checkoutInput.value;
      if (roomId && checkin && checkout) {
        roomSelect.style.borderColor = '#28a745';
        if (new Date(checkin) >= new Date(checkout)) {
          checkinInput.style.borderColor = '#dc3545';
          checkoutInput.style.borderColor = '#dc3545';
        } else {
          checkinInput.style.borderColor = '#28a745';
          checkoutInput.style.borderColor = '#28a745';
        }
      }
    }

    function validateCapacity() {
      if (!roomSelect || !occupantsInput) return;
      const selectedOption = roomSelect.options[roomSelect.selectedIndex];
      if (selectedOption && occupantsInput.value) {
        const text = selectedOption.text;
        const match = text.match(/(\d+)\s+persons/);
        if (match) {
          const capacity = parseInt(match[1]);
          const occupants = parseInt(occupantsInput.value);
          if (occupants > capacity) {
            occupantsInput.style.borderColor = '#dc3545';
            occupantsInput.title = `Maximum capacity is ${capacity} persons`;
          } else {
            occupantsInput.style.borderColor = '#28a745';
            occupantsInput.title = '';
          }
        }
      }
    }

    if (roomSelect) roomSelect.addEventListener('change', checkAvailability);
    if (checkinInput) checkinInput.addEventListener('change', checkAvailability);
    if (checkoutInput) checkoutInput.addEventListener('change', checkAvailability);
    if (occupantsInput) occupantsInput.addEventListener('input', validateCapacity);

    const reservationForm = document.getElementById('reservationForm');
    const pencilForm = document.getElementById('pencilForm');

    function handleFormSubmission(form, buttonId) {
      const submitBtn = document.getElementById(buttonId);
      if (!submitBtn) return;
      const originalHtml = submitBtn.innerHTML;
      const requiredFields = form.querySelectorAll('[required]');
      let isValid = true;
      requiredFields.forEach(field => {
        if (!field.value.trim()) { field.style.borderColor = '#dc3545'; isValid = false; }
        else { field.style.borderColor = '#28a745'; }
      });
      if (!isValid) { showAlert('Please fill in all required fields.', 'danger'); return; }
      submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
      submitBtn.disabled = true;
      const formData = new FormData(form);
      const urlEncodedData = new URLSearchParams(formData).toString();
      fetch('database/user_auth.php', {
        method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' }, body: urlEncodedData
      })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          showAlert(data.message || 'Booking submitted successfully!', 'success');
          form.reset();
          form.querySelectorAll('input, select, textarea').forEach(field => field.style.borderColor = '');
        } else { throw new Error(data.error || 'Unknown error occurred'); }
      })
      .catch(err => { console.error('Error:', err); showAlert(err.message || 'Failed to submit booking. Please try again.', 'danger'); })
      .finally(() => { submitBtn.innerHTML = originalHtml; submitBtn.disabled = false; });
    }

    function showAlert(message, type = 'info') {
      const alertClass = `alert-${type}`;
      const iconClass = type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-triangle' : 'info-circle';
      const alert = document.createElement('div');
      alert.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
      alert.style.top = '20px'; alert.style.right = '20px'; alert.style.zIndex = '9999'; alert.style.maxWidth = '400px';
      alert.innerHTML = `<i class="fas fa-${iconClass} me-2"></i>${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
      document.body.appendChild(alert);
      setTimeout(() => { if (alert.parentNode) alert.remove(); }, 5000);
    }

    if (reservationForm) {
      reservationForm.addEventListener('submit', function (e) { e.preventDefault(); handleFormSubmission(this, 'reservationSubmitBtn'); });
    }
    if (pencilForm) {
      pencilForm.addEventListener('submit', function (e) { e.preventDefault(); if (!window.pencilReminder || window.pencilReminder() !== false) handleFormSubmission(this, 'pencilSubmitBtn'); });
    }

    const feedbackForm = document.getElementById('feedback-form');
    const submitFeedbackBtn = document.getElementById('submit-feedback');
    if (feedbackForm && submitFeedbackBtn) {
      feedbackForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const rating = document.getElementById('rating-value').value;
        if (!rating) { showAlert('Please select a star rating.', 'danger'); return; }
        const originalHtml = submitFeedbackBtn.innerHTML;
        submitFeedbackBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
        submitFeedbackBtn.disabled = true;
        const formData = new FormData(this);
        const urlEncodedData = new URLSearchParams(formData).toString();
        fetch('database/user_auth.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' }, body: urlEncodedData })
          .then(r => r.json())
          .then(data => {
            if (data.success) {
              showAlert(data.message || 'Feedback submitted successfully!', 'success');
              this.reset();
              document.getElementById('rating-value').value = '';
              document.querySelectorAll('.star').forEach(star => star.classList.remove('active'));
              document.getElementById('rating-text').textContent = 'Click to rate';
              submitFeedbackBtn.disabled = true;
            } else { throw new Error(data.error || 'Unknown error occurred'); }
          })
          .catch(err => { console.error('Error:', err); showAlert(err.message || 'Failed to submit feedback. Please try again.', 'danger'); })
          .finally(() => { submitFeedbackBtn.innerHTML = originalHtml; if (document.getElementById('rating-value').value) submitFeedbackBtn.disabled = false; });
      });
    }

    // Discount fields show/hide
    const discountType = document.getElementById('discount_type');
    const proofSection = document.getElementById('discount_proof_section');
    const detailsSection = document.getElementById('discount_details_section');
    const infoText = document.getElementById('discount_info_text');
    if (discountType) {
      discountType.addEventListener('change', function () {
        if (this.value === '') { proofSection.style.display = 'none'; detailsSection.style.display = 'none'; infoText.style.display = 'none'; }
        else {
          proofSection.style.display = ''; detailsSection.style.display = ''; infoText.style.display = '';
          if (this.value === 'pwd_senior') infoText.innerHTML = '<b>20% Discount</b> for PWD/Senior Citizens. Please upload a valid government-issued ID.';
          else if (this.value === 'lcuppersonnel') infoText.innerHTML = '<b>10% Discount</b> for LCUP Personnel. Please upload your personnel ID or certificate.';
          else if (this.value === 'lcupstudent') infoText.innerHTML = '<b>7% Discount</b> for LCUP Students/Alumni. Please upload your student/alumni ID.';
        }
      });
    }
  });
})();
