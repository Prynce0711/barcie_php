<?php
$bootstrapPath = __DIR__ . '/Bootstrap.php';
if (!is_file($bootstrapPath)) {
    $entries = @scandir(__DIR__);
    if (is_array($entries)) {
        foreach ($entries as $entry) {
            if (strcasecmp($entry, 'Bootstrap.php') === 0) {
                $bootstrapPath = __DIR__ . '/' . $entry;
                break;
            }
        }
    }
}

if (!is_file($bootstrapPath)) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'API bootstrap file not found',
    ]);
    exit;
}

require $bootstrapPath;

$info = [
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => phpversion(),
    'mysqli_extension' => extension_loaded('mysqli'),
];

// Optional DB check
try {
    $ok = $conn && !$conn->connect_error;
    $info['db_connected'] = $ok;
    if ($ok && table_exists($conn, 'items')) {
        $r = $conn->query('SELECT COUNT(*) AS c FROM items');
        if ($r) { $info['items'] = (int)$r->fetch_assoc()['c']; }
    }
} catch (Throwable $e) {
    $info['db_error'] = $e->getMessage();
}

json_ok(['message' => 'API healthy', 'info' => $info]);
