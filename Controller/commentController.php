<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../User/Login.php");
    exit();
}

require_once '../DBConnection/DBLocal.php';

$userId = $_SESSION['user_id'];
$questionId = isset($_POST['question_id']) ? (int)$_POST['question_id'] : null;
$content = trim($_POST['comment'] ?? '');

if (!$questionId || $content === '') {
    header("Location: ../questionDetails.php?id=" . ($questionId ?: ''));
    exit();
}

try {
    $stmt = $localPdo->prepare("INSERT INTO comments (question_id, user_id, comment, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$questionId, $userId, $content]);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}

header("Location: ../questionDetails.php?id=" . $questionId);
exit();
