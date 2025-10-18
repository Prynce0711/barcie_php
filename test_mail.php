<?php
require __DIR__ . '/vendor/autoload.php';
$config = require __DIR__ . '/database/mail_config.php';

$to = getenv('TEST_MAIL_TO') ?: ($config['username'] ?? '');
if (!$to) {
    http_response_code(500);
    echo "Set TEST_MAIL_TO env or configure username in mail_config.php";
    exit(1);
}

$subject = 'BarCIE test mail from container';
$body = '<p>This is a test email sent from the Docker container at ' . date('c') . '.</p>';

$mail = new PHPMailer\PHPMailer\PHPMailer(true);
$mail->SMTPDebug = 2;
$mail->Debugoutput = function($str, $level) { error_log("PHPMailer: " . $str); };
$mail->isSMTP();
$mail->Host = $config['host'];
$mail->SMTPAuth = true;
$mail->Username = $config['username'];
$mail->Password = $config['password'];
$mail->SMTPSecure = $config['secure'];
$mail->Port = $config['port'];
$mail->SMTPOptions = ['ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]];
$mail->setFrom($config['from_email'], $config['from_name']);
$mail->addAddress($to);
$mail->Subject = $subject;
$mail->Body = $body;
$mail->AltBody = strip_tags($body);
$mail->isHTML(true);

try {
    $ok = $mail->send();
    echo $ok ? "OK\n" : "Failed\n";
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Mailer error: ' . $e->getMessage();
}
