<?php
// Bookings Section Template
// This section displays bookings management and discount applications
?>

<!-- Section Navigation Buttons -->
<div class="row mb-3">
  <div class="col-12">
    <div class="btn-group" role="group" aria-label="Booking sections">
      <button type="button" class="btn btn-primary section-nav-btn active" data-section="bookings-tab">
        <i class="fas fa-calendar-check me-1"></i> Booking Management
      </button>
      <button type="button" class="btn btn-outline-primary section-nav-btn" data-section="pencil-tab">
        <i class="fas fa-pencil-alt me-1"></i> Pencil Book Management
      </button>
      <button type="button" class="btn btn-outline-primary section-nav-btn" data-section="discounts-tab">
        <i class="fas fa-id-card-alt me-1"></i> Discount Applications
      </button>
      <button type="button" class="btn btn-outline-primary section-nav-btn" data-section="payments-tab">
        <i class="fas fa-credit-card me-1"></i> Payment Verification
      </button>
    </div>
  </div>
</div>

<!-- Bookings Management Section -->
<div id="bookings-tab" class="booking-tab-content" style="display:block;">
  <div class="row mb-4">
    <div class="col-12">
      <div class="card">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0">
            <i class="fas fa-calendar-alt me-2"></i>Bookings Management
          </h5>
          <small class="opacity-75">Manage all guest reservations and bookings</small>
        </div>
        <div class="card-body">
          <!-- Filter Controls -->
          <div class="row mb-3">
            <div class="col-md-3">
              <label class="form-label">Filter by Status:</label>
              <select class="form-select" id="statusFilter" onchange="filterBookings()">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="confirmed">Confirmed</option>
                <option value="checked_in">Checked In</option>
                <option value="checked_out">Checked Out</option>
                <option value="cancelled">Cancelled</option>
                <option value="rejected">Rejected</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Filter by Type:</label>
              <!-- Button group for quick type filtering (keeps select for compatibility but hidden) -->
              <div class="btn-group w-100 mb-2" role="group" aria-label="Type filter">
                <button type="button" class="btn btn-secondary type-filter-btn active" data-type=""><i class="fas fa-list me-1"></i>All</button>
                <button type="button" class="btn btn-outline-secondary type-filter-btn" data-type="room"><i class="fas fa-bed me-1"></i>Room</button>
                <button type="button" class="btn btn-outline-secondary type-filter-btn" data-type="facility"><i class="fas fa-building me-1"></i>Facility</button>
              </div>
              <select class="form-select d-none" id="typeFilter" onchange="filterBookings()">
                <option value="">All Types</option>
                <option value="room">Room</option>
                <option value="facility">Facility</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Search Guest:</label>
              <input type="text" class="form-control" id="guestSearch"
                placeholder="Search by guest name or booking details..." onkeyup="filterBookings()">
            </div>
            <div class="col-md-2">
              <label class="form-label">&nbsp;</label>
              <button class="btn btn-outline-secondary w-100" onclick="resetFilters()">
                <i class="fas fa-refresh me-1"></i>Reset
              </button>
            </div>
          </div>

          <!-- Bookings Table -->
    <div id="admin_discount_alert" class="mb-2"></div>
    <!-- Top pagination controls for bookings table -->
    <div id="bookingsPaginationTop" class="mb-2"></div>
    <div class="table-responsive">
      <table class="table table-hover align-middle" id="bookingsTable">
              <thead class="table-dark">
                <tr>
                  <th style="width: 7%;">Receipt #</th>
                  <th style="width: 10%;">Room/Facility</th>
                  <th style="width: 6%;">Type</th>
                  <th style="width: 15%;">Guest Details</th>
                  <th style="width: 11%;">Schedule</th>
                  <th style="width: 8%;">Booking Status</th>
                  <th style="width: 8%;">Discount Status</th>
                  <th style="width: 8%;">Created</th>
                  <th style="width: 9%;">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php include 'bookings_table_content.php'; ?>
              </tbody>
            </table>
          </div>
          <!-- Pagination controls for bookings table -->
          <div id="bookingsPagination" class="mt-2"></div>
        </div>
      </div>
    </div>
  </div>
  <script>
    (function(){
      // Small animations for pagination and row transitions
      const styleId = 'bookings-pagination-animations';
      if (!document.getElementById(styleId)) {
        const css = `
          /* fade rows when shown */
          #bookingsTable tbody tr { transition: opacity 220ms ease-in-out; }
          /* initial hidden state used by JS */
          #bookingsTable tbody tr[data-hidden-by-pagination="true"] { display: none !important; opacity: 0 !important; }
          /* pagination nav slide/fade */
          #bookingsPagination .pagination { opacity: 0; transform: translateY(6px); transition: opacity 180ms ease-out, transform 180ms ease-out; }
          #bookingsPagination .pagination.show { opacity: 1; transform: translateY(0); }
          /* small hover transition for page links */
          #bookingsPagination .page-link { transition: background-color 120ms ease, color 120ms ease; }
          /* table spinner overlay used between page transitions */
          .table-spinner-overlay { position: absolute; inset: 0; display:flex; align-items:center; justify-content:center; background: rgba(255,255,255,0.7); z-index: 60; }
          .table-spinner-overlay .spinner-border { width: 2.4rem; height: 2.4rem; }
        `;
        const s = document.createElement('style'); s.id = styleId; s.appendChild(document.createTextNode(css));
        document.head.appendChild(s);
      }
      // Delegated handler for approve/reject buttons
      document.addEventListener('click', function(e){
        const btn = e.target.closest('.discount-action');
        if (!btn) return;
        const bookingId = btn.dataset.bookingId;
        const action = btn.dataset.action; // approve|reject
        if (!bookingId || !action) return;

        const confirmFn = window.showConfirmModal || function(msg){ return Promise.resolve(confirm(msg)); };
        const alertFn = window.showAdminAlert || function(msg, type){ try { alert(msg); } catch(e){ console.log(msg); } };

        confirmFn('Are you sure you want to ' + action + ' this discount application?').then(function(confirmed){
          if (!confirmed) return;
          btn.disabled = true;
          // show spinner overlay over discounts table while request runs
          let removeSpinner = function(){};
          try {
            const parentEl = btn.closest('.table-responsive') || document.querySelector('#discountsTable');
            if (typeof showTableSpinner === 'function') {
              removeSpinner = showTableSpinner(parentEl);
            } else {
              // fallback: create a simple overlay
              try {
                const parent = parentEl && parentEl.closest && parentEl.closest('.table-responsive') ? parentEl.closest('.table-responsive') : (parentEl || document.body);
                const prevPos = parent.style.position || '';
                const computed = window.getComputedStyle(parent).position;
                if (computed === 'static') parent.style.position = 'relative';
                const overlay = document.createElement('div'); overlay.className = 'table-spinner-overlay'; overlay.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
                parent.appendChild(overlay);
                removeSpinner = function(){ try { if (overlay && overlay.parentNode) overlay.parentNode.removeChild(overlay); } catch(e){}; try { if (computed === 'static') parent.style.position = prevPos || ''; } catch(e){} };
              } catch (e) { /* ignore fallback errors */ }
            }
          } catch(e) { /* ignore */ }

          const body = 'action=admin_update_discount&booking_id=' + encodeURIComponent(bookingId) + '&discount_action=' + encodeURIComponent(action);
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
              const statusCell = document.getElementById('discount-row-' + bookingId)?.querySelector('td:nth-child(6)');
              if (statusCell) statusCell.innerHTML = action === 'approve' ? '<span class="badge bg-success">Approved</span>' : '<span class="badge bg-danger">Rejected</span>';
              alertFn(json.message || 'Discount updated', action === 'approve' ? 'success' : 'danger');
              setTimeout(()=>{ const row = document.getElementById('discount-row-' + bookingId); if (row) row.classList.add('table-success'); }, 50);
            } else {
              alertFn((json && (json.error || json.message)) || 'Failed to update discount', 'danger');
              btn.disabled = false;
            }
          }).catch(err => {
            try { removeSpinner(); } catch(e){}
            console.error(err);
            alertFn('Request failed — check console', 'danger');
            btn.disabled = false;
          });
        });
      });
    })();
  </script>
  <script>
    (function(){
      // Sync type filter buttons with hidden select and call filterBookings()
      function setTypeFilter(type, trigger){
        const sel = document.getElementById('typeFilter');
        if (!sel) return;
        sel.value = type;
        // update button visuals: active becomes filled, others outline
        document.querySelectorAll('.type-filter-btn').forEach(b=>{
          b.classList.remove('active');
          b.classList.remove('btn-secondary');
          b.classList.add('btn-outline-secondary');
        });
        const btn = document.querySelector('.type-filter-btn[data-type="' + type + '"]');
        if (btn) {
          btn.classList.add('active');
          btn.classList.remove('btn-outline-secondary');
          btn.classList.add('btn-secondary');
        }
        if (trigger !== false) {
          try {
            window.filterBookings && window.filterBookings();
            // Also trigger discounts and payment verification updates when changing type
            try { if (typeof window.filterDiscounts === 'function') window.filterDiscounts(); } catch(e){ console.error('filterDiscounts error', e); }
            try { if (typeof window.loadPaymentVerification === 'function') window.loadPaymentVerification(); } catch(e){ /* optional function */ }
            // Scroll bookings table into view for clarity
            const bookingsTable = document.getElementById('bookingsTable');
            if (bookingsTable && bookingsTable.scrollIntoView) bookingsTable.scrollIntoView({ behavior: 'smooth', block: 'start' });
          } catch(e){ console.error('filterBookings() error', e); }
        }
      }

      document.addEventListener('click', function(e){
        const btn = e.target.closest('.type-filter-btn');
        if (!btn) return;
        const type = btn.getAttribute('data-type') || '';
        setTypeFilter(type);
      });

      document.addEventListener('DOMContentLoaded', function(){
        const sel = document.getElementById('typeFilter');
        if (sel) {
          const cur = sel.value || '';
          const initial = document.querySelector('.type-filter-btn[data-type="' + cur + '"]');
          if (initial) {
            document.querySelectorAll('.type-filter-btn').forEach(b=>b.classList.remove('active'));
            initial.classList.add('active');
          }
        }
      });
    
      // Client-side filtering implementation
      try {
        if (typeof window.filterBookings === 'function') {
          // preserve previous implementation if any
          window._serverFilterBookings = window.filterBookings;
        }
      } catch(e) { console.warn(e); }

      window.filterBookings = function(){
        const status = (document.getElementById('statusFilter')?.value || '').toLowerCase();
        const type = (document.getElementById('typeFilter')?.value || '').toLowerCase();
        const query = (document.getElementById('guestSearch')?.value || '').toLowerCase().trim();
        const rows = document.querySelectorAll('#bookingsTable tbody tr');
        let visibleCount = 0;
        rows.forEach(row => {
          // skip template/comment rows
          if (row.closest('tbody') === null) return;
          const rstatus = (row.dataset.status || '').toLowerCase();
          const rtype = (row.dataset.type || '').toLowerCase();
          const rguest = (row.dataset.guest || row.innerText || '').toLowerCase();

          let show = true;
          if (status && rstatus.indexOf(status) === -1) show = false;
          if (type && rtype.indexOf(type) === -1) show = false;
          if (query && rguest.indexOf(query) === -1) show = false;

          row.style.display = show ? '' : 'none';
          if (show) visibleCount++;
        });

        // show a no-results row when filtered out completely
        const noId = 'bookings-no-results';
        let noRow = document.getElementById(noId);
        if (visibleCount === 0) {
          if (!noRow) {
            const cols = document.querySelectorAll('#bookingsTable thead th').length || 9;
            noRow = document.createElement('tr');
            noRow.id = noId;
            noRow.innerHTML = '<td colspan="' + cols + '" class="text-center text-muted">No bookings match your filters.</td>';
            const tbody = document.querySelector('#bookingsTable tbody');
            if (tbody) tbody.appendChild(noRow);
          }
        } else {
          if (noRow && noRow.parentNode) noRow.parentNode.removeChild(noRow);
        }
      };

    })();
  </script>
  <script>
    // Delegated handler to open proof images in a modal with spinner and action buttons
    (function(){
      document.addEventListener('click', function(e){
        const el = e.target.closest('.view-proof');
        if (!el) return;
        e.preventDefault();
        const proof = el.getAttribute('data-proof');
        const bookingId = el.getAttribute('data-booking-id') || '';
        if (!proof) return;

        const modalId = 'proof-modal-' + Date.now();
        const modalHTML = `
          <div class="modal fade" id="${modalId}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Proof of ID</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                        <div class="modal-body text-center p-3">
                          <div class="proof-spinner d-flex align-items-center justify-content-center" style="height:320px;">
                            <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
                          </div>
                          <img class="proof-image" src="" alt="Proof" style="display:none; max-width:100%; height:auto; border-radius:6px; box-shadow:0 6px 20px rgba(0,0,0,0.12);" />
                        </div>
                        <div class="modal-footer">
                          <a href="#" target="_blank" class="btn btn-link proof-download">Open in new tab</a>
                          <button type="button" class="btn btn-outline-secondary btn-retry" style="display:none;">Retry</button>
                          <button type="button" class="btn btn-success btn-approve">Approve</button>
                          <button type="button" class="btn btn-danger btn-reject">Reject</button>
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
              </div>
            </div>
          </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHTML);
        const modalEl = document.getElementById(modalId);
  const img = modalEl.querySelector('.proof-image');
  const spinner = modalEl.querySelector('.proof-spinner');
  const downloadLink = modalEl.querySelector('.proof-download');
  const retryBtn = modalEl.querySelector('.btn-retry');
  const approveBtn = modalEl.querySelector('.btn-approve');
  const rejectBtn = modalEl.querySelector('.btn-reject');

        // Set download href immediately
        downloadLink.href = proof;

        try {
          const bs = new bootstrap.Modal(modalEl);
          bs.show();

          // The new preload/retry logic below will handle loading, timeouts and retries.
            // Helper to create a fresh spinner element (used for retries)
            function createSpinnerElement() {
              const wrapper = document.createElement('div');
              wrapper.className = 'proof-spinner d-flex align-items-center justify-content-center';
              wrapper.style.height = '320px';
              wrapper.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
              return wrapper;
            }

            // Preload with timeout and DOM removal of spinner on success
            let preloadTimeout;
            function startPreload() {
              // ensure spinner exists
              if (!modalEl.querySelector('.proof-spinner')) {
                const newSpinner = createSpinnerElement();
                const body = modalEl.querySelector('.modal-body');
                body.insertBefore(newSpinner, img);
              }
              // hide image while loading
              img.style.display = 'none';
              downloadLink.textContent = 'Open in new tab';
              retryBtn.style.display = 'none';

              const loader = new Image();
              loader.onload = function() {
                clearTimeout(preloadTimeout);
                // remove spinner element from DOM entirely
                const s = modalEl.querySelector('.proof-spinner'); if (s && s.parentNode) s.parentNode.removeChild(s);
                img.src = proof;
                img.style.opacity = 0;
                img.style.display = '';
                // fade-in
                setTimeout(()=>{ img.style.transition = 'opacity 240ms ease-in'; img.style.opacity = 1; }, 10);
                downloadLink.href = proof;
              };
              loader.onerror = function() {
                clearTimeout(preloadTimeout);
                const s = modalEl.querySelector('.proof-spinner'); if (s && s.parentNode) s.parentNode.removeChild(s);
                img.style.display = 'none';
                downloadLink.textContent = 'Open in new tab (image failed to load)';
                retryBtn.style.display = '';
              };

              // start loader
              loader.src = proof;
              if (loader.complete) {
                // some browsers may mark cached images complete
                try { loader.onload(); } catch(e){}
              }

              // safety timeout
              preloadTimeout = setTimeout(function(){
                const s = modalEl.querySelector('.proof-spinner'); if (s && s.parentNode) s.parentNode.removeChild(s);
                img.style.display = 'none';
                downloadLink.textContent = 'Open in new tab (timed out)';
                retryBtn.style.display = '';
              }, 15000);
            }

            // Start initial preload
            startPreload();

            // Retry handler
            retryBtn.addEventListener('click', function(){
              retryBtn.style.display = 'none';
              startPreload();
            });

          // Approve/Reject handlers
          const performAction = function(action){
            const confirmFn = window.showConfirmModal || function(msg){ return Promise.resolve(confirm(msg)); };
            const alertFn = window.showAdminAlert || function(msg, type){ try { alert(msg); } catch(e){ console.log(msg); } };

            confirmFn('Are you sure you want to ' + action + ' this discount application?').then(function(confirmed){
              if (!confirmed) return;
              approveBtn.disabled = true;
              rejectBtn.disabled = true;

              const body = 'action=admin_update_discount&booking_id=' + encodeURIComponent(bookingId) + '&discount_action=' + encodeURIComponent(action);
              fetch('database/user_auth.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                  'Content-Type': 'application/x-www-form-urlencoded',
                  'X-Requested-With': 'XMLHttpRequest'
                },
                body: body
              }).then(r => r.json()).then(json => {
                if (json && json.success) {
                  // Update the row: replace actions cell with status badge
                  const row = document.getElementById('discount-row-' + bookingId);
                  if (row) {
                    const actionsCell = row.querySelector('td:last-child');
                    if (actionsCell) actionsCell.innerHTML = action === 'approve' ? '<span class="badge bg-success">Approved</span>' : '<span class="badge bg-danger">Rejected</span>';
                    row.classList.add('table-success');
                  }
                  alertFn(json.message || 'Discount ' + action + 'd', action === 'approve' ? 'success' : 'danger');
                  try { bs.hide(); } catch(e) {}
                } else {
                  alertFn((json && (json.error || json.message)) || 'Failed to update discount', 'danger');
                  approveBtn.disabled = false;
                  rejectBtn.disabled = false;
                }
              }).catch(err => {
                console.error(err);
                alertFn('Request failed — check console', 'danger');
                approveBtn.disabled = false;
                rejectBtn.disabled = false;
              });
            });
          };

          approveBtn.addEventListener('click', function(){ performAction('approve'); });
          rejectBtn.addEventListener('click', function(){ performAction('reject'); });

          modalEl.addEventListener('hidden.bs.modal', function(){ modalEl.remove(); });
        } catch (err) {
          console.error('Failed to show proof modal', err);
          // Fallback: open in new tab
          window.open(proof, '_blank');
        }
      });
    })();
  </script>
  
  <script>
    (function(){
  // Simple client-side pagination for #bookingsTable
  const PER_PAGE = 10;
  let state = { perPage: PER_PAGE, currentPage: 1, totalPages: 1 };
  // token to avoid overlapping fade animations
  let bookingsFadeToken = 0;

      function getAllRows(){
        return Array.from(document.querySelectorAll('#bookingsTable tbody tr')).filter(r => r.id !== 'bookings-no-results');
      }

      // Determine whether a row matches the current filter controls (independent of its current style)
      function doesRowMatchFilter(row){
        const status = (document.getElementById('statusFilter')?.value || '').toLowerCase();
        const type = (document.getElementById('typeFilter')?.value || '').toLowerCase();
        const query = (document.getElementById('guestSearch')?.value || '').toLowerCase().trim();
        const rstatus = (row.dataset.status || '').toLowerCase();
        const rtype = (row.dataset.type || '').toLowerCase();
        const rguest = (row.dataset.guest || row.innerText || '').toLowerCase();

        if (status && rstatus.indexOf(status) === -1) return false;
        if (type && rtype.indexOf(type) === -1) return false;
        if (query && rguest.indexOf(query) === -1) return false;
        return true;
      }

      // helper: returns a promise that resolves when the provided rows finish fading out (or after timeout)
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
            // ensure transition is set
            r.style.transition = r.style.transition || 'opacity 220ms ease-in-out';
            // listen for transition end
            r.addEventListener('transitionend', onEnd);
            // start fade
            requestAnimationFrame(() => { r.style.opacity = 0; });
            // safety timeout in case transitionend doesn't fire
            setTimeout(() => { try { r.removeEventListener('transitionend', onEnd); } catch(e){}; finishOne(r); }, timeout);
          });
        });
      }

      // show a small overlay spinner over a table container; returns a remover function
      function showTableSpinner(container){
        try {
          if (!container) return function(){};
          // find table-responsive ancestor if a table element was passed
          let parent = container.closest && container.closest('.table-responsive') ? container.closest('.table-responsive') : container;
          // ensure positioned parent so absolute overlay is positioned correctly
          const prevPos = parent.style.position || '';
          const computed = window.getComputedStyle(parent).position;
          if (computed === 'static') parent.style.position = 'relative';

          const overlay = document.createElement('div');
          overlay.className = 'table-spinner-overlay';
          overlay.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
          parent.appendChild(overlay);

          return function removeSpinner(){
            try { if (overlay && overlay.parentNode) overlay.parentNode.removeChild(overlay); } catch(e){}
            try { if (computed === 'static') parent.style.position = prevPos || ''; } catch(e){}
          };
        } catch (err) { return function(){}; }
      }

      async function recalcPagination(){
        const rows = getAllRows();
        // Compute visibleRows based on filter criteria (not current style.display which pagination modifies)
        const visibleRows = rows.filter(r => doesRowMatchFilter(r));
        const totalVisible = visibleRows.length;
        state.totalPages = Math.max(1, Math.ceil(totalVisible / state.perPage));
        if (state.currentPage > state.totalPages) state.currentPage = state.totalPages;

        // Determine currently visible rows (those not hidden by pagination)
        const currentlyVisible = rows.filter(r => r.style.display !== 'none' && !r.hasAttribute('data-hidden-by-pagination'));

        // bump token to cancel overlapping ops
        const myToken = ++bookingsFadeToken;

  // show spinner while transitioning
  const removeSpinner = showTableSpinner(document.querySelector('#bookingsTable'));

  // fade out current page rows first
  await fadeOutRows(currentlyVisible);

  // if another pagination action started, abort showing this page and remove spinner
  if (myToken !== bookingsFadeToken) { removeSpinner(); return; }

  // Now hide all and show the new slice
  rows.forEach(r => { r.style.display = 'none'; r.setAttribute('data-hidden-by-pagination','true'); r.style.opacity = 0; });
        const start = (state.currentPage - 1) * state.perPage;
        const end = start + state.perPage;
        visibleRows.slice(start, end).forEach(r => {
          r.removeAttribute('data-hidden-by-pagination');
          r.style.display = '';
          r.style.opacity = 0;
          requestAnimationFrame(() => { r.style.transition = r.style.transition || 'opacity 220ms ease-in-out'; r.style.opacity = 1; });
        });
        // remove spinner after new rows are visible
        setTimeout(() => { try { removeSpinner(); } catch(e){} }, 220);
        renderPaginationControls();
      }

      function renderPaginationControls(){
        const container = document.getElementById('bookingsPagination');
        if (!container) return;
        // render into both top and bottom containers
        const topContainer = document.getElementById('bookingsPaginationTop');
        [container, topContainer].forEach(c => { if (c) c.innerHTML = ''; });
        if (state.totalPages <= 1) return; // no controls needed  

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
          btn.addEventListener('click', function(e){
            e.preventDefault();
            if (disabled) return;
            state.currentPage = page;
            recalcPagination();
          });
          li.appendChild(btn);
          return li;
        };

        // Prev
        ul.appendChild(createPageItem('«', Math.max(1, state.currentPage - 1), state.currentPage === 1, false));

        // page window
        const maxButtons = 7;
        let start = Math.max(1, state.currentPage - 3);
        let end = Math.min(state.totalPages, start + maxButtons - 1);
        if (end - start < maxButtons - 1) start = Math.max(1, end - maxButtons + 1);

        if (start > 1) {
          ul.appendChild(createPageItem('1', 1, false, state.currentPage === 1));
          if (start > 2) {
            const gap = document.createElement('li'); gap.className = 'page-item disabled'; gap.innerHTML = '<span class="page-link">…</span>'; ul.appendChild(gap);
          }
        }

        for (let p = start; p <= end; p++) {
          ul.appendChild(createPageItem(String(p), p, false, p === state.currentPage));
        }

        if (end < state.totalPages) {
          if (end < state.totalPages - 1) {
            const gap = document.createElement('li'); gap.className = 'page-item disabled'; gap.innerHTML = '<span class="page-link">…</span>'; ul.appendChild(gap);
          }
          ul.appendChild(createPageItem(String(state.totalPages), state.totalPages, false, state.currentPage === state.totalPages));
        }

        // Next
        ul.appendChild(createPageItem('»', Math.min(state.totalPages, state.currentPage + 1), state.currentPage === state.totalPages, false));

        nav.appendChild(ul);
        // append to bottom and top (if present)
        if (container) container.appendChild(nav.cloneNode(true));
        if (topContainer) topContainer.appendChild(nav.cloneNode(true));
        // animate pagination controls into view
        requestAnimationFrame(() => {
          const p1 = container && container.querySelector('.pagination'); if (p1) p1.classList.add('show');
          const p2 = topContainer && topContainer.querySelector('.pagination'); if (p2) p2.classList.add('show');
        });
      }

      // Wrap existing filterBookings so pagination recalculates after filters run
      document.addEventListener('DOMContentLoaded', function(){
        // Insert pagination container if not present (fallback)
        const tableResp = document.querySelector('#bookingsTable')?.closest('.table-responsive');
        if (tableResp) {
          let container = document.getElementById('bookingsPagination');
          if (!container) {
            container = document.createElement('div');
            container.id = 'bookingsPagination';
            container.className = 'mt-2';
            tableResp.parentNode.insertBefore(container, tableResp.nextSibling);
          }
          // ensure a top pagination container is present above the table
          let topContainer = document.getElementById('bookingsPaginationTop');
          if (!topContainer) {
            topContainer = document.createElement('div');
            topContainer.id = 'bookingsPaginationTop';
            topContainer.className = 'mb-2';
            tableResp.parentNode.insertBefore(topContainer, tableResp);
          }
        }

        const orig = window.filterBookings;
        if (typeof orig === 'function') {
          window.filterBookings = function(){
            // run original filter (which sets display on rows)
            try { orig(); } catch(e) { console.error('filterBookings error', e); }
            // reset to first page and recalc pagination
            state.currentPage = 1;
            recalcPagination();
          };
        }

        // initial pagination run
        recalcPagination();
      });

      // Expose API in case other scripts want to change page size or navigate
      window._bookingsPagination = {
        setPerPage: function(n){ state.perPage = Math.max(1, Number(n) || PER_PAGE); state.currentPage = 1; recalcPagination(); },
        goToPage: function(p){ state.currentPage = Math.min(Math.max(1, Number(p)||1), state.totalPages); recalcPagination(); },
        next: function(){ if (state.currentPage < state.totalPages) { state.currentPage++; recalcPagination(); } },
        prev: function(){ if (state.currentPage > 1) { state.currentPage--; recalcPagination(); } }
      };
    })();
  </script>
  </div>
</div>
<!-- End Bookings Management Section -->

<!-- Pencil Book Management Section -->
<div id="pencil-tab" class="booking-tab-content" style="display:none;">
  <?php include __DIR__ . '/pencil_book_management.php'; ?>
</div>

<!-- Discount Applications Section -->
<div id="discounts-tab" class="booking-tab-content" style="display:none;">
  <?php include __DIR__ . '/discount_applications.php'; ?>
</div>

<!-- Payment Verification Section -->
<div id="payments-tab" class="booking-tab-content" style="display:none;">
  <?php include __DIR__ . '/payment_verification.php'; ?>
</div>

<script>
// Section navigation handler
(function(){
  document.addEventListener('click', function(e){
    const btn = e.target.closest('.section-nav-btn');
    if (!btn) return;
    
    const targetSection = btn.getAttribute('data-section');
    if (!targetSection) return;
    
    // Update button states
    document.querySelectorAll('.section-nav-btn').forEach(b => {
      b.classList.remove('active', 'btn-primary');
      b.classList.add('btn-outline-primary');
    });
    btn.classList.remove('btn-outline-primary');
    btn.classList.add('btn-primary', 'active');
    
    // Hide all booking tabs only (not other sections in dashboard)
    document.querySelectorAll('.booking-tab-content').forEach(s => {
      s.style.display = 'none';
    });
    
    // Show target section
    const target = document.getElementById(targetSection);
    if (target) {
      target.style.display = 'block';
    }
  });
})();
</script>
