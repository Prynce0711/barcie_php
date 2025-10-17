<?php
/**
 * Simple API Test - No Dependencies
 * Upload this file to your live server and access it to test if PHP is working
 */

// Prevent any HTML output
header('Content-Type: application/json');

// Start output buffering
ob_start();
    
try {
    $result = [
        'success' => true,
        'message' => 'PHP is working correctly',
        'timestamp' => date('Y-m-d H:i:s'),
        'php_version' => phpversion(),
        'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
        'script_filename' => $_SERVER['SCRIPT_FILENAME'] ?? 'Unknown'
    ];
    
    // Test PHP extensions
    $result['extensions'] = [
        'mysqli' => extension_loaded('mysqli'),
        'json' => extension_loaded('json'),
        'mbstring' => extension_loaded('mbstring'),
        'curl' => extension_loaded('curl')
    ];
    
    // Test file system
    $result['file_system'] = [
        'current_directory' => __DIR__,
        'database_folder_exists' => is_dir(__DIR__ . '/database'),
        'db_connect_exists' => file_exists(__DIR__ . '/database/db_connect.php'),
        'user_auth_exists' => file_exists(__DIR__ . '/database/user_auth.php'),
        'writable' => is_writable(__DIR__)
    ];
    
    // Try to connect to database if credentials file exists
    if (file_exists(__DIR__ . '/database/db_connect.php')) {
        try {
            include __DIR__ . '/database/db_connect.php';
            
            if (isset($conn)) {
                if ($conn->connect_error) {
                    $result['database'] = [
                        'status' => 'error',
                        'error' => $conn->connect_error
                    ];
                } else {
                    $result['database'] = [
                        'status' => 'connected',
                        'database_name' => $conn->query("SELECT DATABASE()")->fetch_row()[0] ?? 'Unknown'
                    ];
                    
                    // Test tables
                    $tables = ['items', 'bookings', 'feedback'];
                    $result['database']['tables'] = [];
                    
                    foreach ($tables as $table) {
                        $check = $conn->query("SHOW TABLES LIKE '$table'");
                        if ($check && $check->num_rows > 0) {
                            $count = $conn->query("SELECT COUNT(*) as cnt FROM $table")->fetch_assoc()['cnt'];
                            $result['database']['tables'][$table] = [
                                'exists' => true,
                                'count' => $count
                            ];
                        } else {
                            $result['database']['tables'][$table] = [
                                'exists' => false
                            ];
                        }
                    }
                    
                    $conn->close();
                }
            } else {
                $result['database'] = [
                    'status' => 'error',
                    'error' => 'Connection object not created'
                ];
            }
        } catch (Exception $e) {
            $result['database'] = [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    } else {
        $result['database'] = [
            'status' => 'not_configured',
            'message' => 'db_connect.php not found'
        ];
    }
    
    // Clear output buffer and send JSON
    ob_end_clean();
    echo json_encode($result, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Test failed',
        'message' => $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine()
    ], JSON_PRETTY_PRINT);
}
?>
