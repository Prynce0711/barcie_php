<?php
// JSON API to send a test email. Called by debug_live.php via fetch().
header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/../database/mail_config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0777, true);
}
$debugLog = $logDir . '/email_debug.log';

function append_log($text, $password = '') {
    global $debugLog;
    if (!empty($password)) {
        $text = str_replace($password, str_repeat('*', 8), $text);
    }
    @file_put_contents($debugLog, date('[Y-m-d H:i:s] ') . $text . PHP_EOL, FILE_APPEND);
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON payload']);
    exit;
}

$to = trim($data['email'] ?? '');
if (empty($to) || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid or missing recipient email']);
    exit;
}

$response = ['success' => false, 'message' => 'Unknown error'];

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = $config['host'] ?? 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = $config['username'] ?? '';
    $mail->Password = $config['password'] ?? '';
    $mail->SMTPSecure = $config['secure'] ?? 'tls';
    $mail->Port = (int)($config['port'] ?? 587);

    // Minimal debug: capture messages to log but don't echo to client
    $mail->SMTPDebug = 0;
    $mail->Debugoutput = function($str, $level) use ($config) {
        append_log($str, $config['password'] ?? '');
    };

    $mail->setFrom($config['from_email'] ?? ($config['username'] ?? ''), $config['from_name'] ?? 'BarCIE');
    $mail->addAddress($to);
    $mail->Subject = 'PHPMailer test from BarCIE';
    $mail->Body = '<p>This is a test message. If you received this, SMTP is working.</p>';
    $mail->AltBody = 'This is a test message.';

    $sent = $mail->send();
    if ($sent) {
        $response = ['success' => true, 'message' => 'Mail sent successfully (PHPMailer reported success).'];
    } else {
        $response = ['success' => false, 'message' => 'Mail send returned false - check debug log.'];
    }
} catch (Exception $e) {
    $msg = 'PHPMailer exception: ' . $e->getMessage();
    append_log($msg, $config['password'] ?? '');
    $response = ['success' => false, 'message' => 'PHPMailer exception: ' . $e->getMessage()];
}

echo json_encode($response);

?>
