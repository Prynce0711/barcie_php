
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
        
      </div>
    </section>
  </div>

  <!-- ✅ Hidden Admin Login Section (not inside sidebar anymore) -->
  <section id="admin-login" class="content-section">
    <div class="admin-login-section">
      <h1>🔐 Admin Login</h1>
      <form class="login-form" method="post" action="dashboard.php">
        <label for="username">👤 Username</label>
        <input type="text" id="username" name="username" placeholder="Enter username" required>

        <label for="password">🔑 Password</label>
        <input type="password" id="password" name="password" placeholder="Enter password" required>

        <button type="submit">➡️ Login</button>
        <button type="button" onclick="window.location.href='index.php'">⬅️ Back</button>
      </form>
    </div>
  </section>
  </div>



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
</section>


</html>
