<?php
// Safe environment checker — DELETE after use.
// Shows presence of key env variables without printing secrets.

require_once __DIR__ . '/vendor/autoload.php';

if (class_exists(\Dotenv\Dotenv::class)) {
    try {
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->safeLoad();
    } catch (Exception $e) {
        // ignore
    }
}

function is_set_mask($key) {
    $val = getenv($key);
    if ($val === false) $val = $_ENV[$key] ?? null;
    if (empty($val)) return "MISSING";
    return 'SET (' . str_repeat('*', 8) . ')';
}

echo "ENV check (safe):\n";
echo " .env file: " . (file_exists(__DIR__ . '/.env') ? 'EXISTS' : 'MISSING') . "\n";
echo " SMTP_HOST: " . is_set_mask('SMTP_HOST') . "\n";
echo " SMTP_USERNAME: " . is_set_mask('SMTP_USERNAME') . "\n";
echo " SMTP_PASSWORD: " . (is_set_mask('SMTP_PASSWORD') === 'MISSING' ? 'MISSING' : 'SET (hidden)') . "\n";
echo " SMTP_PORT: " . is_set_mask('SMTP_PORT') . "\n";
echo " FROM_EMAIL: " . is_set_mask('FROM_EMAIL') . "\n";

// Reminder: delete this file after use to avoid exposing diagnostics.
?>