<?php
header('Content-Type: application/json; charset=utf-8');

$raw = file_get_contents('php://input');
$message = '';
// Try JSON body first
if ($raw !== false && trim($raw) !== '') {
    $decoded = json_decode($raw, true);
    if (is_array($decoded) && isset($decoded['message'])) {
        $message = trim((string) $decoded['message']);
    } else {
        // try parse as urlencoded form body
        parse_str($raw, $parsed);
        if (isset($parsed['message'])) {
            $message = trim((string) $parsed['message']);
        }
    }
}

// fallback to _POST/_GET/_REQUEST
if ($message === '') {
    if (isset($_POST['message']))
        $message = trim((string) $_POST['message']);
    elseif (isset($_GET['message']))
        $message = trim((string) $_GET['message']);
    elseif (isset($_REQUEST['message']))
        $message = trim((string) $_REQUEST['message']);
}

if ($message === '') {
    // helpful debugging hint for clients
    echo json_encode(['error' => 'No message provided', 'hint' => 'Send JSON body {"message":"..."} or form field message=...']);
    exit;
}

$lower = mb_strtolower($message, 'UTF-8');

// Helper to safely read text files
function safe_read($path)
{
    if (!file_exists($path))
        return null;
    $content = @file_get_contents($path);
    return $content === false ? null : $content;
}

$base = realpath(__DIR__ . '/../');
$readme = safe_read($base . DIRECTORY_SEPARATOR . 'README.md');
$composer = safe_read($base . DIRECTORY_SEPARATOR . 'composer.json');
$package = safe_read($base . DIRECTORY_SEPARATOR . 'package.json');

$sources = [];
if ($readme)
    $sources[] = 'README.md';
if ($composer)
    $sources[] = 'composer.json';
if ($package)
    $sources[] = 'package.json';

$answer = null;

// Check for Google Gemini API key (OPENAIAPI_KEY in .env is actually a Google key)
$gemini_key = getenv('GEMINI_API_KEY') ?: getenv('OPENAIAPI_KEY') ?: null;
// If there's a .env in project root, attempt to read it for API key
if (!$gemini_key) {
    $envPath = $base . DIRECTORY_SEPARATOR . '.env';
    if (file_exists($envPath)) {
        $envContent = file_get_contents($envPath);
        if ($envContent !== false) {
            // Try OPENAIAPI_KEY first (current .env key name)
            if (preg_match('/^\s*OPENAIAPI_KEY\s*=\s*(.+)\s*$/m', $envContent, $m)) {
                $gemini_key = trim($m[1], " \t\"'\r\n");
            }
            // Also try GEMINI_API_KEY
            if (!$gemini_key && preg_match('/^\s*GEMINI_API_KEY\s*=\s*(.+)\s*$/m', $envContent, $m)) {
                $gemini_key = trim($m[1], " \t\"'\r\n");
            }
        }
    }
}

// helper: call Google Gemini API
function call_gemini_api($apiKey, $prompt, $max_tokens = 800)
{
    // Use Gemini 2.5 Flash for responses (stable, fast and efficient model)
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $apiKey;

    $payload = json_encode([
        'contents' => [
            [
                'parts' => [['text' => $prompt]]
            ]
        ],
        'generationConfig' => [
            'temperature' => 0.3,
            'maxOutputTokens' => $max_tokens,
            'topP' => 0.8,
            'topK' => 40
        ],
        'safetySettings' => [
            ['category' => 'HARM_CATEGORY_HARASSMENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
            ['category' => 'HARM_CATEGORY_HATE_SPEECH', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
            ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
            ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE']
        ]
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $resp = curl_exec($ch);
    $err = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($resp === false || $code >= 400) {
        return ['error' => $err ?: 'HTTP ' . $code, 'raw' => $resp];
    }
    $j = json_decode($resp, true);
    if (!is_array($j))
        return ['error' => 'Invalid JSON from Gemini', 'raw' => $resp];
    return $j;
}


// If the user asked about the project or codebase, return a generated summary
if (preg_match('/\b(project|what is this|about (this )?project|describe (this )?project|codebase|repo|repository)\b/i', $message)) {
    $parts = [];
    if ($composer) {
        $json = json_decode($composer, true);
        if (is_array($json)) {
            $name = $json['name'] ?? ($json['description'] ?? null);
            if ($name)
                $parts[] = "Project (from composer.json): " . trim($name);
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
            if ($pname)
                $parts[] = "Node/package name: " . $pname;
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
                if (!empty($para))
                    break;
                continue;
            }
            // skip badges or markdown headings lines that look like images
            if (preg_match('/^!\[.*\]\(.*\)/', $ln))
                continue;
            $para[] = $ln;
        }
        if (!empty($para)) {
            $parts[] = 'README summary: ' . trim(implode(' ', array_slice($para, 0, 5)));
        }
    }

    // Add quick run / local dev hint
    $parts[] = "How to run locally: this is a PHP/HTML project. Using XAMPP place the repo in your webroot (htdocs) and open the site in your browser (index.php). API endpoints are under /api and frontend assets under /assets/.";

    $local_summary = implode("\n\n", $parts);

    // If Gemini key available, ask the model to produce a short project overview using the files
    if (!empty($gemini_key)) {
        // --- Build smarter context by indexing project files and extracting relevant snippets ---
        $allowed_exts = ['php', 'md', 'js', 'json', 'css', 'html', 'txt'];
        $exclude_dirs = ['vendor', 'node_modules', '.git', 'uploads', 'storage', 'tailscale-nginx'];

        // list files recursively (limited depth and total files)
        $filesList = [];
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base, RecursiveDirectoryIterator::SKIP_DOTS));
        $maxFiles = 1000; // safety cap
        foreach ($it as $file) {
            if (count($filesList) >= $maxFiles)
                break;
            $path = $file->getPathname();
            $rel = substr($path, strlen($base) + 1);
            // skip excluded dirs
            $skip = false;
            foreach ($exclude_dirs as $ex) {
                if (stripos($rel, $ex . DIRECTORY_SEPARATOR) === 0 || stripos($rel, DIRECTORY_SEPARATOR . $ex . DIRECTORY_SEPARATOR) !== false) {
                    $skip = true;
                    break;
                }
            }
            if ($skip)
                continue;
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed_exts))
                continue;
            $filesList[] = $path;
        }

        // If user asked a code-level question (contains file/class/function hints) prefer file content search.
        $query = $message;
        $tokens = preg_split('/\W+/', mb_strtolower($query));
        $tokens = array_filter($tokens, function ($t) {
            return strlen($t) > 2;
        });

        // Score files by filename match and content match (simple counts)
        $scores = [];
        foreach ($filesList as $p) {
            $score = 0;
            $fname = strtolower(basename($p));
            foreach ($tokens as $t) {
                if ($t === '')
                    continue;
                if (strpos($fname, $t) !== false)
                    $score += 3;
            }
            // quick content scan (limit read to 100KB)
            $cont = @file_get_contents($p, false, null, 0, 102400);
            if ($cont !== false) {
                $low = strtolower($cont);
                foreach ($tokens as $t) {
                    if ($t === '')
                        continue;
                    $pos = strpos($low, $t);
                    if ($pos !== false)
                        $score += 5;
                }
            }
            if ($score > 0)
                $scores[$p] = $score;
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
                if ($included >= $maxFilesToInclude)
                    break;
                $snips = [];
                $text = @file_get_contents($p);
                if ($text === false)
                    continue;
                $low = mb_strtolower($text, 'UTF-8');
                // find up to 3 matches and extract context
                $found = 0;
                foreach ($tokens as $t) {
                    if ($found >= 3)
                        break;
                    if ($t === '')
                        continue;
                    $pos = mb_strpos($low, $t, 0, 'UTF-8');
                    if ($pos === false)
                        continue;
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
                if ($totalChars + $len > $charLimit)
                    break;
                $context_items[] = $block;
                $totalChars += $len;
                $included++;
            }
        } else {
            if ($readme)
                $context_items[] = "README:\n" . substr($readme, 0, 3000);
            if ($composer)
                $context_items[] = "COMPOSER.JSON:\n" . substr($composer, 0, 2000);
            if ($package)
                $context_items[] = "PACKAGE.JSON:\n" . substr($package, 0, 2000);
        }

        // Add comprehensive BarCIE website context
        $barcie_context = "\n\n=== BARCIE INTERNATIONAL CENTER WEBSITE INFORMATION ===\n\n";
        $barcie_context .= "ABOUT: BarCIE International Center is a hotel and event venue located at La Consolacion University Philippines (LCUP). We provide comfortable accommodations and function halls for guests, students, alumni, and event organizers.\n\n";
        $barcie_context .= "ROOMS & FACILITIES:\n- Standard Rooms: â‚±1,500+/night with AC, WiFi, cable TV, private bathroom\n- Deluxe Rooms: â‚±2,500+/night with premium amenities\n- Function Halls: Small (50pax), Medium (100pax), Large (200pax) - â‚±3,000-â‚±8,000\n- All rooms: Air conditioning, free WiFi, clean linens, 24/7 security, parking\n\n";
        $barcie_context .= "BOOKING PROCESS:\n1. Visit Guest Portal â†’ Booking & Reservation\n2. Choose Reservation (rooms) or Pencil Booking (function halls)\n3. Fill in details: name, contact, dates, room type\n4. Upload ID if claiming discount\n5. Submit - receive email confirmation with payment details\n6. No account needed - direct booking system\n\n";
        $barcie_context .= "DISCOUNTS AVAILABLE:\n- PWD/Senior Citizens: 20% off\n- LCUP Personnel: 10% off\n- LCUP Students/Alumni: 7% off\n- Upload valid ID (School ID, PWD card, Company ID) during booking\n\n";
        $barcie_context .= "CHECK-IN/OUT:\n- Check-in: 2:00 PM onwards (early check-in if available)\n- Check-out: 12:00 PM (late checkout on request + charges)\n- 24/7 front desk for late arrivals\n- Bring valid ID and booking confirmation\n\n";
        $barcie_context .= "PAYMENT: Bank transfer, GCash, on-site payment. QR code provided after booking approval. Deposit may be required.\n\n";
        $barcie_context .= "CONTACT:\n- Email: barcieinternationalcenter.web@gmail.com\n- Location: LCUP Campus\n- Available for inquiries 24/7\n\n";
        $barcie_context .= "WEBSITE FEATURES:\n- Real-time availability calendar\n- Online booking system (rooms + function halls)\n- Discount application with ID upload\n- Guest feedback system\n- AI chatbot assistance\n- Email confirmations and notifications\n\n";
        $barcie_context .= "SECTIONS:\n1. Overview: Dashboard with quick stats\n2. Availability Calendar: Real-time room/hall status\n3. Rooms & Facilities: Browse all options with photos\n4. Booking & Reservation: Direct booking forms\n5. Feedback: Guest reviews and ratings\n\n";

        // Build comprehensive prompt for Gemini
        $prompt = "You are the BarCIE International Center AI Assistant. Answer the guest's question using the information provided.\n\n";
        $prompt .= $barcie_context;

        if (!empty($context_items)) {
            $prompt .= "\n=== PROJECT CODE SNIPPETS ===\n" . implode("\n\n---\n\n", array_slice($context_items, 0, 8)) . "\n\n";
        }

        $prompt .= "\n=== GUEST QUESTION ===\n" . $message . "\n\n";
        $prompt .= "INSTRUCTIONS:\n";
        $prompt .= "- Answer naturally and conversationally as a helpful hotel assistant\n";
        $prompt .= "- Use the BarCIE information provided above\n";
        $prompt .= "- Include relevant details (prices, times, requirements)\n";
        $prompt .= "- Be friendly, professional, and concise\n";
        $prompt .= "- If asked about technical/code details, use the project snippets\n";
        $prompt .= "- Format with line breaks for readability\n";
        $prompt .= "- Keep response under 500 words\n\n";
        $prompt .= "ANSWER:";

        $res = call_gemini_api($gemini_key, $prompt, 1000);
        if (isset($res['candidates'][0]['content']['parts'][0]['text'])) {
            $model_answer = $res['candidates'][0]['content']['parts'][0]['text'];
            // Extract relevant quick replies based on content
            $qr = [];
            if (stripos($model_answer, 'book') !== false || stripos($message, 'book') !== false)
                $qr[] = 'booking process';
            if (stripos($model_answer, 'room') !== false || stripos($message, 'room') !== false)
                $qr[] = 'room availability';
            if (stripos($model_answer, 'price') !== false || stripos($model_answer, 'cost') !== false)
                $qr[] = 'pricing';
            if (stripos($model_answer, 'discount') !== false)
                $qr[] = 'discount';
            if (stripos($model_answer, 'contact') !== false || stripos($model_answer, 'email') !== false)
                $qr[] = 'contact';
            if (stripos($model_answer, 'payment') !== false)
                $qr[] = 'payment';
            if (empty($qr))
                $qr = ['booking process', 'room availability', 'facilities'];
            echo json_encode(['answer' => trim($model_answer), 'quickReplies' => array_slice(array_unique($qr), 0, 3), 'sourceFiles' => $sources]);
            exit;
        }
        // If Gemini call failed, fall back to local summary below
    }

    $answer = $local_summary;
    echo json_encode(['answer' => $answer, 'sourceFiles' => $sources]);
    exit;
}

// For general queries (not specifically about project code), use Gemini with BarCIE context
error_log("CHATBOT DEBUG - Gemini key available: " . (!empty($gemini_key) ? 'YES' : 'NO'));
error_log("CHATBOT DEBUG - Message: " . $message);

if (!empty($gemini_key)) {
    error_log("CHATBOT DEBUG - Calling Gemini API...");
    $barcie_context = "You are the BarCIE International Center AI Assistant, a helpful chatbot for our hotel and event venue website.\n\n";
    $barcie_context .= "=== ABOUT BARCIE ===\n";
    $barcie_context .= "BarCIE International Center is located at La Consolacion University Philippines (LCUP). We offer:\n";
    $barcie_context .= "- Comfortable hotel rooms (Standard ₱1,500+, Deluxe ₱2,500+/night)\n";
    $barcie_context .= "- Function halls for events (50-200 capacity, ₱3,000-₱8,000)\n";
    $barcie_context .= "- Amenities: AC, WiFi, cable TV, parking, 24/7 security\n";
    $barcie_context .= "- Discounts: PWD/Senior 20%, LCUP Personnel 10%, Students/Alumni 7%\n";
    $barcie_context .= "- Check-in 2PM, Check-out 12PM\n";
    $barcie_context .= "- Contact: barcieinternationalcenter.web@gmail.com\n";
    $barcie_context .= "- Easy online booking - no account needed!\n\n";

    $barcie_context .= "=== WEBSITE FEATURES ===\n";
    $barcie_context .= "1. Real-time Availability Calendar\n";
    $barcie_context .= "2. Direct booking system (rooms & function halls)\n";
    $barcie_context .= "3. Discount application with ID upload\n";
    $barcie_context .= "4. Email confirmations\n";
    $barcie_context .= "5. Guest feedback system\n";
    $barcie_context .= "6. AI chatbot (that's me!)\n\n";

    $prompt = $barcie_context;
    $prompt .= "GUEST QUESTION: " . $message . "\n\n";
    $prompt .= "Provide a helpful, friendly answer. Include relevant details about BarCIE if applicable. ";
    $prompt .= "If asked about bookings/rooms/prices, provide the information above. ";
    $prompt .= "If asked general questions, answer naturally. ";
    $prompt .= "Keep response conversational and under 400 words.\n\n";
    $prompt .= "ANSWER:";

    $res = call_gemini_api($gemini_key, $prompt, 800);
    error_log("CHATBOT DEBUG - Gemini response: " . json_encode($res));

    // Check if we got a valid response
    if (isset($res['candidates'][0]['content']['parts'][0]['text'])) {
        $model_answer = $res['candidates'][0]['content']['parts'][0]['text'];
        error_log("CHATBOT DEBUG - Got answer: " . substr($model_answer, 0, 100));
        // Suggest relevant quick replies
        $qr = [];
        if (stripos($message, 'book') !== false)
            $qr[] = 'booking process';
        if (stripos($message, 'room') !== false)
            $qr[] = 'room availability';
        if (stripos($message, 'price') !== false || stripos($message, 'cost') !== false)
            $qr[] = 'pricing';
        if (stripos($message, 'discount') !== false)
            $qr[] = 'discount';
        if (empty($qr))
            $qr = ['booking process', 'facilities', 'pricing'];
        echo json_encode(['answer' => trim($model_answer), 'quickReplies' => array_slice(array_unique($qr), 0, 3)]);
        exit;
    } else if (isset($res['error'])) {
        // Log the error but continue to fallback
        error_log("CHATBOT ERROR - Gemini API failed: " . $res['error'] . " - Falling back to local KB");
    }
}

// Final fallback - return null to use frontend local knowledge base
echo json_encode(['answer' => null, 'sourceFiles' => $sources]);
exit;
?>