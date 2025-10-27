<?php
// Common bootstrap for BarCIE API endpoints

// Start output buffering to prevent stray output
ob_start();

// Error handling: convert fatal errors to JSON
function api_handle_fatal_error() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Fatal Error',
            'message' => $error['message'] ?? 'Unknown error',
        ]);
        exit;
    }
}
register_shutdown_function('api_handle_fatal_error');

// Basic error reporting; avoid HTML in production output
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// CORS and content type
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Short-circuit OPTIONS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Helper: JSON response
function json_ok($data = [], $status = 200) {
    while (ob_get_level()) { ob_end_clean(); }
    http_response_code($status);
    echo json_encode(array_merge(['success' => true], $data));
    exit;
}

function json_error($message, $status = 400, $extra = []) {
    while (ob_get_level()) { ob_end_clean(); }
    http_response_code($status);
    echo json_encode(array_merge([
        'success' => false,
        'error' => $message,
    ], $extra));
    exit;
}

// Include DB connection
$db_file = __DIR__ . '/../database/db_connect.php';
if (!file_exists($db_file)) {
    json_error('Database configuration file not found', 500);
}
include $db_file; // defines $conn

if (!isset($conn) || $conn->connect_error) {
    json_error('Database connection failed', 500, [
        'db_error' => isset($conn) ? $conn->connect_error : 'not initialized'
    ]);
}

// Utility: ensure items table exists (lightweight check)
function table_exists($conn, $name) {
    $res = $conn->query("SHOW TABLES LIKE '" . $conn->real_escape_string($name) . "'");
    return $res && $res->num_rows > 0;
}
