<?php
/* ---------------------------
   ADMIN: Approve/Reject Feedback
   --------------------------- */
if ($action === 'approve_feedback' || $action === 'reject_feedback') {
    // Check admin authentication
    if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        if ($is_ajax) {
            echo json_encode(['success' => false, 'error' => 'Access denied. Admin login required.']);
        } else {
            handleResponse('Access denied. Admin login required.', false, '../dashboard.php');
        }
        exit();
    }

    // feedback moderation short-circuited above

    $feedback_id = (int) ($_POST['feedback_id'] ?? 0);

    if ($feedback_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid feedback ID']);
        exit();
    }

    // Moderation disabled — approve/reject logic is not used anymore.
    echo json_encode(['success' => false, 'error' => 'Feedback moderation disabled']);
    exit();
}


