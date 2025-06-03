<?php
session_start();
header('Content-Type: application/json');

$apiKey = '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'; // Replace with your real OpenRouter API key

// Reset chat
$data = json_decode(file_get_contents('php://input'), true);
if (!empty($data['reset'])) {
    $_SESSION['chat'] = [];
    echo json_encode(['status' => 'reset']);
    exit;
}

// Ask AI
$message = trim($data['message'] ?? '');
if (!$message) {
    echo json_encode(['reply' => 'No message provided.']);
    exit;
}

if (!isset($_SESSION['chat'])) $_SESSION['chat'] = [];

$chat = [['role' => 'system', 'content' => 'You are a helpful tutor. Answer briefly and clearly.']];
foreach ($_SESSION['chat'] as $entry) {
    $chat[] = ['role' => 'user', 'content' => $entry['user']];
    $chat[] = ['role' => 'assistant', 'content' => $entry['ai']];
}
$chat[] = ['role' => 'user', 'content' => $message];

$payload = [
    'model' => 'openai/gpt-3.5-turbo',
    'messages' => $chat,
];

$ch = curl_init('https://openrouter.ai/api/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey,
    'HTTP-Referer: http://localhost'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

$response = curl_exec($ch);
if (curl_errno($ch)) {
    echo json_encode(['reply' => 'Curl error: ' . curl_error($ch)]);
    exit;
}
curl_close($ch);

$result = json_decode($response, true);
$aiReply = $result['choices'][0]['message']['content'] ?? 'AI failed to respond.';

$_SESSION['chat'][] = ['user' => $message, 'ai' => $aiReply];

echo json_encode(['reply' => $aiReply]);
