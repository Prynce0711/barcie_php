<?php
/* ---------------------------
   POST actions
   --------------------------- */
$action = $_POST['action'] ?? '';


// SECURITY: Disable legacy guest user login/signup via this endpoint.
// If a POST contains a 'password' field it is likely a login/signup attempt from
// the old guest auth UI. We intentionally block these requests and return a
// clear JSON response so external callers know guest accounts are disabled.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['password'])) {
    // Clear any output buffer
    while (ob_get_level())
        ob_end_clean();
    header('Content-Type: application/json');
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'error' => 'Guest accounts disabled',
        'message' => 'User login/signup is no longer supported. Please contact the administrator for access.'
    ]);
    $conn->close();
    exit;
}




