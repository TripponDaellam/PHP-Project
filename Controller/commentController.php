<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../User/Login.php");
    exit();
}

require_once '../DBConnection/DBConnector.php';

$userId = $_SESSION['user_id'];
$questionId = isset($_POST['question_id']) ? (int)$_POST['question_id'] : null;
$parentId = isset($_POST['parent_id']) ? (int)$_POST['parent_id'] : null; // <-- new line
$content = trim($_POST['comment'] ?? '');

if (!$questionId || $content === '') {
    header("Location: ../questionDetails.php?id=" . ($questionId ?: ''));
    exit();
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO comments (question_id, user_id, content, parent_id, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$questionId, $userId, $content, $parentId]); // <-- added $parentId
    if (is_null($parentId)) {
        $stmt = $pdo->prepare("UPDATE questions SET answer = answer + 1 WHERE id = ?");
        $stmt->execute([$questionId]);

        if ($stmt->rowCount() === 0) {
            echo "⚠️ Update affected 0 rows. Question may not exist.";
        }
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}

header("Location: ../questionDetails.php?id=" . $questionId);
exit();
