<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="icon" type="image/png" href="assets/images/imageBg/barcie_logo.jpg">
  <title>Barcie International Center</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    /* ---------------- Animated Gradient Overlay ---------------- */
    @keyframes gradientOverlay {
      0% {
        background-position: 0% 50%;
      }

      50% {
        background-position: 100% 50%;
      }

      100% {
        background-position: 0% 50%;
      }
    }

    .animated-overlay {
      background: linear-gradient(135deg, rgba(30, 60, 114, 0.3), rgba(42, 82, 152, 0.3), rgba(255, 221, 87, 0.2));
      background-size: 400% 400%;
      animation: gradientOverlay 20s ease infinite;
    }

    /* ---------------- Button Hover Effects ---------------- */
    .btn {
      transition: all 0.3s ease;
    }

    .btn:hover {
      transform: scale(1.05);
      box-shadow: 0 10px 20px rgba(255, 221, 87, 0.4);
    }

    /* ---------------- Glassmorphism Card ---------------- */
    .glass-card {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(15px);
      border-radius: 1.5rem;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
      transition: all 0.3s ease;
    }

    .glass-card:hover {
      transform: translateY(-5px) scale(1.02);
      box-shadow: 0 15px 35px rgba(255, 221, 87, 0.4);
    }

    /* ---------------- Sidebar Toggle ---------------- */
    #sidebarToggle {
      transition: all 0.4s ease;
    }

    /* Smooth scrolling for modals */
    .content-section {
      transition: opacity 0.3s ease, transform 0.3s ease;
    }
  </style>
</head>

<body class="min-h-screen text-white font-sans relative overflow-x-hidden"
  style="background: url('assets/images/imageBg/BarCIE-0.jpg') no-repeat center center fixed; background-size: cover;">

  <!-- Gradient overlay -->
  <div class="absolute inset-0 animated-overlay z-0"></div>
  <!-- Dark overlay for readability -->
  <div class="absolute inset-0 bg-black/50 z-10"></div>

  <!-- Sidebar toggle -->
  <button id="sidebarToggle"
    class="fixed top-5 left-5 z-50 p-2 bg-white/20 rounded-md text-2xl hover:bg-white/30 transition transform"
    onclick="toggleSidebar()">➤</button>

  <!-- Sidebar -->
  <aside id="sidebar"
    class="fixed top-0 left-0 h-full w-64 bg-black/70 backdrop-blur-lg p-6 flex flex-col space-y-4 -translate-x-64 transform transition-transform z-20">
    <h2 class="text-xl font-bold text-white mb-4">Admin Panel</h2>
    <a href="#" onclick="showSection('admin-login'); toggleSidebar();"
      class="hover:text-yellow-400 transition-all duration-300">Admin Login</a>
  </aside>

  <!-- Main Content -->
  <div id="mainContentWrapper" class="relative z-20 md:ml-0 transition-all duration-500">

    <div id="mainContent">
      <header class="text-center py-10 bg-black/30 backdrop-blur-md shadow-md relative z-20">
        <h1 class="text-4xl font-bold">Barcie International Center</h1>
      </header>

      <section class="flex justify-center items-center h-[calc(100vh-160px)] px-6 relative z-20">
        <div
          class="glass-card p-10 text-center max-w-xl space-y-4 transition transform hover:-translate-y-2">
          <h2 class="text-3xl font-bold text-yellow-400">Welcome to Barcie International Center</h2>
          <p>Barasoain Center for Innovative Education (BarCIE)</p>
          <p>LCUP's Laboratory Facility for BS Tourism Mana</p>
          <button onclick="showSection('user-auth')"
            class="btn mt-4 px-6 py-2 rounded-md font-bold bg-yellow-400 text-black hover:shadow-xl">Get Started</button>
        </div>
      </section>
    </div>
  </div>

  <!-- User Login & Signup Modal -->
  <section id="user-auth" class="content-section hidden">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex justify-center items-center z-40">
      <div class="glass-card p-8 max-w-md shadow-lg space-y-6 w-full relative">
        <button onclick="closeSection('user-auth')"
          class="absolute top-3 right-3 text-white text-xl font-bold hover:text-yellow-400 transition">✕</button>
        <div class="text-center space-y-2">
          <span class="block w-16 h-16 bg-yellow-400 rounded-full mx-auto"></span>
          <h2 class="text-xl font-bold">User Portal</h2>
          <p>Login or create a new account</p>
        </div>

        <!-- Login Form -->
        <form id="user-login-form" action="database/user_auth.php" method="post" class="space-y-4">
          <input type="hidden" name="action" value="login">
          <h3 class="text-lg font-bold">Login</h3>
          <div class="flex flex-col">
            <label for="user-login-username" class="mb-1">Username</label>
            <input type="text" id="user-login-username" name="username" placeholder="Enter username" required
              class="p-2 rounded-md bg-white/20 placeholder-white/60 text-white focus:outline-none focus:ring-2 focus:ring-yellow-400">
          </div>
          <div class="flex flex-col">
            <label for="user-login-password" class="mb-1">Password</label>
            <input type="password" id="user-login-password" name="password" placeholder="••••••••" required
              class="p-2 rounded-md bg-white/20 placeholder-white/60 text-white focus:outline-none focus:ring-2 focus:ring-yellow-400">
          </div>
          <button type="submit"
            class="btn w-full py-2 bg-yellow-400 text-black font-bold rounded-md hover:shadow-xl">Login</button>
          <p class="text-center">Don't have an account? <a href="#user-signup-form"
              class="signup-link text-yellow-400 hover:underline cursor-pointer">Sign Up</a></p>
        </form>

        <hr class="border-white/30">

        <!-- Signup Form -->
        <form id="user-signup-form" action="database/user_auth.php" method="post" class="space-y-4 hidden">
          <input type="hidden" name="action" value="signup">
          <h3 class="text-lg font-bold">Sign Up</h3>
          <div class="flex flex-col">
            <label for="user-signup-username" class="mb-1">Username</label>
            <input type="text" id="user-signup-username" name="username" placeholder="Enter username" required
              class="p-2 rounded-md bg-white/20 placeholder-white/60 text-white focus:outline-none focus:ring-2 focus:ring-yellow-400">
          </div>
          <div class="flex flex-col">
            <label for="user-signup-email" class="mb-1">Email</label>
            <input type="email" id="user-signup-email" name="email" placeholder="Enter email" required
              class="p-2 rounded-md bg-white/20 placeholder-white/60 text-white focus:outline-none focus:ring-2 focus:ring-yellow-400">
          </div>
          <div class="flex flex-col">
            <label for="user-signup-password" class="mb-1">Password</label>
            <input type="password" id="user-signup-password" name="password" placeholder="••••••••" required
              class="p-2 rounded-md bg-white/20 placeholder-white/60 text-white focus:outline-none focus:ring-2 focus:ring-yellow-400">
          </div>
          <div class="flex flex-col">
            <label for="user-signup-confirm" class="mb-1">Confirm Password</label>
            <input type="password" id="user-signup-confirm" name="confirm_password" placeholder="••••••••" required
              class="p-2 rounded-md bg-white/20 placeholder-white/60 text-white focus:outline-none focus:ring-2 focus:ring-yellow-400">
          </div>
          <button type="submit"
            class="btn w-full py-2 bg-yellow-400 text-black font-bold rounded-md hover:shadow-xl">Sign Up</button>
          <p class="text-center">Already have an account? <a href="#user-login-form"
              class="login-link text-yellow-400 hover:underline cursor-pointer">Login</a></p>
        </form>

        <a href="index.php" class="block text-center mt-4 text-yellow-400 hover:underline cursor-pointer">Back to Homepage</a>
      </div>
    </div>
  </section>

  <!-- Admin Login Modal -->
  <section id="admin-login" class="content-section hidden">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex justify-center items-center z-50">
      <div class="glass-card p-8 max-w-md shadow-lg space-y-6 w-full relative">
        <button onclick="closeSection('admin-login')"
          class="absolute top-3 right-3 text-white text-xl font-bold hover:text-yellow-400 transition">✕</button>
        <div class="text-center space-y-2">
          <span class="block w-16 h-16 bg-yellow-400 rounded-full mx-auto"></span>
          <h2 class="text-xl font-bold">Barcie Admin Login</h2>
          <p>Access your unique admin portal</p>
        </div>
        <form action="database/admin_login.php" method="post" class="space-y-4">
          <div class="flex flex-col">
            <label for="username" class="mb-1">Username</label>
            <input type="text" id="username" name="username" placeholder="admin" required
              class="p-2 rounded-md bg-white/20 placeholder-white/60 text-white focus:outline-none focus:ring-2 focus:ring-yellow-400">
          </div>
          <div class="flex flex-col">
            <label for="password" class="mb-1">Password</label>
            <input type="password" id="password" name="password" placeholder="••••••••" required
              class="p-2 rounded-md bg-white/20 placeholder-white/60 text-white focus:outline-none focus:ring-2 focus:ring-yellow-400">
          </div>
          <button type="submit"
            class="btn w-full py-2 bg-yellow-400 text-black font-bold rounded-md hover:shadow-xl">Sign In</button>
        </form>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-black/50 backdrop-blur-lg text-center py-4 relative z-20">
    <p>© BarCIE International Center 2025</p>
  </footer>

  <!-- Scripts -->
  <script>
    const sidebar = document.getElementById('sidebar');
    const mainWrapper = document.getElementById('mainContentWrapper');
    const sidebarToggle = document.getElementById('sidebarToggle');

    function toggleSidebar() {
      sidebar.classList.toggle('-translate-x-64');
      if (!sidebar.classList.contains('-translate-x-64')) {
        mainWrapper.style.transform = "translateX(250px)";
        sidebarToggle.style.transform = "translateX(250px) rotate(180deg)";
      } else {
        mainWrapper.style.transform = "translateX(0)";
        sidebarToggle.style.transform = "translateX(0) rotate(0deg)";
      }
    }

    function closeSection(id) {
      document.getElementById(id).classList.add('hidden');
    }

    function showSection(id) {
      document.querySelectorAll('.content-section').forEach(section => section.classList.add('hidden'));
      document.getElementById(id).classList.remove('hidden');
    }

    // Login/signup toggle
    const loginForm = document.getElementById('user-login-form');
    const signupForm = document.getElementById('user-signup-form');
    const signupLink = document.querySelector('.signup-link');
    const loginLink = document.querySelector('.login-link');

    signupLink.addEventListener('click', function (e) {
      e.preventDefault();
      loginForm.classList.add('hidden');
      signupForm.classList.remove('hidden');
    });

    loginLink.addEventListener('click', function (e) {
      e.preventDefault();
      signupForm.classList.add('hidden');
      loginForm.classList.remove('hidden');
    });
  </script>

</body>

</html>
