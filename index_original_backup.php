<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="icon" type="image/png" href="assets/images/imageBg/barcie_logo.jpg">
  <title>BarCIE International Center - Your Gateway to Hospitality Excellence</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Custom Landing Page CSS -->
  <link rel="stylesheet" href="assets/css/landing-page.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <!-- Bootstrap JavaScript -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <!-- AOS Animation Library -->
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

  <style>
    /* Custom CSS Variables */
    :root {
      --primary-color: #1e3c72;
      --secondary-color: #2a5298;
      --accent-color: #ffdd57;
      --text-dark: #2c3e50;
      --text-light: #ffffff;
      --glass-bg: rgba(255, 255, 255, 0.1);
    }

    /* Smooth Scrolling */
    html {
      scroll-behavior: smooth;
    }

    /* Background Animations */
    @keyframes gradientBG {
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

    .animated-bg {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color), var(--accent-color));
      background-size: 400% 400%;
      animation: gradientBG 15s ease infinite;
    }

    /* Glassmorphism Effects */
    .glass-card {
      background: var(--glass-bg);
      backdrop-filter: blur(20px);
      border-radius: 20px;
      border: 1px solid rgba(255, 255, 255, 0.2);
      box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
      transition: all 0.3s ease;
    }

    .glass-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 20px 40px rgba(255, 221, 87, 0.3);
    }

    /* Navigation Styles */
    .navbar-custom {
      background: rgba(30, 60, 114, 0.95);
      backdrop-filter: blur(10px);
      transition: all 0.3s ease;
    }

    .navbar-custom.scrolled {
      background: rgba(30, 60, 114, 0.98);
      box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
    }

    
    /* Hero Section */
    .hero-section {
      height: 100vh;
      background: linear-gradient(135deg, rgba(30, 60, 114, 0.8), rgba(42, 82, 152, 0.8)),
        url('assets/images/imageBg/BarCIE-0.jpg') center/cover;
      display: flex;
      align-items: center;
      position: relative;
    }

    /* Button Styles */
    .btn-primary-custom {
      background: linear-gradient(45deg, var(--accent-color), #ffd700);
      border: none;
      color: var(--text-dark);
      padding: 12px 30px;
      border-radius: 50px;
      font-weight: bold;
      transition: all 0.3s ease;
      transform: perspective(1px) translateZ(0);
      box-shadow: 0 4px 15px rgba(255, 221, 87, 0.3);
    }

    .btn-primary-custom:hover {
      transform: scale(1.05);
      box-shadow: 0 8px 25px rgba(255, 221, 87, 0.5);
      color: var(--text-dark);
    }

    .btn-outline-custom {
      border: 2px solid var(--accent-color);
      color: var(--accent-color);
      background: transparent;
      padding: 12px 30px;
      border-radius: 50px;
      font-weight: bold;
      transition: all 0.3s ease;
    }

    .btn-outline-custom:hover {
      background: var(--accent-color);
      color: var(--text-dark);
      transform: scale(1.05);
    }

    /* Feature Cards */
    .feature-card {
      background: var(--glass-bg);
      backdrop-filter: blur(15px);
      border-radius: 15px;
      padding: 2rem;
      text-align: center;
      transition: all 0.3s ease;
      border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .feature-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 30px rgba(255, 221, 87, 0.2);
    }

    /* Section Styles */
    .section-padding {
      padding: 80px 0;
    }

    /* Statistics */
    .stat-number {
      font-size: 3rem;
      font-weight: bold;
      color: var(--accent-color);
    }

    /* Testimonials */
    .testimonial-card {
      background: white;
      border-radius: 15px;
      padding: 2rem;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
    }

    .testimonial-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }

    /* Custom scrollbar */
    ::-webkit-scrollbar {
      width: 8px;
    }

    ::-webkit-scrollbar-track {
      background: #f1f1f1;
    }

    ::-webkit-scrollbar-thumb {
      background: var(--accent-color);
      border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb:hover {
      background: #ffd700;
    }

    /* Loading Animation */
    .loading-spinner {
      border: 4px solid #f3f3f3;
      border-top: 4px solid var(--accent-color);
      border-radius: 50%;
      width: 40px;
      height: 40px;
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      0% {
        transform: rotate(0deg);
      }

      100% {
        transform: rotate(360deg);
      }
    }
  </style>
</head>

<body class="overflow-x-hidden">

  <!-- Navigation -->
  <nav class="navbar navbar-expand-lg navbar-dark navbar-custom fixed-top" id="mainNavbar">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center" href="#home">
        <img src="assets/images/imageBg/barcie_logo.jpg" alt="BarCIE Logo" width="40" height="40"
          class="rounded-circle me-2">
        <span class="fw-bold">BarCIE</span>
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav mx-auto">
          <li class="nav-item">
            <a class="nav-link" href="#home">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#about">About</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#features">Features</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#services">Services</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#contact">Contact</a>
          </li>
        </ul>

        <div class="navbar-nav">
         
          <button class="btn btn-primary-custom" onclick="showSection('admin-login')">
            <i class="fas fa-shield-alt me-1"></i> Admin
          </button>
        </div>
      </div>
    </div>
  </nav>

  <!-- Hero Section -->
  <section id="home" class="hero-section">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-6" data-aos="fade-right">
          <h1 class="display-3 fw-bold text-white mb-4">
            Welcome to <span class="text-warning">BarCIE</span> International Center
          </h1>
          <p class="lead text-white mb-4">
            Barasoain Center for Innovative Education (BarCIE) - LCUP's premier laboratory facility for BS Tourism
            Management. Experience world-class hospitality education and services.
          </p>
          <div class="d-flex flex-wrap gap-3">
            <button class="btn btn-primary-custom btn-lg" onclick="window.location.href='Guest.php'">
              <i class="fas fa-arrow-right me-2"></i>Get Started
            </button>
            <button class="btn btn-outline-custom btn-lg" onclick="scrollToSection('about')">
              <i class="fas fa-info-circle me-2"></i>Learn More
            </button>
          </div>
        </div>
        <div class="col-lg-6" data-aos="fade-left">
          <div class="glass-card p-4">
            <img src="assets/images/imageBg/barcie_logo.jpg" alt="BarCIE Hotel" class="img-fluid rounded-3 shadow-lg">
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- About Section -->
  <section id="about" class="section-padding bg-light">
    <div class="container">
      <div class="row">
        <div class="col-lg-12 text-center mb-5" data-aos="fade-up">
          <h2 class="display-5 fw-bold text-dark mb-3">About BarCIE International Center</h2>
          <p class="lead text-muted">Your gateway to hospitality excellence</p>
        </div>
      </div>
      <div class="row align-items-center">
        <div class="col-lg-6 mb-4" data-aos="fade-right">
          <div class="pe-lg-4">
            <h3 class="h2 fw-bold text-primary mb-4">Excellence in Hospitality Education</h3>
            <p class="text-muted mb-4">
              BarCIE International Center serves as La Consolacion University Philippines' premier laboratory facility
              for BS Tourism Management students. We provide hands-on experience in hotel operations, guest services,
              and hospitality management.
            </p>
            <div class="row">
              <div class="col-sm-6 mb-3">
                <div class="d-flex align-items-center">
                  <i class="fas fa-check-circle text-success me-2"></i>
                  <span>Professional Training</span>
                </div>
              </div>
              <div class="col-sm-6 mb-3">
                <div class="d-flex align-items-center">
                  <i class="fas fa-check-circle text-success me-2"></i>
                  <span>Real-world Experience</span>
                </div>
              </div>
              <div class="col-sm-6 mb-3">
                <div class="d-flex align-items-center">
                  <i class="fas fa-check-circle text-success me-2"></i>
                  <span>Industry Standards</span>
                </div>
              </div>
              <div class="col-sm-6 mb-3">
                <div class="d-flex align-items-center">
                  <i class="fas fa-check-circle text-success me-2"></i>
                  <span>Modern Facilities</span>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-6" data-aos="fade-left">
          <div class="row">
            <div class="col-6 mb-4">
              <div class="text-center">
                <div class="stat-number">50+</div>
                <p class="text-muted mb-0">Rooms & Facilities</p>
              </div>
            </div>
            <div class="col-6 mb-4">
              <div class="text-center">
                <div class="stat-number">1000+</div>
                <p class="text-muted mb-0">Students Trained</p>
              </div>
            </div>
            <div class="col-6 mb-4">
              <div class="text-center">
                <div class="stat-number">24/7</div>
                <p class="text-muted mb-0">Guest Services</p>
              </div>
            </div>
            <div class="col-6 mb-4">
              <div class="text-center">
                <div class="stat-number">5★</div>
                <p class="text-muted mb-0">Service Rating</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Features Section -->
  <section id="features" class="section-padding animated-bg">
    <div class="container">
      <div class="row">
        <div class="col-lg-12 text-center mb-5" data-aos="fade-up">
          <h2 class="display-5 fw-bold text-white mb-3">System Features</h2>
          <p class="lead text-white">Comprehensive hotel management solution</p>
        </div>
      </div>
      <div class="row">
        <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
          <div class="feature-card h-100">
            <div class="mb-4">
              <i class="fas fa-user-shield fa-3x text-warning"></i>
            </div>
            <h4 class="text-white mb-3">Dual Authentication</h4>
            <p class="text-white-50">Separate secure login systems for guests and administrators with role-based access
              control.</p>
          </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
          <div class="feature-card h-100">
            <div class="mb-4">
              <i class="fas fa-calendar-alt fa-3x text-warning"></i>
            </div>
            <h4 class="text-white mb-3">Smart Booking System</h4>
            <p class="text-white-50">Advanced reservation management with real-time availability and automated
              confirmation.</p>
          </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
          <div class="feature-card h-100">
            <div class="mb-4">
              <i class="fas fa-comments fa-3x text-warning"></i>
            </div>
            <h4 class="text-white mb-3">Live Chat Support</h4>
            <p class="text-white-50">Professional customer support chat system with quick response templates and
              real-time messaging.</p>
          </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="400">
          <div class="feature-card h-100">
            <div class="mb-4">
              <i class="fas fa-tachometer-alt fa-3x text-warning"></i>
            </div>
            <h4 class="text-white mb-3">Admin Dashboard</h4>
            <p class="text-white-50">Comprehensive management interface with real-time statistics and interactive
              calendar.</p>
          </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="500">
          <div class="feature-card h-100">
            <div class="mb-4">
              <i class="fas fa-mobile-alt fa-3x text-warning"></i>
            </div>
            <h4 class="text-white mb-3">Responsive Design</h4>
            <p class="text-white-50">Mobile-first approach ensuring perfect experience across all devices and screen
              sizes.</p>
          </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="600">
          <div class="feature-card h-100">
            <div class="mb-4">
              <i class="fas fa-shield-alt fa-3x text-warning"></i>
            </div>
            <h4 class="text-white mb-3">Secure & Reliable</h4>
            <p class="text-white-50">Enterprise-level security with encrypted data transmission and secure session
              management.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Services Section -->
  <section id="services" class="section-padding bg-light">
    <div class="container">
      <div class="row">
        <div class="col-lg-12 text-center mb-5" data-aos="fade-up">
          <h2 class="display-5 fw-bold text-dark mb-3">Our Services</h2>
          <p class="lead text-muted">Everything you need for exceptional hospitality experience</p>
        </div>
      </div>
      <div class="row">
        <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
          <div class="text-center p-4">
            <div class="mb-4">
              <i class="fas fa-bed fa-4x text-primary"></i>
            </div>
            <h4 class="mb-3">Room Management</h4>
            <p class="text-muted">Complete room and facility management with real-time availability and pricing control.
            </p>
          </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
          <div class="text-center p-4">
            <div class="mb-4">
              <i class="fas fa-concierge-bell fa-4x text-primary"></i>
            </div>
            <h4 class="mb-3">Guest Services</h4>
            <p class="text-muted">Professional guest portal with booking management, profile updates, and feedback
              system.</p>
          </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
          <div class="text-center p-4">
            <div class="mb-4">
              <i class="fas fa-users fa-4x text-primary"></i>
            </div>
            <h4 class="mb-3">Event Hosting</h4>
            <p class="text-muted">Specialized function hall bookings for events, conferences, and special occasions.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- User Login & Signup Modal -->
  <section id="user-auth" class="content-section hidden">
    <div class="fixed inset-0 bg-dark bg-opacity-50 d-flex justify-content-center align-items-center" style="z-index: 1050; backdrop-filter: blur(5px);">
      <div class="glass-card p-4 shadow-lg" style="max-width: 400px; width: 90%; position: relative;">
        <button onclick="closeSection('user-auth')"
          class="btn-close btn-close-white position-absolute top-0 end-0 m-3" style="z-index: 10;"></button>

        <div class="text-center mb-4">
          <div class="bg-warning rounded-circle mx-auto mb-3" style="width: 60px; height: 60px;"></div>
          <h2 class="h4 fw-bold text-white">User Portal</h2>
          <p class="text-white-50">Login or create a new account</p>
        </div>

        <!-- Login Form -->
        <form id="user-login-form" class="">
          <input type="hidden" name="action" value="login">
          <h3 class="h5 fw-bold text-white mb-3">Login</h3>
          <div id="login-error" class="alert alert-danger d-none"></div>

          <div class="mb-3">
            <label for="user-login-username" class="form-label text-white">Username</label>
            <input type="text" id="user-login-username" name="username" placeholder="Enter username" required
              class="form-control">
          </div>

          <div class="mb-3 position-relative">
            <label for="user-login-password" class="form-label text-white">Password</label>
            <input type="password" id="user-login-password" name="password" placeholder="••••••••" required
              class="form-control">
            <span class="position-absolute top-50 end-0 translate-middle-y me-3 text-white" style="cursor: pointer; margin-top: 12px;"
              onclick="togglePassword('user-login-password')">👁️</span>
          </div>

          <button type="submit" class="btn btn-primary-custom w-100 mb-3">Login</button>

          <p class="text-center text-white">
            Don't have an account?
            <a href="#" class="signup-link text-warning text-decoration-none">Sign Up</a>
          </p>
        </form>

        <hr class="border-white opacity-25 my-4">  

        <!-- Signup Form -->
        <form id="user-signup-form" class="d-none" method="post" action="database/user_auth.php">
          <input type="hidden" name="action" value="signup">
          <h3 class="h5 fw-bold text-white mb-3">Sign Up</h3>

          <div class="mb-3">
            <label for="user-signup-username" class="form-label text-white">Username</label>
            <input type="text" id="user-signup-username" name="username" placeholder="Enter username" required
              class="form-control">
          </div>

          <div class="mb-3">
            <label for="user-signup-email" class="form-label text-white">Email</label>
            <input type="email" id="user-signup-email" name="email" placeholder="Enter email" required
              class="form-control">
            <span id="email-msg" class="text-danger small d-none"></span>
          </div>

          <div class="mb-3 position-relative">
            <label for="user-signup-password" class="form-label text-white">Password</label>
            <input type="password" id="user-signup-password" name="password" placeholder="••••••••" required
              class="form-control">
            <span class="position-absolute top-50 end-0 translate-middle-y me-3 text-white" style="cursor: pointer; margin-top: 12px;"
              onclick="togglePassword('user-signup-password')">👁️</span>
            <span id="password-msg" class="text-danger small d-none"></span>
          </div>

          <div class="mb-3 position-relative">
            <label for="user-signup-confirm" class="form-label text-white">Confirm Password</label>
            <input type="password" id="user-signup-confirm" name="confirm_password" placeholder="••••••••" required
              class="form-control">
            <span class="position-absolute top-50 end-0 translate-middle-y me-3 text-white" style="cursor: pointer; margin-top: 12px;"
              onclick="togglePassword('user-signup-confirm')">👁️</span>
            <span id="confirm-msg" class="text-danger small d-none"></span>
          </div>

          <button type="submit" class="btn btn-primary-custom w-100 mb-3">Sign Up</button>

          <p class="text-center text-white">
            Already have an account?
            <a href="#" class="login-link text-warning text-decoration-none">Login</a>
          </p>
        </form>

        <a href="index.php" class="d-block text-center mt-3 text-warning text-decoration-none">Back to Homepage</a>
      </div>
    </div>
  </section>

  <!-- Admin Login Modal -->
  <section id="admin-login" class="content-section hidden">
    <div class="fixed inset-0 bg-dark bg-opacity-50 d-flex justify-content-center align-items-center" style="z-index: 1050; backdrop-filter: blur(5px);">
      <div class="glass-card p-4 shadow-lg" style="max-width: 400px; width: 90%; position: relative;">
        <button onclick="closeSection('admin-login')"
          class="btn-close btn-close-white position-absolute top-0 end-0 m-3" style="z-index: 10;"></button>

        <div class="text-center mb-4">
          <div class="bg-warning rounded-circle mx-auto mb-3" style="width: 60px; height: 60px;"></div>
          <h2 class="h4 fw-bold text-white">BarCIE Admin Login</h2>
          <p class="text-white-50">Access your unique admin portal</p>
        </div>

        <div id="admin-login-error" class="alert alert-danger d-none"></div>

        <form id="admin-login-form">
          <div class="mb-3">
            <label for="admin-username" class="form-label text-white">Username</label>
            <input type="text" id="admin-username" name="username" placeholder="admin" required
              class="form-control">
          </div>
          <div class="mb-3 position-relative">
            <label for="admin-password" class="form-label text-white">Password</label>
            <input type="password" id="admin-password" name="password" placeholder="••••••••" required
              class="form-control">
            <button type="button" id="toggleAdminPassword"
              class="position-absolute top-50 end-0 translate-middle-y me-3 btn btn-link text-white p-0" style="margin-top: 12px;">👁️</button>
          </div>
          <button type="submit" class="btn btn-primary-custom w-100">Sign In</button>
        </form>
      </div>
    </div>
  </section>

  <!-- Contact Section -->
  <section id="contact" class="section-padding bg-dark text-white">
    <div class="container">
      <div class="row">
        <div class="col-lg-12 text-center mb-5" data-aos="fade-up">
          <h2 class="display-5 fw-bold text-warning mb-3">Get In Touch</h2>
          <p class="lead">Ready to experience world-class hospitality? Contact us today!</p>
        </div>
      </div>
      <div class="row">
        <div class="col-lg-8 mx-auto">
          <div class="row">
            <div class="col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
              <div class="glass-card p-4 h-100">
                <div class="d-flex align-items-center mb-3">
                  <i class="fab fa-viber fa-2x text-warning me-3"></i>
                  <div>
                    <h5 class="mb-1 text-white">Viber</h5>
                    <a href="viber://chat?number=+639399057425" class="text-warning text-decoration-none">0939 905
                      7425</a>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
              <div class="glass-card p-4 h-100">
                <div class="d-flex align-items-center mb-3">
                  <i class="fas fa-phone fa-2x text-warning me-3"></i>
                  <div>
                    <h5 class="mb-1 text-white">Telephone</h5>
                    <a href="tel:+63447917424" class="text-warning text-decoration-none d-block">044 791 7424</a>
                    <a href="tel:+63449198410" class="text-warning text-decoration-none">044 919 8410</a>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
              <div class="glass-card p-4 h-100">
                <div class="d-flex align-items-start mb-3">
                  <i class="fas fa-envelope fa-2x text-warning me-3"></i>
                  <div>
                    <h5 class="mb-1 text-white">Email</h5>
                    <a href="mailto:barcieinternationalcenter@gmail.com"
                      class="text-warning text-decoration-none d-block small">barcieinternationalcenter@gmail.com</a>
                    <a href="mailto:barcie@lcup.edu.ph"
                      class="text-warning text-decoration-none small">barcie@lcup.edu.ph</a>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-6 mb-4" data-aos="fade-up" data-aos-delay="400">
              <div class="glass-card p-4 h-100">
                <div class="d-flex align-items-start mb-3">
                  <i class="fas fa-map-marker-alt fa-2x text-warning me-3"></i>
                  <div>
                    <h5 class="mb-1 text-white">Address</h5>
                    <a href="https://maps.app.goo.gl/qcmi2CzQz7pCHiav6" target="_blank"
                      class="text-warning text-decoration-none small">
                      Valenzuela St. Capitol View Park Subd. Brgy. Bulihan, City of Malolos, Bulacan 3000
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-dark text-white py-4">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-md-6">
          <p class="mb-0">&copy; 2025 BarCIE International Center. All rights reserved.</p>
        </div>
        <div class="col-md-6 text-md-end">
          <p class="mb-0">Built with ❤️ for hospitality excellence</p>
        </div>
      </div>
    </div>
  </footer>

  <script>
    // Initialize AOS (Animate On Scroll)
    AOS.init({
      duration: 1000,
      once: true,
      offset: 100
    });

    // Navbar scroll effect
    window.addEventListener('scroll', function() {
      const navbar = document.getElementById('mainNavbar');
      if (navbar && window.scrollY > 50) {
        navbar.classList.add('scrolled');
      } else if (navbar) {
        navbar.classList.remove('scrolled');
      }
    });

    // Smooth scrolling for anchor links
    function scrollToSection(sectionId) {
      const section = document.getElementById(sectionId);
      if (section) {
        section.scrollIntoView({
          behavior: 'smooth'
        });
      }
    }

    // Update active nav link on scroll
    window.addEventListener('scroll', function() {
      const sections = document.querySelectorAll('section[id]');
      const navLinks = document.querySelectorAll('.nav-link');
      
      let current = '';
      sections.forEach(section => {
        const sectionTop = section.offsetTop - 100;
        if (pageYOffset >= sectionTop) {
          current = section.getAttribute('id');
        }
      });

      navLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === `#${current}`) {
          link.classList.add('active');
        }
      });
    });

    // Show/Hide Sections for modals
    function closeSection(id) {
      const element = document.getElementById(id);
      if (element) {
        element.classList.add('hidden');
        document.body.style.overflow = 'auto';
      }
    }

    function showSection(id) {
      document.querySelectorAll('.content-section').forEach(sec => sec.classList.add('hidden'));
      const element = document.getElementById(id);
      if (element) {
        element.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
      }
    }

    // Password Toggle
    function togglePassword(id) {
      const input = document.getElementById(id);
      if (input) {
        input.type = input.type === "password" ? "text" : "password";
      }
    }

    // Login/Signup Toggle
    const loginForm = document.getElementById('user-login-form');
    const signupForm = document.getElementById('user-signup-form');

    if (loginForm && signupForm) {
      document.querySelectorAll('.login-link').forEach(el => el.addEventListener('click', e => {
        e.preventDefault();
        signupForm.classList.add('d-none');
        loginForm.classList.remove('d-none');
      }));

      document.querySelectorAll('.signup-link').forEach(el => el.addEventListener('click', e => {
        e.preventDefault();
        loginForm.classList.add('d-none');
        signupForm.classList.remove('d-none');
      }));
    }

    // Real-time Signup Validation
    const emailInput = document.getElementById('user-signup-email');
    const passwordInput = document.getElementById('user-signup-password');
    const confirmInput = document.getElementById('user-signup-confirm');
    const emailMsg = document.getElementById('email-msg');
    const passwordMsg = document.getElementById('password-msg');
    const confirmMsg = document.getElementById('confirm-msg');

    if (emailInput && emailMsg) {
      emailInput.addEventListener('input', () => {
        if (!/@gmail\.com$/.test(emailInput.value)) {
          emailMsg.textContent = "Email must end with @gmail.com";
          emailMsg.classList.remove('d-none');
        } else {
          emailMsg.classList.add('d-none');
        }
      });
    }

    if (passwordInput && passwordMsg && confirmInput && confirmMsg) {
      passwordInput.addEventListener('input', () => {
        const val = passwordInput.value;
        if (!/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/.test(val)) {
          passwordMsg.textContent = "Password must be 8+ chars, letters & numbers";
          passwordMsg.classList.remove('d-none');
        } else {
          passwordMsg.classList.add('d-none');
        }

        if (confirmInput.value && confirmInput.value !== val) {
          confirmMsg.textContent = "Passwords do not match";
          confirmMsg.classList.remove('d-none');
        } else {
          confirmMsg.classList.add('d-none');
        }
      });

      confirmInput.addEventListener('input', () => {
        if (confirmInput.value !== passwordInput.value) {
          confirmMsg.textContent = "Passwords do not match";
          confirmMsg.classList.remove('d-none');
        } else {
          confirmMsg.classList.add('d-none');
        }
      });
    }

    // User AJAX Login
    const loginErrorEl = document.getElementById('login-error');
    if (loginForm && loginErrorEl) {
      loginForm.addEventListener('submit', e => {
        e.preventDefault();
        loginErrorEl.classList.add('d-none');
        loginErrorEl.textContent = "";

        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<div class="loading-spinner mx-auto"></div>';
        submitBtn.disabled = true;

        const formData = new FormData(loginForm);
        fetch('database/user_auth.php', { method: 'POST', body: formData })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              submitBtn.innerHTML = '<i class="fas fa-check me-2"></i>Success!';
              setTimeout(() => {
                window.location.href = data.is_admin ? 'dashboard.php' : 'Guest.php';
              }, 1000);
            } else {
              loginErrorEl.textContent = data.error || "Login failed.";
              loginErrorEl.classList.remove('d-none');
              submitBtn.innerHTML = originalText;
              submitBtn.disabled = false;
            }
          })
          .catch(() => {
            loginErrorEl.textContent = "An unexpected error occurred.";
            loginErrorEl.classList.remove('d-none');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
          });
      });
    }

    // User Signup Validation Before Submit
    if (signupForm && emailInput && passwordInput && confirmInput) {
      signupForm.addEventListener('submit', e => {
        const emailVal = emailInput.value;
        const passVal = passwordInput.value;
        const confirmVal = confirmInput.value;

        if (!/@gmail\.com$/.test(emailVal) ||
            !/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/.test(passVal) ||
            passVal !== confirmVal) {
          e.preventDefault();
          alert("Please fix signup errors before submitting.");
        }
      });
    }

    // Admin AJAX Login
    const adminForm = document.getElementById('admin-login-form');
    const adminError = document.getElementById('admin-login-error');
    const toggleAdminPasswordBtn = document.getElementById('toggleAdminPassword');
    const adminPasswordInput = document.getElementById('admin-password');

    if (toggleAdminPasswordBtn && adminPasswordInput) {
      toggleAdminPasswordBtn.addEventListener('click', () => {
        if (adminPasswordInput.type === 'password') {
          adminPasswordInput.type = 'text';
          toggleAdminPasswordBtn.textContent = '🙈';
        } else {
          adminPasswordInput.type = 'password';
          toggleAdminPasswordBtn.textContent = '👁️';
        }
      });
    }

    if (adminForm && adminError) {
      adminForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        adminError.classList.add('d-none');
        adminError.textContent = '';

        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<div class="loading-spinner mx-auto"></div>';
        submitBtn.disabled = true;

        const formData = new FormData(adminForm);

        try {
          const res = await fetch('database/admin_login.php', { method: 'POST', body: formData });
          const data = await res.json();

          if (data.success) {
            submitBtn.innerHTML = '<i class="fas fa-check me-2"></i>Success!';
            setTimeout(() => {
              window.location.href = 'dashboard.php';
            }, 1000);
          } else {
            adminError.textContent = data.message || 'Login failed.';
            adminError.classList.remove('d-none');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
          }
        } catch {
          adminError.textContent = 'Something went wrong. Try again.';
          adminError.classList.remove('d-none');
          submitBtn.innerHTML = originalText;
          submitBtn.disabled = false;
        }
      });
    }

    // Close modal when clicking outside
    document.addEventListener('click', function(e) {
      const modals = document.querySelectorAll('.content-section');
      modals.forEach(modal => {
        if (!modal.classList.contains('hidden') && e.target === modal) {
          closeSection(modal.id);
        }
      });
    });

    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        const openModals = document.querySelectorAll('.content-section:not(.hidden)');
        openModals.forEach(modal => {
          closeSection(modal.id);
        });
      }
    });

    // Loading animation for page
    window.addEventListener('load', function() {
      document.body.style.opacity = '0';
      document.body.style.transition = 'opacity 0.5s ease';
      setTimeout(() => {
        document.body.style.opacity = '1';
      }, 100);
    });
  </script>

</body>
</html>