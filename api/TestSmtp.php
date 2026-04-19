<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed. Use POST.', 405, ['allowed_method' => 'POST']);
}

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    json_error('Not authenticated. Please log in as admin first.', 401);
}

$payload = [];
$contentType = (string) ($_SERVER['CONTENT_TYPE'] ?? '');

if (stripos($contentType, 'application/json') !== false) {
    $raw = file_get_contents('php://input');
    if (is_string($raw) && trim($raw) !== '') {
        $decoded = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            json_error('Invalid JSON payload.', 400);
        }
        $payload = $decoded;
    }
}

if ($payload === [] && !empty($_POST) && is_array($_POST)) {
    $payload = $_POST;
}

$to = trim((string) ($payload['to'] ?? ''));
$subject = trim((string) ($payload['subject'] ?? 'BarCIE live SMTP test'));
$message = trim((string) ($payload['message'] ?? 'If you received this email, SMTP is working on the live server.'));

if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
    json_error('A valid recipient email is required in "to".', 422);
}

if ($subject === '') {
    $subject = 'BarCIE live SMTP test';
}

if ($message === '') {
    $message = 'If you received this email, SMTP is working on the live server.';
}

require_once __DIR__ . '/../Components/Email/smtp_mailer.php';

$safeMessage = nl2br(htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
$body = '<h2>BarCIE SMTP Test</h2>'
    . '<p>This is an automated SMTP test from the live server.</p>'
    . '<p><strong>Message:</strong><br>' . $safeMessage . '</p>'
    . '<p><strong>Sent at:</strong> ' . date('Y-m-d H:i:s') . '</p>';

$sent = send_smtp_mail($to, $subject, $body, $message);

if (!$sent) {
    json_error('Email send failed. Check server logs and SMTP credentials.', 500);
}

$config = require __DIR__ . '/../database/mail_config.php';

json_ok([
    'message' => 'Test email sent successfully.',
    'to' => $to,
    'subject' => $subject,
    'sent_at' => date('Y-m-d H:i:s'),
    'smtp' => [
        'host' => (string) ($config['host'] ?? ''),
        'port' => (int) ($config['port'] ?? 0),
        'secure' => (string) ($config['secure'] ?? ''),
        'from_email' => (string) ($config['from_email'] ?? ''),
    ],
]);
