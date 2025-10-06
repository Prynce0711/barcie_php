<?php
// Comprehensive test script for chat system
session_start();

// Simulate a guest user session
$_SESSION['user_id'] = 1;
$_SESSION['user_logged_in'] = true;
$_SESSION['username'] = 'test_guest';

echo "<h2>Testing Chat System Integration</h2>\n";

// Test 1: Initialize chat tables
echo "<h3>Test 1: Initialize Chat Tables</h3>\n";
$url = "database/user_auth.php?action=init_chat";
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "Cookie: " . http_build_query($_SESSION, '', '; ')
    ]
]);
$response = file_get_contents($url, false, $context);
echo "URL: $url<br>\n";
echo "Response: $response<br><br>\n";

// Test 2: Send a test message
echo "<h3>Test 2: Send Test Message</h3>\n";
$postData = http_build_query([
    'action' => 'send_chat_message',
    'sender_id' => 1,
    'sender_type' => 'guest',
    'receiver_id' => 1,
    'receiver_type' => 'admin',
    'message' => 'Hello, this is a test message from guest!'
]);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/x-www-form-urlencoded\r\n" .
                   "Cookie: " . http_build_query($_SESSION, '', '; '),
        'content' => $postData
    ]
]);

$response = file_get_contents("database/user_auth.php", false, $context);
echo "Post Data: $postData<br>\n";
echo "Response: $response<br><br>\n";

// Test 3: Get unread count
echo "<h3>Test 3: Get Unread Count</h3>\n";
$url = "database/user_auth.php?action=get_unread_count&user_id=1&user_type=guest";
$response = file_get_contents($url, false, $context);
echo "URL: $url<br>\n";
echo "Response: $response<br><br>\n";

// Test 4: Get chat messages  
echo "<h3>Test 4: Get Chat Messages</h3>\n";
$url = "database/user_auth.php?action=get_chat_messages&user_id=1&user_type=guest&other_user_id=1&other_user_type=admin";
$response = file_get_contents($url, false, $context);
echo "URL: $url<br>\n";
echo "Response: $response<br><br>\n";

// Test 5: Get conversations
echo "<h3>Test 5: Get Conversations</h3>\n";
$url = "database/user_auth.php?action=get_chat_conversations&user_id=1&user_type=guest";
$response = file_get_contents($url, false, $context);
echo "URL: $url<br>\n";
echo "Response: $response<br><br>\n";

echo "<p><strong>All tests completed!</strong></p>\n";
?>