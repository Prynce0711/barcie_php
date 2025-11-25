<?php
// write_env.php  — one-time helper. EDIT all the values below BEFORE uploading.
// IMPORTANT: Replace the example values (right side of =) with your real values
// then upload this file to your server, run it once in the browser, and delete it.
// Do NOT commit your real secrets to git.

$env_data = <<<TXT
APP_ENV=production

SMTP_HOST=smtp.gmail.com
SMTP_USER=barcieinternationalcenter.web@gmail.com
SMTP_PASS=mhtmuqvjqepkujff
SMTP_PORT=587
SMTP_SECURE=tls
FROM_EMAIL=barcieinternationalcenter.web@gmail.com
FROM_NAME="Barcie International Center"

OPENAIAPI_KEY=AIzaSyB4zwmnRj0p6jVHz92LqgAX3c32W1s1DW8


TXT;

file_put_contents(__DIR__ . '/.env', $env_data);
echo "DONE. .env created";
