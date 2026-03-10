<!doctype html>
<html lang="en">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="user-id" content="<?php echo $user_id; ?>">
<link rel="icon" type="image/jpeg" href="public/images/imageBg/barcie_logo.jpg">
<link rel="shortcut icon" type="image/jpeg" href="public/images/imageBg/barcie_logo.jpg">
<link rel="apple-touch-icon" href="public/images/imageBg/barcie_logo.jpg">


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




  <?php

  date_default_timezone_set('Asia/Manila');
  $v = time() . '_' . rand(1000, 9999);
  ?>
  <script>console.log('🔄 Cache bust version: <?php echo $v; ?>');</script>
  <script src="assets/js/page-state-manager.js?v=<?php echo $v; ?>"></script>
  <script src="Components/Landing/main.js?v=<?php echo $v; ?>"></script>
  <script src="Components/Landing/auth.js?v=<?php echo $v; ?>"></script>

  <script src="Components/Landing/verify-components.js?v=<?php echo $v; ?>"></script>

  <?php include __DIR__ . '/Components/Popup/ConfirmPopup.php'; ?>
  <?php include __DIR__ . '/Components/Popup/ErrorPopup.php'; ?>
  <?php include __DIR__ . '/Components/Popup/LoadingPopup.php'; ?>
  <?php include __DIR__ . '/Components/Popup/SuccessPopup.php'; ?>

</body>

</html>