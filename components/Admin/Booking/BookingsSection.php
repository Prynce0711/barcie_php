<?php
// Bookings Section Template
// This section displays bookings management and discount applications
?>

<!-- Bookings Management Section -->
<div id="bookings-tab" class="booking-tab-content" style="display:block;">
  <?php ob_start(); ?>
  <div class="d-flex align-items-center gap-2 flex-wrap py-1">
    <?php $dateScope = 'bookings'; include __DIR__ . '/../../Filter/DateFilter.php'; ?>
    <div class="vr d-none d-md-block" style="height:28px;"></div>
    <?php $searchScope = 'bookings'; $searchPlaceholder = 'Search guest or booking...'; include __DIR__ . '/../../Filter/Searchbar.php'; ?>
    <div class="vr d-none d-md-block" style="height:28px;"></div>
    <select class="form-select form-select-sm" id="statusFilter" style="width:auto; min-width:130px;">
      <option value="">All Status</option>
      <option value="pending">Pending</option>
      <option value="approved">Approved</option>
      <option value="confirmed">Confirmed</option>
      <option value="checked_in">Checked In</option>
      <option value="checked_out">Checked Out</option>
      <option value="cancelled">Cancelled</option>
      <option value="rejected">Rejected</option>
    </select>
    <?php $filterScope = 'bookings'; include __DIR__ . '/../../Filter/FilterTypes.php'; ?>
    <select class="form-select d-none" id="typeFilter"><option value="">All Types</option><option value="room">Room</option><option value="facility">Facility</option></select>
    <div class="ms-auto d-flex align-items-center gap-2">
      <?php $resetScope = 'bookings'; include __DIR__ . '/../../Filter/ResetFilter.php'; ?>
      <button type="button" class="btn btn-sm btn-outline-success" onclick="downloadBookingsExcel()">
        <i class="fas fa-file-excel me-1"></i>Excel
      </button>
      <button type="button" class="btn btn-sm btn-success" onclick="downloadBookingsPDF()">
        <i class="fas fa-file-alt me-1"></i>Text
      </button>
    </div>
  </div>
  <?php $sectionFilters = ob_get_clean(); ?>
  <?php
  $sectionTitle    = 'Bookings Management';
  $sectionIcon     = 'fa-calendar-alt';
  $sectionSubtitle = 'Manage all guest reservations and bookings';
  include __DIR__ . '/../Shared/SectionHeader.php';
  ?>
  <div class="row mb-4">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <!-- Bridge: sync reusable components → existing filter logic -->
          <script>
          (function(){
            function sync(){ if(typeof filterBookings==='function') filterBookings(); }
            document.addEventListener('date-filter-changed', function(e){
              if(e.detail.scope!=='bookings') return;
              var el=document.getElementById('bookingDateFilter');
              if(!el){el=document.createElement('input');el.type='hidden';el.id='bookingDateFilter';document.body.appendChild(el);}
              el.value=e.detail.from||'';
              var elTo=document.getElementById('bookingDateFilterTo');
              if(!elTo){elTo=document.createElement('input');elTo.type='hidden';elTo.id='bookingDateFilterTo';document.body.appendChild(elTo);}
              elTo.value=e.detail.to||'';
              sync();
            });
            document.addEventListener('search-changed', function(e){
              if(e.detail.scope!=='bookings') return;
              var el=document.getElementById('guestSearch');
              if(!el){el=document.createElement('input');el.type='hidden';el.id='guestSearch';document.body.appendChild(el);}
              el.value=e.detail.value||'';
              sync();
            });
            document.addEventListener('filter-changed', function(e){
              if(e.detail.scope!=='bookings') return;
              var f=e.detail&&e.detail.filter||'';
              var el=document.getElementById('typeFilter');
              if(el) el.value=(f==='all'?'':f);
              sync();
            });
            var st=document.getElementById('statusFilter');
            if(st) st.addEventListener('change', sync);
            document.addEventListener('filters-reset', function(e){
              if(e.detail&&e.detail.scope&&e.detail.scope!=='bookings') return;
              var st2=document.getElementById('statusFilter');if(st2) st2.value='';
              if(typeof resetFilters==='function') resetFilters();
            });
          })();
          </script>

          <!-- Bookings Table -->
          <div id="admin_discount_alert" class="mb-2"></div>
          <?php
          $tableId = 'bookingsTable';
          $tableScope = 'bookings';
          $tablePageSize = 10;
          $tableColumns = [
              ['label' => 'Reservation No.', 'width' => '8%'],
              ['label' => 'Room/Facility',   'width' => '11%'],
              ['label' => 'Type',            'width' => '5%'],
              ['label' => 'Guest Details',   'width' => '15%'],
              ['label' => 'Schedule',        'width' => '12%'],
              ['label' => 'Booking Status',  'width' => '9%'],
              ['label' => 'Discount Status', 'width' => '9%'],
              ['label' => 'Approved',        'width' => '8%'],
              ['label' => 'Actions',         'width' => '9%'],
          ];
          include __DIR__ . '/../../Table/Table.php';
          ?>
                <?php include __DIR__ . '/BookingsTableContent.php'; ?>
          <?php $tableClose = true; include __DIR__ . '/../../Table/Table.php'; ?>
        </div>
      </div>
    </div>
  </div>
  <script>
    (function () {
      // Delegated handler for approve/reject buttons
      document.addEventListener('click', function (e) {
        const btn = e.target.closest('.discount-action');
        if (!btn) return;
        const bookingId = btn.dataset.bookingId;
        const action = btn.dataset.action; // approve|reject
        if (!bookingId || !action) return;

        const confirmFn = window.showConfirmModal || function () { return Promise.resolve(false); };
        const alertFn = window.showAdminAlert || function (msg, type) {
          if (typeof window.showToast === 'function') {
            window.showToast(msg, type || 'info');
          } else {
            console.log(msg);
          }
        };

        confirmFn('Are you sure you want to ' + action + ' this discount application?').then(function (confirmed) {
          if (!confirmed) return;
          btn.disabled = true;
          // show spinner overlay over discounts table while request runs
          let removeSpinner = function () { };
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
                removeSpinner = function () { try { if (overlay && overlay.parentNode) overlay.parentNode.removeChild(overlay); } catch (e) { }; try { if (computed === 'static') parent.style.position = prevPos || ''; } catch (e) { } };
              } catch (e) { /* ignore fallback errors */ }
            }
          } catch (e) { /* ignore */ }

          const body = 'action=admin_update_discount&booking_id=' + encodeURIComponent(bookingId) + '&discount_action=' + encodeURIComponent(action);
          fetch('database/index.php?endpoint=user_auth', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
              'X-Requested-With': 'XMLHttpRequest'
            },
            body: body
          }).then(r => r.json()).then(json => {
            try { removeSpinner(); } catch (e) { }
            if (json && json.success) {
              const statusCell = document.getElementById('discount-row-' + bookingId)?.querySelector('td:nth-child(6)');
              if (statusCell) statusCell.innerHTML = action === 'approve' ? '<span class="badge bg-success">Approved</span>' : '<span class="badge bg-danger">Rejected</span>';
              alertFn(json.message || 'Discount updated', action === 'approve' ? 'success' : 'danger');
              setTimeout(() => { const row = document.getElementById('discount-row-' + bookingId); if (row) row.classList.add('table-success'); }, 50);
            } else {
              alertFn((json && (json.error || json.message)) || 'Failed to update discount', 'danger');
              btn.disabled = false;
            }
          }).catch(err => {
            try { removeSpinner(); } catch (e) { }
            console.error(err);
            alertFn('Request failed — check console', 'danger');
            btn.disabled = false;
          });
        });
      });
    })();
  </script>
  <script>
    (function () {
      // Sync type filter buttons with hidden select and call filterBookings()
      function setTypeFilter(type, trigger) {
        const sel = document.getElementById('typeFilter');
        if (!sel) return;
        sel.value = type;
        // Update button active state
        document.querySelectorAll('.type-filter-btn').forEach(b => {
          b.classList.remove('active');
        });
        const btn = document.querySelector('.type-filter-btn[data-type="' + type + '"]');
        if (btn) {
          btn.classList.add('active');
        }
        if (trigger !== false) {
          try {
            if (typeof window.filterBookings === 'function') {
              window.filterBookings();
            }
          } catch (e) { console.error('filterBookings() error', e); }
        }
      }

      document.addEventListener('click', function (e) {
        const btn = e.target.closest('.type-filter-btn');
        if (!btn) return;
        const type = btn.getAttribute('data-type') || '';
        setTypeFilter(type);
      });

      document.addEventListener('DOMContentLoaded', function () {
        const sel = document.getElementById('typeFilter');
        if (sel) {
          const cur = sel.value || '';
          const initial = document.querySelector('.type-filter-btn[data-type="' + cur + '"]');
          if (initial) {
            document.querySelectorAll('.type-filter-btn').forEach(b => b.classList.remove('active'));
            initial.classList.add('active');
          }
        }
      });

      // Client-side filtering implementation
      try {
        if (typeof window.filterBookings === 'function') {
          window._serverFilterBookings = window.filterBookings;
        }
      } catch (e) { console.warn(e); }

      // filterBookings is a trigger to recalc the unified BarcieTable pagination
      window.filterBookings = function () {
        if (window.BarcieTable && window.BarcieTable.bookings) {
          window.BarcieTable.bookings.refresh();
        }
      };

      // Helper functions for date filter
      window.setBookingDateToday = function () {
        const today = new Date().toISOString().split('T')[0];
        const dateInput = document.getElementById('bookingDateFilter');
        if (dateInput) {
          dateInput.value = today;
          filterBookings();
        }
        // Update button styles
        const todayBtn = document.getElementById('todayFilterBtn');
        const allBtn = document.getElementById('allFilterBtn');
        if (todayBtn && allBtn) {
          todayBtn.className = 'btn btn-sm btn-primary';
          allBtn.className = 'btn btn-sm btn-outline-secondary';
        }
      };

      window.clearBookingDate = function () {
        const dateInput = document.getElementById('bookingDateFilter');
        if (dateInput) {
          dateInput.value = '';
          filterBookings();
        }
        // Update button styles
        const todayBtn = document.getElementById('todayFilterBtn');
        const allBtn = document.getElementById('allFilterBtn');
        if (todayBtn && allBtn) {
          todayBtn.className = 'btn btn-sm btn-outline-primary';
          allBtn.className = 'btn btn-sm btn-secondary';
        }
      };

      // DateFilter component handles initialization (localStorage restore + dispatch).
      // No manual DOMContentLoaded override needed.

    })();
  </script>
  <script>
    // Delegated handler to open proof images in a modal with spinner and action buttons
    (function () {
      document.addEventListener('click', function (e) {
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
            loader.onload = function () {
              clearTimeout(preloadTimeout);
              // remove spinner element from DOM entirely
              const s = modalEl.querySelector('.proof-spinner'); if (s && s.parentNode) s.parentNode.removeChild(s);
              img.src = proof;
              img.style.opacity = 0;
              img.style.display = '';
              // fade-in
              setTimeout(() => { img.style.transition = 'opacity 240ms ease-in'; img.style.opacity = 1; }, 10);
              downloadLink.href = proof;
            };
            loader.onerror = function () {
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
              try { loader.onload(); } catch (e) { }
            }

            // safety timeout
            preloadTimeout = setTimeout(function () {
              const s = modalEl.querySelector('.proof-spinner'); if (s && s.parentNode) s.parentNode.removeChild(s);
              img.style.display = 'none';
              downloadLink.textContent = 'Open in new tab (timed out)';
              retryBtn.style.display = '';
            }, 15000);
          }

          // Start initial preload
          startPreload();

          // Retry handler
          retryBtn.addEventListener('click', function () {
            retryBtn.style.display = 'none';
            startPreload();
          });

          // Approve/Reject handlers
          const performAction = function (action) {
            const confirmFn = window.showConfirm || window.showConfirmModal || function (msg) { return showConfirm(msg); };
            const alertFn = window.showAdminAlert || function (msg, type) { try { showToast(msg, type || 'info'); } catch (e) { console.log(msg); } };

            confirmFn('Are you sure you want to ' + action + ' this discount application?').then(function (confirmed) {
              if (!confirmed) return;
              approveBtn.disabled = true;
              rejectBtn.disabled = true;

              const body = 'action=admin_update_discount&booking_id=' + encodeURIComponent(bookingId) + '&discount_action=' + encodeURIComponent(action);
              fetch('database/index.php?endpoint=user_auth', {
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
                  try { bs.hide(); } catch (e) { }
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

          approveBtn.addEventListener('click', function () { performAction('approve'); });
          rejectBtn.addEventListener('click', function () { performAction('reject'); });

          modalEl.addEventListener('hidden.bs.modal', function () { modalEl.remove(); });
        } catch (err) {
          console.error('Failed to show proof modal', err);
          // Fallback: open in new tab
          window.open(proof, '_blank');
        }
      });
    })();
  </script>

  <script>
    (function () {
      // Register filter function with unified BarcieTable pagination
      function doesRowMatchFilter(row) {
        var status = (document.getElementById('statusFilter')?.value || '').toLowerCase();
        var type = (document.getElementById('typeFilter')?.value || '').toLowerCase();
        // Read date from hidden bridge elements first, fallback to DateFilter API
        var dateFilter = document.getElementById('bookingDateFilter')?.value || '';
        var dateFilterTo = document.getElementById('bookingDateFilterTo')?.value || '';
        if (!dateFilter && !dateFilterTo && window.DateFilter && window.DateFilter['bookings']) {
          var vals = window.DateFilter['bookings'].getValues();
          dateFilter = vals.from || '';
          dateFilterTo = vals.to || '';
        }
        // Read search from hidden bridge element first, fallback to Searchbar API
        var guestSearch = (document.getElementById('guestSearch')?.value || '').toLowerCase();
        if (!guestSearch && window.Searchbar && window.Searchbar['bookings']) {
          guestSearch = (window.Searchbar['bookings'].getValue() || '').toLowerCase();
        }
        var rstatus = (row.dataset.status || '').toLowerCase();
        var rtype = (row.dataset.type || '').toLowerCase();
        var rdate = row.dataset.date || '';

        if (status && rstatus.indexOf(status) === -1) return false;
        if (type && rtype.indexOf(type) === -1) return false;
        if (dateFilter && rdate < dateFilter) return false;
        if (dateFilterTo && rdate > dateFilterTo) return false;
        if (guestSearch && row.textContent.toLowerCase().indexOf(guestSearch) === -1) return false;
        return true;
      }

      function registerFilter() {
        if (window.BarcieTable && window.BarcieTable.bookings) {
          window.BarcieTable.bookings.setFilter(doesRowMatchFilter);
        } else {
          setTimeout(registerFilter, 50);
        }
      }

      // filterBookings = trigger for filter changes → refresh pagination
      window.filterBookings = function () {
        if (window.BarcieTable && window.BarcieTable.bookings) {
          window.BarcieTable.bookings.refresh();
        }
      };

      // Register immediately if BarcieTable is ready, otherwise retry
      registerFilter();
    })();
  </script>
</div>
<!-- End Bookings Management Section -->

<!-- Payment Verification moved to its own dashboard section (see main dashboard include) -->

<script>

  // Download bookings as text backup
  function downloadBookingsPDF() {
    const rows = Array.from(document.querySelectorAll('#bookingsTable tbody tr')).filter(row => {
      return row.style.display !== 'none' && !row.id;
    });

    if (rows.length === 0) {
      showToast('No bookings to export with current filters', 'warning');
      return;
    }

    const dateFilter = document.getElementById('bookingDateFilter')?.value || 'All Dates';
    const statusFilter = document.getElementById('statusFilter')?.value || 'All Status';
    const typeFilter = document.getElementById('typeFilter')?.value || 'All Types';

    let content = `BARCIE INTERNATIONAL CENTER - BOOKINGS BACKUP
Generated: ${new Date().toLocaleString()}
Total Records: ${rows.length}

FILTERS APPLIED:
- Date: ${dateFilter}
- Status: ${statusFilter}
- Type: ${typeFilter}

${'='.repeat(80)}

`;

    rows.forEach((row, index) => {
      const cells = row.querySelectorAll('td');
      if (cells.length >= 8) {
        const receipt = cells[0].textContent.trim();
        const room = cells[1].textContent.trim();
        const type = cells[2].textContent.trim();
        const guest = cells[3].textContent.trim().replace(/\n/g, ' ');
        const schedule = cells[4].textContent.trim().replace(/\n/g, ' ');
        const status = cells[5].textContent.trim();
        const discount = cells[6].textContent.trim();
        const created = cells[7].textContent.trim().replace(/\n/g, ' ');

        content += `${index + 1}. ${receipt}
   Room/Facility: ${room}
   Type: ${type}
   Guest: ${guest}
   Schedule: ${schedule}
   Status: ${status}
   Discount: ${discount}
   Created: ${created}
${'-'.repeat(80)}

`;
      }
    });

    const blob = new Blob([content], { type: 'text/plain' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `bookings_backup_${new Date().toISOString().split('T')[0]}.txt`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);

    showToast('Bookings backup downloaded successfully', 'success');
  }

  // Download bookings as Excel
  function downloadBookingsExcel() {
    const rows = Array.from(document.querySelectorAll('#bookingsTable tbody tr')).filter(row => {
      return row.style.display !== 'none' && !row.id;
    });

    if (rows.length === 0) {
      showToast('No bookings to export with current filters', 'warning');
      return;
    }

    const dateFilter = document.getElementById('bookingDateFilter')?.value || 'All Dates';
    const statusFilter = document.getElementById('statusFilter')?.value || 'All Status';
    const typeFilter = document.getElementById('typeFilter')?.value || 'All Types';

    let csv = 'Reservation No.,Room/Facility,Type,Guest Name,Guest Contact,Schedule,Booking Status,Discount Status,Created\n';

    rows.forEach(row => {
      const cells = row.querySelectorAll('td');
      if (cells.length >= 8) {
        const receipt = cells[0].textContent.trim().replace(/,/g, ';');
        const room = cells[1].textContent.trim().replace(/,/g, ';');
        const type = cells[2].textContent.trim().replace(/,/g, ';');
        const guestText = cells[3].textContent.trim().replace(/\n/g, ' ').replace(/,/g, ';');
        const guestParts = guestText.split(/[📞✉]/);
        const guestName = guestParts[0].trim();
        const guestContact = guestParts.slice(1).join(' | ').trim();
        const schedule = cells[4].textContent.trim().replace(/\n/g, ' | ').replace(/,/g, ';');
        const status = cells[5].textContent.trim().replace(/,/g, ';');
        const discount = cells[6].textContent.trim().replace(/,/g, ';');
        const created = cells[7].textContent.trim().replace(/\n/g, ' ').replace(/,/g, ';');

        csv += `"${receipt}","${room}","${type}","${guestName}","${guestContact}","${schedule}","${status}","${discount}","${created}"\n`;
      }
    });

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `bookings_${dateFilter.replace(/[^0-9-]/g, '') || 'all'}_${new Date().toISOString().split('T')[0]}.csv`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);

    showToast(`Exported ${rows.length} bookings to Excel (Filters: Date=${dateFilter}, Status=${statusFilter}, Type=${typeFilter})`, 'success');
  }

  // Role-based access control for Bookings section
  // Staff: Can add data but CANNOT edit/delete/approve (❌)
  // Admin/Manager/Super Admin: Full access (✓)
  (function () {
    function applyBookingsRoleRestrictions() {
      const role = (window.currentAdmin && window.currentAdmin.role) || 'staff';
      console.log('Applying bookings restrictions for role:', role);

      if (role === 'staff') {
        // Hide all action buttons (edit, delete, approve, reject, status change)
        document.querySelectorAll('.booking-action-btn, .btn-danger, .btn-warning, .discount-action').forEach(btn => {
          const btnText = btn.textContent.toLowerCase();
          const isViewBtn = btnText.includes('view') || btnText.includes('details') || btn.classList.contains('btn-info');
          if (!isViewBtn) {
            btn.style.display = 'none';
          }
        });

        // Disable export buttons
        const exportBtns = document.querySelectorAll('[onclick*="downloadBookings"]');
        exportBtns.forEach(btn => btn.style.display = 'none');

        console.log('Bookings: Staff restricted from edit/delete/approve actions');
      }
    }

    // Run on load
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', applyBookingsRoleRestrictions);
    } else {
      applyBookingsRoleRestrictions();
    }

    // Re-run when table content changes
    const observer = new MutationObserver(applyBookingsRoleRestrictions);
    const bookingsTable = document.querySelector('#bookingsTable tbody');
    if (bookingsTable) {
      observer.observe(bookingsTable, { childList: true, subtree: true });
    }

    // Also observe discount table
    const discountsTable = document.querySelector('#discountsTable tbody');
    if (discountsTable) {
      observer.observe(discountsTable, { childList: true, subtree: true });
    }

    setTimeout(applyBookingsRoleRestrictions, 200);
  })();
</script>
