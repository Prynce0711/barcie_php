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
    <div id="home" class="content-section active">
      <h1>Welcome to BarCIE International Center</h1>
      <p>We are delighted to have you as our guest. Explore our rooms, facilities, and get in touch with us through this guest portal.</p>
    </div>

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
    <div id="contacts" class="content-section">
      <h1>Contact Us</h1>
      <p><strong>Address:</strong> La Consolacion University Philippines</p>
      <p><strong>Telephone:</strong> (044) 931 8600</p>
      <p><strong>Cellphone:</strong> 0919 002 7151 / 0933 611 8059</p>
      <p><strong>Email:</strong></p>
      <ul>
        <li>laconsolacionu@lcup.edu.ph</li>
        <li>laconsolacionu@email.lcup.edu.ph</li>
      </ul>
      <h3>Location Map</h3>
      <iframe 
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3856.0396081838277!2d120.8130679!3d14.8530896!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x339653da86e2991f%3A0x9fdc1a1d4c59134b!2sLa%20Consolacion%20University%20Philippines!5e0!3m2!1sen!2sph!4v1694232912345!5m2!1sen!2sph" 
        allowfullscreen=""
        loading="lazy">
      </iframe>
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
