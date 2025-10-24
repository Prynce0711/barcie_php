<?php
// Allow overriding via environment variables when running in Docker
return [
    'host' => getenv('SMTP_HOST') ?: 'smtp.gmail.com',
    'username' => getenv('SMTP_USER') ?: '',
    'password' => getenv('SMTP_PASS') ?: '', // Use env in production
    'secure' => getenv('SMTP_SECURE') ?: 'tls',
    'port' => getenv('SMTP_PORT') ?: 587,
    'from_email' => getenv('FROM_EMAIL') ?: 'barcieinternationalcenter@gmail.com',
    'from_name' => getenv('FROM_NAME') ?: 'Barcie International Center'
];