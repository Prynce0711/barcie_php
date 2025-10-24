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
          <div class="table-responsive">
            <table class="table table-striped table-hover" id="bookingsTable">
              <thead class="table-dark">
                <tr>
                  <th>Booking ID</th>
                  <th>Guest</th>
                  <th>Type</th>
                  <th>Details</th>
                  <th>Check-in</th>
                  <th>Check-out</th>
                  <th>Status</th>
                  <th>Created</th>
                  <th>Actions</th>
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

  <!-- Discount Applications Section -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="card">
        <div class="card-header bg-warning text-dark">
          <h5 class="mb-0">
            <i class="fas fa-percent me-2"></i>Discount Applications
          </h5>
          <small class="opacity-75">Review and approve/reject guest discount requests</small>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th>Guest Name</th>
                  <th>Discount Type</th>
                  <th>Percentage</th>
                  <th>Supporting Documents</th>
                  <th>Original Amount</th>
                  <th>Discounted Amount</th>
                  <th>Status</th>
                  <th>Date Applied</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php include 'discount_applications_content.php'; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
