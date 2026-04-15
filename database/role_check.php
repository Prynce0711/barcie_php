<?php
// Role check helpers for server-side permission enforcement
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../components/Login/remember_me.php';

if ((empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) && isset($conn) && $conn instanceof mysqli) {
    remember_me_restore_session($conn);
}

function api_require_roles(array $allowed_roles) {
    header('Content-Type: application/json');
    if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized - admin login required']);
        exit;
    }
    $role = $_SESSION['admin_role'] ?? 'staff';
    if (!in_array($role, $allowed_roles, true)) {
        echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
        exit;
    }
}

function page_require_roles(array $allowed_roles, $redirect = '../index.php?view=dashboard', $flash_message = 'Insufficient permissions') {
    if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        $_SESSION['error_message'] = 'Unauthorized - please login';
        header('Location: ' . $redirect);
        exit;
    }
    $role = $_SESSION['admin_role'] ?? 'staff';
    if (!in_array($role, $allowed_roles, true)) {
        $_SESSION['error_message'] = $flash_message;
        header('Location: ' . $redirect);
        exit;
    }
}

?>
