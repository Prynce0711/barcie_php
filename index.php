<?php
// Resolve project root for environments where the app may live in a subfolder.
$candidateRoots = array_filter(array_unique([
  __DIR__,
  realpath(__DIR__) ?: '',
  isset($_SERVER['DOCUMENT_ROOT']) ? rtrim((string) $_SERVER['DOCUMENT_ROOT'], DIRECTORY_SEPARATOR) : '',
  isset($_SERVER['DOCUMENT_ROOT'])
  ? rtrim((string) $_SERVER['DOCUMENT_ROOT'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . basename(__DIR__)
  : '',
]));

$projectRoot = __DIR__;
foreach ($candidateRoots as $candidateRoot) {
  if ($candidateRoot !== '' && is_dir($candidateRoot . DIRECTORY_SEPARATOR . 'Components')) {
    $projectRoot = $candidateRoot;
    break;
  }
}

$componentRoot = $projectRoot . DIRECTORY_SEPARATOR . 'Components';
$includeComponent = static function (string $relativePath) use ($componentRoot): void {
  $normalizedRelativePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($relativePath, '/\\'));
  $fullPath = $componentRoot . DIRECTORY_SEPARATOR . $normalizedRelativePath;

  if (!is_file($fullPath)) {
    throw new RuntimeException('Missing component include: ' . $fullPath);
  }

  include $fullPath;
};
?>

<!doctype html>
<html lang="en">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="user-id" content="<?php echo $user_id; ?>">
<link rel="icon" type="image/jpeg" href="public/images/imageBg/barcie_logo.jpg">
<link rel="apple-touch-icon" href="public/images/imageBg/barcie_logo.jpg">


<?php $includeComponent('Landing/head.php'); ?>

<body class="overflow-x-hidden">

  <?php $includeComponent('Landing/navigation.php'); ?>

  <?php $includeComponent('Landing/sections/hero.php'); ?>

  <?php $includeComponent('Landing/sections/about.php'); ?>

  <?php $includeComponent('Landing/sections/vision_mission.php'); ?>

  <?php $includeComponent('Landing/sections/news.php'); ?>

  <?php $includeComponent('Landing/sections/event_stylists.php'); ?>

  <?php $includeComponent('Landing/sections/caterings.php'); ?>

  <?php $includeComponent('Landing/sections/brochure.php'); ?>

  <?php $includeComponent('Landing/sections/features.php'); ?>


  <?php $includeComponent('Landing/sections/contact.php'); ?>

  <?php $includeComponent('Landing/footer.php'); ?>

  <?php

  date_default_timezone_set('Asia/Manila');
  $v = time() . '_' . rand(1000, 9999);
  ?>
  <script>console.log('🔄 Cache bust version: <?php echo $v; ?>');</script>
  <script src="assets/js/page-state-manager.js?v=<?php echo $v; ?>"></script>
  <script src="Components/Landing/main.js?v=<?php echo $v; ?>"></script>
  <script src="Components/Landing/auth.js?v=<?php echo $v; ?>"></script>

  <script src="Components/Landing/verify-components.js?v=<?php echo $v; ?>"></script>

  <?php $includeComponent('Popup/ConfirmPopup.php'); ?>
  <?php $includeComponent('Popup/ErrorPopup.php'); ?>
  <?php $includeComponent('Popup/LoadingPopup.php'); ?>
  <?php $includeComponent('Popup/SuccessPopup.php'); ?>

</body>

</html>