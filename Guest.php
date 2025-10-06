<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit;
}

include __DIR__ . '/database/db_connect.php';
$user_id = $_SESSION['user_id'];

$success = $error = "";

// ✅ Handle Feedback Submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === "feedback") {
  $message = trim($_POST['message']);
  if (!empty($message)) {
    $stmt = $conn->prepare("INSERT INTO feedback (user_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $message);
    if ($stmt->execute()) {
      $success = "Feedback submitted successfully!";
    } else {
      $error = "Error: " . $stmt->error;
    }
  } else {
    $error = "Feedback cannot be empty.";
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
  <!-- Custom CSS -->
  <link rel="stylesheet" href="assets/css/guest.css">
  <link rel="stylesheet" href="assets/css/guest-enhanced.css">
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
    <button class="btn btn-outline-light mb-2 text-start" onclick="showSection('payments')">
      <i class="fas fa-credit-card me-2"></i>Payments
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

      <!-- Payments -->
      <section id="payments" class="content-section">
        <h2>Payments</h2>
        <p>Choose a payment method:</p>
        <form>
          <label><input type="radio" name="payment" checked> Credit Card</label>
          <label><input type="radio" name="payment"> GCash</label>
          <label><input type="radio" name="payment"> PayPal</label>
          <button type="submit">Pay Now</button>
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
        <h2>Feedback</h2>
        <?php
        if (!empty($success))
          echo "<p style='color:green;'>$success</p>";
        if (!empty($error))
          echo "<p style='color:red;'>$error</p>";
        ?>
        <form method="post">
          <input type="hidden" name="action" value="feedback">
          <textarea name="message" rows="5" placeholder="Write your feedback..." required></textarea><br><br>
          <button type="submit">Submit Feedback</button>
        </form>
      </section>
    </div> <!-- Close container-fluid -->
  </main>

  <footer class="footer">
    <p>© BarCIE International Center 2025</p>
  </footer>






</body>

</html>