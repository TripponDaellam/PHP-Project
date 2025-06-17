<?php
session_start();
require_once '../DBConnection/DBConnector.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: ../Login.php");
  exit();
}

$user_id = $_SESSION['user_id'];
$post_id = $_POST['post_id'] ?? null;

if ($post_id) {
  // Ensure the post belongs to the current user
  $stmt = $pdo->prepare("DELETE FROM questions WHERE id = ? AND user_id = ?");
  $stmt->execute([$post_id, $user_id]);
}

header("Location: ../User/profile.php?tab=post");
exit();
