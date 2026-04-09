<?php date_default_timezone_set('Asia/Manila'); ?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="user-id" content="<?php echo $user_id; ?>">
<link rel="icon" type="image/x-icon" href="favicon.ico">
<link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
<link rel="apple-touch-icon" href="public/images/imageBg/barcie_logo.jpg">
<title>Guest Portal</title>
<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<!-- Google Sign-In -->
<script src="https://accounts.google.com/gsi/client" async defer></script>

<!-- FullCalendar CSS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">

<!-- Tailwind CSS (Play CDN – preflight disabled to preserve Bootstrap) -->
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    corePlugins: { preflight: false },
    theme: {
      extend: {
        colors: {
          'sidebar-dark': '#07263f',
          'sidebar-light': '#0b3a5f',
          'brand-blue': '#3498db',
          'brand-dark': '#2c3e50',
          'brand-green': '#27ae60',
        }
      }
    }
  }
</script>

<!-- Global scoped styles (body, scrollbar, accessibility, content-section toggling) -->
<style>
  /* Body base */
  body {
    font-family: "Segoe UI", Arial, sans-serif;
    background: linear-gradient(135deg, #dbe9ff, #e8f0ff);
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
  }

  /* Scrollbars */
  ::-webkit-scrollbar {
    width: 8px;
    height: 8px;
  }

  ::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.05);
    border-radius: 8px;
  }

  ::-webkit-scrollbar-thumb {
    background: linear-gradient(180deg, #3498db, #1d6fa5);
    border-radius: 8px;
  }

  ::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(180deg, #1d6fa5, #0e3f6d);
  }

  /* Content section show/hide */
  .content-section {
    display: none !important;
    opacity: 0;
  }

  .content-section.active {
    display: block !important;
    opacity: 1 !important;
  }

  .content-section[style*="display: block"] {
    display: block !important;
    opacity: 1 !important;
  }

  section.active,
  div.active.content-section {
    display: block !important;
    opacity: 1 !important;
    visibility: visible !important;
  }

  /* Focus states */
  *:focus-visible {
    outline: 3px solid rgba(52, 152, 219, 0.6);
    outline-offset: 2px;
    border-radius: 4px;
  }

  /* Screen reader */
  .sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
  }

  /* Status badges */
  .status-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    text-align: center;
    min-width: 80px;
  }

  .status-pending {
    background: #f39c12;
    color: white;
  }

  .status-confirmed {
    background: #27ae60;
    color: white;
  }

  .status-cancelled {
    background: #e74c3c;
    color: white;
  }

  .status-completed {
    background: #2980b9;
    color: white;
  }

  /* Loading overlay */
  .loading-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    backdrop-filter: blur(5px);
  }

  .loading-content {
    background: white;
    padding: 30px;
    border-radius: 15px;
    text-align: center;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
  }

  /* Loading spinner */
  .loading-spinner {
    display: inline-block;
    width: 40px;
    height: 40px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #3498db;
    border-radius: 50%;
    animation: tw-spin 1s linear infinite;
  }

  @keyframes tw-spin {
    0% {
      transform: rotate(0deg);
    }

    100% {
      transform: rotate(360deg);
    }
  }

  /* FullCalendar overrides */
  .fc {
    max-width: 100%;
    margin: 0 auto;
    font-family: "Segoe UI", Arial, sans-serif;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  }

  .fc-event {
    cursor: pointer;
    border-radius: 4px;
    padding: 2px 6px;
    font-size: 0.85rem;
    font-weight: 500;
    border: none !important;
  }

  .fc-event-title {
    font-weight: 500;
  }

  .fc-today {
    background-color: rgba(52, 152, 219, 0.1) !important;
  }

  .fc-toolbar-title {
    font-size: 1.4em !important;
    font-weight: 600;
    color: #2c3e50;
  }

  /* Form validation states (Bootstrap override) */
  .form-control.is-valid {
    border-color: #28a745;
  }

  .form-control.is-invalid {
    border-color: #dc3545;
  }

  /* Toast container */
  .toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1060;
  }

  /* Modal backdrop transparent */
  .modal-backdrop {
    background-color: transparent !important;
    opacity: 0 !important;
    pointer-events: none !important;
  }

  .modal {
    pointer-events: auto !important;
  }

  /* Utility animation class (WAA fallback) */
  .hidden {
    display: none !important;
  }

  .visible {
    display: block !important;
  }

  /* Reduced motion */
  @media (prefers-reduced-motion: reduce) {

    *,
    *::before,
    *::after {
      animation-duration: 0.01ms !important;
      animation-iteration-count: 1 !important;
      transition-duration: 0.01ms !important;
    }
  }

  /* High contrast */
  @media (prefers-contrast: high) {
    .sidebar-guest {
      background: #000;
      border-right: 3px solid #fff;
    }

    .sidebar-guest a,
    .sidebar-guest button {
      border: 2px solid #fff;
    }

    .content-section {
      background: #fff;
      border: 3px solid #000;
    }
  }

  /* Print */
  @media print {

    .sidebar-guest,
    .mobile-menu-toggle,
    footer {
      display: none !important;
    }

    .main-content {
      margin-left: 0 !important;
      width: 100% !important;
    }
  }

  /* Desktop hide mobile-only */
  @media (min-width: 769px) {
    .mobile-menu-toggle {
      display: none;
    }

    .sidebar-overlay {
      display: none !important;
    }
  }

  /* Table styles (dynamically generated booking tables) */
  .table-container {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(52, 152, 219, 0.3);
    margin-top: 15px;
  }

  table {
    width: 100%;
    border-collapse: collapse;
    background: transparent;
    font-size: 0.95rem;
  }

  table th {
    background: linear-gradient(135deg, #3498db, #2980b9);
    color: #fff;
    padding: 15px 12px;
    text-align: left;
    font-weight: 600;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  table td {
    padding: 15px 12px;
    border-bottom: 1px solid rgba(52, 152, 219, 0.1);
    color: #2c3e50;
    vertical-align: top;
  }

  table tr:nth-child(even) {
    background: rgba(52, 152, 219, 0.02);
  }

  table tr:hover {
    background: rgba(52, 152, 219, 0.08);
  }

  .booking-details {
    max-width: 300px;
    line-height: 1.5;
    font-size: 0.9rem;
  }

  .booking-details .detail-item {
    margin: 4px 0;
    display: flex;
    flex-wrap: wrap;
  }

  .booking-details .detail-label {
    font-weight: 600;
    color: #2c3e50;
    min-width: 100px;
    margin-right: 8px;
  }

  .booking-details .detail-value {
    color: #546e7a;
    flex: 1;
  }

  .booking-type {
    font-weight: 600;
    color: #3498db;
    text-transform: capitalize;
  }

  .booking-date {
    font-size: 0.9rem;
    color: #546e7a;
  }

  .no-bookings {
    text-align: center;
    padding: 40px 20px;
    color: #7f8c8d;
    font-style: italic;
  }

  .no-bookings i {
    font-size: 3rem;
    margin-bottom: 15px;
    display: block;
  }

  @media (max-width: 768px) {
    .table-container {
      overflow-x: auto;
    }

    table {
      min-width: 600px;
      font-size: 0.85rem;
    }

    table th,
    table td {
      padding: 10px 8px;
    }

    .booking-details {
      max-width: 200px;
    }
  }

  /* Chat box */
  .chat-box {
    border-radius: 15px;
    border: 2px solid rgba(52, 152, 219, 0.25);
    height: 250px;
    overflow-y: auto;
    padding: 15px;
    margin-bottom: 15px;
    background: rgba(255, 255, 255, 0.6);
    backdrop-filter: blur(8px);
    box-shadow: 0 6px 15px rgba(52, 152, 219, 0.2);
  }

  .chat-message {
    padding: 10px 14px;
    border-radius: 12px;
    margin: 8px 0;
    max-width: 75%;
    clear: both;
  }

  .chat-message.user {
    background: linear-gradient(135deg, #3498db, #1d6fa5);
    color: #fff;
    margin-left: auto;
    box-shadow: 0 0 12px rgba(52, 152, 219, 0.4);
  }

  .chat-message.guest {
    background: rgba(52, 152, 219, 0.1);
    color: #1b2838;
    margin-right: auto;
    border: 1px solid rgba(52, 152, 219, 0.3);
  }

  /* Better button hover styles */
  .btn {
    border-radius: 6px;
    font-weight: 500;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
  }

  .btn::before {
    content: "";
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
  }

  .btn:hover::before {
    left: 100%;
  }

  .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
  }

  .btn:active {
    transform: translateY(0);
  }

  /* Alert enhancements */
  .alert {
    border: none;
    border-radius: 8px;
    font-weight: 500;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }

  .alert-success {
    background: linear-gradient(135deg, #d4edda, #c3e6cb);
    color: #155724;
    border-left: 4px solid #28a745;
  }

  .alert-danger {
    background: linear-gradient(135deg, #f8d7da, #f5c6cb);
    color: #721c24;
    border-left: 4px solid #dc3545;
  }

  .alert-info {
    background: linear-gradient(135deg, #d1ecf1, #bee5eb);
    color: #0c5460;
    border-left: 4px solid #17a2b8;
  }
</style>

<!-- Web Animations API (framer-motion style spring animations) -->
<script>
    (function () {
      'use strict';
      const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

      // Spring-like easing curves (mimic framer-motion defaults)
      const SPRING_EASE = 'cubic-bezier(0.34, 1.56, 0.64, 1)';
      const EASE_OUT = 'cubic-bezier(0.22, 1, 0.36, 1)';
      const EASE_IN_OUT = 'cubic-bezier(0.65, 0, 0.35, 1)';

      /**
       * Animate an element with Web Animations API (framer-motion style)
       * @param {Element} el
       * @param {Object} opts - { from, to, duration, easing, delay, fill }
       * @returns {Animation|null}
       */
      window.motionAnimate = function (el, opts) {
        if (!el || prefersReducedMotion) return null;
        const defaults = { duration: 400, easing: SPRING_EASE, delay: 0, fill: 'forwards' };
        const cfg = Object.assign({}, defaults, opts);
        return el.animate([cfg.from, cfg.to], {
          duration: cfg.duration,
          easing: cfg.easing,
          delay: cfg.delay,
          fill: cfg.fill
        });
      };

      /** Fade-in-up (replaces fadeInUp/slideInUp keyframes) */
      window.motionFadeInUp = function (el, duration, delay) {
        return window.motionAnimate(el, {
          from: { opacity: 0, transform: 'translateY(24px)' },
          to: { opacity: 1, transform: 'translateY(0)' },
          duration: duration || 500,
          easing: SPRING_EASE,
          delay: delay || 0
        });
      };

      /** Fade-in (replaces fadeIn keyframe) */
      window.motionFadeIn = function (el, duration) {
        return window.motionAnimate(el, {
          from: { opacity: 0 },
          to: { opacity: 1 },
          duration: duration || 350,
          easing: EASE_OUT
        });
      };

      /** Slide-in from right (replaces slideInRight keyframe) */
      window.motionSlideInRight = function (el, duration) {
        return window.motionAnimate(el, {
          from: { opacity: 0, transform: 'translateX(100%)' },
          to: { opacity: 1, transform: 'translateX(0)' },
          duration: duration || 500,
          easing: SPRING_EASE
        });
      };

      /** Slide-in from left (for sidebar) */
      window.motionSlideInLeft = function (el, duration) {
        return window.motionAnimate(el, {
          from: { opacity: 0, transform: 'translateX(-100%)' },
          to: { opacity: 1, transform: 'translateX(0)' },
          duration: duration || 400,
          easing: SPRING_EASE
        });
      };

      /** Scale-in (spring bounce for cards/buttons) */
      window.motionScaleIn = function (el, duration) {
        return window.motionAnimate(el, {
          from: { opacity: 0, transform: 'scale(0.9)' },
          to: { opacity: 1, transform: 'scale(1)' },
          duration: duration || 350,
          easing: SPRING_EASE
        });
      };

      // Auto-animate content-section.active transitions
      const observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (m) {
          if (m.type === 'attributes' && m.attributeName === 'class') {
            const el = m.target;
            if (el.classList.contains('content-section') && el.classList.contains('active')) {
              window.motionFadeInUp(el, 500);
            }
          }
        });
      });

      document.addEventListener('DOMContentLoaded', function () {
        // Observe all content-sections for active class
        document.querySelectorAll('.content-section').forEach(function (section) {
          observer.observe(section, { attributes: true, attributeFilter: ['class'] });
        });

        // Animate sidebar buttons on load with stagger
        var sidebarBtns = document.querySelectorAll('.sidebar-btn');
        sidebarBtns.forEach(function (btn, i) {
          window.motionFadeInUp(btn, 400, i * 80);
        });

        // Animate the active section on initial load
        var activeSection = document.querySelector('.content-section.active');
        if (activeSection) window.motionFadeInUp(activeSection, 600);
      });
    })();
</script>

<link rel="stylesheet" href="assets/css/page-state.css">

<!-- Vendor JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Tesseract.js OCR for ID Validation -->
<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>

<!-- Popup Manager -->
<script src="Components/Popup/popup-manager.js"></script>

<!-- App JS (migrated to PHP includes by feature folder) -->
<?php include __DIR__ . '/Sidebar/guest-core-init.php'; ?>
<?php include __DIR__ . '/Sidebar/guest-navigation.php'; ?>
<?php include __DIR__ . '/Booking/guest-booking-filters.php'; ?>
<?php include __DIR__ . '/RoomsAndFacilities.php/guest-item-buttons.php'; ?>
<?php include __DIR__ . '/RoomsAndFacilities.php/guest-item-modal.php'; ?>
<?php include __DIR__ . '/Booking/guest-booking-flow.php'; ?>
<?php include __DIR__ . '/Booking/guest-ui-forms-toast.php'; ?>
<?php include __DIR__ . '/Dashboard/guest-sidebar-overview-stats.php'; ?>
<?php include __DIR__ . '/Dashboard/guest-overview-chat.php'; ?>
<?php include __DIR__ . '/AvailabilityCalendar.php/guest-rating-calendar.php'; ?>
<?php include __DIR__ . '/RoomsAndFacilities.php/guest-gallery-core.php'; ?>
<?php include __DIR__ . '/RoomsAndFacilities.php/guest-gallery-bindings.php'; ?>
<?php
$guestDebugInitPath = __DIR__ . '/guest-debug-init.php';
if (file_exists($guestDebugInitPath)) {
  include $guestDebugInitPath;
}
?>
<script src="Components/Guest/Booking/guest-inline.js" defer></script>