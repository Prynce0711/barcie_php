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


  <script>
    function toggleSidebar() {
      document.getElementById("sidebar").classList.toggle("active");
    }
  </script>

</body>

<!-- Footer Section -->
<section id="footer-section" class="footer-section">
  <div class="footer">
    <p>© BarCIE International Center 2025</p>
  </div>

  <script>
  function togglePasswordVisibility() {
    const passwordInput = document.getElementById("password");
    passwordInput.type = passwordInput.type === "password" ? "text" : "password";
  }
</script>




  <script>
    // Hide main content and show login
    function showLogin() {
      document.getElementById("mainContent").style.display = "none";
      document.getElementById("loginSection").style.display = "block";
    }

    // Show main content again
    function backToMain() {
      document.getElementById("loginSection").style.display = "none";
      document.getElementById("mainContent").style.display = "block";
    }
  </script>




  <!-- Scripts -->
  <script>
    function toggleSidebar() {
      document.getElementById("sidebar").classList.toggle("active");
    }

    function showSection(sectionId) {
      // Hide all sections
      document.querySelectorAll('.content-section, .content-background').forEach(sec => {
        sec.style.display = 'none';
      });

      // Show the targeted section
      document.getElementById(sectionId).style.display = 'block';
    }
  </script>


  <!-- ✅ Browsersync live reload script (always last, before </body>) -->
  <script id="__bs_script__">//<![CDATA[
    (function () {
      try {
        var script = document.createElement('script');
        if ('async') {
          script.async = true;
        }
        script.src = 'http://HOST:3002/browser-sync/browser-sync-client.js?v=3.0.4'.replace("HOST", location.hostname);
        if (document.body) {
          document.body.appendChild(script);
        } else if (document.head) {
          document.head.appendChild(script);
        }
      } catch (e) {
        console.error("Browsersync: could not append script tag", e);
      }
    })()
    //]]></script>



  <script>
    function togglePasswordVisibility() {
      const passwordInput = document.getElementById('password');
      const icon = document.querySelector('.password-toggle-icon');
      const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
      passwordInput.setAttribute('type', type);

      // You can also change the icon image here to reflect the state
      if (type === 'text') {
        // Change to a 'visible' icon
        icon.style.backgroundImage = 'url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'white\'%3E%3Cpath d=\'M12 2c-5.523 0-10 4.477-10 10s4.477 10 10 10 10-4.477 10-10-4.477-10-10-10zm0 16c-3.314 0-6-2.686-6-6s2.686-6 6-6 6 2.686 6 6-2.686 6-6 6zm0-10c-2.209 0-4 1.791-4 4s1.791 4 4 4 4-1.791 4-4-1.791-4-4-4zm-1.077 5.077l-1.923-1.923-1.077 1.077 1.923 1.923zm5.077-5.077l-1.923 1.923 1.077 1.077 1.923-1.923zm-2.046-2.046l-1.077 1.077 1.923 1.923 1.077-1.077zm-1.046 5.046l-1.923-1.923-1.077 1.077 1.923 1.923z\'/%3E%3C/svg%3E")';
      } else {
        // Change back to a 'hidden' icon
        icon.style.backgroundImage = 'url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'white\'%3E%3Cpath d=\'M12 4.5c-6.627 0-12 7.042-12 7.042s5.373 7.042 12 7.042 12-7.042 12-7.042-5.373-7.042-12-7.042zm0 12c-2.485 0-4.5-2.015-4.5-4.5s2.015-4.5 4.5-4.5 4.5 2.015 4.5 4.5-2.015 4.5-4.5 4.5zm0-7c-1.381 0-2.5 1.119-2.5 2.5s1.119 2.5 2.5 2.5 2.5-1.119 2.5-2.5-1.119-2.5-2.5-2.5z\'/%3E%3C/svg%3E")';
      }
    }
  </script>
</section>


</html>