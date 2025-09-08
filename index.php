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
      <div>
        <a href="login.php" class="cta-btn">Login</a>
        <a href="register.php" class="cta-btn">Register</a>
      </div>
    </div>
  </header>

  <section class="content-background">
    <div class="main-content">
      <h2>Welcome to Barcie International Center</h2>
      <p>
        Barasoain Center for Innovative Education (BarCIE)
      </p>
      <p>LCUP's Laboratory Facility for BS Tourism Mana</p>
        
    </div>

  </section>


  <section class="rooms container">
    <h2>Available Rooms</h2>
    <div class="grid">

      <?php foreach ($rooms as $r): ?>
        <article class="room-card">
          <img src="assets/images/room-<?= htmlspecialchars($r['id']) ?>.jpg" alt="<?= htmlspecialchars($r['name']) ?>">
          <h3><?= htmlspecialchars($r['name']) ?></h3>
          <p><?= htmlspecialchars($r['description']) ?></p>
          <p class="price">₱<?= number_format($r['price'], 2) ?></p>
          <a class="btn" href="booking.php?room_id=<?= $r['id'] ?>">Book</a>
        </article>
      <?php endforeach; ?>
    </div>
  </section>
  <script src="assets/js/app.js"></script> -->
</body>

</html>