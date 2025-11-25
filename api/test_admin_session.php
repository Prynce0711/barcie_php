<?php
session_start();
header('Content-Type: application/json');

// Simple test to check admin session and database
require_once __DIR__ . '/../database/db_connect.php';

$response = [
    'session_active' => isset($_SESSION['admin_logged_in']),
    'admin_logged_in' => $_SESSION['admin_logged_in'] ?? false,
    'admin_id' => $_SESSION['admin_id'] ?? null,
    'admin_username' => $_SESSION['admin_username'] ?? null,
];

// Try to get admins count
try {
    $result = $conn->query("SELECT COUNT(*) as count FROM admins");
    if ($result) {
        $row = $result->fetch_assoc();
        $response['admins_count'] = $row['count'];
        $response['db_connected'] = true;
    } else {
        $response['db_connected'] = false;
        $response['db_error'] = $conn->error;
    }
} catch (Exception $e) {
    $response['db_connected'] = false;
    $response['db_error'] = $e->getMessage();
}

echo json_encode($response, JSON_PRETTY_PRINT);
