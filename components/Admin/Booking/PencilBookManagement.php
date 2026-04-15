<?php
// Pencil Book Management Section
// This section displays pencil bookings management specifically

require_once __DIR__ . '/../Shared/BadgeHelper.php';

// Set timezone to ensure consistent time display
date_default_timezone_set('Asia/Manila');
?>

<!-- Pencil Book Management -->
<?php ob_start(); ?>
<div class="d-flex align-items-center gap-2 flex-wrap py-1">
  <?php $dateScope = 'pencil'; include __DIR__ . '/../../Filter/DateFilter.php'; ?>
  <div class="vr d-none d-md-block" style="height:28px;"></div>
  <select class="form-select form-select-sm" id="pencilStatusFilter" style="width:auto; min-width:130px;">
    <option value="">All Statuses</option>
    <option value="pending">Pending</option>
    <option value="approved">Approved</option>
    <option value="confirmed">Confirmed</option>
    <option value="cancelled">Cancelled</option>
    <option value="rejected">Rejected</option>
  </select>
  <div class="ms-auto d-flex align-items-center gap-2">
    <?php $resetScope = 'pencil'; include __DIR__ . '/../../Filter/ResetFilter.php'; ?>
    <button type="button" class="btn btn-sm btn-outline-warning" onclick="downloadPencilBookingsExcel()">
      <i class="fas fa-file-excel me-1"></i>Excel
    </button>
    <button type="button" class="btn btn-sm btn-warning" onclick="downloadPencilBookingsPDF()">
      <i class="fas fa-file-alt me-1"></i>Text
    </button>
  </div>
</div>
<?php $sectionFilters = ob_get_clean(); ?>
<?php
$sectionTitle    = 'Pencil Book Management';
$sectionIcon     = 'fa-pencil-alt';
$sectionSubtitle = 'Manage all pencil bookings - tentative reservations awaiting confirmation.';
include __DIR__ . '/../Shared/SectionHeader.php';
?>
<div class="row mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <!-- Bridge: sync reusable components → existing pencil filter logic -->
        <script>
        (function(){
          function sync(){ if(typeof filterPencilBookings==='function') filterPencilBookings(); }
          document.addEventListener('date-filter-changed', function(e){
            if(e.detail.scope!=='pencil') return;
            var el=document.getElementById('pencilDateFilter');
            if(!el){el=document.createElement('input');el.type='hidden';el.id='pencilDateFilter';document.body.appendChild(el);}
            el.value=e.detail.from||'';
            sync();
          });
          var st=document.getElementById('pencilStatusFilter');
          if(st) st.addEventListener('change', sync);
          document.addEventListener('filters-reset', function(e){
            if(e.detail&&e.detail.scope&&e.detail.scope!=='pencil') return;
            var st2=document.getElementById('pencilStatusFilter');if(st2) st2.value='';
            sync();
          });
        })();
        </script>

        <!-- Pencil Bookings Table -->
        <div id="pencil_alert" class="mb-2"></div>
        <?php
        $tableId = 'pencilTable';
        $tableScope = 'pencil';
        $tablePageSize = 10;
        $tableColumns = [
            ['label' => 'Pencil Book No.'],
            ['label' => 'Room/Facility'],
            ['label' => 'Guest Details'],
            ['label' => 'Schedule'],
            ['label' => 'Status'],
            ['label' => 'Expires'],
            ['label' => 'Created'],
            ['label' => 'Actions'],
        ];
        include __DIR__ . '/../../Table/Table.php';
        ?>
              <?php
              // Query pencil bookings from dedicated table
              $pencilBookings = $conn->query("SELECT pb.*, i.name as room_name, i.room_number,
                                              DATEDIFF(pb.token_expires_at, NOW()) as days_remaining
                                              FROM pencil_bookings pb
                                              LEFT JOIN items i ON pb.room_id = i.id 
                                              ORDER BY pb.created_at DESC");
              while ($booking = $pencilBookings->fetch_assoc()):
                $badge_color = admin_badge_booking_status_class($booking['status'] ?? '');

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
                <tr data-status="<?= htmlspecialchars($booking['status'] ?? '') ?>"
                  data-date="<?= htmlspecialchars($booking_date) ?>"
                  data-guest="<?= htmlspecialchars(($guest_name ?? '') . ' ' . ($guest_phone ?? '') . ' ' . ($guest_email ?? '') . ' ' . ($room_facility ?? '') . ' ' . ($booking['details'] ?? '')) ?>">
                  <td>
                    <strong
                      style="font-size: 0.7rem;">BARCIE-<?= date('Ymd', strtotime($booking['created_at'])) ?>-<?= str_pad($booking['id'], 4, '0', STR_PAD_LEFT) ?></strong>
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
                        <small style="font-size: 0.65rem;"><i class="fas fa-phone"></i>
                          <?= htmlspecialchars($guest_phone) ?></small><br>
                      <?php endif; ?>
                      <?php if ($guest_email): ?>
                        <small style="font-size: 0.65rem;" class="text-truncate d-inline-block" style="max-width: 150px;"><i
                            class="fas fa-envelope"></i> <?= htmlspecialchars($guest_email) ?></small>
                      <?php endif; ?>
                    </div>
                  </td>
                  <td>
                    <div style="line-height: 1.3; font-size: 0.7rem;">
                      <strong>In:</strong> <?= date('M j, Y', strtotime($booking['checkin'])) ?><br>
                      <small class="text-muted"
                        style="font-size: 0.65rem;"><?= date('H:i', strtotime($booking['checkin'])) ?></small><br>
                      <strong>Out:</strong> <?= date('M j, Y', strtotime($booking['checkout'])) ?><br>
                      <small class="text-muted"
                        style="font-size: 0.65rem;"><?= date('H:i', strtotime($booking['checkout'])) ?></small>
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
                      <small class="text-muted"
                        style="font-size: 0.65rem;"><?php echo $expiry_field ? date('M j, Y', strtotime($expiry_field)) : 'N/A'; ?></small>
                    </div>
                  </td>
                  <td>
                    <div style="font-size: 0.7rem; line-height: 1.3;">
                      <?= date('M j, Y', strtotime($booking['created_at'])) ?><br>
                      <small class="text-muted"
                        style="font-size: 0.65rem;"><?= date('H:i', strtotime($booking['created_at'])) ?></small>
                    </div>
                  </td>
                  <td>
                    <div class="d-flex flex-column" style="gap: 0.25rem;">
                      <button class="btn btn-info btn-sm view-pencil-btn"
                        onclick="viewPencilBookingDetails(<?= $booking['id'] ?>)"
                        style="font-size: 0.65rem; padding: 0.3rem 0.5rem;">
                        <i class="fas fa-eye"></i> View
                      </button>

                      <?php if ($booking['status'] === 'pending'): ?>
                        <button class="btn btn-success btn-sm pencil-action-btn"
                          onclick="updatePencilBookingStatus(<?= $booking['id'] ?>, 'approved')"
                          style="font-size: 0.65rem; padding: 0.3rem 0.5rem;">
                          <i class="fas fa-check"></i> Approve
                        </button>
                        <button class="btn btn-danger btn-sm pencil-action-btn"
                          onclick="updatePencilBookingStatus(<?= $booking['id'] ?>, 'rejected')"
                          style="font-size: 0.65rem; padding: 0.3rem 0.5rem;">
                          <i class="fas fa-times"></i> Reject
                        </button>
                      <?php elseif ($booking['status'] === 'approved'): ?>
                        <button class="btn btn-secondary btn-sm pencil-action-btn"
                          onclick="updatePencilBookingStatus(<?= $booking['id'] ?>, 'cancelled')"
                          style="font-size: 0.65rem; padding: 0.3rem 0.5rem;">
                          <i class="fas fa-ban"></i> Cancel
                        </button>
                        <small class="text-info" style="font-size: 0.6rem;">
                          <i class="fas fa-info-circle"></i> Auto-converts when guest proceeds
                        </small>
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
        <?php $tableClose = true; include __DIR__ . '/../../Table/Table.php'; ?>
      </div>
    </div>
  </div>
</div>

<script>
  (function () {
    // Hide pencil booking action buttons for staff (except view)
    function hideStaffPencilActions() {
      const role = (window.currentAdmin && window.currentAdmin.role) || 'staff';
      if (role === 'staff') {
        document.querySelectorAll('.pencil-action-btn').forEach(btn => {
          btn.style.display = 'none';
        });
      }
    }

    // Run on load and after table updates
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', hideStaffPencilActions);
    } else {
      hideStaffPencilActions();
    }

    // Re-run when pencil table updates
    const observer = new MutationObserver(hideStaffPencilActions);
    const pencilTable = document.querySelector('#pencilTable tbody');
    if (pencilTable) {
      observer.observe(pencilTable, { childList: true, subtree: true });
    }

    // Register filter function with unified BarcieTable pagination
    function doesRowMatchFilter(row) {
      var status = (document.getElementById('pencilStatusFilter')?.value || '').toLowerCase();
      var dateFilter = document.getElementById('pencilDateFilter')?.value || '';
      // Fallback: read from DateFilter API
      if (!dateFilter && window.DateFilter && window.DateFilter['pencil']) {
        var vals = window.DateFilter['pencil'].getValues();
        dateFilter = vals.from || '';
      }
      var rstatus = (row.dataset.status || '').toLowerCase();
      var rdate = row.dataset.date || '';

      if (status && rstatus.indexOf(status) === -1) return false;
      if (dateFilter && rdate !== dateFilter) return false;
      return true;
    }

    function registerFilter() {
      if (window.BarcieTable && window.BarcieTable.pencil) {
        window.BarcieTable.pencil.setFilter(doesRowMatchFilter);
      } else {
        setTimeout(registerFilter, 50);
      }
    }

    window.filterPencilBookings = function () {
      if (window.BarcieTable && window.BarcieTable.pencil) {
        window.BarcieTable.pencil.refresh();
      }
    };

    // Register immediately if BarcieTable is ready, otherwise retry
    registerFilter();
  })();

  // Pencil Booking Management Functions
  function viewPencilBookingDetails(bookingId) {
    fetch(`database/index.php?endpoint=user_auth&action=get_pencil_booking_details&id=${bookingId}`)
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          const booking = data.booking;
          const details = `
          <div class="booking-details">
            <h5>Pencil Booking Details</h5>
            <table class="table table-sm">
              <tr><th>Pencil Book No:</th><td>${booking.receipt_no}</td></tr>
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
            document.getElementById('pencilDetailsModal').addEventListener('hidden.bs.modal', function () {
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
    if (typeof window.showConfirm !== 'function') {
      if (typeof window.showToast === 'function') {
        window.showToast('Confirmation popup is not available right now.', 'error');
      }
      return;
    }

    const confirmed = await window.showConfirm(`Are you sure you want to ${newStatus} this pencil booking?`, {
      title: 'Update Pencil Booking',
      confirmText: 'Yes, Continue',
      confirmClass: 'btn-primary',
      cancelText: 'Cancel'
    });

    if (!confirmed) {
      return;
    }

    const formData = new FormData();
    formData.append('action', 'update_pencil_booking_status');
    formData.append('booking_id', bookingId);
    formData.append('status', newStatus);

    fetch('database/index.php?endpoint=user_auth', {
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
  window.setPencilDateToday = function () {
    const today = new Date().toISOString().split('T')[0];
    const dateInput = document.getElementById('pencilDateFilter');
    if (dateInput) {
      dateInput.value = today;
      filterPencilBookings();
    }
  };

  window.clearPencilDate = function () {
    const dateInput = document.getElementById('pencilDateFilter');
    if (dateInput) {
      dateInput.value = '';
      filterPencilBookings();
    }
  };

  // DateFilter component handles initialization (localStorage restore + dispatch).
  // No manual DOMContentLoaded override needed.

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

    let csv = 'Pencil Book No.,Room/Facility,Guest Name,Guest Contact,Schedule,Status,Expires,Created\n';

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
