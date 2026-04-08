<?php
/**
 * Check whether a directory looks like this project root.
 */
$hasLandingHead = static function (string $root): bool {
  $paths = [
    $root . DIRECTORY_SEPARATOR . 'Components' . DIRECTORY_SEPARATOR . 'Landing' . DIRECTORY_SEPARATOR . 'head.php',
    $root . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'landing' . DIRECTORY_SEPARATOR . 'head.php',
    $root . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'Landing' . DIRECTORY_SEPARATOR . 'head.php',
    $root . DIRECTORY_SEPARATOR . 'Components' . DIRECTORY_SEPARATOR . 'landing' . DIRECTORY_SEPARATOR . 'head.php',
  ];

  foreach ($paths as $path) {
    if (is_file($path)) {
      return true;
    }
  }

  return false;
};

/**
 * Breadth-first search for a root folder that contains the landing head component.
 */
$findProjectRoot = static function (array $baseDirs, int $maxDepth = 3) use ($hasLandingHead): string {
  $skipNames = ['.', '..', '.git', 'node_modules', 'vendor'];
  $visited = [];

  foreach ($baseDirs as $baseDir) {
    if (!is_dir($baseDir)) {
      continue;
    }

    $queue = [[$baseDir, 0]];
    while (!empty($queue)) {
      [$currentDir, $depth] = array_shift($queue);

      $real = realpath($currentDir);
      if ($real === false || isset($visited[$real])) {
        continue;
      }
      $visited[$real] = true;

      if ($hasLandingHead($real)) {
        return $real;
      }

      if ($depth >= $maxDepth) {
        continue;
      }

      $entries = @scandir($real);
      if ($entries === false) {
        continue;
      }

      foreach ($entries as $entry) {
        if (in_array($entry, $skipNames, true)) {
          continue;
        }

        $child = $real . DIRECTORY_SEPARATOR . $entry;
        if (is_dir($child) && !is_link($child)) {
          $queue[] = [$child, $depth + 1];
        }
      }
    }
  }

  return '';
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

$projectRoot = $findProjectRoot($baseDirs, 3);
if ($projectRoot === '') {
  $projectRoot = __DIR__;
}

$componentFolder = is_dir($projectRoot . DIRECTORY_SEPARATOR . 'Components') ? 'Components' : 'components';
$componentRoot = $projectRoot . DIRECTORY_SEPARATOR . $componentFolder;

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