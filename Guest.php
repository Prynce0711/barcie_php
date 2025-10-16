<?php
session_start();
include __DIR__ . '/database/db_connect.php';



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
  $rating = (int) ($_POST['rating'] ?? 0);

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

// Set default values for guest access
$username = "Guest";
$email = "";
$user_id = 0; // Default guest user ID
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
    /* Available Now Card Hover Effect */
    .available-now-card {
      transition: all 0.3s ease;
    }

    .available-now-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(23, 162, 184, 0.2);
      border-color: #17a2b8;
    }

    /* Availability Badge */
    .availability-badge {
      position: absolute;
      top: 10px;
      right: 10px;
      padding: 5px 10px;
      border-radius: 15px;
      font-size: 0.8rem;
      font-weight: bold;
      color: white;
      z-index: 10;
    }

    .availability-badge.available {
      background-color: #28a745;
      box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
    }

    .availability-badge.occupied {
      background-color: #dc3545;
      box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
    }

    .card-image {
      position: relative;
      overflow: hidden;
    }

    .available-now-card:hover .card-body {
      background: linear-gradient(135deg, #f8f9fa 0%, #e9f7fd 100%);
    }

    .available-now-card:active {
      transform: translateY(-2px);
    }

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

    /* Amenity Cards Styling */
    .amenity-card {
      transition: all 0.3s ease;
      border: 1px solid #e9ecef;
    }

    .amenity-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
      border-color: #667eea;
    }

    .amenity-card .card-img-top {
      transition: transform 0.3s ease;
    }

    .amenity-card:hover .card-img-top {
      transform: scale(1.05);
    }

    .amenity-card .card-body {
      position: relative;
      overflow: hidden;
    }

    .amenity-card:hover .card-body::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
      pointer-events: none;
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
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    /* Booking Form Highlight */
    .booking-form-highlight {
      animation: pulse 2s infinite;
    }

    @keyframes pulse {
      0% {
        box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
      }

      70% {
        box-shadow: 0 0 0 10px rgba(40, 167, 69, 0);
      }

      100% {
        box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
      }
    }
  </style>

  <!-- FullCalendar JavaScript -->
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
  <!-- Bootstrap JavaScript -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/guest-bootstrap.js"></script>

  <script>
    // Enhanced booking form validation and feedback
    document.addEventListener('DOMContentLoaded', function () {
      const roomSelect = document.getElementById('room_select');
      const checkinInput = document.querySelector('input[name="checkin"]');
      const checkoutInput = document.querySelector('input[name="checkout"]');
      const occupantsInput = document.querySelector('input[name="occupants"]');

      // Add availability checking
      function checkAvailability() {
        const roomId = roomSelect.value;
        const checkin = checkinInput.value;
        const checkout = checkoutInput.value;

        if (roomId && checkin && checkout) {
          // Add visual feedback
          roomSelect.style.borderColor = '#28a745';

          // Simple validation
          if (new Date(checkin) >= new Date(checkout)) {
            checkinInput.style.borderColor = '#dc3545';
            checkoutInput.style.borderColor = '#dc3545';
          } else {
            checkinInput.style.borderColor = '#28a745';
            checkoutInput.style.borderColor = '#28a745';
          }
        }
      }

      // Add capacity validation
      function validateCapacity() {
        const selectedOption = roomSelect.options[roomSelect.selectedIndex];
        if (selectedOption && occupantsInput.value) {
          const text = selectedOption.text;
          const match = text.match(/(\d+)\s+persons/);
          if (match) {
            const capacity = parseInt(match[1]);
            const occupants = parseInt(occupantsInput.value);

            if (occupants > capacity) {
              occupantsInput.style.borderColor = '#dc3545';
              occupantsInput.title = `Maximum capacity is ${capacity} persons`;
            } else {
              occupantsInput.style.borderColor = '#28a745';
              occupantsInput.title = '';
            }
          }
        }
      }

      // Event listeners
      if (roomSelect) roomSelect.addEventListener('change', checkAvailability);
      if (checkinInput) checkinInput.addEventListener('change', checkAvailability);
      if (checkoutInput) checkoutInput.addEventListener('change', checkAvailability);
      if (occupantsInput) occupantsInput.addEventListener('input', validateCapacity);

      // Form submission with AJAX and loading states
      const reservationForm = document.getElementById('reservationForm');
      const pencilForm = document.getElementById('pencilForm');

      if (reservationForm) {
        reservationForm.addEventListener('submit', function (e) {
          e.preventDefault();
          handleFormSubmission(this, 'reservationSubmitBtn');
        });
      }

      if (pencilForm) {
        pencilForm.addEventListener('submit', function (e) {
          e.preventDefault();
          if (!pencilReminder()) return;
          handleFormSubmission(this, 'pencilSubmitBtn');
        });
      }

      // Generic form submission handler with loading states
      function handleFormSubmission(form, buttonId) {
        const submitBtn = document.getElementById(buttonId);
        const originalHtml = submitBtn.innerHTML;

        // Validate required fields
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(field => {
          if (!field.value.trim()) {
            field.style.borderColor = '#dc3545';
            isValid = false;
          } else {
            field.style.borderColor = '#28a745';
          }
        });

        if (!isValid) {
          showAlert('Please fill in all required fields.', 'danger');
          return;
        }

        // Show loading state
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
        submitBtn.disabled = true;

        // Prepare form data
        const formData = new FormData(form);

        // Convert to URL-encoded format
        const urlEncodedData = new URLSearchParams(formData).toString();

        // Send AJAX request
        fetch('database/user_auth.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: urlEncodedData
        })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              showAlert(data.message || 'Booking submitted successfully!', 'success');
              form.reset(); // Clear the form

              // Reset visual validation
              const fields = form.querySelectorAll('input, select, textarea');
              fields.forEach(field => {
                field.style.borderColor = '';
              });
            } else {
              throw new Error(data.error || 'Unknown error occurred');
            }
          })
          .catch(error => {
            console.error('Error:', error);
            showAlert(error.message || 'Failed to submit booking. Please try again.', 'danger');
          })
          .finally(() => {
            // Restore button state
            submitBtn.innerHTML = originalHtml;
            submitBtn.disabled = false;
          });
      }

      // Function to show alerts
      function showAlert(message, type = 'info') {
        const alertClass = `alert-${type}`;
        const iconClass = type === 'success' ? 'check-circle' :
          type === 'danger' ? 'exclamation-triangle' : 'info-circle';

        const alert = document.createElement('div');
        alert.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
        alert.style.top = '20px';
        alert.style.right = '20px';
        alert.style.zIndex = '9999';
        alert.style.maxWidth = '400px';
        alert.innerHTML = `
          <i class="fas fa-${iconClass} me-2"></i>
          ${message}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(alert);

        // Auto-remove after 5 seconds
        setTimeout(() => {
          if (alert.parentNode) {
            alert.remove();
          }
        }, 5000);
      }

      // Feedback form handling
      const feedbackForm = document.getElementById('feedback-form');
      const submitFeedbackBtn = document.getElementById('submit-feedback');

      if (feedbackForm && submitFeedbackBtn) {
        feedbackForm.addEventListener('submit', function (e) {
          e.preventDefault();

          const rating = document.getElementById('rating-value').value;
          if (!rating) {
            showAlert('Please select a star rating.', 'danger');
            return;
          }

          const originalHtml = submitFeedbackBtn.innerHTML;

          // Show loading state
          submitFeedbackBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
          submitFeedbackBtn.disabled = true;

          // Prepare form data
          const formData = new FormData(this);
          const urlEncodedData = new URLSearchParams(formData).toString();

          // Send AJAX request
          fetch('database/user_auth.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
              'X-Requested-With': 'XMLHttpRequest'
            },
            body: urlEncodedData
          })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                showAlert(data.message || 'Feedback submitted successfully!', 'success');
                this.reset(); // Clear the form

                // Reset star rating
                document.getElementById('rating-value').value = '';
                document.querySelectorAll('.star').forEach(star => {
                  star.classList.remove('active');
                });
                document.getElementById('rating-text').textContent = 'Click to rate';
                submitFeedbackBtn.disabled = true;
              } else {
                throw new Error(data.error || 'Unknown error occurred');
              }
            })
            .catch(error => {
              console.error('Error:', error);
              showAlert(error.message || 'Failed to submit feedback. Please try again.', 'danger');
            })
            .finally(() => {
              // Restore button state
              submitFeedbackBtn.innerHTML = originalHtml;
              if (document.getElementById('rating-value').value) {
                submitFeedbackBtn.disabled = false;
              }
            });
        });
      }
    });
  </script>
</head>

<body>

  <!-- Mobile Menu Toggle -->
  <button class="mobile-menu-toggle d-lg-none" onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
  </button>

  <!-- Mobile Sidebar Overlay -->
  <div class="sidebar-overlay" onclick="closeSidebar()"></div>

  <!-- Sidebar -->
  <aside class="sidebar-guest">
    <h2><i class="fas fa-user-circle me-2"></i>Guest Portal</h2>
    <button class="btn btn-outline-light mb-2 text-start" onclick="showSection('overview')">
      <i class="fas fa-home me-2"></i>Overview
    </button>
    <button class="btn btn-outline-light mb-2 text-start" onclick="showSection('availability')">
      <i class="fas fa-calendar-alt me-2"></i>Availability Calendar
    </button>
    <button class="btn btn-outline-light mb-2 text-start" onclick="showSection('rooms')">
      <i class="fas fa-door-open me-2"></i>Rooms & Facilities
    </button>
    <button class="btn btn-outline-light mb-2 text-start" onclick="showSection('booking')">
      <i class="fas fa-calendar-check me-2"></i>Booking & Reservation
    </button>

    <button class="btn btn-outline-light mb-2 text-start" onclick="showSection('feedback')">
      <i class="fas fa-star me-2"></i>Feedback
    </button>
    <a href="index.php" class="btn btn-primary mt-3 text-start">
      <i class="fas fa-home me-2"></i>Back to Home
    </a>
  </aside>

  <!-- Main Content -->
  <main class="main-content">
    <div class="container-fluid">
      <header class="mb-4">
        <div class="row">
          <div class="col-12">
            <h1 class="display-6 text-center mb-3">Welcome to BarCIE International Center</h1>
            <p class="lead text-center text-muted">Explore our rooms and facilities, make bookings without any account
              required!</p>
          </div>
        </div>
      </header>

      <!-- Overview -->
      <section id="overview" class="content-section">
        <div class="row mb-4">
          <div class="col-12">
            <div class="card bg-primary text-white">
              <div class="card-body">
                <div class="row align-items-center">
                  <div class="col-md-8">
                    <h3 class="card-title mb-2">Welcome!</h3>
                    <p class="card-text mb-0">Explore our facilities, make instant bookings, and discover everything
                      BarCIE International Center has to offer. No account required!</p>
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
          <div class="col-lg-4 col-md-6 mb-3">
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
          <div class="col-lg-4 col-md-6 mb-3">
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
          <div class="col-lg-4 col-md-6 mb-3">
            <div class="card text-center h-100 available-now-card" style="cursor: pointer;"
              onclick="scrollToAvailability()" title="Click to view availability calendar">
              <div class="card-body">
                <div class="text-info mb-3">
                  <i class="fas fa-check-circle fa-2x"></i>
                </div>
                <h4 class="card-title text-info" id="available-rooms">0</h4>
                <p class="card-text text-muted">Available Now</p>
                <small class="text-info"><i class="fas fa-mouse-pointer me-1"></i>Click to view calendar</small>
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
                      class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-4"
                      onclick="showSection('rooms')">
                      <i class="fas fa-search fa-3x mb-3"></i>
                      <span class="fw-bold">Browse Rooms</span>
                      <small class="text-muted mt-1">Explore our accommodations</small>
                    </button>
                  </div>
                  <div class="col-lg-3 col-md-6 mb-3">
                    <button
                      class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center py-4"
                      onclick="showSection('booking')">
                      <i class="fas fa-plus-circle fa-3x mb-3"></i>
                      <span class="fw-bold">Make Booking</span>
                      <small class="text-muted mt-1">Reserve your stay today</small>
                    </button>
                  </div>
                  <div class="col-lg-3 col-md-6 mb-3">
                    <button
                      class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center py-4"
                      onclick="showSection('communication')">
                      <i class="fas fa-phone fa-3x mb-3"></i>
                      <span class="fw-bold">Contact Us</span>
                      <small class="text-muted mt-1">Get in touch with our team</small>
                    </button>
                  </div>
                  <div class="col-lg-3 col-md-6 mb-3">
                    <button
                      class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center py-4"
                      onclick="showSection('feedback')">
                      <i class="fas fa-star fa-3x mb-3"></i>
                      <span class="fw-bold">Give Feedback</span>
                      <small class="text-muted mt-1">Share your experience</small>
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

      <!-- Availability Calendar -->
      <section id="availability" class="content-section">
        <h2>Room & Facility Availability</h2>

        <div class="row mb-4" id="availability-calendar-section">
          <div class="col-12">
            <div class="card">
              <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                  <i class="fas fa-calendar-alt me-2"></i>Availability Calendar
                </h5>
                <small class="opacity-75">View room and facility availability for planning your stay</small>
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
                        <h6>ℹ️ Information</h6>
                        <small class="text-muted">
                          This calendar shows room/facility availability only.
                          Hover over events to see specific room details.
                        </small>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>




      <!-- Rooms & Facilities -->
      <section id="rooms" class="content-section">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h2 class="mb-0"><i class="fas fa-door-open me-2"></i>Rooms & Facilities</h2>
          <div class="filter-controls">
            <div class="btn-group" role="group" aria-label="Filter by type">
              <input type="radio" class="btn-check" name="type" id="filter-room" value="room" checked>
              <label class="btn btn-outline-primary" for="filter-room">
                <i class="fas fa-bed me-1"></i>Rooms
              </label>

              <input type="radio" class="btn-check" name="type" id="filter-facility" value="facility">
              <label class="btn btn-outline-primary" for="filter-facility">
                <i class="fas fa-building me-1"></i>Facilities
              </label>
            </div>
          </div>
        </div>

        <div class="cards-grid" id="cards-grid"></div>

      </section>


      <!-- Booking -->
      <section id="booking" class="content-section">
        <h2>Booking & Reservation</h2>

        <?php
        // Display booking feedback messages
        if (isset($_SESSION['booking_msg'])) {
          $msg = $_SESSION['booking_msg'];
          $alertClass = (strpos($msg, 'Error') !== false || strpos($msg, 'Sorry') !== false) ? 'alert-danger' : 'alert-success';
          echo "<div class='alert $alertClass alert-dismissible fade show' role='alert'>
                  <i class='fas fa-" . ($alertClass === 'alert-success' ? 'check-circle' : 'exclamation-circle') . " me-2'></i>
                  $msg
                  <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                </div>";
          unset($_SESSION['booking_msg']);
        }
        ?>

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

          <!-- Discount Application Section -->
          <div class="card mb-3">
            <div class="card-header bg-warning text-dark">
              <strong><i class="fas fa-percent me-2"></i>Apply for Discount</strong>
            </div>
            <div class="card-body">
              <div class="mb-3">
                <label for="discount_type" class="form-label">Discount Type</label>
                <select name="discount_type" id="discount_type" class="form-select">
                  <option value="">No Discount</option>
                  <option value="pwd_senior">PWD / Senior Citizen (20%)</option>
                  <option value="lcuppersonnel">LCUP Personnel (10%)</option>
                  <option value="lcupstudent">LCUP Student/Alumni (7%)</option>
                </select>
              </div>
              <div class="mb-3" id="discount_proof_section" style="display:none;">
                <label for="discount_proof" class="form-label">Upload Valid ID/Proof <span
                    class="text-danger">*</span></label>
                <input type="file" name="discount_proof" id="discount_proof" class="form-control"
                  accept="image/*,application/pdf">
                <small class="form-text text-muted">Accepted: ID, certificate, or other proof (image or PDF)</small>
              </div>
              <div class="mb-3" id="discount_details_section" style="display:none;">
                <label for="discount_details" class="form-label">Discount Details</label>
                <input type="text" name="discount_details" id="discount_details" class="form-control"
                  placeholder="ID number, personnel/student number, etc.">
              </div>
              <div class="alert alert-info mb-0" id="discount_info_text" style="display:none;"></div>
            </div>
          </div>

          <script>
            // Show/hide discount fields based on selection
            document.addEventListener('DOMContentLoaded', function () {
              const discountType = document.getElementById('discount_type');
              const proofSection = document.getElementById('discount_proof_section');
              const detailsSection = document.getElementById('discount_details_section');
              const infoText = document.getElementById('discount_info_text');
              if (discountType) {
                discountType.addEventListener('change', function () {
                  if (this.value === '') {
                    proofSection.style.display = 'none';
                    detailsSection.style.display = 'none';
                    infoText.style.display = 'none';
                  } else {
                    proofSection.style.display = '';
                    detailsSection.style.display = '';
                    infoText.style.display = '';
                    if (this.value === 'pwd_senior') {
                      infoText.innerHTML = '<b>20% Discount</b> for PWD/Senior Citizens. Please upload a valid government-issued ID.';
                    } else if (this.value === 'lcuppersonnel') {
                      infoText.innerHTML = '<b>10% Discount</b> for LCUP Personnel. Please upload your personnel ID or certificate.';
                    } else if (this.value === 'lcupstudent') {
                      infoText.innerHTML = '<b>7% Discount</b> for LCUP Students/Alumni. Please upload your student/alumni ID.';
                    }
                  }
                });
              }
            });
          </script>


          <div class="form-grid">
            <label class="full-width">
              <span class="label-text">Reservation no:</span>
              <input type="text" name="receipt_no" id="receipt_no" readonly>
            </label>

            <label class="full-width">
              <span class="label-text">Select Room/Facility *</span>
              <select name="room_id" id="room_select" required>
                <option value="">Choose a room or facility...</option>
                <?php
                // Fetch available rooms and facilities from database with status
                $room_stmt = $conn->prepare("SELECT id, name, item_type, room_number, capacity, price, room_status FROM items WHERE item_type IN ('room', 'facility') AND room_status IN ('available', 'clean') ORDER BY item_type, name");
                $room_stmt->execute();
                $room_result = $room_stmt->get_result();

                $current_type = '';
                while ($room = $room_result->fetch_assoc()) {
                  // Add optgroup headers for different types
                  if ($current_type !== $room['item_type']) {
                    if ($current_type !== '')
                      echo "</optgroup>";
                    $current_type = $room['item_type'];
                    echo "<optgroup label='" . ucfirst($current_type) . "s'>";
                  }

                  $room_display = $room['name'];
                  if ($room['room_number']) {
                    $room_display .= " (Room #" . $room['room_number'] . ")";
                  }
                  $room_display .= " - " . $room['capacity'] . " persons";
                  if ($room['price'] > 0) {
                    $room_display .= " - ₱" . number_format($room['price']) . "/night";
                  }

                  // Add status indicator
                  $status = $room['room_status'] ?: 'available';
                  $status_text = '';
                  if ($status === 'clean')
                    $status_text = ' (Ready)';
                  elseif ($status === 'available')
                    $status_text = ' (Available)';

                  echo "<option value='" . $room['id'] . "'>" . htmlspecialchars($room_display . $status_text) . "</option>";
                }
                if ($current_type !== '')
                  echo "</optgroup>";
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
              <input type="email" name="email" required pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                title="Only Gmail Address are accepted (@gmail.com)">
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

            <button type="submit" id="reservationSubmitBtn">
              <i class="fas fa-calendar-check me-2"></i>Confirm Reservation
            </button>
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
              <span class="label-text">Function Hall/Facility *</span>
              <select name="room_id" required>
                <option value="">Choose a hall or facility...</option>
                <?php
                // Fetch available facilities/halls from database
                $facility_stmt = $conn->prepare("SELECT id, name, room_number, capacity, price, room_status FROM items WHERE item_type = 'facility' AND room_status IN ('available', 'clean') ORDER BY name");
                $facility_stmt->execute();
                $facility_result = $facility_stmt->get_result();
                while ($facility = $facility_result->fetch_assoc()) {
                  $facility_display = $facility['name'];
                  if ($facility['room_number']) {
                    $facility_display .= " (Hall #" . $facility['room_number'] . ")";
                  }
                  $facility_display .= " - " . $facility['capacity'] . " persons";
                  if ($facility['price'] > 0) {
                    $facility_display .= " - ₱" . number_format($facility['price']) . "/event";
                  }

                  // Add status indicator
                  $status = $facility['room_status'] ?: 'available';
                  $status_text = '';
                  if ($status === 'clean')
                    $status_text = ' (Ready)';
                  elseif ($status === 'available')
                    $status_text = ' (Available)';

                  echo "<option value='" . $facility['id'] . "'>" . htmlspecialchars($facility_display . $status_text) . "</option>";
                }
                $facility_stmt->close();
                ?>
              </select>
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

            <button type="submit" id="pencilSubmitBtn" onclick="return pencilReminder()">
              <i class="fas fa-edit me-2"></i>Submit Pencil Booking
            </button>
          </div>
        </form>

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
                    <textarea class="form-control" name="message" id="feedback-message" rows="4"
                      placeholder="Share specific details about your experience..."></textarea>
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


      </section>
    </div> <!-- Close container-fluid -->
  </main>

  <footer class="footer">
    <p>© BarCIE International Center 2025</p>
  </footer>

  <!-- Mobile Sidebar Functions -->
  <script>
    // Mobile sidebar toggle functions
    function toggleSidebar() {
      const sidebar = document.querySelector('.sidebar-guest');
      const overlay = document.querySelector('.sidebar-overlay');
      
      if (sidebar.classList.contains('open')) {
        closeSidebar();
      } else {
        openSidebar();
      }
    }
    
    function openSidebar() {
      const sidebar = document.querySelector('.sidebar-guest');
      const overlay = document.querySelector('.sidebar-overlay');
      
      sidebar.classList.add('open');
      overlay.classList.add('show');
      document.body.style.overflow = 'hidden'; // Prevent background scrolling
    }
    
    function closeSidebar() {
      const sidebar = document.querySelector('.sidebar-guest');
      const overlay = document.querySelector('.sidebar-overlay');
      
      sidebar.classList.remove('open');
      overlay.classList.remove('show');
      document.body.style.overflow = ''; // Restore scrolling
    }
    
    // Close sidebar when clicking on navigation items on mobile
    document.addEventListener('DOMContentLoaded', function() {
      const navButtons = document.querySelectorAll('.sidebar-guest button[onclick*="showSection"]');
      navButtons.forEach(button => {
        button.addEventListener('click', function() {
          // Close sidebar on mobile after navigation
          if (window.innerWidth <= 768) {
            setTimeout(closeSidebar, 300); // Small delay for smooth transition
          }
        });
      });
      
      // Handle window resize
      window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
          closeSidebar(); // Close mobile sidebar when switching to desktop
        }
      });
    });
  </script>

</body>

</html>