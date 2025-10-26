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
  <?php $v = time(); // Cache busting ?>
  <script src="src/assets/js/page-state-manager.js?v=<?php echo $v; ?>"></script>
  <script src="src/assets/js/landing/main.js?v=<?php echo $v; ?>"></script>
  <script src="src/assets/js/landing/auth.js?v=<?php echo $v; ?>"></script>
  
  <!-- Component Verification Script (for testing) -->
  <script src="src/assets/js/landing/verify-components.js?v=<?php echo $v; ?>"></script>

</body>
</html>