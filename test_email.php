<?php
// Simple email test script
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Email Configuration Test</h2>";
echo "<pre>";

// Check if autoloader exists
$autoload_path = __DIR__ . '/vendor/autoload.php';
echo "1. Checking autoloader...\n";
if (file_exists($autoload_path)) {
    echo "   ✓ Autoloader found at: $autoload_path\n";
    require $autoload_path;
} else {
    echo "   ✗ Autoloader NOT found at: $autoload_path\n";
    echo "   Run: composer install\n";
    exit;
}

// Check if mail config exists
$config_path = __DIR__ . '/database/mail_config.php';
echo "\n2. Checking mail config...\n";
if (file_exists($config_path)) {
    echo "   ✓ Config found at: $config_path\n";
    $config = require $config_path;
    echo "   Host: " . $config['host'] . "\n";
    echo "   Username: " . $config['username'] . "\n";
    echo "   Port: " . $config['port'] . "\n";
    echo "   Secure: " . $config['secure'] . "\n";
} else {
    echo "   ✗ Config NOT found at: $config_path\n";
    exit;
}

// Check PHPMailer class
echo "\n3. Checking PHPMailer...\n";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    echo "   ✓ PHPMailer class loaded\n";
} else {
    echo "   ✗ PHPMailer class NOT loaded\n";
    exit;
}

// Test email sending
echo "\n4. Testing email send...\n";
echo "   Enter your test email address below:\n\n";

if (isset($_GET['email']) && !empty($_GET['email'])) {
    $test_email = $_GET['email'];
    echo "   Sending test email to: $test_email\n\n";
    
    try {
        $mail = new PHPMailer(true);
        
        // Enable verbose debug output
        $mail->SMTPDebug = 2;
        $mail->Debugoutput = 'html';
        
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        $mail->SMTPSecure = $config['secure'];
        $mail->Port = $config['port'];
        
        // SSL/TLS options for local development
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addAddress($test_email);
        $mail->Subject = 'Test Email from BarCIE System';
        $mail->Body = '<h2>Test Email</h2><p>If you received this, your email configuration is working!</p>';
        $mail->isHTML(true);
        
        if ($mail->send()) {
            echo "\n\n   ✓✓✓ EMAIL SENT SUCCESSFULLY! ✓✓✓\n";
            echo "   Check your inbox (and spam folder) at: $test_email\n";
        }
    } catch (Exception $e) {
        echo "\n\n   ✗✗✗ EMAIL FAILED ✗✗✗\n";
        echo "   Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "\n";
    echo "================================================\n";
    echo "To test email, visit:\n";
    echo "http://localhost/barcie_php/test_email.php?email=YOUR_EMAIL@gmail.com\n";
    echo "================================================\n";
}

echo "\n5. PHP Error Log Location:\n";
echo "   " . ini_get('error_log') . "\n";
echo "   (Check this file for detailed email errors)\n";

echo "</pre>";
?>
