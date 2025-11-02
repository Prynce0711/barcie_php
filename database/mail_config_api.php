<?php
/**
 * Alternative Mail Configuration - Using API instead of SMTP
 * This approach is more secure and doesn't require storing email credentials
 */

// Load environment variables
$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
    
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

function env($key, $default = null) {
    $value = getenv($key);
    if ($value === false) {
        $value = $_ENV[$key] ?? $default;
    }
    return $value;
}

// Email API configuration
return [
    // API Method: 'sendgrid', 'mailgun', 'ses', or 'smtp' (fallback to SMTP)
    'method' => env('MAIL_METHOD', 'smtp'),
    
    // SendGrid API
    'sendgrid_api_key' => env('SENDGRID_API_KEY', ''),
    
    // Mailgun API
    'mailgun_api_key' => env('MAILGUN_API_KEY', ''),
    'mailgun_domain' => env('MAILGUN_DOMAIN', ''),
    
    // Amazon SES
    'ses_key' => env('AWS_SES_KEY', ''),
    'ses_secret' => env('AWS_SES_SECRET', ''),
    'ses_region' => env('AWS_SES_REGION', 'us-east-1'),
    
    // Fallback SMTP (if API not configured)
    'smtp_host' => env('SMTP_HOST', 'smtp.gmail.com'),
    'smtp_username' => env('SMTP_USERNAME', ''),
    'smtp_password' => env('SMTP_PASSWORD', ''),
    'smtp_secure' => env('SMTP_SECURE', 'tls'),
    'smtp_port' => (int) env('SMTP_PORT', 587),
    
    // Common settings
    'from_email' => env('FROM_EMAIL', 'barcieinternationalcenter@gmail.com'),
    'from_name' => env('FROM_NAME', 'Barcie International Center')
];
