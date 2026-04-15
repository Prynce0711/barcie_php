<?php
/**
 * Application Configuration
 * Central configuration file for BarCIE Hotel Management System
 * 
 * @package BarCIE
 * @version 1.0.0
 */

// Load environment variables if .env file exists
if (file_exists(__DIR__ . '/../.env')) {
    // Simple .env parser (or use vlucas/phpdotenv if available)
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0)
            continue;

        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv("$name=$value");
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// Application Settings
if (!defined('APP_ENV')) {
    define('APP_ENV', getenv('APP_ENV') ?: 'development');
}
if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', getenv('APP_DEBUG') === 'true' || getenv('APP_DEBUG') === '1');
}

$requestScheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$requestHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$appBasePath = rtrim(str_replace('\\', '/', dirname(dirname($scriptName))), '/');
if ($appBasePath === '/') {
    $appBasePath = '';
}

if (!defined('APP_BASE_PATH')) {
    define('APP_BASE_PATH', $appBasePath);
}
if (!defined('APP_URL')) {
    define('APP_URL', getenv('APP_URL') ?: ($requestScheme . '://' . $requestHost . APP_BASE_PATH));
}

// Debug Mode Control
if (!defined('DEBUG_MODE')) {
    define('DEBUG_MODE', APP_DEBUG && APP_ENV !== 'production');
}
if (!defined('LOG_ERRORS')) {
    define('LOG_ERRORS', true);
}
if (!defined('DISPLAY_ERRORS')) {
    define('DISPLAY_ERRORS', DEBUG_MODE);
}

// Security Settings
if (!defined('CSRF_ENABLED')) {
    define('CSRF_ENABLED', true);
}
if (!defined('SESSION_LIFETIME')) {
    define('SESSION_LIFETIME', (int) (getenv('SESSION_LIFETIME') ?: 7200));
}
if (!defined('PASSWORD_MIN_LENGTH')) {
    define('PASSWORD_MIN_LENGTH', (int) (getenv('PASSWORD_MIN_LENGTH') ?: 8));
}

// Database Settings
if (!defined('DB_HOST')) {
    define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
}
if (!defined('DB_USER')) {
    define('DB_USER', getenv('DB_USER') ?: 'root');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', getenv('DB_PASS') ?: '');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', getenv('DB_NAME') ?: 'barcie_db');
}
if (!defined('DB_PORT')) {
    define('DB_PORT', (int) (getenv('DB_PORT') ?: 3306));
}

// Email Settings
if (!defined('SMTP_HOST')) {
    define('SMTP_HOST', getenv('SMTP_HOST') ?: 'smtp.gmail.com');
}
if (!defined('SMTP_USERNAME')) {
    define('SMTP_USERNAME', getenv('SMTP_USERNAME') ?: '');
}
if (!defined('SMTP_PASSWORD')) {
    define('SMTP_PASSWORD', getenv('SMTP_PASSWORD') ?: '');
}
if (!defined('SMTP_PORT')) {
    define('SMTP_PORT', (int) (getenv('SMTP_PORT') ?: 587));
}
if (!defined('SMTP_SECURE')) {
    define('SMTP_SECURE', getenv('SMTP_SECURE') ?: 'tls');
}
if (!defined('FROM_EMAIL')) {
    define('FROM_EMAIL', getenv('FROM_EMAIL') ?: '');
}
if (!defined('FROM_NAME')) {
    define('FROM_NAME', getenv('FROM_NAME') ?: 'BarCIE International Center');
}

// File Upload Settings
if (!defined('MAX_UPLOAD_SIZE')) {
    define('MAX_UPLOAD_SIZE', (int) (getenv('MAX_UPLOAD_SIZE') ?: 5242880)); // 5MB
}
if (!defined('ALLOWED_IMAGE_TYPES')) {
    define('ALLOWED_IMAGE_TYPES', explode(',', getenv('ALLOWED_IMAGE_TYPES') ?: 'jpg,jpeg,png,gif,webp'));
}
if (!defined('UPLOAD_PATH')) {
    define('UPLOAD_PATH', getenv('UPLOAD_PATH') ?: __DIR__ . '/../uploads');
}

// Logging Settings
if (!defined('LOG_PATH')) {
    define('LOG_PATH', getenv('LOG_PATH') ?: __DIR__ . '/../logs');
}
if (!defined('LOG_LEVEL')) {
    define('LOG_LEVEL', getenv('LOG_LEVEL') ?: (DEBUG_MODE ? 'debug' : 'info'));
}

// API Keys
if (!defined('OPENAI_API_KEY')) {
    define('OPENAI_API_KEY', getenv('OPENAI_API_KEY') ?: '');
}

// Configure PHP error handling
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT & ~E_NOTICE);
    ini_set('display_errors', '0');
}

ini_set('log_errors', '1');
ini_set('error_log', LOG_PATH . '/php_errors.log');

// Timezone
date_default_timezone_set('Asia/Manila');

// Session Configuration
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? '1' : '0');
    ini_set('session.cookie_samesite', 'Strict');
    session_start();
}

/**
 * Helper function to check if debug mode is enabled
 */
function isDebugMode()
{
    return DEBUG_MODE;
}

/**
 * Helper function to log messages (respects debug mode)
 */
function logMessage($message, $level = 'INFO')
{
    if (!LOG_ERRORS && !DEBUG_MODE) {
        return;
    }

    $timestamp = date('Y-m-d H:i:s');
    $logFile = LOG_PATH . '/app.log';

    if (!is_dir(LOG_PATH)) {
        @mkdir(LOG_PATH, 0755, true);
    }

    $logEntry = sprintf("[%s] [%s] %s\n", $timestamp, $level, $message);
    @file_put_contents($logFile, $logEntry, FILE_APPEND);
}


function config($key, $default = null)
{
    $key = strtoupper($key);
    return defined($key) ? constant($key) : $default;
}

/**
 * Get PDO Database Connection
 * Returns a PDO instance for database operations
 * 
 * @return PDO
 * @throws PDOException
 */
function getDBConnection()
{
    static $pdo = null;

    if ($pdo === null) {
        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
                DB_HOST,
                DB_PORT,
                DB_NAME
            );

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("PDO Connection Error: " . $e->getMessage());
            throw new PDOException("Database connection failed");
        }
    }

    return $pdo;
}
