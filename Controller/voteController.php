<?php
require_once __DIR__ . '/../config/DBConnector.php';

$pdo = Database::getConnection();

$question_id = $_POST['question_id'] ?? null;
$vote = $_POST['vote'] ?? null;

if (!$question_id || !in_array($vote, ['up', 'down'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid vote']);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO votes (question_id, vote_type) VALUES (?, ?)");
$stmt->execute([$question_id, $vote]);
$upvotes = $pdo->query("SELECT COUNT(*) FROM votes WHERE question_id = $question_id AND vote_type = 'up'")->fetchColumn();
$downvotes = $pdo->query("SELECT COUNT(*) FROM votes WHERE question_id = $question_id AND vote_type = 'down'")->fetchColumn();

echo json_encode([
    'success' => true,
    'count' => $upvotes - $downvotes
]);
exit;
