<?php
// Ensure any remaining requests get a JSON error instead of plain text.
if (!headers_sent()) {
    header('Content-Type: application/json');
}
echo json_encode(['success' => false, 'error' => 'Invalid request.']);
exit;
