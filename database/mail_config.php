<?php
// Load environment variables from .env file (if composer autoload exists)
$autoload = __DIR__ . '/../vendor/autoload.php';
$autoloadUsable = true;
$autoloadFilesPath = __DIR__ . '/../vendor/composer/autoload_files.php';
if (file_exists($autoloadFilesPath)) {
    $autoloadFiles = @include $autoloadFilesPath;
    if (!is_array($autoloadFiles)) {
        $autoloadUsable = false;
    } else {
        foreach ($autoloadFiles as $autoloadFile) {
            if (!file_exists((string) $autoloadFile)) {
                $autoloadUsable = false;
                error_log('mail_config: Composer autoload dependency missing: ' . $autoloadFile . '. Skipping Dotenv autoload.');
                break;
            }
        }
    }
}

if (file_exists($autoload) && $autoloadUsable) {
    require_once $autoload;

    // Load .env if Dotenv is available.
    // Use string-based lookup so static analyzers do not require Dotenv at design time.
    $dotenvClass = 'Dotenv\\Dotenv';
    if (class_exists($dotenvClass)) {
        $root = dirname(__DIR__);
        try {
            $dotenv = $dotenvClass::createImmutable($root);
            $dotenv->safeLoad();
        } catch (Exception $e) {
            error_log("Failed to load .env: " . $e->getMessage());
        }
    }
} else {
    // Fallback parser so SMTP env vars can still be read when Composer autoload is broken.
    $envPath = dirname(__DIR__) . '/.env';
    if (file_exists($envPath)) {
        $lines = @file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (is_array($lines)) {
            foreach ($lines as $line) {
                $line = trim((string) $line);
                if ($line === '' || $line[0] === '#') {
                    continue;
                }

                $parts = explode('=', $line, 2);
                if (count($parts) !== 2) {
                    continue;
                }

                $key = trim((string) $parts[0]);
                $value = trim((string) $parts[1]);
                if ($key === '') {
                    continue;
                }

                $len = strlen($value);
                if ($len >= 2) {
                    $first = $value[0];
                    $last = $value[$len - 1];
                    if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                        $value = substr($value, 1, -1);
                    }
                }

                if (getenv($key) === false) {
                    putenv($key . '=' . $value);
                }
                if (!isset($_ENV[$key])) {
                    $_ENV[$key] = $value;
                }
            }
        }
    }
}

// Helper function to read environment variables with fallback
// Helper function to read environment variables with fallback
if (!function_exists('env')) {
    function env($key, $default = null)
    {
        $value = getenv($key);
        if ($value === false) {
            $value = $_ENV[$key] ?? $default;
        }
        return $value;
    }
}

// Normalize accepted environment variable names to be forgiving.
// Hardcoded production fallbacks are used when no .env file exists AND
// the server/container does not inject the env vars at runtime.
$smtp_host     = env('SMTP_HOST',     env('MAIL_HOST',    'smtp.gmail.com'));
$smtp_username = env('SMTP_USERNAME', env('SMTP_USER',    env('MAIL_USER', 'barcieinternationalcenter.web@gmail.com')));
$smtp_password = env('SMTP_PASSWORD', env('SMTP_PASS',    env('MAIL_PASS', 'mhtmuqvjqepkujff')));
$smtp_secure   = strtolower(env('SMTP_SECURE', env('MAIL_SECURE', 'tls')));
$smtp_port     = (int) env('SMTP_PORT', env('MAIL_PORT', 587));
$from_email    = env('FROM_EMAIL',    env('MAIL_FROM',   'barcieinternationalcenter@gmail.com'));
$from_name     = env('FROM_NAME',     env('MAIL_NAME',   'BarCIE International Center'));

// Optional: allow falling back to PHP's mail() if SMTP repeatedly fails
$use_php_mail = filter_var(env('USE_PHP_MAIL', 'false'), FILTER_VALIDATE_BOOLEAN);

// Return mail configuration from environment variables
return [
    'host' => $smtp_host,
    'username' => $smtp_username,
    'password' => $smtp_password,
    'secure' => $smtp_secure,
    'port' => $smtp_port,
    'from_email' => $from_email,
    'from_name' => $from_name,
    'use_php_mail' => $use_php_mail
];
