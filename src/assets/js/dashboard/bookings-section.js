// Bookings Section JavaScript
// Initialize bookings functionality when document is ready

document.addEventListener('DOMContentLoaded', function () {
  initializeBookingsFiltering();
  initializeBookingsActions();
});

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
  document.getElementById('statusFilter').value = '';
  document.getElementById('typeFilter').value = '';
  document.getElementById('guestSearch').value = '';
  filterBookings();
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
}

function initializeBookingsActions() {
  // Initialize booking action functions
  console.log('Bookings actions initialized');
}

// Booking management functions
function updateBookingStatus(bookingId, newStatus) {
  if (!confirm(`Are you sure you want to change this booking status to "${newStatus.replace('_', ' ')}"?`)) {
    return;
  }

  // Create a form and submit it
  const form = document.createElement('form');
  form.method = 'POST';
  form.style.display = 'none';

  const actionInput = document.createElement('input');
  actionInput.name = 'action';
  actionInput.value = 'update_booking_status';
  form.appendChild(actionInput);

  const idInput = document.createElement('input');
  idInput.name = 'booking_id';
  idInput.value = bookingId;
  form.appendChild(idInput);

  const statusInput = document.createElement('input');
  statusInput.name = 'new_status';
  statusInput.value = newStatus;
  form.appendChild(statusInput);

  document.body.appendChild(form);
  form.submit();
}

function viewBookingDetails(bookingId) {
  // For now, just show an alert. You can implement a modal later
  alert(`Viewing details for booking #${bookingId}`);
  
  // TODO: Implement modal or redirect to booking details page
  // Example: showBookingModal(bookingId);
}

function deleteBooking(bookingId) {
  if (!confirm('Are you sure you want to delete this booking? This action cannot be undone.')) {
    return;
  }

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
}

// Discount management functions
function processDiscount(discountId, action) {
  const actionText = action === 'approved' ? 'approve' : 'reject';
  
  if (!confirm(`Are you sure you want to ${actionText} this discount application?`)) {
    return;
  }

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