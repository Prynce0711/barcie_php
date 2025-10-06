<?php
// Initialize Chat System Database Tables via user_auth.php endpoint
echo "🚀 Initializing Chat System Database...\n\n";

// Use the user_auth.php endpoint to initialize chat tables
$url = 'http://localhost/barcie_php/database/user_auth.php?action=init_chat';

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

// Execute request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ cURL Error: $error\n";
    exit(1);
}

if ($httpCode !== 200) {
    echo "❌ HTTP Error $httpCode\n";
    echo "Response: $response\n";
    exit(1);
}

// Parse JSON response
$data = json_decode($response, true);

if ($data === null) {
    echo "❌ Invalid JSON response: $response\n";
    exit(1);
}

if ($data['success']) {
    echo "✅ " . $data['message'] . "\n";
    echo "\n🎉 Chat system is ready to use!\n";
} else {
    echo "❌ " . $data['error'] . "\n";
    exit(1);
}
?>