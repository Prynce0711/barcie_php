<?php
/**
 * Test Reports API Endpoint
 * Simplified version to test if the API is working
 */

// Set timezone
date_default_timezone_set('Asia/Manila');

require_once '../database/db_connect.php';

header('Content-Type: application/json');

try {
    // Test database connection
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception('Database connection failed: ' . ($conn->connect_error ?? 'Connection not established'));
    }
    
    // Test simple query
    $result = $conn->query("SELECT COUNT(*) as count FROM bookings");
    if (!$result) {
        throw new Exception('Query failed: ' . $conn->error);
    }
    
    $row = $result->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'message' => 'API is working',
        'total_bookings' => $row['count'],
        'timezone' => date_default_timezone_get(),
        'current_time' => date('Y-m-d H:i:s'),
        'mysql_timezone' => $conn->query("SELECT @@session.time_zone as tz")->fetch_assoc()['tz']
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => __FILE__,
        'line' => $e->getLine()
    ]);
}
