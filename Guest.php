<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: index.php"); // change this to your login page
  exit;
}

?>


<?php
include __DIR__ . '/database/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['message'])) {
  $message = $conn->real_escape_string($_POST['message']);

  $sql = "INSERT INTO feedback (message) VALUES ('$message')";
  if ($conn->query($sql)) {
    echo "<script>
                if (confirm('Feedback submitted successfully! Click OK to go to Home page, Cancel to stay.')) {
                    window.location.href = 'index.php'; // change to your home page
                } else {
                    window.location.href = window.location.pathname + '#feedback';
                }
              </script>";
    exit;
  } else {
    echo "<script>
                if (confirm('Error: " . addslashes($conn->error) . " Click OK to go back, Cancel to stay.')) {
                    window.location.href = window.location.pathname + '#feedback';
                }
              </script>";
    exit;
  }
}
?>

<?php

include 'database/db_connect.php';

// ✅ Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
  die("You must be logged in to view this page.");
}

$user_id = $_SESSION['user_id'];

// ✅ Fetch current user details
$stmt = $conn->prepare("SELECT username, email FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username, $email);
$stmt->fetch();
$stmt->close();
$conn->close();
?>







<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
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
    <button onclick="showSection('reports')">Report & Analytics</button>
    <button onclick="showSection('communication')">Communication</button>
    <button onclick="showSection('feedback')">Feedback</button>
    <a href="index.php">Back to Homepage</a>
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
    <section id="rooms" class="content-section">
      <h2>Rooms & Facilities</h2>
      <p>Check availability by date and view details.</p>

      <!-- Date Selection -->
      <label>Select Date: <input type="date"></label>

      <!-- Radio Button Selection -->
      <div class="selection-type">
        <p>Select Type:</p>
        <label>
          <input type="radio" name="type" value="room" checked> Room
        </label>
        <label>
          <input type="radio" name="type" value="facility"> Facility
        </label>
      </div>

      <!-- Cards Grid -->
      <div class="cards-grid">
        <!-- Room Card Example -->
        <div class="card" data-type="room">
          <h3>Deluxe Room</h3>
          <p>Capacity: 2 persons</p>
          <p>Price: $100/night</p>
          <button>Select for Booking</button>
        </div>

        <div class="card" data-type="room">
          <h3>Deluxe Room</h3>
          <p>Capacity: 2 persons</p>
          <p>Price: $100/night</p>
          <button>Select for Booking</button>
        </div>
        <div class="card" data-type="room">
          <h3>Deluxe Room</h3>
          <p>Capacity: 2 persons</p>
          <p>Price: $100/night</p>
          <button>Select for Booking</button>
        </div>

        <!-- Facility Card Example -->
        <div class="card" data-type="facility">
          <h3>Conference Hall</h3>
          <p>Capacity: 50 people</p>
          <p>Price: $200/day</p>
          <button>Select for Booking</button>
        </div>


        <div class="card" data-type="facility">
          <h3>Conference Hall</h3>
          <p>Capacity: 50 people</p>
          <p>Price: $200/day</p>
          <button>Select for Booking</button>
        </div>

        <div class="card" data-type="facility">
          <h3>Conference Hall</h3>
          <p>Capacity: 50 people</p>
          <p>Price: $200/day</p>
          <button>Select for Booking</button>
        </div>
      </div>
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
      <form id="reservationForm" method="POST" action="database/save_booking.php">
        <h3>Reservation Form</h3>
        <label>Official Receipt No.: <input type="text" name="receipt_no" value="AUTO" readonly></label>
        <label>Guest Name: <input type="text" name="guest_name" required></label>
        <label>Contact Number: <input type="text" name="contact_number" required></label>
        <label>Email Address: <input type="email" name="email" required></label>
        <label>Check-in Date & Time: <input type="datetime-local" name="checkin" required></label>
        <label>Check-out Date & Time: <input type="datetime-local" name="checkout" required></label>
        <label>Number of Occupants: <input type="number" name="occupants" min="1" required></label>
        <label>Company Affiliation (optional): <input type="text" name="company"></label>
        <label>Company Contact Number (optional): <input type="text" name="company_contact"></label>
        <input type="hidden" name="type" value="reservation">
        <button type="submit">Confirm Reservation</button>
      </form>

      <!-- Pencil Booking Form -->
      <form id="pencilForm" method="POST" action="database/save_booking.php" style="display:none;">
        <h3>Pencil Booking Form (Function Hall)</h3>
        <label>Date of Pencil Booking: <input type="date" name="pencil_date" value="<?php echo date('Y-m-d'); ?>"
            readonly></label>
        <label>Event Type: <input type="text" name="event_type" required></label>
        <label>Function Hall: <input type="text" name="hall" required></label>
        <label>Number of Pax: <input type="number" name="pax" min="1" required></label>
        <label>Time of Event (From): <input type="time" name="time_from" required></label>
        <label>Time of Event (To): <input type="time" name="time_to" required></label>
        <label>Food Provider/Caterer: <input type="text" name="caterer" required></label>
        <label>Contact Person: <input type="text" name="contact_person" required></label>
        <label>Contact Number: <input type="text" name="contact_number" required></label>
        <label>Company Affiliation (optional): <input type="text" name="company"></label>
        <label>Company Number (optional): <input type="text" name="company_number"></label>
        <input type="hidden" name="type" value="pencil">
        <button type="submit" onclick="return pencilReminder()">Submit Pencil Booking</button>
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
      <form action="database/user_auth.php" method="POST">
        <input type="hidden" name="action" value="update">

        <label>Username:
          <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
        </label>

        <label>Email:
          <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
        </label>

        <label>New Password (leave blank if unchanged):
          <input type="password" name="password">
        </label>

        <button type="submit">Update Profile</button>
      </form>
      <h3>Your Bookings</h3>
      <ul>
        <tr>
          <th>ID</th>
          <th>Type</th>
          <th>Details</th>
          <th>Date</th>
          <th>Status</th>
        </tr>

        <?php
        include __DIR__ . '/database/db_connect.php';

        $result = $conn->query("SELECT * FROM bookings ORDER BY id DESC");
        while ($row = $result->fetch_assoc()) {
          echo "<tr>
                <td>{$row['id']}</td>
                <td>{$row['type']}</td>
                <td>{$row['details']}</td>
                <td>{$row['created_at']}</td>
                <td>{$row['status']}</td>
              </tr>";
        }
        ?>
        </table>




      </ul>
    </section>



    <!-- Reports -->
    <section id="reports" class="content-section">
      <h2>Report & Analytics</h2>
      <p>View your personal booking history.</p>
      <table border="1">
        <tr>
          <th>ID</th>
          <th>Type</th>
          <th>Details</th>
          <th>Date</th>
          <th>Status</th>
        </tr>

        <?php
        include __DIR__ . '/database/db_connect.php';

        $result = $conn->query("SELECT * FROM bookings ORDER BY id DESC");
        while ($row = $result->fetch_assoc()) {
          echo "<tr>
                <td>{$row['id']}</td>
                <td>{$row['type']}</td>
                <td>{$row['details']}</td>
                <td>{$row['created_at']}</td>
                <td>{$row['status']}</td>
              </tr>";
        }
        ?>
      </table>
    </section>


    <!-- Communication -->
    <section id="communication" class="content-section">
      <h2>Communication</h2>
      <p>Contact us via:</p>


      <ul>
        <li>Email: guest@example.com ✅</li>
        <li>SMS: +63 912 345 6789 ✅</li>
      </ul>
    </section>



    <!-- Feedback Section -->
    <section id="feedback" class="content-section">
      <h2>Feedback</h2>

      <?php
      if (!empty($success)) {
        echo "<p style='color:green;'>$success</p>";
      } elseif (!empty($error)) {
        echo "<p style='color:red;'>$error</p>";
      }
      ?>

      <form method="post" action="">
        <textarea name="message" rows="5" placeholder="Write your feedback..." required></textarea><br><br>
        <button type="submit">Submit Feedback</button>
      </form>
    </section>


  </main>


   <footer class="footer">
    <p>© BarCIE International Center 2025</p>
  </footer>



  <script>function showSection(sectionId, button) {
  // Hide all sections
  document.querySelectorAll('.content-section').forEach(sec => {
    sec.classList.remove('active');
  });

  // Show the clicked section
  const section = document.getElementById(sectionId);
  if (section) {
    section.classList.add('active');
  }

  // Remove active class from all sidebar buttons
  document.querySelectorAll('.sidebar-guest button').forEach(btn => {
    btn.classList.remove('active');
  });

  // Highlight the clicked button
  if (button) {
    button.classList.add('active');
  }
}

// ✅ Set "Overview" as default section when page loads
document.addEventListener("DOMContentLoaded", () => {
  showSection('overview', document.querySelector('.sidebar-guest button'));
});
</script>



  <script src="assets/js/guest.js"></script>

</body>



</html>