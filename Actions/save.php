<?php
session_start();
require_once '../DBConnection/DBConnector.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
  echo json_encode(['success' => false, 'message' => 'You must be logged in to save questions.']);
  exit;
}

$user_id = $_SESSION['user_id'];
$question_id = $_GET['id'] ?? null;

if (!$question_id) {
  echo json_encode(['success' => false, 'message' => 'Invalid request.']);
  exit;
}

$check = $pdo->prepare("SELECT 1 FROM savedQuestions WHERE user_id = :user_id AND question_id = :question_id");
$check->execute([
  ':user_id' => $user_id,
  ':question_id' => $question_id
]);

if ($check->rowCount() === 0) {
  $stmt = $pdo->prepare("INSERT INTO savedQuestions (user_id, question_id) VALUES (:user_id, :question_id)");
  $stmt->execute([
    ':user_id' => $user_id,
    ':question_id' => $question_id
  ]);
  echo json_encode(['success' => true, 'message' => 'The question has been saved successfully.']);
} else {
  echo json_encode(['success' => false, 'message' => 'You have already saved this question.']);
}
