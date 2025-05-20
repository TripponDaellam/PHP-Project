<?php
session_start();
require_once '../DBConnection/DBConnector.php';

$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    header('Location: ../User/Login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    try {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
        $stmt->execute([$name, $email, $phone, $userId]);

        header("Location: ../User/profile.php?success=1");
        exit;
    } catch (PDOException $e) {
        echo "Update Error: " . $e->getMessage();
    }
}
?>
