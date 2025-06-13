<?php
session_start();
require_once '../DBConnection/DBConnector.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? null;
$sender_id = $_GET['id'] ?? null;

if (!$action || !$sender_id) {
    die("Invalid request.");
}

if ($action === 'accept') {
    $stmt = $pdo->prepare("
        UPDATE friend_requests
        SET status = 'accepted'
        WHERE sender_id = ? AND receiver_id = ? AND status = 'pending'
    ");
    $stmt->execute([$sender_id, $user_id]);
} elseif ($action === 'decline') {
    $stmt = $pdo->prepare("
        DELETE FROM friend_requests
        WHERE sender_id = ? AND receiver_id = ? AND status = 'pending'
    ");
    $stmt->execute([$sender_id, $user_id]);
}

header("Location: ../friend.php");
exit();
