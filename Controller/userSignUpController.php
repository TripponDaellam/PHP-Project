<?php
session_start();
require_once '../DBConnection/DBConnector.php'; // Adjust path if needed

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($password !== $confirm) {
        $_SESSION['signup_error'] = "Passwords do not match.";
        header("Location: signup.php");
        exit;
    }

    try {
        // Check for existing user/email
        $check = $pdo->prepare("SELECT * FROM users WHERE username = :username OR email = :email");
        $check->execute([':username' => $username, ':email' => $email]);

        if ($check->fetch()) {
            $_SESSION['signup_error'] = "Username or email already exists.";
            header("Location: signup.php");
            exit;
        }

        // Insert new user
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, created_at) VALUES (:username, :email, :password, NOW())");
        $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password' => $hash
        ]);

        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['username'] = $username;

        header("Location: ../index.php");
        exit;

    } catch (PDOException $e) {
        $_SESSION['signup_error'] = "Signup failed: " . $e->getMessage();
        header("Location: signup.php");
        exit;
    }
} else {
    header("Location: signup.php");
    exit;
}
