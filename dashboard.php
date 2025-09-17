<?php
session_start();
require "database/db_connect.php";  // <-- fixed path

// protect page (only for logged in admins)
if (!isset($_SESSION['admin_id'])) {
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
      <a href="#" data-section="manage-booking" onclick="showSection('manage-booking'); return false;">Manage Booking</a>
      <a href="#" data-section="manage-facilities" onclick="showSection('manage-facilities'); return false;">Manage Facilities</a>
      <a href="index.php">Logout</a>
    </div>

    <!-- Main Content Area -->
    <main class="main-content">

      <!-- Home Section -->
      <section id="home" class="content-section active card">
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
          <tr><th>Room</th><th>Type</th><th>Status</th></tr>
          <?php
          $rooms = $conn->query("SELECT * FROM rooms");
          while ($r = $rooms->fetch_assoc()) {
            echo "<tr><td>{$r['room_number']}</td><td>{$r['type']}</td><td>{$r['status']}</td></tr>";
          }
          ?>
        </table>
      </section>

      <!-- Manage Bookings -->
      <section id="manage-booking" class="content-section card">
        <h2>Manage Bookings</h2>
        <h3>Recent Bookings</h3>
        <table>
          <tr><th>Guest</th><th>Room</th><th>Check-in</th><th>Check-out</th><th>Status</th></tr>
          <?php
          $bookings = $conn->query("SELECT b.*, r.room_number FROM bookings b 
                                   JOIN rooms r ON b.room_id = r.id ORDER BY b.id DESC LIMIT 5");
          while ($b = $bookings->fetch_assoc()) {
            echo "<tr>
                    <td>{$b['guest_name']}</td>
                    <td>{$b['room_number']}</td>
                    <td>{$b['check_in']}</td>
                    <td>{$b['check_out']}</td>
                    <td>{$b['status']}</td>
                  </tr>";
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
          <tr><th>Name</th><th>Capacity</th><th>Price</th></tr>
          <?php
          $facilities = $conn->query("SELECT * FROM facilities");
          while ($f = $facilities->fetch_assoc()) {
            echo "<tr><td>{$f['name']}</td><td>{$f['capacity']}</td><td>{$f['price']}</td></tr>";
          }
          ?>
        </table>
      </section>

    </main>
  </div>

  <!-- JS -->
  <script>
    function toggleSidebar() {
      document.getElementById("sidebar").classList.toggle("active");
    }

    function showSection(sectionId) {
      document.querySelectorAll('.content-section').forEach(sec => {
        sec.classList.remove('active');
      });
      document.getElementById(sectionId).classList.add('active');

      document.querySelectorAll('#sidebar a[data-section]').forEach(a => {
        if (a.dataset.section === sectionId) {
          a.classList.add('active-link');
        } else {
          a.classList.remove('active-link');
        }
      });
    }
  </script>

  <!-- Footer -->
  <section id="footer-section" class="footer-section">
    <div class="footer">
      <p>© BarCIE International Center 2025</p>
    </div>
  </section>
</body>
</html>
