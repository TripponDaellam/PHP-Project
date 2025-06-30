<?php
session_start();
require_once '../DBConnection/DBConnector.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    // Strong password validation
    if ($password !== $confirm) {
        $_SESSION['signup_error'] = "Passwords do not match.";
        header("Location: ../Admin/signup.php");
        exit;
    }

    // // Enforce strong password: min 8 chars, 1 uppercase, 1 lowercase, 1 digit, 1 special char
    // if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z0-9]).{8,}$/', $password)) {
    //     $_SESSION['signup_error'] = "Password must be at least 8 characters long and include uppercase, lowercase, a number, and a special character.";
    //     header("Location: ../Admin/SignUp.php");
    //     exit;
    // }

    try {
        $check = $pdo->prepare("SELECT * FROM admins WHERE username = :username OR email = :email");
        $check->execute([':username' => $username, ':email' => $email]);

        if ($check->fetch()) {
            $_SESSION['signup_error'] = "Username or email already exists.";
            header("Location: ../Admin/signup.php");
            exit;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO admins (username, email, password, created_at) VALUES (:username, :email, :password, NOW())");
        $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password' => $hash
        ]);

        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['username'] = $username;

        header("Location: ../Admin/home.php");
        exit;

    } catch (PDOException $e) {
        $_SESSION['signup_error'] = "Signup failed: " . $e->getMessage();
        header("Location: ../Admin/signup.php");
        exit;
    }
} else {
    header("Location: ../Admin/signup.php");
    exit;
}