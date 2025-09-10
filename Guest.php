<?php
// guest.php
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Guest Page - BarCIE</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar guest">
    <h2>Guest Panel</h2>
    
    <a onclick="showSection('home')">Home</a>
    <a onclick="showSection('rooms')">Rooms</a>
    <a onclick="showSection('facilities')">Facilities</a>
    <a onclick="showSection('contacts')">Contacts</a>
    <a href="index.php">Back to Homepage</a>
    
    
  </div>

  <!-- Main Content -->
  <div class="main-content-guest">
    <!-- Home Section -->
    <header id="header" class="header">
  <h1>Welcome to BarCIE International Center</h1>
  <p>We are delighted to have you as our guest. Explore our rooms, facilities, and get in touch with us through this guest portal.</p>
</header>


    <!-- Rooms Section -->
    <div id="rooms" class="content-section">
      <h1>Room Viewing & Price Checking</h1>
      <div class="room-card">
        <h3>Standard Room</h3>
        <p>₱1,200 / night</p>
      </div>
      <div class="room-card">
        <h3>Deluxe Room</h3>
        <p>₱2,000 / night</p>
      </div>
      <div class="room-card">
        <h3>Suite</h3>
        <p>₱3,500 / night</p>
      </div>
    </div>

    <!-- Facilities Section -->
    <div id="facilities" class="content-section">
      <h1>Facilities - Hall</h1>
      <div class="facility-card">
        <h3>Main Function Hall</h3>
        <p>Perfect for conferences, seminars, and social gatherings. Capacity: 300 guests.</p>
      </div>
      <div class="facility-card">
        <h3>Mini Hall</h3>
        <p>Ideal for small events and meetings. Capacity: 80 guests.</p>
      </div>
    </div>

    <!-- Contacts Section -->
<div id="contacts" class="content-section contacts-section">
  <h1 >Contact Us</h1>
  <p><strong>Address:</strong> La Consolacion University Philippines</p>
  <p><strong>Telephone:</strong> (044) 931 8600</p>
  <p><strong>Cellphone:</strong> 0919 002 7151 / 0933 611 8059</p>
  <p><strong>Email:</strong></p>
  <ul>
    <li>laconsolacionu@lcup.edu.ph</li>
    <li>laconsolacionu@email.lcup.edu.ph</li>
  </ul>

  <h3>Location</h3>
  <p>
    <a 
      href="https://www.google.com/maps/place/Barcie+International+Center/@14.8528398,120.8114192,15.4z/data=!4m6!3m5!1s0x339653da628ae773:0xb35ee8def0552c2!8m2!3d14.8538889!4d120.8125!16s%2Fg%2F1vcl197l?entry=ttu&g_ep=EgoyMDI1MDkwNy4wIKXMDSoASAFQAw%3D%3D" 
      target="_blank" 
      class="map-link">
      Main Campus - Valenzuela St., Capitol View Park Subdivision, Bulihan, City of Malolos, Bulacan 3000 Philippines
    </a>
  </p>
</div>

  </div>

  <!-- JS for switching sections -->
  <script>
    function showSection(sectionId) {
      document.querySelectorAll('.content-section').forEach(sec => {
        sec.classList.remove('active');
      });
      document.getElementById(sectionId).classList.add('active');
    }
  </script>
</body>

 <footer class="footer">
    <p>© BarCIE International Center 2025</p>
  </footer>

</html>
