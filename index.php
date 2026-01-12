<!doctype html>
<html lang="en">

<?php include 'components/landing/head.php'; ?>

<body class="overflow-x-hidden">

  <?php include 'components/landing/navigation.php'; ?>

  <?php include 'components/landing/sections/hero.php'; ?>

  <?php include 'components/landing/sections/about.php'; ?>

  <?php include 'components/landing/sections/vision_mission.php'; ?>

  <?php include 'components/landing/sections/news.php'; ?>

  <?php include 'components/landing/sections/event_stylists.php'; ?>

  <?php include 'components/landing/sections/caterings.php'; ?>
  
  <?php include 'components/landing/sections/brochure.php'; ?>
  
  <?php include 'components/landing/sections/features.php'; ?>


  <?php include 'components/landing/sections/contact.php'; ?>

  <?php include 'components/landing/footer.php'; ?>



  <!-- Landing Page JavaScript -->
  <?php
  // Ensure server-side times use Philippine time
  date_default_timezone_set('Asia/Manila');
  $v = time() . '_' . rand(1000, 9999); // Strong cache busting
  ?>
  <script>console.log('🔄 Cache bust version: <?php echo $v; ?>');</script>
  <script src="assets/js/page-state-manager.js?v=<?php echo $v; ?>"></script>
  <script src="assets/js/landing/main.js?v=<?php echo $v; ?>"></script>
  <script src="assets/js/landing/auth.js?v=<?php echo $v; ?>"></script>

  <!-- Component Verification Script (for testing) -->
  <script src="assets/js/landing/verify-components.js?v=<?php echo $v; ?>"></script>

</body>

</html>