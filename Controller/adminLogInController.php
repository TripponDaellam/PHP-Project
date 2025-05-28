<?php
session_start();
require_once '../DBConnection/DBConnector.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = :username LIMIT 1");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: ../Admin/home.php");
            exit;
        } else {
            $_SESSION['login_error'] = "Invalid username or password";
            header("Location: ../Admin/login.php");
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['login_error'] = "Database error: " . $e->getMessage();
        header("Location: ../Admin/login.php");
        exit;
    }
} else {
    header("Location: ../Admin/login.php");
    exit;
}
