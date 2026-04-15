<?php
declare(strict_types=1);

/**
 * API front controller.
 *
 * Usage:
 *   /api/index.php?endpoint=items
 *   /api/index.php?endpoint=GetBookingDetails
 */

/**
 * Normalize endpoint identifiers so different naming styles resolve to the same key.
 */
function normalize_api_endpoint_key(string $value): string
{
	return preg_replace('/[^a-z0-9]+/', '', strtolower(trim($value))) ?? '';
}

/**
 * Emit a JSON error response and terminate execution.
 *
 * @param array<string, mixed> $extra
 */
function api_dispatch_error(int $status, string $message, array $extra = []): void
{
	if (!headers_sent()) {
		header('Content-Type: application/json');
	}
	http_response_code($status);

	$payload = array_merge([
		'success' => false,
		'error' => $message,
	], $extra);

	echo json_encode($payload, JSON_UNESCAPED_SLASHES);
	exit;
}

$apiDirectory = __DIR__;
$routeMap = [];

foreach (glob($apiDirectory . DIRECTORY_SEPARATOR . '*.php') ?: [] as $filePath) {
	$fileName = basename($filePath);
	$baseName = pathinfo($fileName, PATHINFO_FILENAME);
	$normalizedKey = normalize_api_endpoint_key($baseName);

	// Index is this front controller, and Bootstrap is an internal include helper.
	if ($normalizedKey === 'index' || $normalizedKey === 'bootstrap' || $normalizedKey === '') {
		continue;
	}

	if (!isset($routeMap[$normalizedKey])) {
		$routeMap[$normalizedKey] = $fileName;
	}
}

if ($routeMap === []) {
	api_dispatch_error(500, 'No API endpoints are available.');
}

$endpointInput = (string)($_GET['endpoint'] ?? $_GET['entry'] ?? '');

if ($endpointInput === '' && isset($_SERVER['PATH_INFO'])) {
	$endpointInput = trim((string)$_SERVER['PATH_INFO'], '/');
}

$endpointKey = normalize_api_endpoint_key($endpointInput);

if ($endpointKey === '') {
	$availableEndpoints = array_map(
		static fn(string $name): string => pathinfo($name, PATHINFO_FILENAME),
		array_values($routeMap)
	);
	sort($availableEndpoints);

	api_dispatch_error(400, 'Missing endpoint parameter.', [
		'usage' => '/api/index.php?endpoint=<name>',
		'available_endpoints' => $availableEndpoints,
	]);
}

if (!isset($routeMap[$endpointKey])) {
	api_dispatch_error(404, 'Unknown endpoint.', [
		'requested' => $endpointInput,
	]);
}

$targetFile = $apiDirectory . DIRECTORY_SEPARATOR . $routeMap[$endpointKey];

if (!is_file($targetFile)) {
	api_dispatch_error(500, 'Endpoint file is missing.', [
		'endpoint' => $routeMap[$endpointKey],
	]);
}

$_SERVER['API_ENDPOINT'] = pathinfo($routeMap[$endpointKey], PATHINFO_FILENAME);

require $targetFile;
