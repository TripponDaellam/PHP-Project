<?php
session_start();
require_once '../DBConnection/DBConnector.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../User/Login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$questionId = $_POST['question_id'] ?? null;
$voteType = $_POST['vote_type'] ?? null;

if (!$questionId || !in_array($voteType, ['up', 'down'])) {
    header("Location: ../index.php?error=invalid_vote");
    exit();
}

// Fetch current question info
$stmt = $pdo->prepare("SELECT upvotes, downvotes, voters FROM questions WHERE id = ?");
$stmt->execute([$questionId]);
$question = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$question) {
    header("Location: ../index.php?error=question_not_found");
    exit();
}

$votersRaw = $question['voters'] ?? '';
$voterList = $votersRaw ? explode(',', $votersRaw) : [];
$votedUsers = [];

foreach ($voterList as $entry) {
    list($uid, $type) = explode(':', $entry);
    $votedUsers[$uid] = $type;
}

// Already voted the same way
if (isset($votedUsers[$userId]) && $votedUsers[$userId] === $voteType) {
    header("Location: ../index.php?message=already_voted");
    exit();
}

// Update vote count
$upvotes = (int)$question['upvotes'];
$downvotes = (int)$question['downvotes'];

// Remove old vote
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
$updateStmt->execute([$upvotes, $downvotes, $votersString, $questionId]);

header("Location: ../index.php?message=vote_recorded");
exit();
