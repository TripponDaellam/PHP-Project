<?php
session_start();
require_once '../DBConnection/DBConnector.php';

$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    header('Location: ../User/Login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $full_name  = $_POST['full_name'];
    $about_me = $_POST['about_me'];
    $website = $_POST['website'];
    $github = $_POST['github'];

    try {
        $stmt = $pdo->prepare("UPDATE users SET username = ?, full_name = ?, about_me = ?, website = ?, github = ? WHERE id = ?");
        $stmt->execute([$username, $full_name, $about_me, $website, $github, $userId]);

        header("Location: ../User/profile.php?success=1");
        exit;
    } catch (PDOException $e) {
        echo "Update Error: " . $e->getMessage();
    }
}
?>