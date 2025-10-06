<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit;
}

include __DIR__ . '/database/db_connect.php';
$user_id = $_SESSION['user_id'];

$success = $error = "";

// Check for session messages
if (isset($_SESSION['feedback_success'])) {
    $success = $_SESSION['feedback_success'];
    unset($_SESSION['feedback_success']);
}
if (isset($_SESSION['feedback_error'])) {
    $error = $_SESSION['feedback_error'];
    unset($_SESSION['feedback_error']);
}

// Initialize feedback table if it doesn't exist
try {
    // First create the table without foreign key constraints
    $createTableQuery = "CREATE TABLE IF NOT EXISTS feedback (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        rating INT NOT NULL DEFAULT 5,
        message TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_rating (rating),
        INDEX idx_created_at (created_at)
    )";
    
    $conn->query($createTableQuery);
    
    // Check if rating column exists, add if missing
    $result = $conn->query("SHOW COLUMNS FROM feedback LIKE 'rating'");
    if ($result && $result->num_rows == 0) {
        $conn->query("ALTER TABLE feedback ADD COLUMN rating INT NOT NULL DEFAULT 5 AFTER user_id");
    }
    
    // Add check constraint for rating if it doesn't exist
    $conn->query("ALTER TABLE feedback ADD CONSTRAINT chk_rating CHECK (rating >= 1 AND rating <= 5)");
    
} catch (Exception $e) {
    // Log error but don't stop execution
    error_log("Error initializing feedback table: " . $e->getMessage());
}

// ✅ Handle Feedback Submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === "feedback") {
  $message = trim($_POST['message'] ?? '');
  $rating = (int)($_POST['rating'] ?? 0);
  
  if ($rating < 1 || $rating > 5) {
    $error = "Please select a star rating.";
  } else {
    try {
      // Ensure table exists before inserting
      $conn->query("CREATE TABLE IF NOT EXISTS feedback (
          id INT AUTO_INCREMENT PRIMARY KEY,
          user_id INT NOT NULL,
          rating INT NOT NULL DEFAULT 5,
          message TEXT,
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          INDEX idx_user_id (user_id),
          INDEX idx_rating (rating),
          INDEX idx_created_at (created_at)
      )");
      
      $stmt = $conn->prepare("INSERT INTO feedback (user_id, rating, message) VALUES (?, ?, ?)");
      $stmt->bind_param("iis", $user_id, $rating, $message);
      
      if ($stmt->execute()) {
        $success = "Thank you for your " . $rating . "-star feedback!";
      } else {
        $error = "Error submitting feedback. Please try again.";
      }
      $stmt->close();
    } catch (Exception $e) {
      $error = "Error submitting feedback. Please try again.";
      error_log("Feedback submission error: " . $e->getMessage());
    }
  }
}

// ✅ Fetch current user details
$stmt = $conn->prepare("SELECT username, email FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username, $email);
$stmt->fetch();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="user-id" content="<?php echo $user_id; ?>">
  <link rel="icon" type="image/png" href="assets/images/imageBg/barcie_logo.jpg">
  <title>Guest Portal</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <!-- FullCalendar CSS -->
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="assets/css/guest.css">
  <link rel="stylesheet" href="assets/css/guest-enhanced.css">
  
  <style>
    /* Calendar Legend Styles */
    .legend-color {
      width: 15px;
      height: 15px;
      border-radius: 3px;
      display: inline-block;
    }
    
    .availability-legend {
      background: #f8f9fa;
      padding: 1rem;
      border-radius: 0.5rem;
      border: 1px solid #dee2e6;
    }
    
    /* Calendar customization for better privacy display */
    .fc-event {
      border: none !important;
      font-size: 0.75rem;
    }
    
    .fc-event-title {
      font-weight: 500;
    }
    
    /* Responsive calendar */
    @media (max-width: 768px) {
      #guestCalendar {
        min-height: 250px !important;
      }
      
      .availability-legend {
        margin-top: 1rem;
      }
    }
    
    /* Item Details Modal Styling */
    .detail-item {
      padding: 0.5rem 0;
      border-bottom: 1px solid #f8f9fa;
    }
    
    .detail-item:last-child {
      border-bottom: none;
    }
    
    .modal-lg {
      max-width: 800px;
    }
    
    /* Card Action Buttons */
    .card-actions {
      display: flex;
      gap: 0.5rem;
      margin-top: auto;
      padding-top: 1rem;
    }
    
    .card-actions .btn {
      flex: 1;
      transition: all 0.3s ease;
    }
    
    .card-actions .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    
    /* Booking Form Highlight */
    .booking-form-highlight {
      animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
      0% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7); }
      70% { box-shadow: 0 0 0 10px rgba(40, 167, 69, 0); }
      100% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0); }
    }
  </style>
  
  <!-- FullCalendar JavaScript -->
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
  <!-- Bootstrap JavaScript -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/guest-bootstrap.js" defer></script>
</head>

<body>

  <!-- Mobile Menu Toggle -->
  <button class="mobile-menu-toggle d-lg-none" onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
  </button>

  <!-- Sidebar -->
  <aside class="sidebar-guest">
    <h2><i class="fas fa-user-circle me-2"></i>Guest Portal</h2>
    <button class="btn btn-outline-light mb-2 text-start" onclick="showSection('overview')">
      <i class="fas fa-home me-2"></i>Overview
    </button>
    <button class="btn btn-outline-light mb-2 text-start" onclick="showSection('rooms')">
      <i class="fas fa-door-open me-2"></i>Rooms & Facilities
    </button>
    <button class="btn btn-outline-light mb-2 text-start" onclick="showSection('booking')">
      <i class="fas fa-calendar-check me-2"></i>Booking & Reservation
    </button>
  
    <button class="btn btn-outline-light mb-2 text-start" onclick="showSection('user')">
      <i class="fas fa-user-cog me-2"></i>User Management
    </button>
    <button class="btn btn-outline-light mb-2 text-start" onclick="showSection('communication')">
      <i class="fas fa-comments me-2"></i>Communication
    </button>
    <button class="btn btn-outline-light mb-2 text-start" onclick="showSection('feedback')">
      <i class="fas fa-star me-2"></i>Feedback
    </button>
    <a href="index.php" class="btn btn-danger mt-3 text-start">
      <i class="fas fa-sign-out-alt me-2"></i>Log out
    </a>
  </aside>

  <!-- Main Content -->
  <main class="main-content">
    <div class="container-fluid">
      <header class="mb-4">
        <div class="row">
          <div class="col-12">
            <h1 class="display-6 text-center mb-3">Welcome to BarCIE International Center</h1>
            <p class="lead text-center text-muted">Explore rooms, manage bookings, and more.</p>
          </div>
        </div>
      </header>

      <!-- Overview -->
      <section id="overview" class="content-section active">
        <div class="row mb-4">
          <div class="col-12">
            <div class="card bg-primary text-white">
              <div class="card-body">
                <div class="row align-items-center">
                  <div class="col-md-8">
                    <h3 class="card-title mb-2">Welcome back!</h3>
                    <p class="card-text mb-0">Manage your bookings, explore our facilities, and enjoy your stay at
                      BarCIE International Center.</p>
                  </div>
                  <div class="col-md-4 text-center">
                    <i class="fas fa-hotel fa-3x opacity-75"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
          <div class="col-lg-3 col-md-6 mb-3">
            <div class="card text-center h-100">
              <div class="card-body">
                <div class="text-primary mb-3">
                  <i class="fas fa-bed fa-2x"></i>
                </div>
                <h4 class="card-title text-primary" id="total-rooms">0</h4>
                <p class="card-text text-muted">Total Rooms</p>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6 mb-3">
            <div class="card text-center h-100">
              <div class="card-body">
                <div class="text-success mb-3">
                  <i class="fas fa-building fa-2x"></i>
                </div>
                <h4 class="card-title text-success" id="total-facilities">0</h4>
                <p class="card-text text-muted">Facilities</p>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6 mb-3">
            <div class="card text-center h-100">
              <div class="card-body">
                <div class="text-warning mb-3">
                  <i class="fas fa-calendar-check fa-2x"></i>
                </div>
                <h4 class="card-title text-warning" id="total-bookings">0</h4>
                <p class="card-text text-muted">Your Bookings</p>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6 mb-3">
            <div class="card text-center h-100">
              <div class="card-body">
                <div class="text-info mb-3">
                  <i class="fas fa-check-circle fa-2x"></i>
                </div>
                <h4 class="card-title text-info" id="available-rooms">0</h4>
                <p class="card-text text-muted">Available Now</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-lg-3 col-md-6 mb-3">
                    <button
                      class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3"
                      onclick="showSection('rooms')">
                      <i class="fas fa-search fa-2x mb-2"></i>
                      <span>Browse Rooms</span>
                    </button>
                  </div>
                  <div class="col-lg-3 col-md-6 mb-3">
                    <button
                      class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3"
                      onclick="showSection('booking')">
                      <i class="fas fa-plus-circle fa-2x mb-2"></i>
                      <span>Make Booking</span>
                    </button>
                  </div>
                  <div class="col-lg-3 col-md-6 mb-3">
                    <button
                      class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3"
                      onclick="showSection('user')">
                      <i class="fas fa-list-alt fa-2x mb-2"></i>
                      <span>My Bookings</span>
                    </button>
                  </div>
                  <div class="col-lg-3 col-md-6 mb-3">
                    <button
                      class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3"
                      onclick="showSection('feedback')">
                      <i class="fas fa-star fa-2x mb-2"></i>
                      <span>Give Feedback</span>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Availability Calendar -->
        <div class="row mb-4">
          <div class="col-12">
            <div class="card">
              <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                  <i class="fas fa-calendar-alt me-2"></i>Room & Facility Availability
                </h5>
                <small class="opacity-75">View availability for planning your stay</small>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-8">
                    <div id="guestCalendar" style="min-height: 300px;"></div>
                  </div>
                  <div class="col-md-4">
                    <div class="availability-legend">
                      <h6 class="mb-3">Availability Legend</h6>
                      <div class="d-flex align-items-center mb-2">
                        <div class="legend-color bg-success me-2"></div>
                        <small>Available</small>
                      </div>
                      <div class="d-flex align-items-center mb-2">
                        <div class="legend-color bg-warning me-2"></div>
                        <small>Pending Booking</small>
                      </div>
                      <div class="d-flex align-items-center mb-2">
                        <div class="legend-color bg-danger me-2"></div>
                        <small>Occupied</small>
                      </div>
                      <div class="d-flex align-items-center mb-3">
                        <div class="legend-color bg-info me-2"></div>
                        <small>Checked In</small>
                      </div>
                      
                      <div class="availability-info mt-3">
                        <h6 class="text-muted">Privacy Notice</h6>
                        <small class="text-muted">
                          This calendar shows room/facility availability only. 
                          Guest information is kept private for security.
                        </small>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Activity / Featured Items -->
        <div class="row">
          <div class="col-lg-8 mb-4">
            <div class="card">
              <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-star me-2"></i>Featured Rooms & Facilities</h5>
              </div>
              <div class="card-body">
                <div id="featured-items" class="row">
                  <!-- Featured items will be populated by JavaScript -->
                </div>
                <div class="text-center mt-3">
                  <button class="btn btn-primary" onclick="showSection('rooms')">
                    View All Rooms & Facilities <i class="fas fa-arrow-right ms-1"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-4 mb-4">
            <div class="card">
              <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Quick Info</h5>
              </div>
              <div class="card-body">
                <div class="mb-3">
                  <h6 class="text-primary"><i class="fas fa-clock me-1"></i> Check-in Time</h6>
                  <p class="mb-0 text-muted">2:00 PM onwards</p>
                </div>
                <div class="mb-3">
                  <h6 class="text-primary"><i class="fas fa-clock me-1"></i> Check-out Time</h6>
                  <p class="mb-0 text-muted">12:00 PM</p>
                </div>
                <div class="mb-3">
                  <h6 class="text-primary"><i class="fas fa-phone me-1"></i> Contact</h6>
                  <p class="mb-0 text-muted">+63 912 345 6789</p>
                </div>
                <div class="mb-3">
                  <h6 class="text-primary"><i class="fas fa-wifi me-1"></i> WiFi</h6>
                  <p class="mb-0 text-muted">Free High-Speed Internet</p>
                </div>
                <div>
                  <h6 class="text-primary"><i class="fas fa-car me-1"></i> Parking</h6>
                  <p class="mb-0 text-muted">Complimentary Parking</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>




      <!-- Rooms & Facilities -->
      <section id="rooms" class="content-section active">
        <h2>Rooms & Facilities</h2>

        <label>Filter Type:
          <input type="radio" name="type" value="room" checked> Room
          <input type="radio" name="type" value="facility"> Facility
        </label>

        <div class="cards-grid" id="cards-grid"></div>


      </section>


      <!-- Booking -->
      <section id="booking" class="content-section">
        <h2>Booking & Reservation</h2>

        <!-- Select Booking Type -->
        <label><input type="radio" name="bookingType" value="reservation" checked onchange="toggleBookingForm()">
          Reservation</label>
        <label><input type="radio" name="bookingType" value="pencil" onchange="toggleBookingForm()"> Pencil Booking
          (Function Hall)</label>

        <!-- Reservation Form -->
        <form id="reservationForm" method="POST" action="database/user_auth.php" class="compact-form">
          <h3>Reservation Form</h3>
          <input type="hidden" name="action" value="create_booking">
          <input type="hidden" name="booking_type" value="reservation">

          <div class="form-grid">
            <label class="full-width">
              <span class="label-text">Official Receipt No.:</span>
              <input type="text" name="receipt_no" id="receipt_no" readonly>
            </label>

            <label class="full-width">
              <span class="label-text">Select Room *</span>
              <select name="room_id" id="room_select" required>
                <option value="">Choose a room...</option>
                <?php
                // Fetch available rooms from database
                $room_stmt = $conn->prepare("SELECT id, name, room_number, capacity, price FROM items WHERE item_type = 'room' ORDER BY name");
                $room_stmt->execute();
                $room_result = $room_stmt->get_result();
                while ($room = $room_result->fetch_assoc()) {
                  $room_display = $room['name'];
                  if ($room['room_number']) {
                    $room_display .= " (Room #" . $room['room_number'] . ")";
                  }
                  $room_display .= " - " . $room['capacity'] . " persons - ₱" . number_format($room['price']) . "/night";
                  echo "<option value='" . $room['id'] . "'>" . htmlspecialchars($room_display) . "</option>";
                }
                $room_stmt->close();
                ?>
              </select>
            </label>

            <label>
              <span class="label-text">Guest Name *</span>
              <input type="text" name="guest_name" required>
            </label>

            <label>
              <span class="label-text">Contact Number *</span>
              <input type="text" name="contact_number" required>
            </label>

            <label>
              <span class="label-text">Email Address *</span>
              <input type="email" name="email" required>
            </label>

            <label>
              <span class="label-text">Check-in Date & Time *</span>
              <input type="datetime-local" name="checkin" required>
            </label>

            <label>
              <span class="label-text">Check-out Date & Time *</span>
              <input type="datetime-local" name="checkout" required>
            </label>

            <label>
              <span class="label-text">Number of Occupants *</span>
              <input type="number" name="occupants" min="1" required>
            </label>

            <label>
              <span class="label-text">Company Affiliation</span>
              <input type="text" name="company" placeholder="Optional">
            </label>

            <label>
              <span class="label-text">Company Contact</span>
              <input type="text" name="company_contact" placeholder="Optional">
            </label>

            <button type="submit">Confirm Reservation</button>
          </div>
        </form>

        <!-- Pencil Booking Form -->
        <form id="pencilForm" method="POST" action="database/user_auth.php" class="compact-form" style="display:none;">
          <h3>Pencil Booking Form (Function Hall)</h3>
          <input type="hidden" name="action" value="create_booking">
          <input type="hidden" name="booking_type" value="pencil">

          <div class="form-grid">
            <label class="full-width">
              <span class="label-text">Date of Pencil Booking:</span>
              <input type="date" name="pencil_date" value="<?php echo date('Y-m-d'); ?>" readonly>
            </label>

            <label>
              <span class="label-text">Event Type *</span>
              <input type="text" name="event_type" required>
            </label>

            <label>
              <span class="label-text">Function Hall *</span>
              <input type="text" name="hall" required>
            </label>

            <label>
              <span class="label-text">Number of Pax *</span>
              <input type="number" name="pax" min="1" required>
            </label>

            <label>
              <span class="label-text">Time From *</span>
              <input type="time" name="time_from" required>
            </label>

            <label>
              <span class="label-text">Time To *</span>
              <input type="time" name="time_to" required>
            </label>

            <label>
              <span class="label-text">Food Provider/Caterer *</span>
              <input type="text" name="caterer" required>
            </label>

            <label>
              <span class="label-text">Contact Person *</span>
              <input type="text" name="contact_person" required>
            </label>

            <label>
              <span class="label-text">Contact Number *</span>
              <input type="text" name="contact_number" required>
            </label>

            <label>
              <span class="label-text">Company Affiliation</span>
              <input type="text" name="company" placeholder="Optional">
            </label>

            <label>
              <span class="label-text">Company Number</span>
              <input type="text" name="company_number" placeholder="Optional">
            </label>

            <button type="submit" onclick="return pencilReminder()">Submit Pencil Booking</button>
          </div>
        </form>

      </section>

   

      <!-- User Management -->
      <section id="user" class="content-section">
        <h2>User Management</h2>

        <form action="database/user_auth.php" method="POST">
          <h3>Update Profile</h3>
          <input type="hidden" name="action" value="update">

          <label>Username:
            <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
          </label>

          <label>Email:
            <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
          </label>

          <label>New Password (leave blank if unchanged):
            <input type="password" name="password" placeholder="Enter new password or leave blank">
          </label>

          <button type="submit">Update Profile</button>
        </form>

        <div class="bookings-section">
          <h3>📋 Your Bookings</h3>
          <?php
          $stmt = $conn->prepare("SELECT id, type, details, created_at, status FROM bookings WHERE user_id=? ORDER BY id DESC");
          $stmt->bind_param("i", $user_id);
          $stmt->execute();
          $result = $stmt->get_result();

          if ($result->num_rows > 0) {
            ?>
            <div class="table-container">
              <table>
                <thead>
                  <tr>
                    <th>Booking ID</th>
                    <th>Type</th>
                    <th>Details</th>
                    <th>Date Created</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  while ($row = $result->fetch_assoc()) {
                    $statusClass = 'status-' . strtolower($row['status']);
                    $bookingType = ucfirst($row['type']);
                    $formattedDate = date('M d, Y g:i A', strtotime($row['created_at']));

                    // Parse and format booking details with labels
                    $details = $row['details'];
                    $formattedDetails = '';

                    // Try to parse JSON details first
                    $detailsArray = json_decode($details, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($detailsArray)) {
                      // If it's JSON, format with labels
                      foreach ($detailsArray as $key => $value) {
                        if (!empty($value)) {
                          $label = ucwords(str_replace('_', ' ', $key));
                          $formattedDetails .= "<div class='detail-item'><span class='detail-label'>{$label}:</span><span class='detail-value'>{$value}</span></div>";
                        }
                      }
                    } else {
                      // If it's plain text, try to extract common patterns
                      $lines = explode(',', $details);
                      foreach ($lines as $line) {
                        $line = trim($line);
                        if (!empty($line)) {
                          if (strpos($line, ':') !== false) {
                            // Already has label
                            list($label, $value) = explode(':', $line, 2);
                            $formattedDetails .= "<div class='detail-item'><span class='detail-label'>" . trim($label) . ":</span><span class='detail-value'>" . trim($value) . "</span></div>";
                          } else {
                            // Plain text, add as general info
                            $formattedDetails .= "<div class='detail-item'><span class='detail-label'>Info:</span><span class='detail-value'>{$line}</span></div>";
                          }
                        }
                      }
                    }

                    // If no formatted details, show original
                    if (empty($formattedDetails)) {
                      $formattedDetails = "<div class='detail-item'><span class='detail-label'>Details:</span><span class='detail-value'>{$details}</span></div>";
                    }

                    echo "<tr>
                        <td><strong>#{$row['id']}</strong></td>
                        <td><span class='booking-type'>{$bookingType}</span></td>
                        <td><div class='booking-details'>{$formattedDetails}</div></td>
                        <td><span class='booking-date'>{$formattedDate}</span></td>
                        <td><span class='status-badge {$statusClass}'>{$row['status']}</span></td>
                      </tr>";
                  }
                  ?>
                </tbody>
              </table>
            </div>
            <?php
          } else {
            ?>
            <div class="no-bookings">
              <i>📅</i>
              <p>You don't have any bookings yet.</p>
              <p>Visit the <a href="#" onclick="showSection('booking', this)" style="color: #3498db;">Booking &
                  Reservation</a> section to make your first booking!</p>
            </div>
            <?php
          }
          ?>
        </div>
      </section>



      <!-- Communication -->
      <section id="communication" class="content-section">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                  <i class="fas fa-comments me-2"></i>Contact Support
                  <span id="unread-count" class="badge bg-danger ms-2" style="display: none;">0</span>
                </h5>
              </div>
              <div class="card-body p-0">
                <div class="row g-0">

                  <!-- Chat Area -->
                  <div class="col-12 d-flex flex-column">
                    <div class="p-3 border-bottom bg-light">
                      <div class="d-flex align-items-center">
                        <div class="avatar-circle bg-success text-white me-3">
                          <i class="fas fa-user-shield"></i>
                        </div>
                        <div>
                          <h6 class="mb-0">BarCIE Support Team</h6>
                          <small class="text-muted">We're here to help you!</small>
                        </div>
                        <div class="ms-auto">
                          <span class="badge bg-success">Online</span>
                        </div>
                      </div>
                    </div>

                    <!-- Messages Area -->
                    <div id="chat-messages" class="flex-grow-1 p-3"
                      style="height: 400px; overflow-y: auto; background: #f8f9fa;">
                      <div class="text-center text-muted">
                        <i class="fas fa-comment-dots fa-3x mb-3 opacity-25"></i>
                        <h5>Welcome to BarCIE Support</h5>
                        <p>Send us a message and we'll respond as soon as possible</p>
                      </div>
                    </div>

                    <!-- Message Input -->
                    <div class="p-3 border-top bg-white">
                      <form id="chat-form" class="d-flex">
                        <div class="input-group">
                          <input type="text" id="chat-input" class="form-control" placeholder="Type your message..."
                            required>
                          <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i>
                          </button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Contact Information -->
        <div class="row mt-4">
          <div class="col-md-6">
            <div class="card">
              <div class="card-header">
                <h6 class="mb-0">
                  <i class="fas fa-phone me-2"></i>Contact Information
                </h6>
              </div>
              <div class="card-body">
                <ul class="list-unstyled mb-0">
                  <li class="mb-2">
                    <i class="fas fa-envelope text-primary me-2"></i>
                    <strong>Email:</strong> info@barcie.com
                  </li>
                  <li class="mb-2">
                    <i class="fas fa-phone text-success me-2"></i>
                    <strong>Phone:</strong> +63 912 345 6789
                  </li>
                  <li class="mb-2">
                    <i class="fas fa-map-marker-alt text-danger me-2"></i>
                    <strong>Address:</strong> BarCIE International Center
                  </li>
                  <li>
                    <i class="fas fa-clock text-info me-2"></i>
                    <strong>Hours:</strong> 24/7 Support Available
                  </li>
                </ul>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="card">
              <div class="card-header">
                <h6 class="mb-0">
                  <i class="fas fa-question-circle me-2"></i>Quick Help
                </h6>
              </div>
              <div class="card-body">
                <div class="list-group list-group-flush">
                  <a href="#" class="list-group-item list-group-item-action"
                    onclick="sendQuickMessage('How do I make a reservation?')">
                    <i class="fas fa-calendar-plus me-2 text-primary"></i>How to make a reservation?
                  </a>
                  <a href="#" class="list-group-item list-group-item-action"
                    onclick="sendQuickMessage('What are your room rates?')">
                    <i class="fas fa-dollar-sign me-2 text-success"></i>Room rates and pricing
                  </a>
                  <a href="#" class="list-group-item list-group-item-action"
                    onclick="sendQuickMessage('What facilities do you have?')">
                    <i class="fas fa-building me-2 text-info"></i>Available facilities
                  </a>
                  <a href="#" class="list-group-item list-group-item-action"
                    onclick="sendQuickMessage('I need to cancel my booking')">
                    <i class="fas fa-times-circle me-2 text-danger"></i>Cancel or modify booking
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Feedback -->
      <section id="feedback" class="content-section">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                  <i class="fas fa-star me-2"></i>Share Your Experience
                </h5>
                <small class="text-white-50">Help us improve by rating your experience</small>
              </div>
              <div class="card-body">
                <?php
                if (!empty($success))
                  echo "<div class='alert alert-success'><i class='fas fa-check-circle me-2'></i>$success</div>";
                if (!empty($error))
                  echo "<div class='alert alert-danger'><i class='fas fa-exclamation-circle me-2'></i>$error</div>";
                ?>
                
                <form method="post" id="feedback-form">
                  <input type="hidden" name="action" value="feedback">
                  <input type="hidden" name="rating" id="rating-value" value="">
                  
                  <!-- Star Rating Section -->
                  <div class="mb-4">
                    <label class="form-label fw-bold">Rate Your Experience</label>
                    <div class="d-flex align-items-center">
                      <div class="star-rating me-3" id="star-rating">
                        <span class="star" data-rating="1">
                          <i class="fas fa-star"></i>
                        </span>
                        <span class="star" data-rating="2">
                          <i class="fas fa-star"></i>
                        </span>
                        <span class="star" data-rating="3">
                          <i class="fas fa-star"></i>
                        </span>
                        <span class="star" data-rating="4">
                          <i class="fas fa-star"></i>
                        </span>
                        <span class="star" data-rating="5">
                          <i class="fas fa-star"></i>
                        </span>
                      </div>
                      <small class="text-muted" id="rating-text">Click to rate</small>
                    </div>
                  </div>

                  <!-- Feedback Message -->
                  <div class="mb-4">
                    <label for="feedback-message" class="form-label fw-bold">Tell us more (optional)</label>
                    <textarea 
                      class="form-control" 
                      name="message" 
                      id="feedback-message"
                      rows="4" 
                      placeholder="Share specific details about your experience..."
                    ></textarea>
                  </div>

                  <!-- Submit Button -->
                  <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                      <i class="fas fa-info-circle me-1"></i>
                      Your feedback helps us serve you better
                    </small>
                    <button type="submit" class="btn btn-primary" id="submit-feedback" disabled>
                      <i class="fas fa-paper-plane me-2"></i>Submit Feedback
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>

        <!-- Previous Feedback -->
        <div class="row mt-4">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h6 class="mb-0">
                  <i class="fas fa-history me-2"></i>Your Previous Feedback
                </h6>
              </div>
              <div class="card-body">
                <div id="previous-feedback">
                  <?php
                  try {
                    // Check if feedback table exists
                    $tableExists = $conn->query("SHOW TABLES LIKE 'feedback'");
                    
                    if ($tableExists && $tableExists->num_rows > 0) {
                      // Check if rating column exists
                      $ratingColumnExists = $conn->query("SHOW COLUMNS FROM feedback LIKE 'rating'");
                      
                      if ($ratingColumnExists && $ratingColumnExists->num_rows > 0) {
                        // Fetch user's previous feedback with rating
                        $feedback_stmt = $conn->prepare("SELECT rating, message, created_at FROM feedback WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
                      } else {
                        // Fetch without rating column if it doesn't exist
                        $feedback_stmt = $conn->prepare("SELECT message, created_at FROM feedback WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
                      }
                      
                      $feedback_stmt->bind_param("i", $user_id);
                      $feedback_stmt->execute();
                      $feedback_result = $feedback_stmt->get_result();
                      
                      if ($feedback_result->num_rows > 0) {
                        while ($feedback = $feedback_result->fetch_assoc()) {
                          $rating = isset($feedback['rating']) ? $feedback['rating'] : 5; // Default to 5 if no rating
                          $stars = str_repeat('<i class="fas fa-star text-warning"></i>', $rating);
                          $stars .= str_repeat('<i class="far fa-star text-muted"></i>', 5 - $rating);
                          
                          echo "<div class='feedback-item border-bottom pb-3 mb-3'>
                                  <div class='d-flex justify-content-between align-items-center mb-2'>
                                    <div class='star-display'>{$stars}</div>
                                    <small class='text-muted'>" . date('M d, Y', strtotime($feedback['created_at'])) . "</small>
                                  </div>
                                  <p class='mb-0 text-muted'>" . htmlspecialchars($feedback['message'] ?: 'No additional comments') . "</p>
                                </div>";
                        }
                      } else {
                        echo "<div class='text-center text-muted py-3'>
                                <i class='fas fa-comment-slash fa-2x mb-3 opacity-50'></i>
                                <p>You haven't submitted any feedback yet.</p>
                                <small>Share your experience using the form above!</small>
                              </div>";
                      }
                      $feedback_stmt->close();
                    } else {
                      echo "<div class='text-center text-muted py-3'>
                              <i class='fas fa-info-circle fa-2x mb-3 text-info'></i>
                              <p>Feedback system is being initialized...</p>
                              <small>Please submit your first feedback to get started!</small>
                            </div>";
                    }
                  } catch (Exception $e) {
                    echo "<div class='text-center text-muted py-3'>
                            <i class='fas fa-exclamation-triangle fa-2x mb-3 text-warning'></i>
                            <p>Unable to load previous feedback at this time.</p>
                            <small>Your new feedback will still be saved successfully.</small>
                          </div>";
                    error_log("Error fetching feedback: " . $e->getMessage());
                  }
                  ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div> <!-- Close container-fluid -->
  </main>

  <footer class="footer">
    <p>© BarCIE International Center 2025</p>
  </footer>






</body>

</html>