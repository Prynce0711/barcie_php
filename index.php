<?php
$normalizeDir = static function (?string $path): string {
  if (!is_string($path) || $path === '') {
    return '';
  }

  $trimmed = rtrim($path, DIRECTORY_SEPARATOR);
  $real = realpath($trimmed);

  return $real !== false ? $real : $trimmed;
};

$findComponentDirsIn = static function (string $root): array {
  if (!is_dir($root)) {
    return [];
  }

  $dirs = [];
  $entries = @scandir($root);
  if ($entries === false) {
    return [];
  }

  foreach ($entries as $entry) {
    if ($entry === '.' || $entry === '..') {
      continue;
    }

    if (strcasecmp($entry, 'Components') !== 0) {
      continue;
    }

    $candidate = $root . DIRECTORY_SEPARATOR . $entry;
    if (is_dir($candidate)) {
      $real = realpath($candidate);
      $dirs[] = $real !== false ? $real : $candidate;
    }
  }

  return $dirs;
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

$baseDirs = array_filter(array_unique([
  __DIR__,
  realpath(__DIR__) ?: '',
  $scriptDir,
  $documentRoot,
  $documentRoot !== '' ? dirname($documentRoot) : '',
  getcwd() ?: '',
]));

$candidateRoots = [];
foreach ($baseDirs as $baseDir) {
  $normalized = $normalizeDir($baseDir);
  if ($normalized !== '') {
    $candidateRoots[] = $normalized;
  }
}
$candidateRoots = array_values(array_unique($candidateRoots));

$skipNames = ['.', '..', '.git', 'node_modules', 'vendor'];
$componentRoots = [];

foreach ($candidateRoots as $root) {
  $componentRoots = array_merge($componentRoots, $findComponentDirsIn($root));

  $entries = @scandir($root);
  if ($entries === false) {
    continue;
  }

  foreach ($entries as $entry) {
    if (in_array($entry, $skipNames, true)) {
      continue;
    }

    $child = $root . DIRECTORY_SEPARATOR . $entry;
    if (!is_dir($child) || is_link($child)) {
      continue;
    }

    $componentRoots = array_merge($componentRoots, $findComponentDirsIn($child));
  }
}

$componentRoots = array_values(array_unique(array_filter($componentRoots)));

$includeComponent = static function (string $relativePath) use ($componentRoots, $resolveCaseInsensitivePath): void {
  $normalizedRelativePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($relativePath, '/\\'));

  foreach ($componentRoots as $componentRoot) {
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
  }

  $warningBase = $componentRoots[0] ?? (__DIR__ . DIRECTORY_SEPARATOR . 'Components');
  trigger_error('Missing component include: ' . $warningBase . DIRECTORY_SEPARATOR . $normalizedRelativePath, E_USER_WARNING);
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