<?php
// Pencil Book Management Section
// This section displays pencil bookings management specifically
?>

<!-- Pencil Book Management -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-header bg-warning text-dark">
        <h6 class="mb-0"><i class="fas fa-pencil-alt me-2"></i>Pencil Book Management</h6>
        <small class="opacity-75">Manage all pencil bookings - tentative reservations awaiting confirmation.</small>
      </div>
      <div class="card-body">
        <!-- Action Buttons -->
        <div class="d-flex justify-content-end mb-2 gap-2">
          <button type="button" class="btn btn-sm btn-outline-warning" onclick="downloadPencilBookingsExcel()">
            <i class="fas fa-file-excel me-1"></i>Export to Excel
          </button>
          <button type="button" class="btn btn-sm btn-warning" onclick="downloadPencilBookingsPDF()">
            <i class="fas fa-file-alt me-1"></i>Export to Text
          </button>
        </div>
        
        <!-- Filters Section -->
        <div class="card mb-3 border-0 bg-light">
          <div class="card-body py-3">
            <div class="row g-3 align-items-end">
              <!-- Date Filter -->
              <div class="col-md-3">
                <label for="pencilDateFilter" class="form-label fw-semibold text-muted small mb-2">
                  <i class="fas fa-calendar-alt me-1"></i>Date
                </label>
                <input type="date" id="pencilDateFilter" class="form-control" onchange="filterPencilBookings()">
              </div>
              
              <!-- Quick Date Actions -->
              <div class="col-md-3">
                <label class="form-label fw-semibold text-muted small mb-2">Quick Filter</label>
                <div class="d-flex gap-2">
                  <button type="button" class="btn btn-sm btn-warning" onclick="setPencilDateToday()">
                    <i class="fas fa-calendar-day me-1"></i>Today
                  </button>
                  <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearPencilDate()">
                    <i class="fas fa-calendar me-1"></i>All
                  </button>
                </div>
              </div>
              
              <!-- Status Filter -->
              <div class="col-md-4">
                <label for="pencilStatusFilter" class="form-label fw-semibold text-muted small mb-2">
                  <i class="fas fa-info-circle me-1"></i>Status
                </label>
                <select id="pencilStatusFilter" class="form-select" onchange="filterPencilBookings()">
                  <option value="">All Statuses</option>
                  <option value="pending">Pending</option>
                  <option value="approved">Approved</option>
                  <option value="confirmed">Confirmed</option>
                  <option value="cancelled">Cancelled</option>
                  <option value="rejected">Rejected</option>
                </select>
              </div>
              
              <!-- Reset Button -->
              <div class="col-md-2">
                <button class="btn btn-sm btn-outline-secondary w-100" onclick="document.getElementById('pencilDateFilter').value='';document.getElementById('pencilStatusFilter').value='';filterPencilBookings();">
                  <i class="fas fa-redo me-1"></i>Reset
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Pencil Bookings Table -->
        <div id="pencil_alert" class="mb-2"></div>
        <!-- Top pagination controls -->
        <div id="pencilPaginationTop" class="mb-2"></div>
        <div class="table-responsive">
          <table class="table table-hover align-middle" id="pencilTable">
            <thead class="table-dark">
              <tr>
                <th style="font-size: 0.75rem;">Receipt #</th>
                <th style="font-size: 0.75rem;">Room/Facility</th>
                <th style="font-size: 0.75rem;">Guest Details</th>
                <th style="font-size: 0.75rem;">Schedule</th>
                <th style="font-size: 0.75rem;">Status</th>
                <th style="font-size: 0.75rem;">Expires</th>
                <th style="font-size: 0.75rem;">Created</th>
                <th style="font-size: 0.75rem;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
              // Query pencil bookings from dedicated table
              $pencilBookings = $conn->query("SELECT pb.*, i.name as room_name, i.room_number,
                                              DATEDIFF(pb.token_expires_at, NOW()) as days_remaining
                                              FROM pencil_bookings pb
                                              LEFT JOIN items i ON pb.room_id = i.id 
                                              ORDER BY pb.created_at DESC");
              while ($booking = $pencilBookings->fetch_assoc()):
                $status_color = [
                  'pending' => 'warning',
                  'approved' => 'success',
                  'confirmed' => 'info',
                  'cancelled' => 'danger',
                  'rejected' => 'danger',
                  'expired' => 'secondary'
                ];
                $badge_color = $status_color[$booking['status']] ?? 'secondary';
                
                // Use direct fields from pencil_bookings table
                $guest_name = $booking['guest_name'] ?? 'Guest';
                $guest_phone = $booking['contact_number'] ?? '';
                $guest_email = $booking['email'] ?? '';
                
                $room_facility = $booking['room_name'] ? $booking['room_name'] : 'Unassigned';
                if ($booking['room_number']) {
                  $room_facility .= ' #' . $booking['room_number'];
                }
                
                // Check expiration status
                $days_remaining = $booking['days_remaining'] ?? 0;
                $expires_class = '';
                $expires_text = '';
                if ($booking['status'] === 'expired' || $days_remaining < 0) {
                  $expires_class = 'text-danger';
                  $expires_text = 'Expired';
                } elseif ($days_remaining <= 3) {
                  $expires_class = 'text-warning';
                  $expires_text = $days_remaining . ' days left';
                } else {
                  $expires_class = 'text-success';
                  $expires_text = $days_remaining . ' days left';
                }
                
                // Extract date from created_at for filtering (format: YYYY-MM-DD)
                $booking_date = date('Y-m-d', strtotime($booking['created_at']));
              ?>
              <tr data-status="<?= htmlspecialchars($booking['status'] ?? '') ?>" data-date="<?= htmlspecialchars($booking_date) ?>" data-guest="<?= htmlspecialchars(($guest_name ?? '') . ' ' . ($guest_phone ?? '') . ' ' . ($guest_email ?? '') . ' ' . ($room_facility ?? '') . ' ' . ($booking['details'] ?? '')) ?>">
                <td>
                  <strong style="font-size: 0.7rem;">BARCIE-<?= date('Ymd', strtotime($booking['created_at'])) ?>-<?= str_pad($booking['id'], 4, '0', STR_PAD_LEFT) ?></strong>
                </td>
                <td>
                  <div style="line-height: 1.3;">
                    <strong style="font-size: 0.75rem;"><?= htmlspecialchars($room_facility ?? 'Unassigned') ?></strong>
                  </div>
                </td>
                <td>
                  <div style="line-height: 1.3;">
                    <strong style="font-size: 0.75rem;"><?= htmlspecialchars($guest_name) ?></strong><br>
                    <?php if ($guest_phone): ?>
                      <small style="font-size: 0.65rem;"><i class="fas fa-phone"></i> <?= htmlspecialchars($guest_phone) ?></small><br>
                    <?php endif; ?>
                    <?php if ($guest_email): ?>
                      <small style="font-size: 0.65rem;" class="text-truncate d-inline-block" style="max-width: 150px;"><i class="fas fa-envelope"></i> <?= htmlspecialchars($guest_email) ?></small>
                    <?php endif; ?>
                  </div>
                </td>
                <td>
                  <div style="line-height: 1.3; font-size: 0.7rem;">
                    <strong>In:</strong> <?= date('M j, Y', strtotime($booking['checkin'])) ?><br>
                    <small class="text-muted" style="font-size: 0.65rem;"><?= date('H:i', strtotime($booking['checkin'])) ?></small><br>
                    <strong>Out:</strong> <?= date('M j, Y', strtotime($booking['checkout'])) ?><br>
                    <small class="text-muted" style="font-size: 0.65rem;"><?= date('H:i', strtotime($booking['checkout'])) ?></small>
                  </div>
                </td>
                <td>
                  <span class="badge bg-<?= $badge_color ?>" style="font-size: 0.65rem; padding: 0.35rem 0.6rem;">
                    <?= ucfirst(str_replace('_', ' ', $booking['status'])) ?>
                  </span>
                </td>
                <td>
                  <div style="font-size: 0.7rem; line-height: 1.3;">
                    <strong class="<?= $expires_class ?>"><?= $expires_text ?></strong><br>
                    <?php $expiry_field = $booking['token_expires_at'] ?? $booking['expires_at'] ?? null; ?>
                    <small class="text-muted" style="font-size: 0.65rem;"><?php echo $expiry_field ? date('M j, Y', strtotime($expiry_field)) : 'N/A'; ?></small>
                  </div>
                </td>
                <td>
                  <div style="font-size: 0.7rem; line-height: 1.3;">
                    <?= date('M j, Y', strtotime($booking['created_at'])) ?><br>
                    <small class="text-muted" style="font-size: 0.65rem;"><?= date('H:i', strtotime($booking['created_at'])) ?></small>
                  </div>
                </td>
                <td>
                  <div class="d-flex flex-column" style="gap: 0.25rem;">
                    <button class="btn btn-info btn-sm" onclick="viewPencilBookingDetails(<?= $booking['id'] ?>)" style="font-size: 0.65rem; padding: 0.3rem 0.5rem;">
                      <i class="fas fa-eye"></i> View
                    </button>
                    
                    <?php if ($booking['status'] === 'pending'): ?>
                      <button class="btn btn-success btn-sm" onclick="updatePencilBookingStatus(<?= $booking['id'] ?>, 'approved')" style="font-size: 0.65rem; padding: 0.3rem 0.5rem;">
                        <i class="fas fa-check"></i> Approve
                      </button>
                      <button class="btn btn-danger btn-sm" onclick="updatePencilBookingStatus(<?= $booking['id'] ?>, 'rejected')" style="font-size: 0.65rem; padding: 0.3rem 0.5rem;">
                        <i class="fas fa-times"></i> Reject
                      </button>
                    <?php elseif ($booking['status'] === 'approved'): ?>
                      <button class="btn btn-primary btn-sm" onclick="updatePencilBookingStatus(<?= $booking['id'] ?>, 'confirmed')" style="font-size: 0.65rem; padding: 0.3rem 0.5rem;">
                        <i class="fas fa-check-circle"></i> Confirm & Convert
                      </button>
                      <button class="btn btn-secondary btn-sm" onclick="updatePencilBookingStatus(<?= $booking['id'] ?>, 'cancelled')" style="font-size: 0.65rem; padding: 0.3rem 0.5rem;">
                        Cancel
                      </button>
                    <?php endif; ?>
                    
                    <?php if ($booking['terms_acknowledged']): ?>
                      <small class="text-success" style="font-size: 0.6rem;">
                        <i class="fas fa-check-circle"></i> Terms Ack.
                      </small>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
        <!-- Bottom pagination -->
        <div id="pencilPagination" class="mt-2"></div>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  // Styling for pagination animations
  const styleId = 'pencil-pagination-animations';
  if (!document.getElementById(styleId)) {
    const css = `
      .pagination { opacity: 0; transition: opacity 200ms ease-in; }
      .pagination.show { opacity: 1; }
      .table-spinner-overlay { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255,255,255,0.8); display: flex; align-items: center; justify-content: center; z-index: 10; }
      .table-spinner-overlay .spinner-border { width: 2.4rem; height: 2.4rem; }
    `;
    const s = document.createElement('style'); s.id = styleId; s.appendChild(document.createTextNode(css));
    document.head.appendChild(s);
  }

  // Client-side filtering implementation
  window.filterPencilBookings = function(){
    const status = (document.getElementById('pencilStatusFilter')?.value || '').toLowerCase();
    const dateFilter = document.getElementById('pencilDateFilter')?.value || '';
    const rows = document.querySelectorAll('#pencilTable tbody tr');
    let visibleCount = 0;
    rows.forEach(row => {
      const rstatus = (row.dataset.status || '').toLowerCase();
      const rdate = row.dataset.date || '';
      
      let show = true;
      if (status && rstatus.indexOf(status) === -1) show = false;
      if (dateFilter && rdate !== dateFilter) show = false;
      
      row.style.display = show ? '' : 'none';
      if (show) visibleCount++;
    });

    // Show no-results row when filtered out completely
    const noId = 'pencil-no-results';
    let noRow = document.getElementById(noId);
    if (visibleCount === 0) {
      if (!noRow) {
        noRow = document.createElement('tr');
        noRow.id = noId;
        noRow.innerHTML = '<td colspan="7" class="text-center text-muted">No pencil bookings match the current filters.</td>';
        document.querySelector('#pencilTable tbody').appendChild(noRow);
      }
      noRow.style.display = '';
    } else {
      if (noRow) noRow.style.display = 'none';
    }
  };

  // Client-side pagination
  const PER_PAGE = 10;
  let state = { perPage: PER_PAGE, currentPage: 1, totalPages: 1 };
  let fadeToken = 0;

  function getAllRows(){
    return Array.from(document.querySelectorAll('#pencilTable tbody tr')).filter(r => r.id !== 'pencil-no-results');
  }

  function doesRowMatchFilter(row){
    const status = (document.getElementById('pencilStatusFilter')?.value || '').toLowerCase();
    const dateFilter = document.getElementById('pencilDateFilter')?.value || '';
    const rstatus = (row.dataset.status || '').toLowerCase();
    const rdate = row.dataset.date || '';

    if (status && rstatus.indexOf(status) === -1) return false;
    if (dateFilter && rdate !== dateFilter) return false;
    return true;
  }

  function fadeOutRows(rows, timeout = 300){
    return new Promise(resolve => {
      if (!rows || rows.length === 0) return resolve();
      let remaining = rows.length;
      const finishOne = (r) => {
        try { r.style.display = 'none'; r.setAttribute('data-hidden-by-pagination','true'); } catch(e){}
        if (--remaining <= 0) resolve();
      };
      const onEnd = (e) => {
        const r = e.currentTarget;
        r.removeEventListener('transitionend', onEnd);
        finishOne(r);
      };
      rows.forEach(r => {
        r.style.transition = r.style.transition || 'opacity 220ms ease-in-out';
        r.addEventListener('transitionend', onEnd);
        requestAnimationFrame(() => { r.style.opacity = 0; });
        setTimeout(() => { try { r.removeEventListener('transitionend', onEnd); } catch(e){}; finishOne(r); }, timeout);
      });
    });
  }

  function showTableSpinner(container){
    try {
      const parent = container && container.closest && container.closest('.table-responsive') ? container.closest('.table-responsive') : (container || document.body);
      const prevPos = parent.style.position || '';
      const computed = window.getComputedStyle(parent).position;
      if (computed === 'static') parent.style.position = 'relative';
      const overlay = document.createElement('div');
      overlay.className = 'table-spinner-overlay';
      overlay.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
      parent.appendChild(overlay);
      return function(){
        try { if (overlay && overlay.parentNode) overlay.parentNode.removeChild(overlay); } catch(e){}
        try { if (computed === 'static') parent.style.position = prevPos || ''; } catch(e){}
      };
    } catch (err) { return function(){}; }
  }

  async function recalcPagination(){
    const rows = getAllRows();
    const visibleRows = rows.filter(r => doesRowMatchFilter(r));
    const totalVisible = visibleRows.length;
    state.totalPages = Math.max(1, Math.ceil(totalVisible / state.perPage));
    if (state.currentPage > state.totalPages) state.currentPage = state.totalPages;

    const currentlyVisible = rows.filter(r => r.style.display !== 'none' && !r.hasAttribute('data-hidden-by-pagination'));
    const myToken = ++fadeToken;

    const removeSpinner = showTableSpinner(document.querySelector('#pencilTable'));
    await fadeOutRows(currentlyVisible);

    if (myToken !== fadeToken) { removeSpinner(); return; }

    rows.forEach(r => { r.style.display = 'none'; r.setAttribute('data-hidden-by-pagination','true'); r.style.opacity = 0; });
    const start = (state.currentPage - 1) * state.perPage;
    const end = start + state.perPage;
    visibleRows.slice(start, end).forEach(r => {
      r.removeAttribute('data-hidden-by-pagination');
      r.style.display = '';
      r.style.opacity = 0;
      requestAnimationFrame(() => { r.style.transition = r.style.transition || 'opacity 220ms ease-in-out'; r.style.opacity = 1; });
    });
    setTimeout(() => { try { removeSpinner(); } catch(e){} }, 220);
    renderPaginationControls();
  }

  function renderPaginationControls(){
    const container = document.getElementById('pencilPagination');
    if (!container) return;
    const topContainer = document.getElementById('pencilPaginationTop');
    [container, topContainer].forEach(c => { if (c) c.innerHTML = ''; });
    if (state.totalPages <= 1) return;

    const nav = document.createElement('nav');
    const ul = document.createElement('ul');
    ul.className = 'pagination justify-content-center mb-0';

    const createPageItem = (label, page, disabled, active) => {
      const li = document.createElement('li');
      li.className = 'page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '');
      const btn = document.createElement('button');
      btn.className = 'page-link';
      btn.type = 'button';
      btn.textContent = label;
      btn.addEventListener('click', e => { e.preventDefault(); if (disabled) return; state.currentPage = page; recalcPagination(); });
      li.appendChild(btn);
      return li;
    };

    ul.appendChild(createPageItem('«', Math.max(1, state.currentPage - 1), state.currentPage === 1, false));

    const maxButtons = 7;
    let start = Math.max(1, state.currentPage - 3);
    let end = Math.min(state.totalPages, start + maxButtons - 1);
    if (end - start < maxButtons - 1) start = Math.max(1, end - maxButtons + 1);

    if (start > 1) {
      ul.appendChild(createPageItem('1', 1, false, state.currentPage === 1));
      if (start > 2) {
        const gap = document.createElement('li');
        gap.className = 'page-item disabled';
        gap.innerHTML = '<span class="page-link">…</span>';
        ul.appendChild(gap);
      }
    }

    for (let p = start; p <= end; p++) {
      ul.appendChild(createPageItem(String(p), p, false, p === state.currentPage));
    }

    if (end < state.totalPages) {
      if (end < state.totalPages - 1) {
        const gap = document.createElement('li');
        gap.className = 'page-item disabled';
        gap.innerHTML = '<span class="page-link">…</span>';
        ul.appendChild(gap);
      }
      ul.appendChild(createPageItem(String(state.totalPages), state.totalPages, false, state.currentPage === state.totalPages));
    }

    ul.appendChild(createPageItem('»', Math.min(state.totalPages, state.currentPage + 1), state.currentPage === state.totalPages, false));

    nav.appendChild(ul);
    if (container) container.appendChild(nav.cloneNode(true));
    if (topContainer) topContainer.appendChild(nav.cloneNode(true));
    requestAnimationFrame(() => {
      const p1 = container && container.querySelector('.pagination'); if (p1) p1.classList.add('show');
      const p2 = topContainer && topContainer.querySelector('.pagination'); if (p2) p2.classList.add('show');
    });
  }

  document.addEventListener('DOMContentLoaded', function(){
    const orig = window.filterPencilBookings;
    if (typeof orig === 'function') {
      window.filterPencilBookings = function(){
        orig();
        state.currentPage = 1;
        recalcPagination();
      };
    }
    recalcPagination();
  });

  window._pencilPagination = {
    setPerPage: function(n){ state.perPage = Math.max(1, Number(n) || PER_PAGE); state.currentPage = 1; recalcPagination(); },
    goToPage: function(p){ state.currentPage = Math.min(Math.max(1, Number(p)||1), state.totalPages); recalcPagination(); },
    next: function(){ if (state.currentPage < state.totalPages) { state.currentPage++; recalcPagination(); } },
    prev: function(){ if (state.currentPage > 1) { state.currentPage--; recalcPagination(); } }
  };
})();

// Pencil Booking Management Functions
function viewPencilBookingDetails(bookingId) {
  fetch(`database/user_auth.php?action=get_pencil_booking_details&id=${bookingId}`)
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        const booking = data.booking;
        const details = `
          <div class="booking-details">
            <h5>Pencil Booking Details</h5>
            <table class="table table-sm">
              <tr><th>Receipt No:</th><td>${booking.receipt_no}</td></tr>
              <tr><th>Guest Name:</th><td>${booking.guest_name}</td></tr>
              <tr><th>Email:</th><td>${booking.email}</td></tr>
              <tr><th>Contact:</th><td>${booking.contact_number}</td></tr>
              <tr><th>Room/Facility:</th><td>${booking.room_name || 'N/A'}</td></tr>
              <tr><th>Check-in:</th><td>${new Date(booking.checkin).toLocaleString()}</td></tr>
              <tr><th>Check-out:</th><td>${new Date(booking.checkout).toLocaleString()}</td></tr>
              <tr><th>Occupants:</th><td>${booking.occupants}</td></tr>
              <tr><th>Company:</th><td>${booking.company || 'N/A'}</td></tr>
              <tr><th>Base Price:</th><td>₱${parseFloat(booking.base_price).toLocaleString()}</td></tr>
              <tr><th>Total Price:</th><td>₱${parseFloat(booking.total_price).toLocaleString()}</td></tr>
              <tr><th>Status:</th><td><span class="badge bg-warning">${booking.status}</span></td></tr>
              <tr><th>Terms Acknowledged:</th><td>${booking.terms_acknowledged ? '<span class="text-success">✓ Yes</span>' : '<span class="text-danger">✗ No</span>'}</td></tr>
              <tr><th>Expires At:</th><td class="text-danger">${(booking.token_expires_at || booking.expires_at) ? new Date(booking.token_expires_at || booking.expires_at).toLocaleString() : 'N/A'}</td></tr>
              <tr><th>Created:</th><td>${new Date(booking.created_at).toLocaleString()}</td></tr>
            </table>
          </div>
        `;
        
        // Show in modal or alert
        if (typeof bootstrap !== 'undefined') {
          const modalHtml = `
            <div class="modal fade" id="pencilDetailsModal" tabindex="-1">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">Pencil Booking Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">${details}</div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                  </div>
                </div>
              </div>
            </div>
          `;
          document.body.insertAdjacentHTML('beforeend', modalHtml);
          const modal = new bootstrap.Modal(document.getElementById('pencilDetailsModal'));
          modal.show();
          document.getElementById('pencilDetailsModal').addEventListener('hidden.bs.modal', function() {
            this.remove();
          });
        } else {
          showToast('Booking details loaded', 'info', 3000);
        }
      } else {
        showAlert('pencil_alert', data.message || 'Failed to load booking details', 'danger');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      showAlert('pencil_alert', 'Error loading booking details', 'danger');
    });
}

async function updatePencilBookingStatus(bookingId, newStatus) {
  const confirmed = await showConfirm(
    `Are you sure you want to ${newStatus} this pencil booking?`,
    { title: 'Confirm Status Change', confirmText: 'Yes, Continue', confirmClass: 'btn-primary' }
  );
  if (!confirmed) {
    return;
  }
  
  const formData = new FormData();
  formData.append('action', 'update_pencil_booking_status');
  formData.append('booking_id', bookingId);
  formData.append('status', newStatus);
  
  fetch('database/user_auth.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showAlert('pencil_alert', data.message || 'Status updated successfully', 'success');
      setTimeout(() => location.reload(), 1500);
    } else {
      showAlert('pencil_alert', data.message || 'Failed to update status', 'danger');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showAlert('pencil_alert', 'Error updating status', 'danger');
  });
}

function showAlert(elementId, message, type) {
  const alertDiv = document.getElementById(elementId);
  if (alertDiv) {
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
      ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    alertDiv.style.display = 'block';
    setTimeout(() => {
      alertDiv.style.display = 'none';
    }, 5000);
  }
}

// Helper functions for date filter
window.setPencilDateToday = function() {
  const today = new Date().toISOString().split('T')[0];
  const dateInput = document.getElementById('pencilDateFilter');
  if (dateInput) {
    dateInput.value = today;
    filterPencilBookings();
  }
};

window.clearPencilDate = function() {
  const dateInput = document.getElementById('pencilDateFilter');
  if (dateInput) {
    dateInput.value = '';
    filterPencilBookings();
  }
};

// Download pencil bookings as text backup
function downloadPencilBookingsPDF() {
  const rows = Array.from(document.querySelectorAll('#pencilTable tbody tr')).filter(row => {
    return row.style.display !== 'none' && !row.id;
  });
  
  if (rows.length === 0) {
    showToast('No pencil bookings to export with current filters', 'warning');
    return;
  }
  
  const dateFilter = document.getElementById('pencilDateFilter')?.value || 'All Dates';
  const statusFilter = document.getElementById('pencilStatusFilter')?.value || 'All Status';
  
  let content = `BARCIE INTERNATIONAL CENTER - PENCIL BOOKINGS BACKUP
Generated: ${new Date().toLocaleString()}
Total Records: ${rows.length}

FILTERS APPLIED:
- Date: ${dateFilter}
- Status: ${statusFilter}

${'='.repeat(80)}

`;

  rows.forEach((row, index) => {
    const cells = row.querySelectorAll('td');
    if (cells.length >= 7) {
      const receipt = cells[0].textContent.trim();
      const room = cells[1].textContent.trim();
      const guest = cells[2].textContent.trim().replace(/\n/g, ' ');
      const schedule = cells[3].textContent.trim().replace(/\n/g, ' ');
      const status = cells[4].textContent.trim();
      const expires = cells[5].textContent.trim().replace(/\n/g, ' ');
      const created = cells[6].textContent.trim().replace(/\n/g, ' ');
      
      content += `${index + 1}. ${receipt}
   Room/Facility: ${room}
   Guest: ${guest}
   Schedule: ${schedule}
   Status: ${status}
   Expires: ${expires}
   Created: ${created}
${'-'.repeat(80)}

`;
    }
  });
  
  const blob = new Blob([content], { type: 'text/plain' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `pencil_bookings_backup_${new Date().toISOString().split('T')[0]}.txt`;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
  
  showToast('Pencil bookings backup downloaded successfully', 'success');
}

// Download pencil bookings as Excel
function downloadPencilBookingsExcel() {
  const rows = Array.from(document.querySelectorAll('#pencilTable tbody tr')).filter(row => {
    return row.style.display !== 'none' && !row.id;
  });
  
  if (rows.length === 0) {
    showToast('No pencil bookings to export with current filters', 'warning');
    return;
  }
  
  const dateFilter = document.getElementById('pencilDateFilter')?.value || 'All Dates';
  const statusFilter = document.getElementById('pencilStatusFilter')?.value || 'All Status';
  
  let csv = 'Receipt #,Room/Facility,Guest Name,Guest Contact,Schedule,Status,Expires,Created\n';
  
  rows.forEach(row => {
    const cells = row.querySelectorAll('td');
    if (cells.length >= 7) {
      const receipt = cells[0].textContent.trim().replace(/,/g, ';');
      const room = cells[1].textContent.trim().replace(/,/g, ';');
      const guestText = cells[2].textContent.trim().replace(/\n/g, ' ').replace(/,/g, ';');
      const guestParts = guestText.split(/[📞✉]/);
      const guestName = guestParts[0].trim();
      const guestContact = guestParts.slice(1).join(' | ').trim();
      const schedule = cells[3].textContent.trim().replace(/\n/g, ' | ').replace(/,/g, ';');
      const status = cells[4].textContent.trim().replace(/,/g, ';');
      const expires = cells[5].textContent.trim().replace(/\n/g, ' ').replace(/,/g, ';');
      const created = cells[6].textContent.trim().replace(/\n/g, ' ').replace(/,/g, ';');
      
      csv += `"${receipt}","${room}","${guestName}","${guestContact}","${schedule}","${status}","${expires}","${created}"\n`;
    }
  });
  
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `pencil_bookings_${dateFilter.replace(/[^0-9-]/g, '') || 'all'}_${new Date().toISOString().split('T')[0]}.csv`;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
  
  showToast(`Exported ${rows.length} pencil bookings to Excel (Filters: Date=${dateFilter}, Status=${statusFilter})`, 'success');
}
</script>
