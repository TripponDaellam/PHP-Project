<?php
session_start();
header('Content-Type: application/json');

require_once '../DBConnection/DBConnector.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Login required']);
    exit;
}

$userId = $_SESSION['user_id'];
$questionId = $_POST['question_id'] ?? null;
$voteType = $_POST['vote_type'] ?? null;

if (!$questionId || !in_array($voteType, ['up', 'down'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid vote']);
    exit;
}

// Fetch current question info
$stmt = $pdo->prepare("SELECT upvotes, downvotes, voters FROM questions WHERE id = ?");
$stmt->execute([$questionId]);
$question = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$question) {
    echo json_encode(['success' => false, 'error' => 'Question not found']);
    exit;
}

$votersRaw = $question['voters'] ?? '';
$voterList = $votersRaw ? explode(',', $votersRaw) : [];
$votedUsers = [];

foreach ($voterList as $entry) {
    if (strpos($entry, ':') !== false) {
        list($uid, $type) = explode(':', $entry);
        $votedUsers[$uid] = $type;
    }
}

// Already voted the same way
if (isset($votedUsers[$userId]) && $votedUsers[$userId] === $voteType) {
    echo json_encode(['success' => false, 'error' => 'Already voted this way']);
    exit;
}

// Update vote counts
$upvotes = (int)$question['upvotes'];
$downvotes = (int)$question['downvotes'];

// Remove old vote if exists
if (isset($votedUsers[$userId])) {
    $prevVote = $votedUsers[$userId];
    if ($prevVote === 'up') $upvotes--;
    if ($prevVote === 'down') $downvotes--;
}

// Apply new vote
if ($voteType === 'up') $upvotes++;
if ($voteType === 'down') $downvotes++;

$votedUsers[$userId] = $voteType;

// Rebuild voters string
$newVoters = [];
foreach ($votedUsers as $uid => $type) {
    $newVoters[] = "$uid:$type";
}
$votersString = implode(',', $newVoters);

// Update DB
$updateStmt = $pdo->prepare("UPDATE questions SET upvotes = ?, downvotes = ?, voters = ? WHERE id = ?");
$success = $updateStmt->execute([$upvotes, $downvotes, $votersString, $questionId]);

if ($success) {
    echo json_encode([
        'success' => true,
        'upvotes' => $upvotes,
        'downvotes' => $downvotes
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to update vote']);
}
exit;
