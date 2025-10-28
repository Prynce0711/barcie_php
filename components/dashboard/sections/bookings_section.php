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
                  <th>Room/Facility</th>
                  <th>Type</th>
                  <th>Schedule</th>
                  <th>Status</th>
                  <th>Date Applied</th>
                  <th>Proof of ID</th> <!-- Added column for uploaded ID -->
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php
                // Include uploaded discount proof images in the discount applications section
                $discountApplications = [
                  [
                    'guest_name' => 'John Doe',
                    'room_facility' => 'Room 101',
                    'type' => 'Room',
                    'schedule' => '2025-10-30',
                    'status' => 'Pending',
                    'date_applied' => '2025-10-28',
                    'proof_image' => 'uploads/discount_proof_john_doe.jpg' // Example uploaded proof
                  ],
                  // Add more applications as needed
                ];

                foreach ($discountApplications as $application) {
                  echo '<tr>';
                  echo '<td>' . htmlspecialchars($application['guest_name']) . '</td>';
                  echo '<td>' . htmlspecialchars($application['room_facility']) . '</td>';
                  echo '<td>' . htmlspecialchars($application['type']) . '</td>';
                  echo '<td>' . htmlspecialchars($application['schedule']) . '</td>';
                  echo '<td>' . htmlspecialchars($application['status']) . '</td>';
                  echo '<td>' . htmlspecialchars($application['date_applied']) . '</td>';
                  echo '<td>';
                  if (!empty($application['proof_image'])) {
                    echo '<img src="' . htmlspecialchars($application['proof_image']) . '" alt="Proof Image" style="max-width: 100px; max-height: 100px;">';
                  } else {
                    echo 'No ID uploaded';
                  }
                  echo '</td>';
                  echo '<td>';
                  echo '<button class="btn btn-success btn-sm">Approve</button> ';
                  echo '<button class="btn btn-danger btn-sm">Reject</button>';
                  echo '</td>';
                  echo '</tr>';
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
