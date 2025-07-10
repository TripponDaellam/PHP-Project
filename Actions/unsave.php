<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
  echo json_encode(['success' => false, 'message' => 'Login required']);
  exit;
}

require_once '../DBConnection/DBConnector.php';

$data = json_decode(file_get_contents("php://input"), true);
$question_id = intval($data['question_id'] ?? 0);
$user_id = $_SESSION['user_id'];

if ($question_id <= 0) {
  echo json_encode(['success' => false, 'message' => 'Invalid question ID']);
  exit;
}

$stmt = $pdo->prepare("DELETE FROM savedQuestions WHERE user_id = ? AND question_id = ?");
if ($stmt->execute([$user_id, $question_id])) {
  echo json_encode(['success' => true]);
} else {
  echo json_encode(['success' => false, 'message' => 'Failed to unsave']);
}
