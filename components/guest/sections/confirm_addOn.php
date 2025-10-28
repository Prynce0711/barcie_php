<?php
// Modal and client-side logic to preview booking details and select add-ons
// This file is intended to be included on the Guest booking page (booking.php)
?>

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
          <div id="modal_bank_details" style="display:none;" class="card p-2 mb-2">
            <div><strong>Account Name:</strong> La Consolacion University Philippines</div>
            <div><strong>Account Number:</strong> 575-7-575007089</div>
            <div><strong>Branch:</strong> Malolos Mc Arthur</div>
          </div>

          <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
              <small class="text-muted">Note: Add-ons are optional and will be added to your booking total.</small>
            </div>
            <div>
              <strong>Total: ₱<span id="previewTotal">0</span></strong>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Back</button>
        <button type="button" id="confirmBookingBtn" class="btn btn-primary">Confirm & Proceed</button>
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

  // Build add-ons UI
  function renderAddons() {
    addonsList.innerHTML = '';
    const list = (currentItem && Array.isArray(currentItem.addons) && currentItem.addons.length) ? currentItem.addons : DEFAULT_ADDONS;
    AVAILABLE_ADDONS = list;

    list.forEach((addon, idx) => {
      const aid = addon.id ? addon.id : ('addon_' + idx + '_' + addon.label.replace(/\s+/g,'_').toLowerCase());
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

  // Calculate nights between two datetime-local inputs (reservation)
  function calcNights(checkin, checkout) {
    try {
      const diff = Math.max(0, new Date(checkout) - new Date(checkin));
      return Math.ceil(diff / (1000 * 60 * 60 * 24));
    } catch (err) { return 1; }
  }

  // Recalculate preview total
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

    const total = base + addonsTotal;
    previewTotal.textContent = total.toLocaleString();
    return total;
  }

  // Helper: find room data from server
  async function fetchItemById(id) {
    try {
      // Use the project-relative fetch path to be consistent with dashboard script
      const res = await fetch('database/fetch_items.php');
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
    return String(s).replace(/[&<>"']/g, function (c) { return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"}[c]; });
  }

  // Show modal, render preview & addons
  async function showPreviewModal(form, bookingData) {
    currentForm = form;
    currentBooking = bookingData;

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
      if (modalPay) {
        const orig = currentForm.querySelector('[name="payment_method"]');
        if (orig) modalPay.value = orig.value || 'cash';
        modalPay.addEventListener('change', () => {
          if (modalBank) modalBank.style.display = (modalPay.value === 'bank') ? 'block' : 'none';
        });
        // set initial visibility
        if (modalBank) modalBank.style.display = (modalPay.value === 'bank') ? 'block' : 'none';
      }
    } catch (err) { /* ignore */ }

    // show bootstrap modal
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
  }

  // Attach to reservation & pencil form submit
  function attachInterceptors() {
    const resForm = document.getElementById('reservationForm');
    const pencilForm = document.getElementById('pencilForm');

    if (resForm) {
      resForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const form = e.target;
        const bookingData = {
          type: 'reservation',
          room_id: form.querySelector('[name="room_id"]').value,
          guest_name: form.querySelector('[name="guest_name"]').value,
          contact: form.querySelector('[name="contact_number"]').value,
          email: form.querySelector('[name="email"]').value,
          checkin: form.querySelector('[name="checkin"]').value,
          checkout: form.querySelector('[name="checkout"]').value,
          occupants: form.querySelector('[name="occupants"]').value
        };
        showPreviewModal(form, bookingData);
      });
    }

    // Also attach to the reservation button (in case button is type=button)
    const reservationBtn = document.getElementById('reservationSubmitBtn');
    if (reservationBtn) {
      reservationBtn.addEventListener('click', function (e) {
        e.preventDefault();
        const form = document.getElementById('reservationForm');
        if (!form) return;
        const bookingData = {
          type: 'reservation',
          room_id: form.querySelector('[name="room_id"]').value,
          guest_name: form.querySelector('[name="guest_name"]').value,
          contact: form.querySelector('[name="contact_number"]').value,
          email: form.querySelector('[name="email"]').value,
          checkin: form.querySelector('[name="checkin"]').value,
          checkout: form.querySelector('[name="checkout"]').value,
          occupants: form.querySelector('[name="occupants"]').value
        };
        showPreviewModal(form, bookingData);
      });
    }

    if (pencilForm) {
      pencilForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const form = e.target;
        const bookingData = {
          type: 'pencil',
          room_id: form.querySelector('[name="room_id"]').value,
          pencil_date: form.querySelector('[name="pencil_date"]').value,
          time_from: form.querySelector('[name="time_from"]').value,
          time_to: form.querySelector('[name="time_to"]').value,
          pax: form.querySelector('[name="pax"]').value
        };
        showPreviewModal(form, bookingData);
      });
    }
  }

  // On confirm, append addon fields to currentForm then submit
  if (confirmBtn) {
    confirmBtn.addEventListener('click', function () {
    if (!currentForm) return;
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

    // Close modal and submit
    const modal = bootstrap.Modal.getInstance(modalEl);
    try {
      // If focus is currently inside the modal, move it to a safe element
      // before hiding to avoid aria-hidden on a focused descendant.
      const active = document.activeElement;
      if (active && modalEl.contains(active)) {
        // prefer returning focus to the original form's primary button
        const fallback = currentForm.querySelector('#reservationSubmitBtn') || currentForm.querySelector('#pencilSubmitBtn') || document.body;
        try { fallback.focus(); } catch (e) { /* ignore */ }
      }
    } catch (err) {
      /* ignore focus errors */
    }

    if (modal) modal.hide();

    // Submit via fetch so we can control post-submit behavior and redirect back to booking
    (async function submitForm() {
      try {
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = 'Processing...';

        const fd = new FormData(currentForm);

        // Get the form action URL properly - use relative path to avoid environment-specific issues
        const actionAttr = currentForm.getAttribute('action');
        // Use form action if specified, otherwise default to user_auth.php
        const targetUrl = actionAttr || 'database/user_auth.php';
        
        console.debug('Submitting booking to', targetUrl);

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
          alert(jsonResponse.message || 'Booking submitted successfully!');
          
          setTimeout(() => {
            window.location.href = 'Guest.php#booking';
          }, 300);
          return;
        }

        // Handle error response
        const errorMsg = jsonResponse?.message || jsonResponse?.error || 'Booking submission failed. Please try again.';
        console.error('Booking failed:', errorMsg);
        alert(errorMsg);
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = 'Confirm & Proceed';
        return;

      } catch (err) {
        console.error('Submit error', err);
        alert('An error occurred while submitting your booking. Please open the browser console for details.');
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
  #previewDetails p { margin: 4px 0; }
</style>
