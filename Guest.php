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
  <link rel="icon" type="image/png" href="assets/images/imageBg/barcie_logo.jpg">
  <title>Guest Portal</title>
  <link rel="stylesheet" href="assets/css/guest.css">
  <script src="assets/js/script.js" defer></script>
</head>

<body>

  <!-- Sidebar -->
  <aside class="sidebar-guest">
    <h2>Guest Portal</h2>
    <button onclick="showSection('overview')">Overview</button>
    <button onclick="showSection('rooms')">Rooms & Facilities</button>
    <button onclick="showSection('booking')">Booking & Reservation</button>
    <button onclick="showSection('payments')">Payments</button>
    <button onclick="showSection('user')">User Management</button>
    <button onclick="showSection('communication')">Communication</button>
    <button onclick="showSection('feedback')">Feedback</button>
    <a href="index.php">Log out</a>
  </aside>

  <!-- Main Content -->
  <main class="main-content">
    <header>
      <h1>Welcome to BarCIE International Center</h1>
      <p>Explore rooms, manage bookings, and more.</p>
    </header>

    <!-- Overview -->
    <section id="overview" class="content-section active">
      <h2>Overview</h2>
      <p>Browse rooms and facilities in detail.</p>
      <div class="filter-bar">
        <label>Type:
          <select>
            <option>All</option>
            <option>Room</option>
            <option>Facility</option>
          </select>
        </label>
        <label>Price:
          <input type="range" min="500" max="5000" step="500">
        </label>
        <label>Availability:
          <select>
            <option>All</option>
            <option>Available</option>
            <option>Occupied</option>
          </select>
        </label>
      </div>
      <div class="cards-grid">
        <div class="card">Sample Room/Facility Info</div>
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
          <p>Visit the <a href="#" onclick="showSection('booking', this)" style="color: #3498db;">Booking & Reservation</a> section to make your first booking!</p>
        </div>
        <?php
        }
        ?>
      </div>
    </section>



    <!-- Communication -->
    <section id="communication" class="content-section">
      <h2>Communication</h2>
      <ul>
        <li>Email: guest@example.com ✅</li>
        <li>SMS: +63 912 345 6789 ✅</li>
      </ul>
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
  </main>

  <footer class="footer">
    <p>© BarCIE International Center 2025</p>
  </footer>

  <script>
    function showSection(sectionId, button) {
      document.querySelectorAll('.content-section').forEach(sec => sec.classList.remove('active'));
      const section = document.getElementById(sectionId);
      if (section) section.classList.add('active');
      document.querySelectorAll('.sidebar-guest button').forEach(btn => btn.classList.remove('active'));
      if (button) button.classList.add('active');
    }
    document.addEventListener("DOMContentLoaded", () => {
      showSection('overview', document.querySelector('.sidebar-guest button'));
    });
  </script>

  <script>
    async function loadItems() {
      const res = await fetch('database/fetch_items.php');
      const items = await res.json();
      const container = document.getElementById('cards-grid');
      container.innerHTML = '';
      items.forEach(item => {
        const card = document.createElement('div');
        card.classList.add('card');
        card.dataset.type = item.item_type;
        card.innerHTML = `
          ${item.image ? `<img src="${item.image}" style="width:100%;height:150px;object-fit:cover;">` : ''}
          <h3>${item.name}</h3>
          ${item.room_number ? `<p>Room Number: ${item.room_number}</p>` : ''}
          <p>Capacity: ${item.capacity} ${item.item_type === 'room' ? 'persons' : 'people'}</p>
          <p>Price: P${item.price}${item.item_type === 'room' ? '/night' : '/day'}</p>
          <p>${item.description}</p>
        `;
        container.appendChild(card);
      });
      filterItems();
    }
    function filterItems() {
      const selectedType = document.querySelector('input[name="type"]:checked').value;
      document.querySelectorAll('.card').forEach(card => {
        card.style.display = card.dataset.type === selectedType ? 'block' : 'none';
      });
    }
    document.querySelectorAll('input[name="type"]').forEach(radio => {
      radio.addEventListener('change', filterItems);
    });
    window.onload = loadItems;
  </script>

  <script src="assets/js/guest.js"></script>
</body>

</html>