
<?php
session_start();
require __DIR__ . "/database/db_connect.php"; // ✅ fixed path

if (!isset($_SESSION['admin_id'])) { // ✅ fixed session check
  header("Location: index.php");
  exit;
}
?>


<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - BarCIE</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
  <!-- Header -->
  <header>
    <h1>BarCIE Admin Dashboard</h1>
  </header>

  <!-- Dashboard Container -->
  <div class="dashboard-container">

    <!-- Toggle Button -->
    <button class="toggle-btn" onclick="toggleSidebar()">☰</button>

    <!-- Sidebar -->
    <div id="sidebar" class="sidebar">
      <h2>Admin Panel</h2>
      <a href="#" data-section="home" onclick="showSection('home'); return false;">Home</a>
      <a href="#" data-section="manage-room" onclick="showSection('manage-room'); return false;">Manage Room</a>
      <a href="#" data-section="manage-booking" onclick="showSection('manage-booking'); return false;">Manage
        Booking</a>
      <a href="#" data-section="manage-facilities" onclick="showSection('manage-facilities'); return false;">Manage
        Facilities</a>
      <a href="index.php">Logout</a>
    </div>

    <!-- Main Content Area -->
    <main class="main-content">

      <!-- Home Section -->
      <section id="home" class="content-section-admin active card">
        <h2>Quick Stats</h2>
        <?php
        $totalRooms = $conn->query("SELECT COUNT(*) AS total FROM rooms")->fetch_assoc()['total'] ?? 0;
        $activeBookings = $conn->query("SELECT COUNT(*) AS total FROM bookings WHERE status='active'")->fetch_assoc()['total'] ?? 0;
        ?>
        <p>Total Rooms: <?php echo $totalRooms; ?></p>
        <p>Active Bookings: <?php echo $activeBookings; ?></p>
      </section>

      <!-- Manage Rooms -->
      <section id="manage-room" class="content-section card">
        <h2>Manage Rooms</h2>
        <form method="post" action="room_add.php">
          <input type="text" name="room_number" placeholder="Room Number" required>
          <input type="text" name="type" placeholder="Room Type">
          <button type="submit">➕ Add Room</button>
        </form>
        <h3>Room List</h3>
        <table>
          <tr>
            <th>Room</th>
            <th>Type</th>
            <th>Status</th>
          </tr>
          <?php
          $rooms = $conn->query("SELECT * FROM rooms");
          while ($r = $rooms->fetch_assoc()) {
            echo "<tr><td>{$r['room_number']}</td><td>{$r['type']}</td><td>{$r['status']}</td></tr>";
          }
          ?>
        </table>
      </section>

      <!-- Manage Facilities -->
      <section id="manage-facilities" class="content-section card">
        <h2>Manage Facilities</h2>
        <form method="post" action="facility_add.php">
          <input type="text" name="name" placeholder="Facility Name" required>
          <input type="number" name="capacity" placeholder="Capacity">
          <input type="number" step="0.01" name="price" placeholder="Price">
          <button type="submit">➕ Add Facility</button>
        </form>
        <h3>Facilities List</h3>
        <table>
          <tr>
            <th>Name</th>
            <th>Capacity</th>
            <th>Price</th>
          </tr>
      
        </table>
      </section>

    </main>
  </div>

      <!-- Manage Bookings -->
       <!-- Rooms & Facilities -->
  <div id="rooms-facilities" class="content-section">
    <h1>Rooms & Facilities</h1>
    <div class="filter-options">
      <label><input type="radio" name="typeFilter" value="room" checked> Rooms</label>
      <label><input type="radio" name="typeFilter" value="facility"> Facilities</label>
      <label><input type="radio" name="typeFilter" value="all"> Show All</label>
    </div>

    <div class="cards-grid">
      <!-- Example: you can also fetch rooms dynamically -->
      <div class="room-card type-room">
        <img src="images/standard.jpg" alt="Standard Room">
        <div class="room-info">
          <h3>Standard Room</h3>
          <p>₱1,200 / night</p>
          <button class="book-now-btn" onclick="showBookingForm()">Book Now</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Booking Form -->
  <section id="bookingFormSection" class="content-section" style="display:none;">
    <h2>Booking Form</h2>
    <form id="bookingForm" method="POST" action="database/booking_handler.php">
      <label><input type="radio" name="bookingType" value="pencil" checked> Pencil Booking Slip</label>
      <label><input type="radio" name="bookingType" value="reservation"> Reservation Form</label>

      
      <!-- Pencil -->
      <div id="pencilFields">
        <label>Event Type: <input type="text" name="event_type"></label><br>
        <label>Function Hall: <input type="text" name="function_hall"></label><br>
        <label>Number of Pax: <input type="number" name="num_pax"></label><br>
        <label>Date of Event: <input type="date" name="event_date"></label><br>
        <label>Time From: <input type="time" name="time_from"></label><br>
        <label>Time To: <input type="time" name="time_to"></label><br>
        <label>Caterer: <input type="text" name="caterer"></label><br>
        <label>Contact Person: <input type="text" name="contact_person"></label><br>
        <label>Contact Numbers: <input type="text" name="contact_numbers"></label><br>
        <label>Company Affiliation: <input type="text" name="company_affiliation"></label><br>
        <label>Company Contact Number: <input type="text" name="company_contact_number"></label><br>
        <label>Front Desk Officer: <input type="text" name="front_desk_officer"></label><br>
      </div>

      <!-- Reservation -->
      <div id="reservationFields" style="display:none;">
        <label>Guest Name: <input type="text" name="guest_name"></label><br>
        <label>Contact Number: <input type="text" name="guest_contact"></label><br>
        <label>Check-in: <input type="datetime-local" name="check_in"></label><br>
        <label>Check-out: <input type="datetime-local" name="check_out"></label><br>
        <label>Occupants: <input type="number" name="num_occupants"></label><br>
        <label>Company Affiliation: <input type="text" name="company_affiliation"></label><br>
        <label>Company Contact Number: <input type="text" name="company_contact_number"></label><br>
        <label>Front Desk Officer: <input type="text" name="front_desk_officer"></label><br>
        <label>Official Receipt: <input type="text" name="official_receipt" value="0001"></label><br>
        <label>Special Request: <textarea name="special_request"></textarea></label><br>
      </div>

      <button type="submit">Submit Booking</button>
    </form>
  </section>

      


  <section id="footer-section-land" class="footer-section-land">
    <div class="footer-land">
      <p>© BarCIE International Center 2025</p>
    </div>
  </section>


  <script src="/barcie_php/assets/js/script.js"></script>
</body>

</html>
