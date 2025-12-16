<?php
// Script to fix the chatbot_answer.php file by removing escaped backslashes

$file = __DIR__ . '/chatbot_answer.php';
$content = file_get_contents($file);

// Replace the escaped backslashes with proper strings
$content = str_replace('\\"', '"', $content);
$content = str_replace('\\n', "\n", $content);

// Write it back
file_put_contents($file, $content);

echo "Fixed chatbot_answer.php\n";
