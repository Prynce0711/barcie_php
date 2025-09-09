
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
  <header>
    <h1>Barcie Admin Dashboard</h1>
  </header>

  <div class="dashboard-container">
    <div class="nav">
      <a href="rooms.php">Manage Rooms</a>
      <a href="bookings.php">View Bookings</a>
      <a href="profile.php">Profile</a>
      <a href="index.php">Back to Home</a>
    </div>

    <div class="card">
      <h2>Quick Stats</h2>
      <p>Total Rooms: 10</p>
      <p>Active Bookings: 3</p>
      <a href="rooms.php" class="get-started">Go to Rooms</a>
    </div>

    <div class="card">
      <h2>Recent Activity</h2>
      <ul>
        <li>Room 101 booked by John Doe</li>
        <li>Room 202 booking canceled</li>
        <li>New user registered: Jane Smith</li>
      </ul>
      <a href="bookings.php" class="get-started">See All Bookings</a>
    </div>
  </div>
</body>
</html>
