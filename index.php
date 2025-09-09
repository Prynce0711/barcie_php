<?php
// Start session
session_start();

// Example: check if user is logged in
// In real use, you set $_SESSION['user'] after login
$isLoggedIn = isset($_SESSION['user']);
$user = $isLoggedIn ? $_SESSION['user'] : null;
?>
<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>BarCIE Rooms</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
  <header class="header">
    <div class="container">
      <h1>BarCIE International Center - Room Booking</h1>
    
    </div>
  </header>

  <section class="content-background">
    <div class="main-content">
      <h2>Welcome to Barcie International Center</h2>
      <p>Barasoain Center for Innovative Education (BarCIE)</p>
      <p>LCUP's Laboratory Facility for BS Tourism Mana</p>

      <!-- Get Started Button -->
      <a href="dashboard.php" class="get-started">Get Started</a>
    </div>
  </section>
  
</body>
</html>