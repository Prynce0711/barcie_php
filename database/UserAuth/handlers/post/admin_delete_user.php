<?php
/* ---------------------------
   ADMIN: delete user
   --------------------------- */
if ($action === 'admin_delete_user') {
    if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        // Return JSON for AJAX clients; for normal requests redirect with session message
        handleResponse('Access denied. Admin login required.', false, '../dashboard.php');
    }

    $userId = (int) ($_POST['user_id'] ?? 0);

    if (isset($_SESSION['admin_id']) && $userId === (int) $_SESSION['admin_id']) {
        $_SESSION['msg'] = "You cannot delete your own account.";
        redirect('../dashboard.php');
    }

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();
    $_SESSION['msg'] = "User deleted.";
    redirect('../dashboard.php');
}


