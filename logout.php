<?php
session_start();
require_once __DIR__ . '/database/db_connect.php';

// Clear last_activity in database before destroying session
if (isset($_SESSION['admin_id'])) {
    $admin_id = $_SESSION['admin_id'];
    
    // Set last_activity to NULL so user shows as offline immediately
    $stmt = $conn->prepare("UPDATE admins SET last_activity = NULL WHERE id = ?");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $stmt->close();
}

// Destroy all session data
$_SESSION = array();

// Delete session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Redirect to landing page
header("Location: index.php");
exit;
