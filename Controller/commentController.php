<?php
require_once __DIR__. '/../config/DBConnector.php';

$pdo = Database::getConnection();
$question_id = $_POST['question_id'];
$comment = trim($_POST['comment']);

if (!empty($comment)) {
  $stmt = $pdo->prepare("INSERT INTO comments (question_id, content) VALUES (?, ?)");
  $stmt->execute([$question_id, $comment]);
}

header("Location: index.php");
exit;
