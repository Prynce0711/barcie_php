<?php
// Simple PHPMailer test page - prints SMTP debug to browser and appends a masked copy to logs/email_debug.log
// Usage: open this page in your browser, enter a recipient email and click Send Test

require __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/../database/mail_config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0777, true);
}
$debugLog = $logDir . '/email_debug.log';

function mask_password($text, $password) {
    if (empty($password)) return $text;
    return str_replace($password, str_repeat('*', 8), $text);
}

// Simple HTML form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to = trim($_POST['recipient'] ?? '');
} else {
    $to = trim($_GET['recipient'] ?? '');
}

?>
<!doctype html>
<html><head><meta charset="utf-8"><title>PHPMailer Test Send</title></head><body>
<h2>PHPMailer Test Send</h2>
<form method="post">
  <label>Recipient email: <input type="email" name="recipient" required value="<?php echo htmlspecialchars($to); ?>" style="width:320px"></label>
  <button type="submit">Send Test</button>
</form>
<?php
if (!empty($to)) {
    echo '<h3>Attempting to send to ' . htmlspecialchars($to) . '</h3>';

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = $config['host'] ?? 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'] ?? '';
        $mail->Password = $config['password'] ?? '';
        $mail->SMTPSecure = $config['secure'] ?? 'tls';
        $mail->Port = (int)($config['port'] ?? 587);

        // Debug output to browser and to log (masked)
        $mail->SMTPDebug = 2;
        $mail->Debugoutput = function($str, $level) use ($debugLog, $config) {
            // Print to browser (HTML-safe)
            echo '<pre style="background:#111;color:#0f0;padding:8px;border-radius:4px;">' . htmlspecialchars($str) . '</pre>';
            // Mask password and append to debug log
            $out = $str;
            if (!empty($config['password'])) {
                $out = str_replace($config['password'], str_repeat('*', 8), $out);
            }
            @file_put_contents($debugLog, date('[Y-m-d H:i:s] ') . $out . PHP_EOL, FILE_APPEND);
        };

        $mail->setFrom($config['from_email'] ?? $config['username'], $config['from_name'] ?? 'BarCIE');
        $mail->addAddress($to);
        $mail->Subject = 'PHPMailer test from BarCIE';
        $mail->Body = '<p>This is a test message. If you received this, SMTP is working.</p>';
        $mail->AltBody = 'This is a test message.';

        $sent = $mail->send();
        if ($sent) {
            echo '<p style="color:green;font-weight:bold;">Mail sent successfully (PHPMailer reported success).</p>';
        } else {
            echo '<p style="color:orange;font-weight:bold;">Mail send returned false - check debug above.</p>';
        }
    } catch (Exception $e) {
        echo '<p style="color:red;font-weight:bold;">PHPMailer exception: ' . htmlspecialchars($e->getMessage()) . '</p>';
        @file_put_contents($debugLog, date('[Y-m-d H:i:s] ') . 'PHPMailer exception: ' . mask_password($e->getMessage(), $config['password'] ?? '') . PHP_EOL, FILE_APPEND);
    }
}

echo '<p>Log file: ' . htmlspecialchars($debugLog) . '</p>';
echo '<p>Note: If you see authentication failures, create a Gmail App Password and set it as SMTP_PASSWORD in your .env.</p>';

?>
</body></html>
