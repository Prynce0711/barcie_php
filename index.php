<?php
$normalizeDir = static function (?string $path): string {
  if (!is_string($path) || $path === '') {
    return '';
  }

  $trimmed = rtrim($path, DIRECTORY_SEPARATOR);
  $real = realpath($trimmed);

  return $real !== false ? $real : $trimmed;
};

$resolveCaseInsensitivePath = static function (string $basePath, string $relativePath): string {
  $current = $basePath;
  $segments = explode(DIRECTORY_SEPARATOR, $relativePath);

  foreach ($segments as $segment) {
    if ($segment === '' || $segment === '.') {
      continue;
    }

    $direct = $current . DIRECTORY_SEPARATOR . $segment;
    if (file_exists($direct)) {
      $current = $direct;
      continue;
    }

    $entries = @scandir($current);
    if ($entries === false) {
      return '';
    }

    $matched = null;
    foreach ($entries as $entry) {
      if (strcasecmp($entry, $segment) === 0) {
        $matched = $entry;
        break;
      }
    }

    if ($matched === null) {
      return '';
    }

    $current = $current . DIRECTORY_SEPARATOR . $matched;
  }

  return $current;
};

$documentRoot = isset($_SERVER['DOCUMENT_ROOT'])
  ? rtrim((string) $_SERVER['DOCUMENT_ROOT'], DIRECTORY_SEPARATOR)
  : '';

$scriptDir = isset($_SERVER['SCRIPT_FILENAME'])
  ? dirname((string) $_SERVER['SCRIPT_FILENAME'])
  : '';

$componentRootCandidates = array_filter(array_unique([
  __DIR__ . DIRECTORY_SEPARATOR . 'Components',
  __DIR__ . DIRECTORY_SEPARATOR . 'components',
  $scriptDir !== '' ? rtrim($scriptDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'Components' : '',
  $scriptDir !== '' ? rtrim($scriptDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'components' : '',
  $documentRoot !== '' ? $documentRoot . DIRECTORY_SEPARATOR . 'Components' : '',
  $documentRoot !== '' ? $documentRoot . DIRECTORY_SEPARATOR . 'components' : '',
  $documentRoot !== '' ? $documentRoot . DIRECTORY_SEPARATOR . 'barcie_php' . DIRECTORY_SEPARATOR . 'Components' : '',
  $documentRoot !== '' ? $documentRoot . DIRECTORY_SEPARATOR . 'barcie_php' . DIRECTORY_SEPARATOR . 'components' : '',
  dirname(__DIR__) . DIRECTORY_SEPARATOR . 'barcie_php' . DIRECTORY_SEPARATOR . 'Components',
  dirname(__DIR__) . DIRECTORY_SEPARATOR . 'barcie_php' . DIRECTORY_SEPARATOR . 'components',
]));

$componentRoot = '';
$fallbackComponentRoot = '';

foreach ($componentRootCandidates as $candidate) {
  $normalized = $normalizeDir($candidate);
  if ($normalized === '' || !is_dir($normalized)) {
    continue;
  }

  if ($fallbackComponentRoot === '') {
    $fallbackComponentRoot = $normalized;
  }

  if (
    is_file($normalized . DIRECTORY_SEPARATOR . 'Landing' . DIRECTORY_SEPARATOR . 'head.php') ||
    is_file($normalized . DIRECTORY_SEPARATOR . 'landing' . DIRECTORY_SEPARATOR . 'head.php')
  ) {
    $componentRoot = $normalized;
    break;
  }
}

if ($componentRoot === '') {
  $componentRoot = $fallbackComponentRoot !== '' ? $fallbackComponentRoot : (__DIR__ . DIRECTORY_SEPARATOR . 'Components');
}

$includeComponent = static function (string $relativePath) use ($componentRoot, $resolveCaseInsensitivePath): void {
  $normalizedRelativePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($relativePath, '/\\'));
  $fullPath = $componentRoot . DIRECTORY_SEPARATOR . $normalizedRelativePath;

  if (is_file($fullPath)) {
    include $fullPath;
    return;
  }

  $resolvedPath = $resolveCaseInsensitivePath($componentRoot, $normalizedRelativePath);
  if ($resolvedPath !== '' && is_file($resolvedPath)) {
    include $resolvedPath;
    return;
  }

  trigger_error('Missing component include: ' . $fullPath, E_USER_WARNING);
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