<head>
  <?php
  $css_version = time() . '_' . rand(1000, 9999);
  if (!isset($assetUrl) || !is_callable($assetUrl)) {
    $basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
    $assetUrl = static function (string $path) use ($basePath): string {
      $normalizedPath = ltrim(str_replace('\\', '/', $path), '/');
      return ($basePath !== '' ? $basePath : '') . '/' . $normalizedPath;
    };
  }
  ?>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="icon" type="image/jpeg"
    href="<?php echo htmlspecialchars($assetUrl('public/images/imageBg/barcie_logo.jpg'), ENT_QUOTES, 'UTF-8'); ?>">
  <link rel="shortcut icon" type="image/jpeg"
    href="<?php echo htmlspecialchars($assetUrl('public/images/imageBg/barcie_logo.jpg'), ENT_QUOTES, 'UTF-8'); ?>">
  <link rel="apple-touch-icon"
    href="<?php echo htmlspecialchars($assetUrl('public/images/imageBg/barcie_logo.jpg'), ENT_QUOTES, 'UTF-8'); ?>">
  <title>BarCIE International Center - Your Gateway to Hospitality Excellence</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Custom Landing Page CSS -->
  <link rel="stylesheet"
    href="<?php echo htmlspecialchars($assetUrl('Components/Landing/landing-page.css') . '?v=' . $css_version, ENT_QUOTES, 'UTF-8'); ?>">
  <!-- Caterings / Event Stylists CSS -->
  <link rel="stylesheet"
    href="<?php echo htmlspecialchars($assetUrl('Components/Landing/caterings.css') . '?v=' . $css_version, ENT_QUOTES, 'UTF-8'); ?>">
  <!-- News Section CSS -->
  <link rel="stylesheet"
    href="<?php echo htmlspecialchars($assetUrl('Components/Admin/News/news.css') . '?v=' . $css_version, ENT_QUOTES, 'UTF-8'); ?>">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <!-- Bootstrap JavaScript -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Popup Manager -->
  <script
    src="<?php echo htmlspecialchars($assetUrl('Components/Popup/popup-manager.js'), ENT_QUOTES, 'UTF-8'); ?>"></script>
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
      background: linear-gradient(135deg, rgba(30, 60, 114, 0.78), rgba(42, 82, 152, 0.72));
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