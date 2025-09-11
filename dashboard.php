<?php
// dashboard.php
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
      <a href="index.php">logout</a>
    </div>

    <!-- Main Content Area -->
    <main class="main-content">

      <!-- Home Section (visible by default) -->
      <section id="home" class="content-section active card">
        <h2>Quick Stats</h2>
        <p>Total Rooms: 10</p>
        <p>Active Bookings: 3</p>
        <a href="rooms.php" class="get-started">Go to Rooms</a>
      </section>

      <!-- Manage Rooms -->
      <section id="manage-room" class="content-section card">
        <h2>Manage Rooms</h2>
        <p>Here you can add, edit, or remove rooms.</p>
      </section>

      <!-- Manage Bookings -->
      <section id="manage-booking" class="content-section card">
        <h2>Manage Bookings</h2>
        <p>Recent booking activity and booking controls.</p>
        <ul>
          <li>Room 101 booked by John Doe</li>
          <li>Room 202 booking canceled</li>
        </ul>
      </section>

      <!-- Manage Facilities -->
      <section id="manage-facilities" class="content-section card">
        <h2>Manage Facilities</h2>
        <p>Update halls, capacities, pricing, etc.</p>
      </section>

    </main>
  </div> <!-- end dashboard-container -->

  <!-- Footer -->
  <footer class="footer">
    <p>© BarCIE International Center 2025</p>
  </footer>

  <!-- JS -->
  <script>
    function toggleSidebar() {
      document.getElementById("sidebar").classList.toggle("active");
    }

    function showSection(sectionId) {
      // hide all sections
      document.querySelectorAll('.content-section').forEach(sec => {
        sec.classList.remove('active');
      });

      // show target section
      const target = document.getElementById(sectionId);
      if (target) {
        target.classList.add('active');
      }

      // update active link styling
      document.querySelectorAll('#sidebar a[data-section]').forEach(a => {
        if (a.dataset.section === sectionId) {
          a.classList.add('active-link');
        } else {
          a.classList.remove('active-link');
        }
      });
    }

    // ensure active link matches initial section
    (function initActiveLink() {
      const initial = document.querySelector('.content-section.active');
      if (initial) {
        const link = document.querySelector(`#sidebar a[data-section="${initial.id}"]`);
        if (link) link.classList.add('active-link');
      }
    })();
  </script>
</body>
</html>
