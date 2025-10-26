<?php
// Comprehensive Debug endpoint for "Add New Item" form
// Usage:
// - Temporarily point your Add Item form to this file with ?debug=1
//   e.g. <form action="src/components/dashboard/debug_add_item.php?debug=1" method="POST" enctype="multipart/form-data">
// - Submit the form. This script will capture POST and FILES, attempt to move uploaded files
//   to uploads/debug-uploads/, perform image checks, capture environment info, and write a detailed
//   JSON debug report to uploads/debug-logs/. It returns JSON to the browser as well.

session_start();

// Require explicit debug flag to reduce accidental exposure
$debugEnabled = (isset($_GET['debug']) && in_array(strtolower($_GET['debug']), ['1','true','yes'], true));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: text/html; charset=utf-8');
    echo "<h2>Add Item Debug Endpoint</h2>";
    echo "<p>Submit the Add Item form to this endpoint with <code>?debug=1</code>.</p>";
    echo "<pre>&lt;form action=\"src/components/dashboard/debug_add_item.php?debug=1\" method=\"POST\" enctype=\"multipart/form-data\"&gt;...&lt;/form&gt;</pre>";
    echo "<p>Response and a JSON log will be created under <code>/uploads/debug-logs/</code>.</p>";
    exit;
}

if (!$debugEnabled) {
    header('Content-Type: application/json');
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'Debug mode not enabled. Add ?debug=1 to the URL to enable.']);
    exit;
}

// Prepare debug container
header('Content-Type: application/json');
$debug = [];
$debug['timestamp'] = date('c');
$debug['script'] = __FILE__;
$debug['request_uri'] = $_SERVER['REQUEST_URI'] ?? null;
$debug['method'] = $_SERVER['REQUEST_METHOD'];
$debug['remote_addr'] = $_SERVER['REMOTE_ADDR'] ?? null;

// Capture POST and FILES
$debug['post'] = $_POST;
$debug['files_raw'] = $_FILES;

// PHP environment
$debug['php'] = [
    'version' => phpversion(),
    'sapi' => PHP_SAPI,
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'file_uploads' => ini_get('file_uploads'),
    'max_file_uploads' => ini_get('max_file_uploads'),
    'memory_limit' => ini_get('memory_limit'),
    'open_basedir' => ini_get('open_basedir'),
    'disable_functions' => ini_get('disable_functions')
];

// Paths and permissions
$projectRoot = realpath(__DIR__ . '/../../..');
$documentRoot = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', DIRECTORY_SEPARATOR);
$uploadsDir = $projectRoot . DIRECTORY_SEPARATOR . 'uploads';
$debugUploadsDir = $uploadsDir . DIRECTORY_SEPARATOR . 'debug-uploads';
$debugLogsDir = $uploadsDir . DIRECTORY_SEPARATOR . 'debug-logs';

$debug['paths'] = [
    'project_root' => $projectRoot,
    'document_root' => $documentRoot,
    'uploads_dir' => $uploadsDir,
    'debug_uploads_dir' => $debugUploadsDir,
    'debug_logs_dir' => $debugLogsDir
];

// Ensure debug directories exist and are writable
foreach ([$uploadsDir, $debugUploadsDir, $debugLogsDir] as $d) {
    if (!file_exists($d)) {
        @mkdir($d, 0755, true);
    }
}

$debug['dirs'] = [
    'uploads_exists' => is_dir($uploadsDir),
    'uploads_writable' => is_writable($uploadsDir),
    'debug_uploads_exists' => is_dir($debugUploadsDir),
    'debug_uploads_writable' => is_writable($debugUploadsDir),
    'debug_logs_exists' => is_dir($debugLogsDir),
    'debug_logs_writable' => is_writable($debugLogsDir),
    'disk_free_space_bytes' => @disk_free_space($projectRoot)
];

// Helper to attempt moving uploaded file and perform image checks
function process_debug_file($file, $targetDir) {
    $out = [
        'original_name' => $file['name'] ?? null,
        'type' => $file['type'] ?? null,
        'size' => $file['size'] ?? null,
        'error_code' => $file['error'] ?? null,
        'tmp_name' => $file['tmp_name'] ?? null,
        'tmp_exists' => isset($file['tmp_name']) ? file_exists($file['tmp_name']) : false,
        'moved' => false,
        'target' => null,
        'mime_check' => null,
        'image_info' => null,
        'message' => ''
    ];

    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        $out['message'] = 'No uploaded tmp file present or not an uploaded file.';
        return $out;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $safeExt = preg_replace('/[^a-z0-9]/', '', $ext);
    $targetName = time() . '_' . bin2hex(random_bytes(6)) . ($safeExt ? '.' . $safeExt : '');
    $targetPath = $targetDir . DIRECTORY_SEPARATOR . $targetName;

    // getimagesize and finfo
    $imageInfo = @getimagesize($file['tmp_name']);
    if ($imageInfo !== false) {
        $out['image_info'] = [
            'width' => $imageInfo[0], 'height' => $imageInfo[1], 'mime' => $imageInfo['mime'] ?? null
        ];
    } else {
        $out['image_info'] = null;
    }

    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        $out['mime_check'] = $mime;
    }

    if (@move_uploaded_file($file['tmp_name'], $targetPath)) {
        @chmod($targetPath, 0644);
        $out['moved'] = true;
        $out['target'] = str_replace('\\', '/', $targetPath);
        $out['message'] = 'File moved to debug uploads.';
    } else {
        $out['message'] = 'move_uploaded_file failed. Check permissions, open_basedir or SELinux.';
        // Attempt to copy as fallback
        if (@copy($file['tmp_name'], $targetPath)) {
            @chmod($targetPath, 0644);
            $out['moved'] = true;
            $out['target'] = str_replace('\\', '/', $targetPath);
            $out['message'] .= ' Copied via fallback copy().';
        }
    }

    return $out;
}

$debug['processed_files'] = [];
if (!empty($_FILES)) {
    foreach ($_FILES as $key => $finfo) {
        if (is_array($finfo['name'])) {
            $debug['processed_files'][$key] = [];
            for ($i=0;$i<count($finfo['name']);$i++) {
                $single = [
                    'name'=>$finfo['name'][$i],'type'=>$finfo['type'][$i],'tmp_name'=>$finfo['tmp_name'][$i],'error'=>$finfo['error'][$i],'size'=>$finfo['size'][$i]
                ];
                $debug['processed_files'][$key][] = process_debug_file($single, $debugUploadsDir);
            }
        } else {
            $debug['processed_files'][$key] = process_debug_file($finfo, $debugUploadsDir);
        }
    }
}

// Provide a SQL preview (do not execute) for the Add Item insert
$name = $debug['post']['name'] ?? ''; $type = $debug['post']['item_type'] ?? '';
$room_number = $debug['post']['room_number'] ?? null; $description = $debug['post']['description'] ?? null;
$capacity = isset($debug['post']['capacity']) ? intval($debug['post']['capacity']) : 0; $price = isset($debug['post']['price']) ? floatval($debug['post']['price']) : 0.0;
$image_rel = null;
foreach ($debug['processed_files'] as $k => $v) {
    // pick first file moved as the image
    if (is_array($v)) {
        if (isset($v[0]) && !empty($v[0]['target'])) { $image_rel = 'uploads/debug-uploads/' . basename($v[0]['target']); break; }
        if (!empty($v['target'])) { $image_rel = 'uploads/debug-uploads/' . basename($v['target']); break; }
    }
}

$debug['sql_preview'] = sprintf(
    "INSERT INTO items (name,item_type,room_number,description,capacity,price,image,room_status,created_at) VALUES (%s, %s, %s, %s, %d, %0.2f, %s, 'available', NOW())",
    var_export($name, true), var_export($type, true), var_export($room_number, true), var_export($description, true), $capacity, $price, var_export($image_rel, true)
);

// Extra diagnostics: file owner info for uploads dir
if (function_exists('fileowner') && file_exists($uploadsDir)) {
    $uid = @fileowner($uploadsDir);
    $debug['uploads_owner'] = $uid;
    if (function_exists('posix_getpwuid') && $uid !== false) {
        $debug['uploads_owner_info'] = posix_getpwuid($uid);
    }
}

// Write debug log
$logFile = $debugLogsDir . DIRECTORY_SEPARATOR . 'add_item_debug_' . date('Ymd_His') . '.json';
@file_put_contents($logFile, json_encode($debug, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
@chmod($logFile, 0644);

$response = [
    'ok' => true,
    'message' => 'Debug report created',
    'log_file' => str_replace('\\', '/', $logFile),
    'data' => $debug
];

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

// EOF
?>
