// Bookings Section JavaScript
// Functions for bookings functionality - called by dashboard-bootstrap.js

// Don't auto-initialize - let dashboard-bootstrap.js handle it
// document.addEventListener('DOMContentLoaded', function () {
//   initializeBookingsFiltering();
//   initializeBookingsActions();
// });

function filterBookings() {
  const statusFilter = document.getElementById('statusFilter').value.toLowerCase();
  const typeFilter = document.getElementById('typeFilter').value.toLowerCase();
  const guestSearch = document.getElementById('guestSearch').value.toLowerCase();
  const table = document.getElementById('bookingsTable');
  const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

  for (let i = 0; i < rows.length; i++) {
    const row = rows[i];
    const status = row.cells[6].textContent.toLowerCase().trim();
    const type = row.cells[2].textContent.toLowerCase().trim();
    const guest = row.cells[1].textContent.toLowerCase().trim();
    const details = row.cells[3].textContent.toLowerCase().trim();

    let showRow = true;

    // Status filter
    if (statusFilter && !status.includes(statusFilter)) {
      showRow = false;
    }

    // Type filter
    if (typeFilter && !type.includes(typeFilter)) {
      showRow = false;
    }

    // Guest search
    if (guestSearch && !guest.includes(guestSearch) && !details.includes(guestSearch)) {
      showRow = false;
    }

    row.style.display = showRow ? '' : 'none';
  }
}

function resetFilters() {
  // Show table overlay spinners for bookings/discounts/payments (if available)
  const tableIds = ['bookingsTable', 'discountsTable', 'paymentsTable'];
  const removers = [];

  // Show a full-section loading overlay for the entire bookings section
  let removeSectionSpinner = function(){};
  try {
    const section = document.getElementById('bookingsSection');
    if (section) {
      if (typeof window.showTableSpinner === 'function') {
        // prefer existing helper which returns a remover
        try { removeSectionSpinner = window.showTableSpinner(section); } catch(e){ removeSectionSpinner = function(){}; }
      } else {
        // fallback: create overlay element that covers the section
        const prevPos = section.style.position || '';
        const computed = window.getComputedStyle(section).position;
        if (computed === 'static') section.style.position = 'relative';
        const overlay = document.createElement('div');
        overlay.className = 'table-spinner-overlay';
        overlay.style.position = 'absolute';
        overlay.style.inset = '0';
        overlay.style.background = 'rgba(255,255,255,0.8)';
        overlay.style.zIndex = 1050;
        overlay.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
        section.appendChild(overlay);
        removeSectionSpinner = function(){ try { if (overlay && overlay.parentNode) overlay.parentNode.removeChild(overlay); } catch(e){}; try { if (computed === 'static') section.style.position = prevPos || ''; } catch(e){} };
      }
    }
  } catch(e){ console.error('section spinner error', e); }

  tableIds.forEach(id => {
    try {
      const tbl = document.getElementById(id);
      if (!tbl) return;
      if (typeof window.showTableSpinner === 'function') {
        const rm = window.showTableSpinner(tbl);
        if (typeof rm === 'function') removers.push(rm);
      } else {
        // fallback overlay
        const parent = tbl.closest && tbl.closest('.table-responsive') ? tbl.closest('.table-responsive') : tbl;
        const prevPos = parent.style.position || '';
        const computed = window.getComputedStyle(parent).position;
        if (computed === 'static') parent.style.position = 'relative';
        const overlay = document.createElement('div'); overlay.className = 'table-spinner-overlay'; overlay.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
        parent.appendChild(overlay);
        removers.push(function(){ try { if (overlay && overlay.parentNode) overlay.parentNode.removeChild(overlay); } catch(e){}; try { if (computed === 'static') parent.style.position = prevPos || ''; } catch(e){} });
      }
    } catch (e) { console.error('spinner error', e); }
  });

  // reset UI controls
  try { document.getElementById('statusFilter').value = ''; } catch(e){}
  try { document.getElementById('typeFilter').value = ''; } catch(e){}
  try { document.getElementById('guestSearch').value = ''; } catch(e){}

  // update type filter button visuals (make the 'All' button active)
  try {
    document.querySelectorAll('.type-filter-btn').forEach(b=>{ b.classList.remove('active'); b.classList.remove('btn-secondary'); b.classList.add('btn-outline-secondary'); });
    const allBtn = document.querySelector('.type-filter-btn[data-type=""]'); if (allBtn) { allBtn.classList.add('active'); allBtn.classList.remove('btn-outline-secondary'); allBtn.classList.add('btn-secondary'); }
  } catch(e){}

  // run filters and also reset discounts and payments
  try { if (typeof filterBookings === 'function') filterBookings(); } catch(e){ console.error(e); }
  try { if (typeof window.filterDiscounts === 'function') window.filterDiscounts(); } catch(e){ console.error(e); }
  try { if (window._discountsPagination && typeof window._discountsPagination.goToPage === 'function') window._discountsPagination.goToPage(1); } catch(e){}
  try { if (window._paymentsPagination && typeof window._paymentsPagination.goToPage === 'function') window._paymentsPagination.goToPage(1); } catch(e){}

  // remove overlays after a short delay to allow DOM updates
  setTimeout(function(){ try { removers.forEach(r=>{ try{ r(); }catch(e){} }); } catch(e){} }, 300);

  // also remove the full-section overlay when done
  setTimeout(function(){ try { try { removeSectionSpinner(); } catch(e){} } catch(e){} }, 350);
}

function initializeBookingsFiltering() {
  // Add event listeners for real-time filtering
  const statusFilter = document.getElementById('statusFilter');
  const typeFilter = document.getElementById('typeFilter');
  const guestSearch = document.getElementById('guestSearch');

  if (statusFilter) {
    statusFilter.addEventListener('change', filterBookings);
  }

  if (typeFilter) {
    typeFilter.addEventListener('change', filterBookings);
  }

  if (guestSearch) {
    guestSearch.addEventListener('input', filterBookings);
  }

  // Type filter button wiring moved to central dashboard bootstrap to ensure
  // other dashboard modules (discounts, payments) are notified when type changes.
}

function initializeBookingsActions() {
  // Initialize booking action functions
  console.log('Bookings actions initialized');
}

// Inline admin alert helper — replaces alert() usage in admin pages
function showAdminAlert(message, type = 'danger', duration = 6000) {
  let container = document.getElementById('admin_discount_alert');

  // If an admin alert container doesn't exist, create a floating one
  if (!container) {
    container = document.createElement('div');
    container.id = 'admin_discount_alert';
    container.style.position = 'fixed';
    container.style.top = '1rem';
    container.style.right = '1rem';
    container.style.zIndex = 1080; // above modals
    document.body.appendChild(container);
  }

  const alertId = 'admin-alert-' + Date.now();
  const alertDiv = document.createElement('div');
  alertDiv.id = alertId;
  alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
  alertDiv.role = 'alert';
  alertDiv.style.minWidth = '260px';
  alertDiv.innerHTML = `
    <div style="font-size:0.95rem;">${message}</div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  `;

  container.appendChild(alertDiv);

  if (duration > 0) {
    setTimeout(() => {
      try { bootstrap && bootstrap.Alert && bootstrap.Alert.getOrCreateInstance(alertDiv).close(); } catch (e) { alertDiv.remove(); }
    }, duration);
  }
}

// Inline confirmation modal helper (returns Promise<boolean>)
function showConfirmModal(message, options = {}) {
  return new Promise((resolve) => {
    const modalId = 'confirm-modal-' + Date.now();
    const title = options.title || 'Please confirm';
    const confirmText = options.confirmText || 'Confirm';
    const cancelText = options.cancelText || 'Cancel';

    const modalHTML = `
      <div class="modal fade" id="${modalId}" tabindex="-1">
        <div class="modal-dialog modal-sm">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">${title}</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">${message.replace(/\n/g,'<br/>')}</div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">${cancelText}</button>
              <button type="button" class="btn btn-primary btn-sm" id="${modalId}-confirm">${confirmText}</button>
            </div>
          </div>
        </div>
      </div>
    `;

    // Insert and show modal
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    const modalEl = document.getElementById(modalId);
    const bsModal = new bootstrap.Modal(modalEl);
    bsModal.show();

    const cleanup = () => {
      try { bsModal.hide(); } catch(e) {}
      setTimeout(() => { modalEl.remove(); }, 300);
    };

    modalEl.addEventListener('hidden.bs.modal', function () {
      resolve(false);
      try { modalEl.remove(); } catch(e) {}
    }, { once: true });

    document.getElementById(`${modalId}-confirm`).addEventListener('click', function () {
      resolve(true);
      cleanup();
    }, { once: true });
  });
}

// Booking management functions
function updateBookingStatus(bookingId, newStatus) {
  // Map the status to admin action
  const actionMap = {
    'approved': 'approve',
    'rejected': 'reject',
    'checked_in': 'checkin',
    'checked_out': 'checkout',
    'cancelled': 'cancel'
  };
  
  const adminAction = actionMap[newStatus];
  if (!adminAction) {
    showAdminAlert('Invalid status update requested.', 'warning');
    return;
  }
  
  showConfirmModal(`Are you sure you want to change this booking status to "${newStatus.replace('_', ' ')}"?`).then((confirmed) => {
    if (!confirmed) return;

    // Create a form and submit it
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'database/user_auth.php';
    form.style.display = 'none';

    const actionInput = document.createElement('input');
    actionInput.name = 'action';
    actionInput.value = 'admin_update_booking';
    form.appendChild(actionInput);

    const idInput = document.createElement('input');
    idInput.name = 'booking_id';
    idInput.value = bookingId;
    form.appendChild(idInput);

    const adminActionInput = document.createElement('input');
    adminActionInput.name = 'admin_action';
    adminActionInput.value = adminAction;
    form.appendChild(adminActionInput);

    document.body.appendChild(form);
    form.submit();
  });
}

function viewBookingDetails(bookingId) {
  // Fetch booking details and show in a modal
  fetch(`api/get_booking_details.php?id=${bookingId}`)
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        showBookingDetailsModal(data.booking);
      } else {
        showAdminAlert('Error loading booking details: ' + (data.message || 'Unknown error'), 'danger');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      showAdminAlert('Failed to load booking details', 'danger');
    });
}

function showBookingDetailsModal(booking) {
  // Create modal HTML
  const modalHTML = `
    <div class="modal fade" id="bookingDetailsModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border: none; border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,0.1);">
          <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 12px 12px 0 0; padding: 1.5rem;">
            <h5 class="modal-title" style="font-weight: 600;"><i class="fas fa-file-invoice me-2"></i>Booking Details</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body" style="padding: 2rem; background: #f8f9fa;">
            <div class="row g-3">
              <div class="col-md-6">
                <div class="p-3 rounded" style="background: white; border-left: 4px solid #667eea;">
                  <label class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Receipt Number</label>
                  <div class="fw-bold" style="color: #2d3748; font-size: 1rem;">${booking.receipt_no || 'N/A'}</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3 rounded" style="background: white; border-left: 4px solid #667eea;">
                  <label class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Booking Type</label>
                  <div><span class="badge" style="background: ${booking.type === 'reservation' ? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' : 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)'}; padding: 0.5rem 1rem; font-size: 0.85rem; border-radius: 20px;">${booking.type === 'reservation' ? 'Reservation' : 'Pencil Booking'}</span></div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3 rounded" style="background: white; border-left: 4px solid #48bb78;">
                  <label class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Guest Name</label>
                  <div class="fw-bold" style="color: #2d3748; font-size: 1rem;"><i class="fas fa-user me-2" style="color: #48bb78;"></i>${booking.guest_name || 'N/A'}</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3 rounded" style="background: white; border-left: 4px solid #48bb78;">
                  <label class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Guest Contact</label>
                  <div style="color: #2d3748;"><i class="fas fa-phone me-2" style="color: #48bb78;"></i>${booking.guest_contact || 'N/A'}</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3 rounded" style="background: white; border-left: 4px solid #4299e1;">
                  <label class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Room/Facility</label>
                  <div class="fw-bold" style="color: #2d3748; font-size: 1rem;"><i class="fas fa-bed me-2" style="color: #4299e1;"></i>${booking.room_name || 'Unassigned'}</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3 rounded" style="background: white; border-left: 4px solid #4299e1;">
                  <label class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Booking Status</label>
                  <div><span class="badge" style="background: ${getStatusGradient(booking.status)}; padding: 0.5rem 1rem; font-size: 0.85rem; border-radius: 20px;">${booking.status.replace('_', ' ').toUpperCase()}</span></div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3 rounded" style="background: white; border-left: 4px solid #ed8936;">
                  <label class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Check-in Date</label>
                  <div class="fw-bold" style="color: #2d3748;"><i class="fas fa-calendar-check me-2" style="color: #ed8936;"></i>${formatDate(booking.checkin)}</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3 rounded" style="background: white; border-left: 4px solid #ed8936;">
                  <label class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Check-out Date</label>
                  <div class="fw-bold" style="color: #2d3748;"><i class="fas fa-calendar-times me-2" style="color: #ed8936;"></i>${formatDate(booking.checkout)}</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3 rounded" style="background: white; border-left: 4px solid #9f7aea;">
                  <label class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Discount Status</label>
                  <div><span class="badge" style="background: ${getDiscountGradient(booking.discount_status)}; padding: 0.5rem 1rem; font-size: 0.85rem; border-radius: 20px;">${booking.discount_status || 'None'}</span></div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3 rounded" style="background: white; border-left: 4px solid #9f7aea;">
                  <label class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Created At</label>
                  <div style="color: #2d3748;"><i class="fas fa-clock me-2" style="color: #9f7aea;"></i>${formatDateTime(booking.created_at)}</div>
                </div>
              </div>
              <div class="col-12">
                <div class="p-3 rounded" style="background: white; border-left: 4px solid #38b2ac;">
                  <label class="text-muted small mb-2" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;"><i class="fas fa-info-circle me-2" style="color: #38b2ac;"></i>Additional Details</label>
                  <div class="rounded p-3" style="background: linear-gradient(135deg, #e6f7ff 0%, #f0f9ff 100%); color: #2d3748; border: 1px solid #bee3f8; font-size: 0.9rem; line-height: 1.6;">${booking.details || 'No additional details'}</div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer" style="background: white; border-top: 1px solid #e2e8f0; border-radius: 0 0 12px 12px; padding: 1rem 2rem;">
            <button type="button" class="btn" data-bs-dismiss="modal" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 0.6rem 2rem; border-radius: 25px; border: none; font-weight: 500; box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);">Close</button>
          </div>
        </div>
      </div>
    </div>
  `;
  
  // Remove existing modal if any
  const existingModal = document.getElementById('bookingDetailsModal');
  if (existingModal) {
    existingModal.remove();
  }
  
  // Add modal to body
  document.body.insertAdjacentHTML('beforeend', modalHTML);
  
  // Show modal
  const modal = new bootstrap.Modal(document.getElementById('bookingDetailsModal'));
  modal.show();
  
  // Clean up modal after it's hidden
  document.getElementById('bookingDetailsModal').addEventListener('hidden.bs.modal', function() {
    this.remove();
  });
}

function getStatusGradient(status) {
  const gradients = {
    'pending': 'linear-gradient(135deg, #ffeaa7 0%, #fdcb6e 100%)',
    'approved': 'linear-gradient(135deg, #55efc4 0%, #00b894 100%)',
    'confirmed': 'linear-gradient(135deg, #74b9ff 0%, #0984e3 100%)',
    'checked_in': 'linear-gradient(135deg, #a29bfe 0%, #6c5ce7 100%)',
    'checked_out': 'linear-gradient(135deg, #b2bec3 0%, #636e72 100%)',
    'cancelled': 'linear-gradient(135deg, #ff7675 0%, #d63031 100%)',
    'rejected': 'linear-gradient(135deg, #ff7675 0%, #d63031 100%)'
  };
  return gradients[status] || 'linear-gradient(135deg, #dfe6e9 0%, #b2bec3 100%)';
}

function getDiscountGradient(status) {
  const gradients = {
    'pending': 'linear-gradient(135deg, #ffeaa7 0%, #fdcb6e 100%)',
    'approved': 'linear-gradient(135deg, #55efc4 0%, #00b894 100%)',
    'rejected': 'linear-gradient(135deg, #ff7675 0%, #d63031 100%)',
    'none': 'linear-gradient(135deg, #dfe6e9 0%, #b2bec3 100%)'
  };
  return gradients[status] || 'linear-gradient(135deg, #dfe6e9 0%, #b2bec3 100%)';
}

function formatDate(dateString) {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
}

function formatDateTime(dateString) {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  return date.toLocaleString('en-US', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function deleteBooking(bookingId) {
  showConfirmModal('Are you sure you want to delete this booking? This action cannot be undone.').then((confirmed) => {
    if (!confirmed) return;

    // Create a form and submit it
    const form = document.createElement('form');
    form.method = 'POST';
    form.style.display = 'none';

    const actionInput = document.createElement('input');
    actionInput.name = 'action';
    actionInput.value = 'delete_booking';
    form.appendChild(actionInput);

    const idInput = document.createElement('input');
    idInput.name = 'booking_id';
    idInput.value = bookingId;
    form.appendChild(idInput);

    document.body.appendChild(form);
    form.submit();
  });
}

// Discount management functions
function processDiscount(discountId, action) {
  const actionText = action === 'approved' ? 'approve' : 'reject';
  
  showConfirmModal(`Are you sure you want to ${actionText} this discount application?`).then((confirmed) => {
    if (!confirmed) return;

    // Create a form and submit it
    const form = document.createElement('form');
    form.method = 'POST';
    form.style.display = 'none';

    const actionInput = document.createElement('input');
    actionInput.name = 'action';
    actionInput.value = 'process_discount';
    form.appendChild(actionInput);

    const idInput = document.createElement('input');
    idInput.name = 'discount_id';
    idInput.value = discountId;
    form.appendChild(idInput);

    const statusInput = document.createElement('input');
    statusInput.name = 'discount_action';
    statusInput.value = action;
    form.appendChild(statusInput);

    document.body.appendChild(form);
    form.submit();
  });
}

// Auto-refresh bookings every 30 seconds (optional)
function enableAutoRefresh() {
  setInterval(function() {
    if (document.getElementById('bookings').classList.contains('active')) {
      location.reload();
    }
  }, 30000);
}

// Export functions for global access
window.filterBookings = filterBookings;
window.resetFilters = resetFilters;
window.updateBookingStatus = updateBookingStatus;
window.viewBookingDetails = viewBookingDetails;
window.deleteBooking = deleteBooking;
window.processDiscount = processDiscount;