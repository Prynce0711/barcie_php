<?php
// Bookings Section Template
// This section displays bookings management and discount applications
?>

<!-- Bookings Management -->
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
              <select class="form-select" id="typeFilter" onchange="filterBookings()">
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
        </div>
      </div>
    </div>
  </div>
  <script>
    (function(){
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
              const statusCell = document.getElementById('discount-row-' + bookingId)?.querySelector('td:nth-child(6)');
              if (statusCell) statusCell.innerHTML = action === 'approve' ? '<span class="badge bg-success">Approved</span>' : '<span class="badge bg-danger">Rejected</span>';
              alertFn(json.message || 'Discount updated', action === 'approve' ? 'success' : 'danger');
              setTimeout(()=>{ const row = document.getElementById('discount-row-' + bookingId); if (row) row.classList.add('table-success'); }, 50);
            } else {
              alertFn((json && (json.error || json.message)) || 'Failed to update discount', 'danger');
              btn.disabled = false;
            }
          }).catch(err => {
            console.error(err);
            alertFn('Request failed — check console', 'danger');
            btn.disabled = false;
          });
        });
      });
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

  <!-- Discount Applications Section -->
  <div class="row mb-4">
    <div class="col-12">
    <!-- Discount Applications Table -->
    <div class="card">
      <div class="card-header bg-secondary text-white">
        <h6 class="mb-0"><i class="fas fa-id-card-alt me-2"></i>Discount Applications (Pending)</h6>
        <small class="opacity-75">Review uploaded ID proofs and approve or reject discounts.</small>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle">
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

                    // Try to extract guest name and discount type from details if available
                    $guest = 'Guest';
                    $discountType = '';
                    if (preg_match('/Guest:\s*([^|]+)/', $details, $m)) $guest = trim($m[1]);
                    if (preg_match('/Discount:\s*([^|]+)/', $details, $m)) $discountType = trim($m[1]);

                    // Use dedicated proof_of_id column when available
                    $proofPath = $row['proof_of_id'] ?: '';

                    echo '<tr id="discount-row-' . $bookingId . '">';
                    echo '<td><strong>' . htmlspecialchars($receipt) . '</strong></td>';
                    echo '<td>' . htmlspecialchars($guest) . '</td>';
                    echo '<td>' . htmlspecialchars($room) . '</td>';
                    echo '<td>' . htmlspecialchars($discountType ?: '—') . '</td>';
                    echo '<td>' . htmlspecialchars(($checkin ? date('M j, Y', strtotime($checkin)) : '—') . ' - ' . ($checkout ? date('M j, Y', strtotime($checkout)) : '—')) . '</td>';
                    echo '<td>';
                    if (!empty($proofPath) && file_exists(__DIR__ . '/../../' . $proofPath)) {
                      $url = '../' . ltrim($proofPath, '/');
                      // Use a view-proof anchor with data-proof so JS can open a modal instead of a new tab
                      echo '<a href="#" class="view-proof" data-proof="' . htmlspecialchars($url) . '" data-booking-id="' . $bookingId . '"><img src="' . htmlspecialchars($url) . '" alt="Proof Image" style="max-width: 120px; max-height: 80px; object-fit:cover; border-radius:4px; border:1px solid #e9ecef;"></a>';
                    } elseif (!empty($proofPath)) {
                      // If file doesn't exist at expected relative path, still provide view-proof link (external/full path)
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
      </div>
    </div>
    </div>
  </div>
