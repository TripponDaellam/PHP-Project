<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../User/Login.php");
    exit();
}

require_once '../DBConnection/DBConnector.php'; // remote DB for questions/users/comments

$userId = $_SESSION['user_id'];
$questionId = isset($_POST['question_id']) ? (int)$_POST['question_id'] : null;
$parentId = isset($_POST['parent_comment_id']) ? (int)$_POST['parent_comment_id'] : null;
$content = trim($_POST['reply_content'] ?? '');

if (!$questionId || !$parentId || $content === '') {
    header("Location: ../questionDetails.php?id=" . ($questionId ?: ''));
    exit();
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO comments (question_id, user_id, content, parent_id, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$questionId, $userId, $content, $parentId]);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}

header("Location: ../questionDetails.php?id=" . $questionId);
exit();
