<!doctype html>
<html lang="en">

<?php include 'src/components/landing/head.php'; ?>

<body class="overflow-x-hidden">

  <?php include 'src/components/landing/navigation.php'; ?>

  <?php include 'src/components/landing/sections/hero.php'; ?>

  <?php include 'src/components/landing/sections/about.php'; ?>

  <?php include 'src/components/landing/sections/features.php'; ?>

  <?php include 'src/components/landing/sections/services.php'; ?>

  <?php include 'src/components/landing/modals/admin_login_modal.php'; ?>

  <?php include 'src/components/landing/sections/contact.php'; ?>

  <?php include 'src/components/landing/footer.php'; ?>

  <!-- Landing Page JavaScript -->
  <script src="src/assets/js/landing/main.js"></script>
  <script src="src/assets/js/landing/auth.js"></script>
  
  <!-- Component Verification Script (for testing) -->
  <script src="src/assets/js/landing/verify-components.js"></script>

</body>
</html>