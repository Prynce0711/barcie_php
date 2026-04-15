<?php
/**
 * Heartbeat API - Updates admin online status
 * Called every 30 seconds by JavaScript to keep admin marked as online
 * 
 * @package BarCIE
 * @version 1.0.0
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../database/db_connect.php';
require_once __DIR__ . '/../components/Login/remember_me.php';

if ((empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) && isset($conn) && $conn instanceof mysqli) {
    remember_me_restore_session($conn);
}

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$admin_id = $_SESSION['admin_id'] ?? 0;
$response = ['success' => false];

try {
    // Check if last_activity column exists (migration has been run)
    $check_column = $conn->query("SHOW COLUMNS FROM admins LIKE 'last_activity'");
    
    if ($check_column && $check_column->num_rows > 0) {
        // Update last_activity timestamp
        $stmt = $conn->prepare("UPDATE admins SET last_activity = NOW() WHERE id = ?");
        $stmt->bind_param("i", $admin_id);
        
        if ($stmt->execute()) {
            // Get current online admins count (active in last 5 minutes)
            $result = $conn->query("
                SELECT COUNT(*) as online_count 
                FROM admins 
                WHERE last_activity >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
            ");
            
            $online_count = $result->fetch_assoc()['online_count'] ?? 0;
            
            $response = [
                'success' => true,
                'timestamp' => date('Y-m-d H:i:s'),
                'online_count' => $online_count,
                'admin_id' => $admin_id
            ];
        }
        
        $stmt->close();
    } else {
        // Migration not run yet - return success but with note
        $response = [
            'success' => true,
            'timestamp' => date('Y-m-d H:i:s'),
            'online_count' => 0,
            'note' => 'Migration pending - online status disabled'
        ];
    }
    
} catch (Exception $e) {
    error_log("Heartbeat error: " . $e->getMessage());
    $response = [
        'success' => false,
        'message' => 'Error updating heartbeat',
        'error' => $e->getMessage()
    ];
    $response = ['success' => false, 'message' => 'Error updating activity'];
}

$conn->close();
echo json_encode($response);
