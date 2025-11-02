<?php
// write_env.php  — one-time helper. EDIT all the values below BEFORE uploading.
// IMPORTANT: Replace the example values (right side of =) with your real values
// then upload this file to your server, run it once in the browser, and delete it.
// Do NOT commit your real secrets to git.

$env_data = <<<TXT
APP_ENV=production
MAIL_USER=barcieinternationalcenter@gmail.com
MAIL_PASS=bwjnpxglrmlsurwg
DB_HOST=10.20.0.2
DB_NAME=barcie_db
DB_USER=root
DB_PASS=root
OPENAI_API_KEY=AIzaSyAAaTeSWW_5BSPldjOMzzQsDeJ5oh1HHII

TXT;

file_put_contents(__DIR__ . '/.env', $env_data);
echo "DONE. .env created";
