<?php
function askAI($question) {
    $apiKey = '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'; // Replace with your real API key

    $data = [
        'model' => 'openai/gpt-3.5-turbo', // Use OpenRouter's model format
        'messages' => [
            ['role' => 'system', 'content' => 'You are a helpful tutor. Answer briefly and clearly.'],
            ['role' => 'user', 'content' => $question]
        ],
    ];

    $ch = curl_init('https://openrouter.ai/api/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey,
        'HTTP-Referer: http://localhost'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        return 'Curl error: ' . curl_error($ch);
    }

    curl_close($ch);

    $result = json_decode($response, true);
    return $result['choices'][0]['message']['content'] ?? 'AI failed to respond.';
}

// Example usage
$question = "How do I solve a quadratic equation?";
$aiAnswer = askAI($question);
echo "<div class='bg-yellow-50 p-4 border-l-4 border-yellow-400'><strong>AI Answer:</strong><br>$aiAnswer</div>";