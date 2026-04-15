<?php
declare(strict_types=1);

$documentRoot = isset($_SERVER['DOCUMENT_ROOT'])
	? rtrim((string) $_SERVER['DOCUMENT_ROOT'], DIRECTORY_SEPARATOR)
	: '';

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
	define('APP_BASE_PATH', $computeAppBasePath(__DIR__, $documentRoot));
}

$requestedView = strtolower(trim((string) ($_GET['view'] ?? 'landing')));

$viewAliases = [
	'' => 'landing',
	'index' => 'landing',
	'home' => 'landing',
	'landing' => 'landing',
	'admin' => 'admin',
	'login' => 'admin',
	'dashboard' => 'dashboard',
	'guest' => 'guest',
	'logout' => 'logout',
];

$resolvedView = $viewAliases[$requestedView] ?? 'landing';

$routes = [
	'landing' => __DIR__ . '/components/landing/index.php',
	'admin' => __DIR__ . '/components/Login/admin.php',
	'dashboard' => __DIR__ . '/components/Admin/index.php',
	'guest' => __DIR__ . '/components/guest/index.php',
	'logout' => __DIR__ . '/components/Login/logout.php',
];

$targetFile = $routes[$resolvedView] ?? $routes['landing'];

if (!is_file($targetFile)) {
	http_response_code(500);
	echo 'Application route is not available.';
	exit;
}

require $targetFile;
