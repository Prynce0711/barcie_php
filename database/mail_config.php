<?php
// Load environment variables from .env file
$autoload = __DIR__ . '/../../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
    
    // Load .env if Dotenv is available
    if (class_exists(\Dotenv\Dotenv::class)) {
        $root = dirname(__DIR__, 2);
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

// Return mail configuration from environment variables
return [
    'host' => env('SMTP_HOST', 'smtp.gmail.com'),
    'username' => env('SMTP_USERNAME', ''),
    'password' => env('SMTP_PASSWORD', ''),
    'secure' => env('SMTP_SECURE', 'tls'),
    'port' => (int) env('SMTP_PORT', 587),
    'from_email' => env('FROM_EMAIL', 'barcieinternationalcenter@gmail.com'),
    'from_name' => env('FROM_NAME', 'Barcie International Center')
];
