<?php
header('Content-Type: application/json; charset=utf-8');

$raw = file_get_contents('php://input');
$message = '';
// Try JSON body first
if ($raw !== false && trim($raw) !== '') {
    $decoded = json_decode($raw, true);
    if (is_array($decoded) && isset($decoded['message'])) {
        $message = trim((string)$decoded['message']);
    } else {
        // try parse as urlencoded form body
        parse_str($raw, $parsed);
        if (isset($parsed['message'])) {
            $message = trim((string)$parsed['message']);
        }
    }
}

// fallback to _POST/_GET/_REQUEST
if ($message === '') {
    if (isset($_POST['message'])) $message = trim((string)$_POST['message']);
    elseif (isset($_GET['message'])) $message = trim((string)$_GET['message']);
    elseif (isset($_REQUEST['message'])) $message = trim((string)$_REQUEST['message']);
}

if ($message === '') {
    // helpful debugging hint for clients
    echo json_encode(['error' => 'No message provided', 'hint' => 'Send JSON body {"message":"..."} or form field message=...']);
    exit;
}

$lower = mb_strtolower($message, 'UTF-8');

// Helper to safely read text files
function safe_read($path) {
    if (!file_exists($path)) return null;
    $content = @file_get_contents($path);
    return $content === false ? null : $content;
}

$base = realpath(__DIR__ . '/../');
$readme = safe_read($base . DIRECTORY_SEPARATOR . 'README.md');
$composer = safe_read($base . DIRECTORY_SEPARATOR . 'composer.json');
$package = safe_read($base . DIRECTORY_SEPARATOR . 'package.json');

$sources = [];
if ($readme) $sources[] = 'README.md';
if ($composer) $sources[] = 'composer.json';
if ($package) $sources[] = 'package.json';

$answer = null;

// Check for OpenAI API key (server environment or .env)
$openai_key = getenv('OPENAI_API_KEY') ?: null;
// If there's a .env in project root, attempt to read it for OPENAI_API_KEY (simple parse)
if (!$openai_key) {
    $envPath = $base . DIRECTORY_SEPARATOR . '.env';
    if (file_exists($envPath)) {
        $envContent = file_get_contents($envPath);
        if ($envContent !== false) {
            if (preg_match('/^\s*OPENAI_API_KEY\s*=\s*(.+)\s*$/m', $envContent, $m)) {
                $openai_key = trim($m[1], " \t\"'\r\n");
            }
        }
    }
}

// helper: call OpenAI Chat Completions
function call_openai_chat($apiKey, $messages, $model = 'gpt-3.5-turbo', $max_tokens = 800) {
    $payload = json_encode([
        'model' => $model,
        'messages' => $messages,
        'max_tokens' => $max_tokens,
        'temperature' => 0.2,
    ]);

    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey,
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $resp = curl_exec($ch);
    $err = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($resp === false || $code >= 400) {
        return ['error' => $err ?: 'HTTP ' . $code, 'raw' => $resp];
    }
    $j = json_decode($resp, true);
    if (!is_array($j)) return ['error' => 'Invalid JSON from OpenAI', 'raw' => $resp];
    return $j;
}


// If the user asked about the project or codebase, return a generated summary
if (preg_match('/\b(project|what is this|about (this )?project|describe (this )?project|codebase|repo|repository)\b/i', $message)) {
    $parts = [];
    if ($composer) {
        $json = json_decode($composer, true);
        if (is_array($json)) {
            $name = $json['name'] ?? ($json['description'] ?? null);
            if ($name) $parts[] = "Project (from composer.json): " . trim($name);
            if (!empty($json['require'])) {
                $deps = array_keys($json['require']);
                $parts[] = 'PHP dependencies: ' . implode(', ', array_slice($deps, 0, 10));
            }
        }
    }

    if ($package) {
        $jsonp = json_decode($package, true);
        if (is_array($jsonp)) {
            $pname = $jsonp['name'] ?? null;
            if ($pname) $parts[] = "Node/package name: " . $pname;
            if (!empty($jsonp['dependencies'])) {
                $ndeps = array_keys($jsonp['dependencies']);
                $parts[] = 'Node dependencies: ' . implode(', ', array_slice($ndeps, 0, 10));
            }
        }
    }

    if ($readme) {
        // Use the first non-empty paragraph from README
        $lines = preg_split('/\r?\n/', $readme);
        $para = [];
        foreach ($lines as $ln) {
            $ln = trim($ln);
            if ($ln === '') {
                if (!empty($para)) break;
                continue;
            }
            // skip badges or markdown headings lines that look like images
            if (preg_match('/^!\[.*\]\(.*\)/', $ln)) continue;
            $para[] = $ln;
        }
        if (!empty($para)) {
            $parts[] = 'README summary: ' . trim(implode(' ', array_slice($para, 0, 5)));
        }
    }

    // Add quick run / local dev hint
    $parts[] = "How to run locally: this is a PHP/HTML project. Using XAMPP place the repo in your webroot (htdocs) and open the site in your browser (index.php). API endpoints are under /api and frontend assets under /assets/.";

    $local_summary = implode("\n\n", $parts);

    // If OpenAI key available, ask the model to produce a short project overview using the files
    if (!empty($openai_key)) {
        // --- Build smarter context by indexing project files and extracting relevant snippets ---
        $allowed_exts = ['php','md','js','json','css','html','txt'];
        $exclude_dirs = ['vendor', 'node_modules', '.git', 'uploads', 'storage', 'tailscale-nginx'];

        // list files recursively (limited depth and total files)
        $filesList = [];
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base, RecursiveDirectoryIterator::SKIP_DOTS));
        $maxFiles = 1000; // safety cap
        foreach ($it as $file) {
            if (count($filesList) >= $maxFiles) break;
            $path = $file->getPathname();
            $rel = substr($path, strlen($base) + 1);
            // skip excluded dirs
            $skip = false;
            foreach ($exclude_dirs as $ex) { if (stripos($rel, $ex . DIRECTORY_SEPARATOR) === 0 || stripos($rel, DIRECTORY_SEPARATOR . $ex . DIRECTORY_SEPARATOR) !== false) { $skip = true; break; } }
            if ($skip) continue;
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed_exts)) continue;
            $filesList[] = $path;
        }

        // If user asked a code-level question (contains file/class/function hints) prefer file content search.
        $query = $message;
        $tokens = preg_split('/\W+/', mb_strtolower($query));
        $tokens = array_filter($tokens, function($t){ return strlen($t) > 2; });

        // Score files by filename match and content match (simple counts)
        $scores = [];
        foreach ($filesList as $p) {
            $score = 0;
            $fname = strtolower(basename($p));
            foreach ($tokens as $t) { if ($t === '') continue; if (strpos($fname, $t) !== false) $score += 3; }
            // quick content scan (limit read to 100KB)
            $cont = @file_get_contents($p, false, null, 0, 102400);
            if ($cont !== false) {
                $low = strtolower($cont);
                foreach ($tokens as $t) { if ($t === '') continue; $pos = strpos($low, $t); if ($pos !== false) $score += 5; }
            }
            if ($score > 0) $scores[$p] = $score;
        }

        // If no scored files, fall back to top-level README/composer/package as before
        $context_items = [];
        if (!empty($scores)) {
            // sort by score desc
            arsort($scores);
            $maxFilesToInclude = 12;
            $included = 0;
            $totalChars = 0;
            $charLimit = 14000; // approximate safety limit for prompt size
            foreach ($scores as $p => $sc) {
                if ($included >= $maxFilesToInclude) break;
                $snips = [];
                $text = @file_get_contents($p);
                if ($text === false) continue;
                $low = mb_strtolower($text, 'UTF-8');
                // find up to 3 matches and extract context
                $found = 0;
                foreach ($tokens as $t) {
                    if ($found >= 3) break;
                    if ($t === '') continue;
                    $pos = mb_strpos($low, $t, 0, 'UTF-8');
                    if ($pos === false) continue;
                    $start = max(0, $pos - 200);
                    $snippet = mb_substr($text, $start, 600, 'UTF-8');
                    $snips[] = trim($snippet);
                    $found++;
                }
                if (empty($snips)) {
                    // include small head of file if no token match but filename matched
                    $snips[] = mb_substr($text, 0, 400, 'UTF-8');
                }
                $block = "FILE: " . $rel = substr($p, strlen($base) + 1) . "\n" . implode("\n\n---\n\n", $snips);
                $len = mb_strlen($block, 'UTF-8');
                if ($totalChars + $len > $charLimit) break;
                $context_items[] = $block;
                $totalChars += $len;
                $included++;
            }
        } else {
            if ($readme) $context_items[] = "README:\n" . substr($readme, 0, 3000);
            if ($composer) $context_items[] = "COMPOSER.JSON:\n" . substr($composer, 0, 2000);
            if ($package) $context_items[] = "PACKAGE.JSON:\n" . substr($package, 0, 2000);
        }

        // Add a small listing of top-level files (names only)
        $files = @scandir($base);
        $topFiles = [];
        if (is_array($files)) {
            foreach ($files as $f) {
                if ($f[0] === '.') continue;
                $topFiles[] = $f;
                if (count($topFiles) >= 40) break;
            }
        }

        $system = "You are an assistant that explains a PHP/HTML web project to a developer or non-technical guest. Use the provided project snippets (clearly marked with filenames) to answer concisely. If asked about how to run, include short instructions. Always include a 'Sources:' section listing the files used. Do not invent secrets or credentials. If the snippet is ambiguous, say so and suggest which file to inspect. Keep answer under 800 words.";

        $user_text = "User question: " . $message . "\n\nProject snippets (truncated and labeled):\n" . implode("\n\n#####\n\n", $context_items) . "\n\nTop-level files: " . implode(', ', $topFiles) . "\n\nProvide a helpful summary and short answers to the user's question.\n";

        $messages = [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $user_text]
        ];

        $res = call_openai_chat($openai_key, $messages, 'gpt-3.5-turbo', 800);
        if (isset($res['choices'][0]['message']['content'])) {
            $model_answer = $res['choices'][0]['message']['content'];
            // return model answer and indicate sources we provided
            echo json_encode(['answer' => trim($model_answer), 'sourceFiles' => $sources]);
            exit;
        }
        // If OpenAI call failed, fall back to local summary below
    }

    $answer = $local_summary;
    echo json_encode(['answer' => $answer, 'sourceFiles' => $sources]);
    exit;
}

// For non-project queries, we don't attempt deep NLP here. Indicate no project-level answer.
echo json_encode(['answer' => null, 'sourceFiles' => $sources]);
exit;

?>
