<?php
/**
 * Quick Health Check for Items System
 * Access: http://localhost/barcie_php/health_check_items.php
 */

header('Content-Type: application/json');
require_once __DIR__ . '/src/database/db_connect.php';

$health = [
    'status' => 'ok',
    'timestamp' => date('Y-m-d H:i:s'),
    'checks' => []
];

try {
    // Check 1: Database connection
    if ($conn->ping()) {
        $health['checks']['database'] = [
            'status' => 'ok',
            'message' => 'Database connection successful'
        ];
    } else {
        throw new Exception('Database connection failed');
    }
    
    // Check 2: Items table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'items'");
    if ($table_check->num_rows > 0) {
        $health['checks']['items_table'] = [
            'status' => 'ok',
            'message' => 'Items table exists'
        ];
    } else {
        $health['checks']['items_table'] = [
            'status' => 'error',
            'message' => 'Items table does not exist'
        ];
        $health['status'] = 'error';
    }
    
    // Check 3: Required columns
    $required_columns = ['id', 'name', 'item_type', 'capacity', 'price', 'image', 'room_status', 'created_at'];
    $existing_columns = [];
    
    $columns_result = $conn->query("DESCRIBE items");
    while ($col = $columns_result->fetch_assoc()) {
        $existing_columns[] = $col['Field'];
    }
    
    $missing_columns = array_diff($required_columns, $existing_columns);
    
    if (empty($missing_columns)) {
        $health['checks']['table_structure'] = [
            'status' => 'ok',
            'message' => 'All required columns exist',
            'columns' => $existing_columns
        ];
    } else {
        $health['checks']['table_structure'] = [
            'status' => 'warning',
            'message' => 'Some columns are missing',
            'missing' => $missing_columns,
            'existing' => $existing_columns
        ];
        $health['status'] = 'warning';
    }
    
    // Check 4: Items count
    $count_result = $conn->query("SELECT COUNT(*) as count FROM items");
    $count = $count_result->fetch_assoc()['count'];
    
    $health['checks']['items_count'] = [
        'status' => $count > 0 ? 'ok' : 'warning',
        'message' => "$count items in database",
        'count' => (int)$count
    ];
    
    // Check 5: Uploads directory
    $uploads_dir = __DIR__ . '/uploads';
    
    if (is_dir($uploads_dir)) {
        if (is_writable($uploads_dir)) {
            $files = array_diff(scandir($uploads_dir), ['.', '..']);
            $health['checks']['uploads_directory'] = [
                'status' => 'ok',
                'message' => 'Uploads directory exists and is writable',
                'path' => $uploads_dir,
                'files_count' => count($files)
            ];
        } else {
            $health['checks']['uploads_directory'] = [
                'status' => 'error',
                'message' => 'Uploads directory exists but is not writable',
                'path' => $uploads_dir
            ];
            $health['status'] = 'error';
        }
    } else {
        $health['checks']['uploads_directory'] = [
            'status' => 'warning',
            'message' => 'Uploads directory does not exist',
            'path' => $uploads_dir,
            'action' => 'Will be created automatically on first upload'
        ];
    }
    
    // Check 6: Sample items with images
    $image_check = $conn->query("SELECT COUNT(*) as count FROM items WHERE image IS NOT NULL AND image != ''");
    $images_count = $image_check->fetch_assoc()['count'];
    
    $health['checks']['items_with_images'] = [
        'status' => 'ok',
        'message' => "$images_count items have images",
        'count' => (int)$images_count
    ];
    
    // Check 7: API endpoint accessible
    $api_file = __DIR__ . '/api/items.php';
    if (file_exists($api_file)) {
        $health['checks']['api_endpoint'] = [
            'status' => 'ok',
            'message' => 'API endpoint file exists',
            'path' => 'api/items.php'
        ];
    } else {
        $health['checks']['api_endpoint'] = [
            'status' => 'error',
            'message' => 'API endpoint file not found',
            'path' => $api_file
        ];
        $health['status'] = 'error';
    }
    
    // Overall summary
    $health['summary'] = [
        'total_items' => (int)$count,
        'items_with_images' => (int)$images_count,
        'items_without_images' => (int)($count - $images_count),
        'uploads_directory_status' => is_dir($uploads_dir) ? 'exists' : 'missing'
    ];
    
} catch (Exception $e) {
    $health['status'] = 'error';
    $health['error'] = $e->getMessage();
}

// Return JSON response
echo json_encode($health, JSON_PRETTY_PRINT);

$conn->close();
?>
