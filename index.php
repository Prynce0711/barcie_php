<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Barcie internation Center</title>
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
       
         <button href="#" onclick="showSection('user-auth')">get Started</button>
      </div>
    </section>
  </div>

  <!-- User Login & Signup -->
<section id="user-auth" class="content-section">
  <div class="user-auth-container">
    <div class="login-card unique-card">

      <div class="logo-container">
        <span class="user-logo-icon"></span>
        <h2 class="card-title">User Portal</h2>
      </div>
      <p class="card-subtitle">Login or create a new account</p>

      <!-- Login Form -->
      <form id="user-login-form" action="database/user_auth.php" method="post">
        <input type="hidden" name="action" value="login">
        <h3>Login</h3>
        <div class="input-group">
          <label for="user-login-username">Username</label>
          <input type="text" id="user-login-username" name="username" placeholder="Enter username" required>
        </div>
        <div class="input-group">
          <label for="user-login-password">Password</label>
          <input type="password" id="user-login-password" name="password" placeholder="••••••••" required>
        </div>
        <button type="submit" id="user-login-button">Login</button>
        <p class="redirect-text">
          Don't have an account? 
          <a href="#user-signup-form" class="signup-link">Sign Up</a>
        </p>
      </form>

      <hr>

      <!-- Signup Form -->
      <form id="user-signup-form" action="database/user_auth.php" method="post">
        <input type="hidden" name="action" value="signup">
        <h3>Sign Up</h3>
        <div class="input-group">
          <label for="user-signup-username">Username</label>
          <input type="text" id="user-signup-username" name="username" placeholder="Enter username" required>
        </div>
        <div class="input-group">
          <label for="user-signup-email">Email</label>
          <input type="email" id="user-signup-email" name="email" placeholder="Enter email" required>
        </div>
        <div class="input-group">
          <label for="user-signup-password">Password</label>
          <input type="password" id="user-signup-password" name="password" placeholder="••••••••" required>
        </div>
        <div class="input-group">
          <label for="user-signup-confirm">Confirm Password</label>
          <input type="password" id="user-signup-confirm" name="confirm_password" placeholder="••••••••" required>
        </div>
        <button type="submit" id="user-signup-button">Sign Up</button>
        <p class="redirect-text">
          Already have an account? 
          <a href="#user-login-form" class="login-link">Login</a>
        </p>
      </form>

      <a href="index.php" class="back-button">Back to Homepage</a>
    </div>
  </div>
</section>






  <section id="admin-login" class="content-section" >
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

<script>
  // Get elements
  const loginForm = document.getElementById('user-login-form');
  const signupForm = document.getElementById('user-signup-form');
  const signupLink = document.querySelector('.signup-link');
  const loginLink = document.querySelector('.login-link');

  // Show signup, hide login
  signupLink.addEventListener('click', function(e) {
    e.preventDefault();
    loginForm.style.display = 'none';
    signupForm.style.display = 'block';
  });

  // Show login, hide signup
  loginLink.addEventListener('click', function(e) {
    e.preventDefault();
    signupForm.style.display = 'none';
    loginForm.style.display = 'block';
  });
</script>
 





<script src="/barcie_php/assets/js/script.js"></script>

</body>

<!-- Footer Section -->
<section id="footer-section-land" class="footer-section-land">
  <div class="footer">
    <p>© BarCIE International Center 2025</p>
  </div>


  
</section>


</html>