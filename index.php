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

$computeAppBasePath = static function (string $componentRootPath, string $docRoot): string {
  $projectRootPath = rtrim(str_replace('\\', '/', dirname($componentRootPath)), '/');
  $normalizedDocRoot = rtrim(str_replace('\\', '/', $docRoot), '/');

  if ($normalizedDocRoot !== '' && strncasecmp($projectRootPath, $normalizedDocRoot, strlen($normalizedDocRoot)) === 0) {
    $relative = trim(substr($projectRootPath, strlen($normalizedDocRoot)), '/');
    return $relative === '' ? '' : '/' . $relative;
  }

  $scriptName = isset($_SERVER['SCRIPT_NAME']) ? (string) $_SERVER['SCRIPT_NAME'] : '';
  $scriptDirName = trim(str_replace('\\', '/', dirname($scriptName)), '/.');

  return $scriptDirName === '' ? '' : '/' . $scriptDirName;
};

$appBasePath = $computeAppBasePath($componentRoot, $documentRoot);
if (!defined('APP_BASE_PATH')) {
  define('APP_BASE_PATH', $appBasePath);
}

$assetUrl = static function (string $path) use ($appBasePath): string {
  $normalizedPath = ltrim(str_replace('\\', '/', $path), '/');
  return ($appBasePath !== '' ? $appBasePath : '') . '/' . $normalizedPath;
};

$includeComponent = static function (string $relativePath) use ($componentRoot, $resolveCaseInsensitivePath, $assetUrl, $appBasePath): void {
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
<link rel="icon" type="image/jpeg"
  href="<?php echo htmlspecialchars($assetUrl('public/images/imageBg/barcie_logo.jpg'), ENT_QUOTES, 'UTF-8'); ?>">
<link rel="apple-touch-icon"
  href="<?php echo htmlspecialchars($assetUrl('public/images/imageBg/barcie_logo.jpg'), ENT_QUOTES, 'UTF-8'); ?>">


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
  <script
    src="<?php echo htmlspecialchars($assetUrl('assets/js/page-state-manager.js') . '?v=' . $v, ENT_QUOTES, 'UTF-8'); ?>"></script>
  <script
    src="<?php echo htmlspecialchars($assetUrl('Components/Landing/main.js') . '?v=' . $v, ENT_QUOTES, 'UTF-8'); ?>"></script>
  <script
    src="<?php echo htmlspecialchars($assetUrl('Components/Landing/auth.js') . '?v=' . $v, ENT_QUOTES, 'UTF-8'); ?>"></script>

  <script
    src="<?php echo htmlspecialchars($assetUrl('Components/Landing/verify-components.js') . '?v=' . $v, ENT_QUOTES, 'UTF-8'); ?>"></script>

  <?php $includeComponent('Popup/ConfirmPopup.php'); ?>
  <?php $includeComponent('Popup/ErrorPopup.php'); ?>
  <?php $includeComponent('Popup/LoadingPopup.php'); ?>
  <?php $includeComponent('Popup/SuccessPopup.php'); ?>

</body>

</html>