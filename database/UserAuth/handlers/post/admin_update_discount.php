<?php
/* ---------------------------
   ADMIN: update discount status (DEPRECATED - Auto-approval enabled)
   --------------------------- */
if ($action === 'admin_update_discount') {
    error_log("WARNING: admin_update_discount action called but discounts are now automatically approved on upload");

    // Return appropriate response
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Manual discount approval is no longer available. Discounts are automatically approved when ID proof is uploaded.'
        ]);
        exit;
    }

    $_SESSION['msg'] = "Discount approvals are now automatic. No manual action needed.";
    redirect('../dashboard.php');
}


