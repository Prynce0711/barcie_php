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
        <!-- Filters -->
        <div class="row mb-3">
          <div class="col-md-4">
            <label for="pencilStatusFilter" class="form-label">Filter by Status:</label>
            <select id="pencilStatusFilter" class="form-select" onchange="filterPencilBookings()">
              <option value="">All Statuses</option>
              <option value="pending">Pending</option>
              <option value="approved">Approved</option>
              <option value="confirmed">Confirmed</option>
              <option value="cancelled">Cancelled</option>
              <option value="rejected">Rejected</option>
            </select>
          </div>
          <div class="col-md-8">
            <label for="pencilSearch" class="form-label">Search:</label>
            <input type="text" id="pencilSearch" class="form-control" placeholder="Search by guest name, phone, email..." oninput="filterPencilBookings()">
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
                <th style="font-size: 0.75rem;">Created</th>
                <th style="font-size: 0.75rem;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
              // Query pencil bookings only
              $pencilBookings = $conn->query("SELECT b.*, i.name as room_name, i.room_number 
                                              FROM bookings b 
                                              LEFT JOIN items i ON b.room_id = i.id 
                                              WHERE b.type = 'pencil_booking'
                                              ORDER BY b.created_at DESC");
              while ($booking = $pencilBookings->fetch_assoc()):
                $status_color = [
                  'pending' => 'warning',
                  'approved' => 'success',
                  'confirmed' => 'info',
                  'checked_in' => 'primary',
                  'checked_out' => 'secondary',
                  'cancelled' => 'danger',
                  'rejected' => 'danger'
                ];
                $badge_color = $status_color[$booking['status']] ?? 'secondary';
                
                // Extract guest info
                $guest_name = 'Guest';
                $guest_phone = '';
                $guest_email = '';
                if (preg_match('/Guest:\s*([^|]+)/', $booking['details'], $matches)) {
                  $guest_name = trim($matches[1]);
                }
                if (preg_match('/(\d{10,11})/', $booking['details'], $matches)) {
                  $guest_phone = $matches[1];
                }
                if (preg_match('/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/', $booking['details'], $matches)) {
                  $guest_email = $matches[1];
                }
                
                $room_facility = $booking['room_name'] ? $booking['room_name'] : 'Unassigned';
                if ($booking['room_number']) {
                  $room_facility .= ' #' . $booking['room_number'];
                }
              ?>
              <tr data-status="<?= htmlspecialchars($booking['status'] ?? '') ?>" data-guest="<?= htmlspecialchars(($guest_name ?? '') . ' ' . ($guest_phone ?? '') . ' ' . ($guest_email ?? '') . ' ' . ($room_facility ?? '') . ' ' . ($booking['details'] ?? '')) ?>">
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
                    <strong style="font-size: 0.75rem;"><?= htmlspecialchars($guest_name ?? 'Guest') ?></strong><br>
                    <?php if ($guest_phone): ?>
                      <small style="font-size: 0.65rem;"><i class="fas fa-phone"></i> <?= htmlspecialchars($guest_phone ?? '') ?></small><br>
                    <?php endif; ?>
                    <?php if ($guest_email): ?>
                      <small style="font-size: 0.65rem;" class="text-truncate d-inline-block" style="max-width: 150px;"><i class="fas fa-envelope"></i> <?= htmlspecialchars($guest_email ?? '') ?></small>
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
                    <?= date('M j, Y', strtotime($booking['created_at'])) ?><br>
                    <small class="text-muted" style="font-size: 0.65rem;"><?= date('H:i', strtotime($booking['created_at'])) ?></small>
                  </div>
                </td>
                <td>
                  <div class="d-flex flex-column" style="gap: 0.25rem;">
                    <button class="btn btn-info btn-sm" onclick="viewBookingDetails(<?= $booking['id'] ?>)" style="font-size: 0.65rem; padding: 0.3rem 0.5rem;">
                      <i class="fas fa-eye"></i> View
                    </button>
                    
                    <?php if ($booking['status'] === 'pending'): ?>
                      <button class="btn btn-success btn-sm" onclick="updateBookingStatus(<?= $booking['id'] ?>, 'approved')" style="font-size: 0.65rem; padding: 0.3rem 0.5rem;">
                        <i class="fas fa-check"></i> Approve
                      </button>
                      <button class="btn btn-danger btn-sm" onclick="updateBookingStatus(<?= $booking['id'] ?>, 'rejected')" style="font-size: 0.65rem; padding: 0.3rem 0.5rem;">
                        <i class="fas fa-times"></i> Reject
                      </button>
                    <?php elseif ($booking['status'] === 'approved'): ?>
                      <button class="btn btn-primary btn-sm" onclick="updateBookingStatus(<?= $booking['id'] ?>, 'confirmed')" style="font-size: 0.65rem; padding: 0.3rem 0.5rem;">
                        <i class="fas fa-check-circle"></i> Confirm
                      </button>
                      <button class="btn btn-secondary btn-sm" onclick="updateBookingStatus(<?= $booking['id'] ?>, 'cancelled')" style="font-size: 0.65rem; padding: 0.3rem 0.5rem;">
                        Cancel
                      </button>
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
    const query = (document.getElementById('pencilSearch')?.value || '').toLowerCase().trim();
    const rows = document.querySelectorAll('#pencilTable tbody tr');
    let visibleCount = 0;
    rows.forEach(row => {
      const rstatus = (row.dataset.status || '').toLowerCase();
      const rguest = (row.dataset.guest || row.innerText || '').toLowerCase();
      
      let show = true;
      if (status && rstatus.indexOf(status) === -1) show = false;
      if (query && rguest.indexOf(query) === -1) show = false;
      
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
    const query = (document.getElementById('pencilSearch')?.value || '').toLowerCase().trim();
    const rstatus = (row.dataset.status || '').toLowerCase();
    const rguest = (row.dataset.guest || row.innerText || '').toLowerCase();

    if (status && rstatus.indexOf(status) === -1) return false;
    if (query && rguest.indexOf(query) === -1) return false;
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
</script>
