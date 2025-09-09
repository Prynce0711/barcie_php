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
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f4f6f9;
      margin: 0;
      padding: 0;
    }
    header {
      background: #007BFF;
      color: white;
      padding: 15px;
      text-align: center;
    }
    .dashboard-container {
      max-width: 1000px;
      margin: 30px auto;
      padding: 20px;
    }
    .nav {
      margin-bottom: 20px;
      text-align: center;
    }
    .nav a {
      margin: 5px;
      text-decoration: none;
      background: #007BFF;
      color: white;
      padding: 10px 18px;
      border-radius: 6px;
      font-weight: bold;
      display: inline-block;
      transition: background 0.3s ease;
    }
    .nav a:hover {
      background: #0056b3;
    }
    .card {
      background: white;
      padding: 20px;
      border-radius: 10px;
      margin-bottom: 20px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .card h2 {
      margin-top: 0;
    }
    .get-started {
      display: inline-block;
      margin-top: 10px;
      padding: 10px 20px;
      font-size: 16px;
      font-weight: bold;
      color: white;
      background: #007BFF;
      text-decoration: none;
      border-radius: 8px;
      transition: background 0.3s ease;
    }
    .get-started:hover {
      background: #0056b3;
    }
  </style>
</head>

<body>
  <header>
    <h1>BarCIE Dashboard</h1>
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
