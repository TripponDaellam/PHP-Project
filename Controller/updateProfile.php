<?php
session_start();
require_once '../DBConnection/DBConnector.php';

$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    header('Location: ../User/Login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $phone = trim($_POST['phone']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }

    try {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
        $stmt->execute([$name, $email, $phone, $userId]);

        header("Location: ../User/profile.php?success=1");
        exit;
    } catch (PDOException $e) {
        error_log("Update Error: " . $e->getMessage());
        header("Location: ../User/profile.php?error=1");
        exit;
    }
}
?>
