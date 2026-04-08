<?php
// Resolve project root for environments where the app may live in a subfolder.
$documentRoot = isset($_SERVER['DOCUMENT_ROOT'])
  ? rtrim((string) $_SERVER['DOCUMENT_ROOT'], DIRECTORY_SEPARATOR)
  : '';

$candidateRoots = array_filter(array_unique([
  __DIR__,
  realpath(__DIR__) ?: '',
  $documentRoot,
  getcwd() ?: '',
  dirname(__DIR__),
  $documentRoot !== '' ? $documentRoot . DIRECTORY_SEPARATOR . 'barcie_php' : '',
]));

$projectRoot = '';
foreach ($candidateRoots as $candidateRoot) {
  if (is_dir($candidateRoot . DIRECTORY_SEPARATOR . 'Components')) {
    $projectRoot = $candidateRoot;
    break;
  }

  // If the app is deployed as a child folder under the web root, detect it automatically.
  if (is_dir($candidateRoot)) {
    foreach ((glob($candidateRoot . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) ?: []) as $childDir) {
      if (is_dir($childDir . DIRECTORY_SEPARATOR . 'Components')) {
        $projectRoot = $childDir;
        break 2;
      }
    }
  }
}

if ($projectRoot === '') {
  $projectRoot = __DIR__;
}

$componentRoot = $projectRoot . DIRECTORY_SEPARATOR . 'Components';
$includeComponent = static function (string $relativePath) use ($componentRoot): void {
  $normalizedRelativePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($relativePath, '/\\'));
  $fullPath = $componentRoot . DIRECTORY_SEPARATOR . $normalizedRelativePath;

  if (!is_file($fullPath)) {
    trigger_error('Missing component include: ' . $fullPath, E_USER_WARNING);
    return;
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