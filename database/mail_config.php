<?php
// Load environment variables from .env file (if composer autoload exists)
$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;

    // Load .env if Dotenv is available
    if (class_exists(\Dotenv\Dotenv::class)) {
        $root = dirname(__DIR__);
        try {
            $dotenv = \Dotenv\Dotenv::createImmutable($root);
            $dotenv->safeLoad();
        } catch (Exception $e) {
            error_log("Failed to load .env: " . $e->getMessage());
        }
    }
}

// Helper function to read environment variables with fallback
function env($key, $default = null) {
    $value = getenv($key);
    if ($value === false) {
        $value = $_ENV[$key] ?? $default;
    }
    return $value;
}

// Normalize accepted environment variable names to be forgiving
$smtp_host = env('SMTP_HOST', env('MAIL_HOST', 'smtp.gmail.com'));
$smtp_username = env('SMTP_USERNAME', env('MAIL_USER', ''));
$smtp_password = env('SMTP_PASSWORD', env('MAIL_PASS', ''));
$smtp_secure = strtolower(env('SMTP_SECURE', env('MAIL_SECURE', 'tls')));
$smtp_port = (int) env('SMTP_PORT', env('MAIL_PORT', 587));
$from_email = env('FROM_EMAIL', env('MAIL_USER', 'barcieinternationalcenter@gmail.com'));
$from_name = env('FROM_NAME', env('MAIL_NAME', 'BarCIE International Center'));

// Return mail configuration from environment variables
return [
    'host' => $smtp_host,
    'username' => $smtp_username,
    'password' => $smtp_password,
    'secure' => $smtp_secure,
    'port' => $smtp_port,
    'from_email' => $from_email,
    'from_name' => $from_name
];
