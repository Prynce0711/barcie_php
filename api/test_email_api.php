<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Use POST.',
    ]);
    exit;
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw ?: '{}', true);

if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON payload.',
    ]);
    exit;
}

$email = trim((string) ($payload['email'] ?? ''));
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Please provide a valid recipient email address.',
    ]);
    exit;
}

$mailConfigPath = __DIR__ . '/../database/mail_config.php';
if (!file_exists($mailConfigPath)) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Mail config file is missing at database/mail_config.php.',
    ]);
    exit;
}

$config = @include $mailConfigPath;
if (!is_array($config)) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Mail config could not be loaded.',
    ]);
    exit;
}

$host = trim((string) ($config['host'] ?? ''));
$username = trim((string) ($config['username'] ?? ''));
$password = trim((string) ($config['password'] ?? ''));
$port = (int) ($config['port'] ?? 0);

$missing = [];
if ($host === '') {
    $missing[] = 'SMTP host';
}
if ($username === '') {
    $missing[] = 'SMTP username';
}
if ($password === '') {
    $missing[] = 'SMTP password';
}
if ($port <= 0) {
    $missing[] = 'SMTP port';
}

if ($missing !== []) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'SMTP config is incomplete: ' . implode(', ', $missing) . '.',
    ]);
    exit;
}

$smtpMailerCandidates = [
    __DIR__ . '/../components/Email/smtp_mailer.php',
    __DIR__ . '/../Components/Email/smtp_mailer.php',
    __DIR__ . '/../Email/smtp_mailer.php',
];

$smtpMailerPath = null;
foreach ($smtpMailerCandidates as $candidate) {
    if (file_exists($candidate)) {
        $smtpMailerPath = $candidate;
        break;
    }
}

if ($smtpMailerPath === null) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'SMTP mailer file is missing. Checked: components/Email/smtp_mailer.php, Components/Email/smtp_mailer.php, Email/smtp_mailer.php.',
    ]);
    exit;
}

require_once $smtpMailerPath;

if (!function_exists('send_smtp_mail')) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'send_smtp_mail function is not available.',
    ]);
    exit;
}

$subject = 'BarCIE Test Email';
$body = '<h2>BarCIE Test Email</h2>'
    . '<p>This is a live SMTP test from debug page.</p>'
    . '<p>Sent at: ' . htmlspecialchars(date('Y-m-d H:i:s'), ENT_QUOTES, 'UTF-8') . '</p>';
$altBody = 'BarCIE Test Email - Sent at ' . date('Y-m-d H:i:s');

$sent = false;
try {
    $sent = send_smtp_mail($email, $subject, $body, $altBody);
} catch (Throwable $e) {
    error_log('test_email_api exception: ' . $e->getMessage());
}

if ($sent) {
    echo json_encode([
        'success' => true,
        'message' => 'Test email sent successfully to ' . $email . '.',
    ]);
    exit;
}

http_response_code(500);
echo json_encode([
    'success' => false,
    'message' => 'SMTP send failed. Check logs/email_debug.log and PHP error log for PHPMailer details.',
]);
