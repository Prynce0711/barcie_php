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

  <!-- Sidebar -->
  <aside class="sidebar" id="sidebar">
    <h2>Admin Panel</h2>
    
    <a href="dashboard.php">Admin Login</a>
  </aside>

  <!-- Main Content -->
  <div class="main-content-area">
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
        <a href="guest.php" class="get-started">Get Started</a>
      </div>
    </section>
  </div>

  <script>
    function toggleSidebar() {
      document.getElementById("sidebar").classList.toggle("active");
    }
  </script>
</body>

 <footer class="footer">
    <p>© BarCIE International Center 2025</p>
  </footer>

</html>

