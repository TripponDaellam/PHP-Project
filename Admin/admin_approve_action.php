<?php
session_start();
require_once '../DBConnection/DBConnector.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../User/Login.php');
    exit;
}

$questionId = $_POST['question_id'] ?? null;

if (!$questionId) {
    die("Invalid request");
}

if (isset($_POST['approve'])) {
    $stmt = $pdo->prepare("UPDATE questions SET is_approved = 1 WHERE id = ?");
    $stmt->execute([$questionId]);
} elseif (isset($_POST['reject'])) {
    $stmt = $pdo->prepare("DELETE FROM questions WHERE id = ?");
    $stmt->execute([$questionId]);
}

header("Location: admin_approve.php");
exit;
