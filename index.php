<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="icon" type="image/png" href="assets/images/imageBg/barcie_logo.jpg">
  <title>Barcie International Center</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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
        <div class="glass-card p-10 text-center max-w-xl space-y-4 transition transform hover:-translate-y-2">
          <h2 class="text-3xl font-bold text-yellow-400">Welcome to Barcie International Center</h2>
    
           <p>Barasoain Center for Innovative Education (BarCIE)</p>
          <p>LCUP's Laboratory Facility for BS Tourism Mana</p>
          <button onclick="showSection('user-auth')"
            class="btn mt-4 px-6 py-2 rounded-md font-bold bg-yellow-400 text-black hover:shadow-xl">Get
            Started</button>
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
        <form id="user-login-form" class="space-y-4">
          <input type="hidden" name="action" value="login">
          <h3 class="text-lg font-bold">Login</h3>
          <div id="login-error" class="p-2 bg-red-500/20 text-red-700 rounded-md text-sm hidden"></div>

          <div class="flex flex-col">
            <label for="user-login-username" class="mb-1">Username</label>
            <input type="text" id="user-login-username" name="username" placeholder="Enter username" required
              class="p-2 rounded-md bg-white/20 placeholder-white/60 text-white focus:outline-none focus:ring-2 focus:ring-yellow-400">
          </div>

          <div class="flex flex-col relative">
            <label for="user-login-password" class="mb-1">Password</label>
            <input type="password" id="user-login-password" name="password" placeholder="••••••••" required
              class="p-2 rounded-md bg-white/20 placeholder-white/60 text-white focus:outline-none focus:ring-2 focus:ring-yellow-400">
            <span class="absolute right-2 top-9 cursor-pointer text-white"
              onclick="togglePassword('user-login-password')">👁️</span>
          </div>

          <button type="submit"
            class="btn w-full py-2 bg-yellow-400 text-black font-bold rounded-md hover:shadow-xl">Login</button>

          <p class="text-center">
            Don't have an account?
            <a href="#" class="signup-link text-yellow-400 hover:underline cursor-pointer">Sign Up</a>
          </p>
        </form>

        <hr class="border-white/30">

        <!-- Signup Form -->
        <form id="user-signup-form" class="space-y-4 hidden" method="post" action="database/user_auth.php">
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
            <span id="email-msg" class="text-red-400 text-sm mt-1 hidden"></span>
          </div>

          <div class="flex flex-col relative">
            <label for="user-signup-password" class="mb-1">Password</label>
            <input type="password" id="user-signup-password" name="password" placeholder="••••••••" required
              class="p-2 rounded-md bg-white/20 placeholder-white/60 text-white focus:outline-none focus:ring-2 focus:ring-yellow-400">
            <span class="absolute right-2 top-9 cursor-pointer text-white"
              onclick="togglePassword('user-signup-password')">👁️</span>
            <span id="password-msg" class="text-red-400 text-sm mt-1 hidden"></span>
          </div>

          <div class="flex flex-col relative">
            <label for="user-signup-confirm" class="mb-1">Confirm Password</label>
            <input type="password" id="user-signup-confirm" name="confirm_password" placeholder="••••••••" required
              class="p-2 rounded-md bg-white/20 placeholder-white/60 text-white focus:outline-none focus:ring-2 focus:ring-yellow-400">
            <span class="absolute right-2 top-9 cursor-pointer text-white"
              onclick="togglePassword('user-signup-confirm')">👁️</span>
            <span id="confirm-msg" class="text-red-400 text-sm mt-1 hidden"></span>
          </div>

          <button type="submit"
            class="btn w-full py-2 bg-yellow-400 text-black font-bold rounded-md hover:shadow-xl">Sign Up</button>

          <p class="text-center">
            Already have an account?
            <a href="#" class="login-link text-yellow-400 hover:underline cursor-pointer">Login</a>
          </p>
        </form>

        <a href="index.php" class="block text-center mt-4 text-yellow-400 hover:underline cursor-pointer">Back to
          Homepage</a>
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

      <div id="admin-login-error" class="p-2 bg-red-500/20 text-red-700 rounded-md text-sm hidden"></div>

      <form id="admin-login-form" class="space-y-4">
        <div class="flex flex-col">
          <label for="admin-username" class="mb-1">Username</label>
          <input type="text" id="admin-username" name="username" placeholder="admin" required
            class="p-2 rounded-md bg-white/20 placeholder-white/60 text-white focus:outline-none focus:ring-2 focus:ring-yellow-400">
        </div>
        <div class="flex flex-col relative">
          <label for="admin-password" class="mb-1">Password</label>
          <input type="password" id="admin-password" name="password" placeholder="••••••••" required
            class="p-2 rounded-md bg-white/20 placeholder-white/60 text-white focus:outline-none focus:ring-2 focus:ring-yellow-400 pr-10">
          <button type="button" id="toggleAdminPassword"
            class="absolute right-2 top-9 text-white/70 hover:text-yellow-400 focus:outline-none">👁️</button>
        </div>
        <button type="submit"
          class="btn w-full py-2 bg-yellow-400 text-black font-bold rounded-md hover:shadow-xl">Sign In</button>
      </form>
    </div>
  </div>
</section>

    <!-- Contact Us Section -->
  <section id="contact" class="relative z-20 py-16 bg-gradient-to-r from-black/70 via-black/50 to-black/70 text-white">
    <div class="max-w-4xl mx-auto px-6">
      <h2 class="text-3xl font-bold text-center text-yellow-400 mb-10">Contact Us</h2>

      <div class="space-y-6 text-lg">
        <p class="flex items-center space-x-3">
          <i class="fa-brands fa-viber text-yellow-400 text-2xl"></i>
          <span>Viber: 
            <a href="viber://chat?number=+639399057425" class="hover:text-yellow-300">0939 905 7425</a>
          </span>
        </p>

        <p class="flex items-center space-x-3">
          <i class="fa-solid fa-phone text-yellow-400 text-2xl"></i>
          <span>
            Telephone: 
            <a href="tel:+63447917424" class="hover:text-yellow-300">044 791 7424</a> / 
            <a href="tel:+63449198410" class="hover:text-yellow-300">044 919 8410</a>
          </span>
        </p>

        <p class="flex items-start space-x-3">
          <i class="fa-solid fa-envelope text-yellow-400 text-2xl"></i>
          <span>
            <a href="mailto:barcieinternationalcenter@gmail.com" class="hover:text-yellow-300">barcieinternationalcenter@gmail.com</a><br>
            <a href="mailto:barcie@lcup.edu.ph" class="hover:text-yellow-300">barcie@lcup.edu.ph</a>
          </span>
        </p>

        <p class="flex items-center space-x-3">
          <i class="fa-solid fa-map-location-dot text-yellow-400 text-2xl"></i>
          <span>
            <a href="https://maps.app.goo.gl/qcmi2CzQz7pCHiav6" target="_blank" class="hover:text-yellow-300">valenzuela st. Capitol View Park Subd. Brgy. Bulihan, City of Malolos, Bulacan 3000</a>
          </span>
        </p>
      </div>
    </div>
  </section>



  <!-- Footer -->
  <footer class="bg-black/50 backdrop-blur-lg text-center py-4 relative z-20">
    <p>© BarCIE International Center 2025</p>
  </footer>


  <!-- Scripts -->
<script>
  // ---------------- Sidebar Toggle ----------------
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

  // ---------------- Show/Hide Sections ----------------
  function closeSection(id) { document.getElementById(id).classList.add('hidden'); }
  function showSection(id) {
    document.querySelectorAll('.content-section').forEach(sec => sec.classList.add('hidden'));
    document.getElementById(id).classList.remove('hidden');
  }

  // ---------------- Password Toggle ----------------
  function togglePassword(id) {
    const input = document.getElementById(id);
    input.type = input.type === "password" ? "text" : "password";
  }

  // ---------------- Login/Signup Toggle ----------------
  const loginForm = document.getElementById('user-login-form');
  const signupForm = document.getElementById('user-signup-form');

  document.querySelectorAll('.login-link').forEach(el => el.addEventListener('click', e => {
    e.preventDefault();
    signupForm.classList.add('hidden');
    loginForm.classList.remove('hidden');
  }));

  document.querySelectorAll('.signup-link').forEach(el => el.addEventListener('click', e => {
    e.preventDefault();
    loginForm.classList.add('hidden');
    signupForm.classList.remove('hidden');
  }));

  // ---------------- Real-time Signup Validation ----------------
  const emailInput = document.getElementById('user-signup-email');
  const passwordInput = document.getElementById('user-signup-password');
  const confirmInput = document.getElementById('user-signup-confirm');
  const emailMsg = document.getElementById('email-msg');
  const passwordMsg = document.getElementById('password-msg');
  const confirmMsg = document.getElementById('confirm-msg');

  emailInput.addEventListener('input', () => {
    if (!/@email\.lcup\.edu\.ph$/.test(emailInput.value)) {
      emailMsg.textContent = "Email must end with @email.lcup.edu.ph";
      emailMsg.classList.remove('hidden');
    } else emailMsg.classList.add('hidden');
  });

  passwordInput.addEventListener('input', () => {
    const val = passwordInput.value;
    if (!/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/.test(val)) {
      passwordMsg.textContent = "Password must be 8+ chars, letters & numbers";
      passwordMsg.classList.remove('hidden');
    } else passwordMsg.classList.add('hidden');

    if (confirmInput.value && confirmInput.value !== val) {
      confirmMsg.textContent = "Passwords do not match";
      confirmMsg.classList.remove('hidden');
    } else confirmMsg.classList.add('hidden');
  });

  confirmInput.addEventListener('input', () => {
    if (confirmInput.value !== passwordInput.value) {
      confirmMsg.textContent = "Passwords do not match";
      confirmMsg.classList.remove('hidden');
    } else confirmMsg.classList.add('hidden');
  });

  // ---------------- User AJAX Login ----------------
  const loginErrorEl = document.getElementById('login-error');
  loginForm.addEventListener('submit', e => {
    e.preventDefault();
    loginErrorEl.classList.add('hidden');
    loginErrorEl.textContent = "";

    const formData = new FormData(loginForm);
    fetch('database/user_auth.php', { method: 'POST', body: formData })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          window.location.href = data.is_admin ? 'dashboard.php' : 'Guest.php';
        } else {
          loginErrorEl.textContent = data.error || "Login failed.";
          loginErrorEl.classList.remove('hidden');
        }
      })
      .catch(() => {
        loginErrorEl.textContent = "An unexpected error occurred.";
        loginErrorEl.classList.remove('hidden');
      });
  });

  // ---------------- User Signup Validation Before Submit ----------------
  signupForm.addEventListener('submit', e => {
    const emailVal = emailInput.value;
    const passVal = passwordInput.value;
    const confirmVal = confirmInput.value;

    if (!/@email\.lcup\.edu\.ph$/.test(emailVal) ||
        !/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/.test(passVal) ||
        passVal !== confirmVal) {
      e.preventDefault();
      alert("Please fix signup errors before submitting.");
    }
  });

  // ---------------- Admin AJAX Login ----------------
  const adminForm = document.getElementById('admin-login-form');
  const adminError = document.getElementById('admin-login-error');
  const toggleAdminPasswordBtn = document.getElementById('toggleAdminPassword');
  const adminPasswordInput = document.getElementById('admin-password');

  toggleAdminPasswordBtn.addEventListener('click', () => {
    if (adminPasswordInput.type === 'password') {
      adminPasswordInput.type = 'text';
      toggleAdminPasswordBtn.textContent = '🙈';
    } else {
      adminPasswordInput.type = 'password';
      toggleAdminPasswordBtn.textContent = '👁️';
    }
  });

  adminForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    adminError.classList.add('hidden');
    adminError.textContent = '';

    const formData = new FormData(adminForm);

    try {
      const res = await fetch('database/admin_login.php', { method: 'POST', body: formData });
      const data = await res.json();

      if (data.success) {
        window.location.href = 'dashboard.php';
      } else {
        adminError.textContent = data.message || 'Login failed.';
        adminError.classList.remove('hidden');
      }
    } catch {
      adminError.textContent = 'Something went wrong. Try again.';
      adminError.classList.remove('hidden');
    }
  });
</script>


</body>

</html>