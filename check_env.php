<?php
declare(strict_types=1);

// Safe environment checker. Delete after use.
// Shows presence of key env variables without printing secrets.

$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

if (class_exists('Dotenv\\Dotenv')) {
    try {
        $dotenvClass = 'Dotenv\\Dotenv';
        $dotenv = $dotenvClass::createImmutable(__DIR__);
        $dotenv->safeLoad();
    } catch (Throwable $e) {
        // Ignore dotenv loading issues for diagnostics.
    }
}

if (PHP_SAPI !== 'cli') {
    header('Content-Type: text/plain; charset=utf-8');

    // Optional key gate to avoid exposing diagnostics publicly.
    $expectedKey = getenv('DEBUG_ACCESS_KEY') ?: ($_ENV['DEBUG_ACCESS_KEY'] ?? '');
    if ($expectedKey !== '') {
        $providedKey = (string) ($_GET['key'] ?? '');
        if (!hash_equals((string) $expectedKey, $providedKey)) {
            http_response_code(403);
            echo "Forbidden\n";
            exit;
        }
    }
}

function env_status(string $key): string
{
    $val = getenv($key);
    if ($val === false) {
        $val = $_ENV[$key] ?? null;
    }

    if ($val === null || trim((string) $val) === '') {
        return 'MISSING';
    }

    return 'SET (********)';
}

echo "ENV check (safe)\n";
echo ".env file: " . (file_exists(__DIR__ . '/.env') ? 'EXISTS' : 'MISSING') . "\n";
echo "vendor/autoload.php: " . (file_exists($autoloadPath) ? 'EXISTS' : 'MISSING') . "\n";
echo "SMTP_HOST: " . env_status('SMTP_HOST') . "\n";
echo "SMTP_USERNAME: " . env_status('SMTP_USERNAME') . "\n";
echo "SMTP_PASSWORD: " . (env_status('SMTP_PASSWORD') === 'MISSING' ? 'MISSING' : 'SET (hidden)') . "\n";
echo "SMTP_PORT: " . env_status('SMTP_PORT') . "\n";
echo "SMTP_ENCRYPTION: " . env_status('SMTP_ENCRYPTION') . "\n";
echo "FROM_EMAIL: " . env_status('FROM_EMAIL') . "\n";
echo "FROM_NAME: " . env_status('FROM_NAME') . "\n";

// Reminder: delete this file after use to avoid exposing diagnostics.