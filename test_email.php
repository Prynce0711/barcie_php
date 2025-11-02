<?php
/**
 * Email Configuration Test Script
 * This script tests if the .env file is properly loaded and displays the email configuration
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Load autoload early so we can load .env and protect the page
$autoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
    // Load .env if available
    if (class_exists(\Dotenv\Dotenv::class)) {
        try {
            $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
            $dotenv->safeLoad();
        } catch (Exception $e) {
            // ignore
        }
    }
}

// Protect the test page with a password stored in .env (TEST_PAGE_PASSWORD)
$required = getenv('TEST_PAGE_PASSWORD') ?: ($_ENV['TEST_PAGE_PASSWORD'] ?? null);
if (!$required) {
    // If no password is set, require the developer to set one and stop
    http_response_code(500);
    echo "<h2>Security error</h2><p>No TEST_PAGE_PASSWORD set in .env. Please set one before testing on a live server.</p>";
    exit;
}

// Check authentication
if (empty($_SESSION['authenticated'])) {
    // Handle login attempt
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
        $provided = $_POST['password'];
        if (function_exists('hash_equals')) {
            $ok = hash_equals($required, $provided);
        } else {
            $ok = ($required === $provided);
        }
        if ($ok) {
            $_SESSION['authenticated'] = true;
            // reload to avoid repost
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $error = 'Incorrect password';
        }
    }

    // Show login form
    echo "<!doctype html><html><head><meta charset=\"utf-8\"><title>Login</title></head><body style=\"font-family:Segoe UI,Arial,sans-serif;\">";
    echo "<h2>Protected test page</h2>";
    if (!empty($error)) {
        echo "<p style='color:red;'>{$error}</p>";
    }
    echo "<form method=\"post\"><label>Password: <input type=\"password\" name=\"password\" autofocus></label> <button type=\"submit\">Enter</button></form>";
    echo "<p>Note: the password is stored in your local <code>.env</code> file as <code>TEST_PAGE_PASSWORD</code>. Change it before using on a live server.</p>";
    echo "</body></html>";
    exit;
}

// If we reach here the user is authenticated — continue and show the test page
echo "<h2>Email Configuration Test</h2>";
echo "<hr>";

echo "<p><strong>Autoload path:</strong> " . $autoload . "</p>";
echo "<p><strong>Autoload exists:</strong> " . (file_exists($autoload) ? 'Yes' : 'No') . "</p>";

// Show whether Dotenv class exists and .env was loaded
echo "<p><strong>Dotenv class exists:</strong> " . (class_exists(\Dotenv\Dotenv::class) ? 'Yes' : 'No') . "</p>";
echo "<p><strong>.env loaded:</strong> " . (getenv('SMTP_HOST') || ($_ENV['SMTP_HOST'] ?? false) ? 'Yes' : 'No') . "</p>";

echo "<hr>";
echo "<h3>Environment Variables:</h3>";
echo "<pre>";
echo "SMTP_HOST: " . (getenv('SMTP_HOST') ?: $_ENV['SMTP_HOST'] ?? 'NOT SET') . "\n";
echo "SMTP_USERNAME: " . (getenv('SMTP_USERNAME') ?: $_ENV['SMTP_USERNAME'] ?? 'NOT SET') . "\n";
echo "SMTP_PASSWORD: " . (getenv('SMTP_PASSWORD') ? str_repeat('*', strlen(getenv('SMTP_PASSWORD'))) : ($_ENV['SMTP_PASSWORD'] ?? 'NOT SET')) . "\n";
echo "SMTP_PORT: " . (getenv('SMTP_PORT') ?: $_ENV['SMTP_PORT'] ?? 'NOT SET') . "\n";
echo "SMTP_SECURE: " . (getenv('SMTP_SECURE') ?: $_ENV['SMTP_SECURE'] ?? 'NOT SET') . "\n";
echo "FROM_EMAIL: " . (getenv('FROM_EMAIL') ?: $_ENV['FROM_EMAIL'] ?? 'NOT SET') . "\n";
echo "FROM_NAME: " . (getenv('FROM_NAME') ?: $_ENV['FROM_NAME'] ?? 'NOT SET') . "\n";
echo "</pre>";

echo "<hr>";
echo "<h3>Mail Config File Test:</h3>";
$config_path = __DIR__ . '/database/mail_config.php';
echo "<p><strong>Config path:</strong> " . $config_path . "</p>";
echo "<p><strong>Config exists:</strong> " . (file_exists($config_path) ? 'Yes' : 'No') . "</p>";

if (file_exists($config_path)) {
    try {
        $config = require $config_path;
        echo "<p><strong>Config loaded:</strong> Yes</p>";
        echo "<pre>";
        echo "Host: " . $config['host'] . "\n";
        echo "Username: " . $config['username'] . "\n";
        echo "Password: " . str_repeat('*', strlen($config['password'])) . "\n";
        echo "Secure: " . $config['secure'] . "\n";
        echo "Port: " . $config['port'] . "\n";
        echo "From Email: " . $config['from_email'] . "\n";
        echo "From Name: " . $config['from_name'] . "\n";
        echo "</pre>";
    } catch (Exception $e) {
        echo "<p><strong>Config loaded:</strong> No - " . $e->getMessage() . "</p>";
    }
}
?>
