<!doctype html>
<html lang="en">

<?php include __DIR__ . '/Components/Landing/head.php'; ?>

<body class="overflow-x-hidden">

  <?php include __DIR__ . '/Components/Landing/navigation.php'; ?>

  <?php include __DIR__ . '/Components/Landing/sections/hero.php'; ?>

  <?php include __DIR__ . '/Components/Landing/sections/about.php'; ?>

  <?php include __DIR__ . '/Components/Landing/sections/vision_mission.php'; ?>

  <?php include __DIR__ . '/Components/Landing/sections/news.php'; ?>

  <?php include __DIR__ . '/Components/Landing/sections/event_stylists.php'; ?>

  <?php include __DIR__ . '/Components/Landing/sections/caterings.php'; ?>

  <?php include __DIR__ . '/Components/Landing/sections/brochure.php'; ?>

  <?php include __DIR__ . '/Components/Landing/sections/features.php'; ?>


  <?php include __DIR__ . '/Components/Landing/sections/contact.php'; ?>

  <?php include __DIR__ . '/Components/Landing/footer.php'; ?>



  <!-- Landing Page JavaScript -->
  <?php
  // Ensure server-side times use Philippine time
  date_default_timezone_set('Asia/Manila');
  $v = time() . '_' . rand(1000, 9999); // Strong cache busting
  ?>
  <script>console.log('🔄 Cache bust version: <?php echo $v; ?>');</script>
  <script src="assets/js/page-state-manager.js?v=<?php echo $v; ?>"></script>
  <script src="Components/Landing/main.js?v=<?php echo $v; ?>"></script>
  <script src="Components/Landing/auth.js?v=<?php echo $v; ?>"></script>

  <!-- Component Verification Script (for testing) -->
  <script src="Components/Landing/verify-components.js?v=<?php echo $v; ?>"></script>

  <?php include __DIR__ . '/Components/Popup/ConfirmPopup.php'; ?>
  <?php include __DIR__ . '/Components/Popup/ErrorPopup.php'; ?>
  <?php include __DIR__ . '/Components/Popup/LoadingPopup.php'; ?>
  <?php include __DIR__ . '/Components/Popup/SuccessPopup.php'; ?>

</body>

</html>