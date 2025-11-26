<?php
// Discount Applications Section
// This section displays discount applications management
?>

<!-- Discount Applications Section -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-header bg-secondary text-white">
        <h6 class="mb-0"><i class="fas fa-id-card-alt me-2"></i>Discount Applications (Pending)</h6>
        <small class="opacity-75">Review uploaded ID proofs and approve or reject discounts.</small>
      </div>
      <div class="card-body">
        <!-- Filters Section -->
        <div class="card mb-3 border-0 bg-light">
          <div class="card-body py-3">
            <div class="row g-3 align-items-end">
              <!-- Date Filter -->
              <div class="col-md-3">
                <label for="discountDateFilter" class="form-label fw-semibold text-muted small mb-2">
                  <i class="fas fa-calendar-alt me-1"></i>Date
                </label>
                <input type="date" id="discountDateFilter" class="form-control" onchange="filterDiscounts()">
              </div>
              
              <!-- Quick Date Actions -->
              <div class="col-md-3">
                <label class="form-label fw-semibold text-muted small mb-2">Quick Filter</label>
                <div class="d-flex gap-2">
                  <button type="button" class="btn btn-sm btn-secondary" onclick="setDiscountDateToday()">
                    <i class="fas fa-calendar-day me-1"></i>Today
                  </button>
                  <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearDiscountDate()">
                    <i class="fas fa-calendar me-1"></i>All
                  </button>
                </div>
              </div>
              
              <!-- Type Filter -->
              <div class="col-md-4">
                <label class="form-label fw-semibold text-muted small mb-2">
                  <i class="fas fa-tag me-1"></i>Type
                </label>
                <div class="btn-group w-100" role="group">
                  <button type="button" class="btn btn-outline-secondary btn-sm type-filter-btn active" data-type="">
                    <i class="fas fa-list me-1"></i>All
                  </button>
                  <button type="button" class="btn btn-outline-secondary btn-sm type-filter-btn" data-type="room">
                    <i class="fas fa-bed me-1"></i>Rooms
                  </button>
                  <button type="button" class="btn btn-outline-secondary btn-sm type-filter-btn" data-type="facility">
                    <i class="fas fa-building me-1"></i>Facilities
                  </button>
                </div>
                <select id="discountTypeFilter" class="form-select d-none">
                  <option value="">All</option>
                  <option value="room">Rooms</option>
                  <option value="facility">Facilities</option>
                </select>
              </div>
              
              <!-- Reset Button -->
              <div class="col-md-2">
                <button class="btn btn-sm btn-outline-secondary w-100" onclick="resetDiscountFilters()">
                  <i class="fas fa-redo me-1"></i>Reset
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="table-responsive">
          <table id="discountsTable" class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>Receipt #</th>
                <th>Guest</th>
                <th>Room/Facility</th>
                <th>Discount</th>
                <th>Schedule</th>
                <th>Proof</th>
                <th>Submitted</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
              // Query pending discount applications
              $stmt = $conn->prepare("SELECT b.id, b.receipt_no, b.details, b.checkin, b.checkout, b.created_at, b.proof_of_id, i.name as room_name, i.room_number FROM bookings b LEFT JOIN items i ON b.room_id = i.id WHERE b.discount_status = 'pending' ORDER BY b.created_at DESC");
              if ($stmt) {
                $stmt->execute();
                $res = $stmt->get_result();
                if ($res && $res->num_rows > 0) {
                  while ($row = $res->fetch_assoc()) {
                    $bookingId = $row['id'];
                    $receipt = $row['receipt_no'] ?: '—';
                    $details = $row['details'] ?: '';
                    $checkin = $row['checkin'];
                    $checkout = $row['checkout'];
                    $created = $row['created_at'];
                    $room = $row['room_name'] ?: 'Unassigned';
                    if ($row['room_number']) $room .= ' #' . $row['room_number'];

                    // Determine type for client-side filtering
                    $row_type = !empty($row['room_name']) ? 'room' : 'facility';

                    // Extract guest name and discount type from details
                    $guest = 'Guest';
                    $discountType = '';
                    if (preg_match('/Guest:\s*([^|]+)/', $details, $m)) $guest = trim($m[1]);
                    if (preg_match('/Discount:\s*([^|]+)/', $details, $m)) $discountType = trim($m[1]);

                    // Use dedicated proof_of_id column
                    $proofPath = $row['proof_of_id'] ?: '';
                    
                    // Extract date from created_at for filtering (format: YYYY-MM-DD)
                    $booking_date = date('Y-m-d', strtotime($created));

                    echo '<tr id="discount-row-' . $bookingId . '" data-type="' . htmlspecialchars($row_type) . '" data-date="' . htmlspecialchars($booking_date) . '">';
                    echo '<td><strong>' . htmlspecialchars($receipt) . '</strong></td>';
                    echo '<td>' . htmlspecialchars($guest) . '</td>';
                    echo '<td>' . htmlspecialchars($room) . '</td>';
                    echo '<td>' . htmlspecialchars($discountType ?: '—') . '</td>';
                    echo '<td>' . htmlspecialchars(($checkin ? date('M j, Y', strtotime($checkin)) : '—') . ' - ' . ($checkout ? date('M j, Y', strtotime($checkout)) : '—')) . '</td>';
                    echo '<td>';
                    if (!empty($proofPath) && file_exists(__DIR__ . '/../../' . $proofPath)) {
                      $url = '../' . ltrim($proofPath, '/');
                      echo '<a href="#" class="view-proof" data-proof="' . htmlspecialchars($url) . '" data-booking-id="' . $bookingId . '"><img src="' . htmlspecialchars($url) . '" alt="Proof Image" style="max-width: 120px; max-height: 80px; object-fit:cover; border-radius:4px; border:1px solid #e9ecef;"></a>';
                    } elseif (!empty($proofPath)) {
                      echo '<a href="#" class="view-proof" data-proof="' . htmlspecialchars($proofPath) . '" data-booking-id="' . $bookingId . '">View Proof</a>';
                    } else {
                      echo 'No ID uploaded';
                    }
                    echo '</td>';
                    echo '<td>' . htmlspecialchars(date('M j, Y H:i', strtotime($created))) . '</td>';
                    echo '<td>';
                    echo '<button class="btn btn-success btn-sm discount-action" data-booking-id="' . $bookingId . '" data-action="approve">Approve</button> ';
                    echo '<button class="btn btn-danger btn-sm discount-action" data-booking-id="' . $bookingId . '" data-action="reject">Reject</button>';
                    echo '</td>';
                    echo '</tr>';
                  }
                } else {
                  echo '<tr><td colspan="8">No pending discount applications.</td></tr>';
                }
                $stmt->close();
              } else {
                echo '<tr><td colspan="8">Failed to load discount applications.</td></tr>';
              }
              ?>
            </tbody>
          </table>
        </div>
        <!-- Pagination for Discount Applications -->
        <div id="discountsPagination" class="mt-2"></div>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  // Sync type filter buttons with hidden select
  function setTypeFilter(type, trigger){
    const sel = document.getElementById('discountTypeFilter');
    if (!sel) return;
    sel.value = type;
    document.querySelectorAll('.type-filter-btn').forEach(b=>{
      b.classList.remove('active');
    });
    const btn = document.querySelector('.type-filter-btn[data-type="' + type + '"]');
    if (btn) {
      btn.classList.add('active');
    }
    if (trigger !== false) {
      if (typeof window.filterDiscounts === 'function') window.filterDiscounts();
    }
  }

  document.addEventListener('click', function(e){
    const btn = e.target.closest('.type-filter-btn');
    if (!btn) return;
    const type = btn.getAttribute('data-type') || '';
    setTypeFilter(type);
  });

  document.addEventListener('DOMContentLoaded', function(){
    const sel = document.getElementById('discountTypeFilter');
    if (sel) {
      sel.addEventListener('change', function(){
        setTypeFilter(sel.value, false);
      });
    }
  });

  // Client-side pagination for Discount Applications table
  const PER_PAGE_D = 8;
  let dstate = { perPage: PER_PAGE_D, currentPage: 1, totalPages: 1 };
  let discountsFadeToken = 0;

  function dGetAllRows(){
    const typeFilter = (document.getElementById('discountTypeFilter')?.value || '').toLowerCase();
    const dateFilter = document.getElementById('discountDateFilter')?.value || '';
    return Array.from(document.querySelectorAll('#discountsTable tbody tr')).filter(r => {
      if (r.id === 'discounts-no-results') return false;
      const rtype = (r.dataset.type || '').toLowerCase();
      const rdate = r.dataset.date || '';
      
      if (typeFilter && typeFilter !== 'all' && rtype !== typeFilter) return false;
      if (dateFilter && rdate !== dateFilter) return false;
      return true;
    });
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

  async function dRecalc(){
    const rows = dGetAllRows();
    const total = rows.length;
    dstate.totalPages = Math.max(1, Math.ceil(total / dstate.perPage));
    if (dstate.currentPage > dstate.totalPages) dstate.currentPage = dstate.totalPages;

    const currentlyVisible = rows.filter(r => r.style.display !== 'none' && !r.hasAttribute('data-hidden-by-pagination'));
    const myToken = ++discountsFadeToken;

    const removeSpinner = showTableSpinner(document.querySelector('#discountsTable'));

    await fadeOutRows(currentlyVisible);
    if (myToken !== discountsFadeToken) { removeSpinner(); return; }

    rows.forEach(r => { r.style.display = 'none'; r.setAttribute('data-hidden-by-pagination','true'); r.style.opacity = 0; });
    const start = (dstate.currentPage - 1) * dstate.perPage;
    const end = start + dstate.perPage;
    rows.slice(start, end).forEach(r => {
      r.removeAttribute('data-hidden-by-pagination'); r.style.display = ''; r.style.opacity = 0;
      requestAnimationFrame(()=>{ r.style.transition = r.style.transition || 'opacity 220ms ease-in-out'; r.style.opacity = 1; });
    });
    setTimeout(()=>{ try { removeSpinner(); } catch(e){} }, 220);

    dRenderControls();
  }

  function dRenderControls(){
    const container = document.getElementById('discountsPagination');
    if (!container) return;
    container.innerHTML = '';
    if (dstate.totalPages <= 1) return;

    const nav = document.createElement('nav');
    const ul = document.createElement('ul'); ul.className = 'pagination justify-content-center mb-0';

    const makeItem = (label, page, disabled, active) => {
      const li = document.createElement('li'); li.className = 'page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '');
      const btn = document.createElement('button'); btn.className = 'page-link'; btn.type = 'button'; btn.textContent = label;
      btn.addEventListener('click', e => { e.preventDefault(); if (disabled) return; dstate.currentPage = page; dRecalc(); });
      li.appendChild(btn); return li;
    };

    ul.appendChild(makeItem('«', Math.max(1, dstate.currentPage - 1), dstate.currentPage === 1, false));
    const maxButtons = 7; let s = Math.max(1, dstate.currentPage - 3); let e = Math.min(dstate.totalPages, s + maxButtons - 1); if (e - s < maxButtons - 1) s = Math.max(1, e - maxButtons + 1);
    if (s > 1) { ul.appendChild(makeItem('1',1,false,dstate.currentPage===1)); if (s>2){ const gap=document.createElement('li'); gap.className='page-item disabled'; gap.innerHTML='<span class="page-link">…</span>'; ul.appendChild(gap);} }
    for (let p=s;p<=e;p++){ ul.appendChild(makeItem(String(p), p, false, p===dstate.currentPage)); }
    if (e < dstate.totalPages) { if (e < dstate.totalPages -1){ const gap=document.createElement('li'); gap.className='page-item disabled'; gap.innerHTML='<span class="page-link">…</span>'; ul.appendChild(gap);} ul.appendChild(makeItem(String(dstate.totalPages), dstate.totalPages, false, dstate.currentPage===dstate.totalPages)); }
    ul.appendChild(makeItem('»', Math.min(dstate.totalPages, dstate.currentPage + 1), dstate.currentPage === dstate.totalPages, false));

    nav.appendChild(ul); container.appendChild(nav);
    requestAnimationFrame(()=>{ const p = container.querySelector('.pagination'); if (p) p.classList.add('show'); });
  }

  document.addEventListener('DOMContentLoaded', function(){ dRecalc(); });

  window._discountsPagination = { 
    setPerPage: function(n){ dstate.perPage = Math.max(1, Number(n)||PER_PAGE_D); dstate.currentPage = 1; dRecalc(); }, 
    goToPage: function(p){ dstate.currentPage = Math.min(Math.max(1, Number(p)||1), dstate.totalPages); dRecalc(); } 
  };

  window._discountsRecalc = function(){ try { dRecalc(); } catch(e){ console.error('dRecalc error', e); } };

  window.filterDiscounts = function(){
    try {
      dstate.currentPage = 1;
      dRecalc();
    } catch (err) { console.error('filterDiscounts error', err); }
  };
  
  // Helper functions for date filter
  window.setDiscountDateToday = function() {
    const today = new Date().toISOString().split('T')[0];
    const dateInput = document.getElementById('discountDateFilter');
    if (dateInput) {
      dateInput.value = today;
      filterDiscounts();
    }
  };
  
  window.clearDiscountDate = function() {
    const dateInput = document.getElementById('discountDateFilter');
    if (dateInput) {
      dateInput.value = '';
      filterDiscounts();
    }
  };
  
  window.resetDiscountFilters = function() {
    document.getElementById('discountDateFilter').value = '';
    document.querySelectorAll('.type-filter-btn').forEach(b => b.classList.remove('active'));
    document.querySelector('.type-filter-btn[data-type=""]').classList.add('active');
    document.getElementById('discountTypeFilter').value = '';
    filterDiscounts();
  };
})();

// Delegated handler for approve/reject buttons
document.addEventListener('click', function(e){
  const btn = e.target.closest('.discount-action');
  if (!btn) return;
  const bookingId = btn.dataset.bookingId;
  const action = btn.dataset.action; // approve|reject
  if (!bookingId || !action) return;

  const confirmFn = window.showConfirm || window.showConfirmModal || function(msg){ return showConfirm(msg); };
  const alertFn = window.showAdminAlert || function(msg, type){ try { showToast(msg, type || 'info'); } catch(e){ console.log(msg); } };

  confirmFn('Are you sure you want to ' + action + ' this discount application?').then(function(confirmed){
    if (!confirmed) return;
    btn.disabled = true;

    let removeSpinner = function(){};
    try {
      const parentEl = btn.closest('.table-responsive') || document.querySelector('#discountsTable');
      if (typeof showTableSpinner === 'function') {
        removeSpinner = showTableSpinner(parentEl);
      } else {
        try {
          const parent = parentEl && parentEl.closest && parentEl.closest('.table-responsive') ? parentEl.closest('.table-responsive') : (parentEl || document.body);
          const prevPos = parent.style.position || '';
          const computed = window.getComputedStyle(parent).position;
          if (computed === 'static') parent.style.position = 'relative';
          const overlay = document.createElement('div'); overlay.className = 'table-spinner-overlay'; overlay.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
          parent.appendChild(overlay);
          removeSpinner = function(){ try { if (overlay && overlay.parentNode) overlay.parentNode.removeChild(overlay); } catch(e){}; try { if (computed === 'static') parent.style.position = prevPos || ''; } catch(e){} };
        } catch (e) { /* ignore */ }
      }
    } catch(e) { /* ignore */ }

    const body = 'action=admin_discount&booking_id=' + encodeURIComponent(bookingId) + '&discount_action=' + encodeURIComponent(action);
    fetch('database/user_auth.php', {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: body
    }).then(r => r.json()).then(json => {
      try { removeSpinner(); } catch(e){}
      if (json && json.success) {
        const row = document.getElementById('discount-row-' + bookingId);
        if (row) {
          const actionsCell = row.querySelector('td:last-child');
          if (actionsCell) {
            actionsCell.innerHTML = action === 'approve' ? '<span class="badge bg-success">Approved</span>' : '<span class="badge bg-danger">Rejected</span>';
          }
          row.classList.add('table-success');
        }
        alertFn(json.message || 'Discount updated', 'success');
      } else {
        alertFn((json && (json.error || json.message)) || 'Failed to update discount', 'error');
        btn.disabled = false;
      }
    }).catch(err => {
      try { removeSpinner(); } catch(e){}
      console.error(err);
      alertFn('Request failed — check console', 'error');
      btn.disabled = false;
    });
  });
});

// View proof modal handler
document.addEventListener('click', function(e){
  const el = e.target.closest('.view-proof');
  if (!el) return;
  e.preventDefault();
  const proof = el.getAttribute('data-proof');
  if (!proof) return;

  const modalId = 'proof-modal-' + Date.now();
  const modalHTML = `
    <div class="modal fade" id="${modalId}" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Discount Proof</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body text-center p-3">
            <div class="proof-spinner" style="display:none;">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </div>
            <img src="" alt="Proof" style="max-width:100%; height:auto; border-radius:6px; box-shadow:0 6px 20px rgba(0,0,0,0.12);" class="proof-image" />
          </div>
          <div class="modal-footer">
            <a href="#" target="_blank" class="btn btn-link proof-download">Open in new tab</a>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
  `;

  document.body.insertAdjacentHTML('beforeend', modalHTML);
  const modalEl = document.getElementById(modalId);
  const img = modalEl.querySelector('.proof-image');
  modalEl.querySelector('.proof-download').href = proof;
  try {
    const bs = new bootstrap.Modal(modalEl);
    bs.show();
    img.src = proof;
    modalEl.addEventListener('hidden.bs.modal', function(){ modalEl.remove(); });
  } catch (err) {
    console.error('Failed to show proof modal', err);
    window.open(proof, '_blank');
  }
});
</script>

<style>
.pagination { opacity: 0; transition: opacity 200ms ease-in; }
.pagination.show { opacity: 1; }
.table-spinner-overlay { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255,255,255,0.8); display: flex; align-items: center; justify-content: center; z-index: 10; }
.table-spinner-overlay .spinner-border { width: 2.4rem; height: 2.4rem; }
</style>
