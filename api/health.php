<?php
require __DIR__ . '/bootstrap.php';

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
