<?php
// Debug endpoint for "Add New Item" form
// USAGE:
// - Temporarily set your add item form's action to this file (relative path)
//   e.g. <form action="src/components/dashboard/debug_add_item.php" method="POST" enctype="multipart/form-data">
// - Submit the form. This script will capture POST and FILES, attempt to move uploaded files
//   to uploads/debug-uploads/, write a debug JSON log to uploads/debug-logs/ and return a JSON response.
// - After debugging, revert the form action back to normal.

session_start();

// Basic security: require a query param ?debug=1 to activate (avoid accidental public use)
$debugEnabled = (isset($_GET['debug']) && ($_GET['debug'] === '1' || $_GET['debug'] === 'true'));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Show a tiny guidance page when accessed via GET
    header('Content-Type: text/html; charset=utf-8');
    echo "<h2>Add Item Debug Endpoint</h2>";
    echo "<p>To use this endpoint, submit your Add Item form to this file with <code>?debug=1</code> appended:</p>";
    echo "<pre>&lt;form action=\"src/components/dashboard/debug_add_item.php?debug=1\" method=\"POST\" enctype=\"multipart/form-data\"&gt;...&lt;/form&gt;</pre>";
    echo "<p>This will return a JSON response with diagnostics and write a debug log to <code>/uploads/debug-logs/</code>.</p>";
    exit;
}

// If debug flag not present, refuse to run to reduce accidental exposure
if (!$debugEnabled) {
    header('Content-Type: application/json');
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'Debug mode not enabled. Add ?debug=1 to the URL to enable.']);
    exit;
}

header('Content-Type: application/json');

$debug = [];
$debug['time'] = date('c');
$debug['script'] = __FILE__;
$debug['request_uri'] = $_SERVER['REQUEST_URI'] ?? null;
$debug['method'] = $_SERVER['REQUEST_METHOD'];
$debug['post'] = $_POST;
$debug['files'] = [];

// PHP upload/post settings
$debug['php'] = [
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'file_uploads' => ini_get('file_uploads'),
    'max_file_uploads' => ini_get('max_file_uploads'),
    'memory_limit' => ini_get('memory_limit')
];

// Paths
$documentRoot = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', DIRECTORY_SEPARATOR);
$projectRoot = realpath(__DIR__ . '/../../..'); // barcie_php/src/components/dashboard -> ../../.. == workspace root
$debug['paths'] = [
    'document_root' => $documentRoot,
    'project_root' => $projectRoot,
    'uploads_dir_expected' => $projectRoot . DIRECTORY_SEPARATOR . 'uploads'
];

// Ensure debug dirs exist
$uploadsDir = $projectRoot . DIRECTORY_SEPARATOR . 'uploads';
$debugUploadsDir = $uploadsDir . DIRECTORY_SEPARATOR . 'debug-uploads';
$debugLogsDir = $uploadsDir . DIRECTORY_SEPARATOR . 'debug-logs';

foreach ([$uploadsDir, $debugUploadsDir, $debugLogsDir] as $d) {
    if (!file_exists($d)) {
        @mkdir($d, 0755, true);
    }
}

$debug['dirs'] = [
    'uploads_exists' => file_exists($uploadsDir) && is_dir($uploadsDir),
    'uploads_writable' => is_writable($uploadsDir),
    'debug_uploads_exists' => file_exists($debugUploadsDir) && is_dir($debugUploadsDir),
    'debug_uploads_writable' => is_writable($debugUploadsDir),
    'debug_logs_exists' => file_exists($debugLogsDir) && is_dir($debugLogsDir),
    'debug_logs_writable' => is_writable($debugLogsDir)
];

// Process uploaded files (if any)
if (!empty($_FILES)) {
    foreach ($_FILES as $key => $fileInfo) {
        // Handle both single and multiple file inputs
        if (is_array($fileInfo['name'])) {
            $count = count($fileInfo['name']);
            for ($i = 0; $i < $count; $i++) {
                $single = [
                    'name' => $fileInfo['name'][$i],
                    'type' => $fileInfo['type'][$i],
                    'tmp_name' => $fileInfo['tmp_name'][$i],
                    'error' => $fileInfo['error'][$i],
                    'size' => $fileInfo['size'][$i]
                ];
                $debug['files'][$key][] = handle_debug_file($single, $debugUploadsDir);
            }
        } else {
            $debug['files'][$key] = handle_debug_file($fileInfo, $debugUploadsDir);
        }
    }
}

// Helper function
function handle_debug_file($file, $debugUploadsDir) {
    $result = [
        'original_name' => $file['name'] ?? null,
        'type' => $file['type'] ?? null,
        'size' => $file['size'] ?? null,
        'error' => $file['error'] ?? null,
        'tmp_name_exists' => isset($file['tmp_name']) ? file_exists($file['tmp_name']) : false,
        'moved' => false,
        'target_path' => null,
        'message' => ''
    ];

    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        $result['message'] = 'No uploaded tmp file present or not an uploaded file.';
        return $result;
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $safeExt = preg_replace('/[^a-zA-Z0-9]/', '', $ext);
    $targetName = time() . '_' . bin2hex(random_bytes(6)) . ($safeExt ? '.' . $safeExt : '');
    $targetPath = $debugUploadsDir . DIRECTORY_SEPARATOR . $targetName;

    if (@move_uploaded_file($file['tmp_name'], $targetPath)) {
        @chmod($targetPath, 0644);
        $result['moved'] = true;
        $result['target_path'] = str_replace('\\', '/', $targetPath);
        $result['message'] = 'File moved to debug uploads.';
    } else {
        $result['message'] = 'move_uploaded_file failed. Check permissions and open_basedir.';
    }

    return $result;
}

// Write debug log
$logFile = $debugLogsDir . DIRECTORY_SEPARATOR . 'add_item_debug_' . date('Ymd_His') . '.json';
@file_put_contents($logFile, json_encode($debug, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
@chmod($logFile, 0644);

// Return debug JSON with path to log
$response = [
    'ok' => true,
    'message' => 'Debug info recorded',
    'log_file' => str_replace('\\', '/', $logFile),
    'data' => $debug
];

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

// End of file
