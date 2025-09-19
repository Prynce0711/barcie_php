


<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>BarCIE Rooms</title>
  <link rel="stylesheet" href="assets/css/style.css">

</head>

<body>
  <!-- Toggle Button -->
  <button class="toggle-btn" onclick="toggleSidebar()">☰</button>

  <!-- Sidebar (Desktop Only) -->
  <aside class="sidebar" id="sidebar">
    <h2>Admin Panel</h2>
    <!-- ✅ Button just triggers showing the form -->
    <a href="#" onclick="showSection('admin-login')">Admin Login</a>
  </aside>


  <!-- Main Content -->
  <div class="main-content-area" id="mainContent">
    <header class="header">
      <div class="container">
        <h1>Barcie International Center</h1>
      </div>
    </header>

    <section class="content-background">
      <div class="main-content">
        <h2>Welcome to Barcie International Center</h2>
        <p>Barasoain Center for Innovative Education (BarCIE)</p>
        <p>LCUP's Laboratory Facility for BS Tourism Mana</p>
        <a href="Guest.php" class="get-started">Get Started</a>
      </div>
    </section>
  </div>

  <section id="admin-login" class="content-section" style="display:none;">
    <div class="admin-login-container">
      <div class="login-card unique-card">
        <div class="logo-container">
          <span class="stax-logo-icon"></span>
          <h2 class="card-title">Barcie Admin Login</h2>
        </div>
        <p class="card-subtitle">Access your unique admin portal</p>

        <!-- ✅ point to admin_login.php in root, not in database/ -->
        <form action="database/admin_login.php" method="post">
  <div class="input-group">
    <label for="username">Username</label>
    <input type="text" id="username" name="username" placeholder="admin" required>
  </div>

  <div class="input-group">
    <label for="password">Password</label>
    <input type="password" id="password" name="password" placeholder="••••••••" required>
  </div>

  <button type="submit">Sign In</button>
</form>

        <a href="index.php" class="back-button">Back to Homepage</a>
      </div>
    </div>
  </section>


 

<script src="/barcie_php/assets/js/script.js"></script>

</body>

<!-- Footer Section -->
<section id="footer-section-land" class="footer-section-land">
  <div class="footer">
    <p>© BarCIE International Center 2025</p>
  </div>


  
</section>


</html>