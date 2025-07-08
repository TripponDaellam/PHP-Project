<?php
session_start();
require_once '../DBConnection/DBConnector.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../User/Login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';
$other_id = $_GET['id'] ?? '';

if (!is_numeric($other_id)) {
    header("Location: ../friends.php?tab=all");
    exit();
}

switch ($action) {
    case 'request':
        // Send friend request
        $stmt = $pdo->prepare("SELECT * FROM friend_requests WHERE sender_id = ? AND receiver_id = ?");
        $stmt->execute([$user_id, $other_id]);

        if ($stmt->rowCount() === 0) {
            $insert = $pdo->prepare("INSERT INTO friend_requests (sender_id, receiver_id, status) VALUES (?, ?, 'pending')");
            $insert->execute([$user_id, $other_id]);
        }
        break;

    case 'accept':
        // Accept received request
        $stmt = $pdo->prepare("UPDATE friend_requests SET status = 'accepted' WHERE receiver_id = ? AND sender_id = ?");
        $stmt->execute([$user_id, $other_id]);
        break;

    case 'decline':
        // Decline (delete) received request
        $stmt = $pdo->prepare("DELETE FROM friend_requests WHERE receiver_id = ? AND sender_id = ?");
        $stmt->execute([$user_id, $other_id]);
        break;

    case 'cancel':
    // Cancel a pending request you sent
    $stmt = $pdo->prepare("DELETE FROM friend_requests WHERE sender_id = ? AND receiver_id = ? AND status = 'pending'");
    $stmt->execute([$user_id, $other_id]);
    break;  
    


    default:
        // Invalid action
        break;
}

header("Location: ../friend.php?tab=" . ($_GET['from'] ?? 'all'));
exit();
