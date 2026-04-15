<?php

declare(strict_types=1);

/**
 * Single HTTP entrypoint for database-layer endpoints.
 *
 * Supported endpoints:
 * - admin_login
 * - user_auth
 * - fetch_items
 * - fetch_calendar_data
 */

$endpoint = strtolower(trim((string) ($_GET['endpoint'] ?? $_GET['entry'] ?? '')));

// Lightweight fallback for common requests that omitted `endpoint`.
if ($endpoint === '') {
	if (isset($_REQUEST['action'])) {
		$endpoint = 'user_auth';
	} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['username']) || isset($_POST['password']))) {
		$endpoint = 'admin_login';
	}
}

$routeMap = [
	'admin_login' => __DIR__ . '/admin_login.php',
	'user_auth' => __DIR__ . '/UserAuth/user_auth.php',
	'fetch_items' => __DIR__ . '/fetch_items.php',
	'fetch_calendar_data' => __DIR__ . '/fetch_calendar_data.php',
];

if (!isset($routeMap[$endpoint])) {
	header('Content-Type: application/json');
	http_response_code(400);
	echo json_encode([
		'success' => false,
		'error' => 'Invalid database endpoint.',
		'hint' => 'Use /database/index.php?endpoint=<name>.',
		'available_endpoints' => array_keys($routeMap),
	]);
	exit;
}

$target = $routeMap[$endpoint];
if (!is_file($target)) {
	header('Content-Type: application/json');
	http_response_code(500);
	echo json_encode([
		'success' => false,
		'error' => 'Database endpoint file not found.',
		'endpoint' => $endpoint,
	]);
	exit;
}

require $target;

