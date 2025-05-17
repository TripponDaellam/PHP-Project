<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once '../DBConnection/DBLocal.php'; // This uses your local DB

$userId = $_SESSION['user_id'];
$questionId = $_POST['question_id'] ?? null;
$content = trim($_POST['content'] ?? '');

if ($questionId && $content) {
  $stmt = $localPdo->prepare("INSERT INTO comments (question_id, user_id, comment, created_at) VALUES (?, ?, ?, NOW())");
$stmt->execute([$questionId, $userId, $content]);

}

header("Location: index.php");
exit();
