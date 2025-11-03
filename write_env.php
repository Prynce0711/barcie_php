<?php
// write_env.php  — one-time helper. EDIT all the values below BEFORE uploading.
// IMPORTANT: Replace the example values (right side of =) with your real values
// then upload this file to your server, run it once in the browser, and delete it.
// Do NOT commit your real secrets to git.

$env_data = <<<TXT
APP_ENV=production

SMTP_HOST=smtp.gmail.com
SMTP_USERNAME=pc.clemente11@gmail.com
SMTP_PASSWORD=bwjnpxglrmlsurwg
SMTP_PORT=587
SMTP_SECURE=tls
FROM_EMAIL=barcieinternationalcenter@gmail.com
FROM_NAME="BarCIE International Center"
OPENAI_API_KEY=AIzaSyAAaTeSWW_5BSPldjOMzzQsDeJ5oh1HHII

TXT;

file_put_contents(__DIR__ . '/.env', $env_data);
echo "DONE. .env created";
