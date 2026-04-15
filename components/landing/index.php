<?php
declare(strict_types=1);

$projectRoot = dirname(__DIR__, 2);
$componentsRoot = $projectRoot . DIRECTORY_SEPARATOR . 'components';
$documentRoot = isset($_SERVER['DOCUMENT_ROOT'])
  ? rtrim((string) $_SERVER['DOCUMENT_ROOT'], DIRECTORY_SEPARATOR)
  : '';

$resolveCaseInsensitivePath = static function (string $basePath, string $relativePath): string {
  $current = rtrim($basePath, DIRECTORY_SEPARATOR);
  $segments = explode(
    DIRECTORY_SEPARATOR,
    str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($relativePath, '/\\'))
  );

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

$computeAppBasePath = static function (string $projectRootPath, string $docRoot): string {
  $normalizedProjectRoot = rtrim(str_replace('\\', '/', $projectRootPath), '/');
  $normalizedDocRoot = rtrim(str_replace('\\', '/', $docRoot), '/');

  if (
    $normalizedDocRoot !== '' &&
    strncasecmp($normalizedProjectRoot, $normalizedDocRoot, strlen($normalizedDocRoot)) === 0
  ) {
    $relative = trim(substr($normalizedProjectRoot, strlen($normalizedDocRoot)), '/');
    return $relative === '' ? '' : '/' . $relative;
  }

  $scriptName = isset($_SERVER['SCRIPT_NAME']) ? (string) $_SERVER['SCRIPT_NAME'] : '';
  $scriptDirName = trim(str_replace('\\', '/', dirname($scriptName)), '/.');

  return $scriptDirName === '' ? '' : '/' . $scriptDirName;
};

if (!defined('APP_BASE_PATH')) {
  define('APP_BASE_PATH', $computeAppBasePath($projectRoot, $documentRoot));
}

$assetUrl = static function (string $path): string {
  $normalizedPath = ltrim(str_replace('\\', '/', $path), '/');
  return (APP_BASE_PATH !== '' ? APP_BASE_PATH : '') . '/' . $normalizedPath;
};

$componentAssetPath = static function (string $relativePath) use (
  $componentsRoot,
  $projectRoot,
  $resolveCaseInsensitivePath
): string {
  $normalizedRelativePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($relativePath, '/\\'));
  $directPath = $componentsRoot . DIRECTORY_SEPARATOR . $normalizedRelativePath;

  $resolvedPath = '';
  if (is_file($directPath)) {
    $resolvedPath = $directPath;
  } else {
    $caseResolvedPath = $resolveCaseInsensitivePath($componentsRoot, $normalizedRelativePath);
    if ($caseResolvedPath !== '' && is_file($caseResolvedPath)) {
      $resolvedPath = $caseResolvedPath;
    }
  }

  if ($resolvedPath !== '') {
    $normalizedProjectRoot = rtrim(str_replace('\\', '/', $projectRoot), '/');
    $normalizedResolvedPath = str_replace('\\', '/', $resolvedPath);
    $projectPrefix = $normalizedProjectRoot . '/';

    if (strpos($normalizedResolvedPath, $projectPrefix) === 0) {
      return ltrim(substr($normalizedResolvedPath, strlen($projectPrefix)), '/');
    }
  }

  return 'components/' . ltrim(str_replace('\\', '/', $relativePath), '/');
};

$componentAssetUrl = static function (string $relativePath) use ($assetUrl, $componentAssetPath): string {
  return $assetUrl($componentAssetPath($relativePath));
};

$includeComponent = static function (string $relativePath) use (
  $componentsRoot,
  $resolveCaseInsensitivePath,
  $assetUrl,
  $componentAssetUrl
): void {
  $normalizedRelativePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($relativePath, '/\\'));
  $directPath = $componentsRoot . DIRECTORY_SEPARATOR . $normalizedRelativePath;

  if (is_file($directPath)) {
    include $directPath;
    return;
  }

  $resolvedPath = $resolveCaseInsensitivePath($componentsRoot, $normalizedRelativePath);
  if ($resolvedPath !== '' && is_file($resolvedPath)) {
    include $resolvedPath;
    return;
  }

  trigger_error(
    'Missing component include: components/' . ltrim(str_replace('\\', '/', $relativePath), '/'),
    E_USER_WARNING
  );
};

date_default_timezone_set('Asia/Manila');
$assetVersion = time() . '_' . mt_rand(1000, 9999);
?>

<!doctype html>
<html lang="en">

<?php $includeComponent('landing/head.php'); ?>

<body class="overflow-x-hidden">

  <?php $includeComponent('landing/navigation.php'); ?>

  <?php $includeComponent('landing/sections/hero.php'); ?>

  <?php $includeComponent('landing/sections/about.php'); ?>

  <?php $includeComponent('landing/sections/vision_mission.php'); ?>

  <?php $includeComponent('landing/sections/news.php'); ?>

  <?php $includeComponent('landing/sections/event_stylists.php'); ?>

  <?php $includeComponent('landing/sections/caterings.php'); ?>

  <?php $includeComponent('landing/sections/brochure.php'); ?>

  <?php $includeComponent('landing/sections/features.php'); ?>

  <?php $includeComponent('landing/sections/contact.php'); ?>

  <?php $includeComponent('landing/footer.php'); ?>

  <script>console.log('Cache bust version: <?php echo $assetVersion; ?>');</script>
  <script
    src="<?php echo htmlspecialchars($assetUrl('assets/js/page-state-manager.js') . '?v=' . $assetVersion, ENT_QUOTES, 'UTF-8'); ?>"></script>
  <script
    src="<?php echo htmlspecialchars($componentAssetUrl('landing/main.js') . '?v=' . $assetVersion, ENT_QUOTES, 'UTF-8'); ?>"></script>

  <script
    src="<?php echo htmlspecialchars($componentAssetUrl('landing/verify-components.js') . '?v=' . $assetVersion, ENT_QUOTES, 'UTF-8'); ?>"></script>

  <?php $includeComponent('Popup/ConfirmPopup.php'); ?>
  <?php $includeComponent('Popup/ErrorPopup.php'); ?>
  <?php $includeComponent('Popup/LoadingPopup.php'); ?>
  <?php $includeComponent('Popup/SuccessPopup.php'); ?>

</body>

</html>