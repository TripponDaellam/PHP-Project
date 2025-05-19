<?php
require '../DBConnection/DBConnector.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'] ?? '';
    $token = $_POST['token'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Check if passwords match
    if ($new_password !== $confirm_password) {
        $_SESSION['reset_error'] = "Passwords do not match.";
        header("Location: ../User/reset_password.php?token=" . urlencode($token));
        exit;
    }

    // Check for strong password
    if (
        strlen($new_password) < 8 ||
        !preg_match('/[A-Z]/', $new_password) ||
        !preg_match('/[a-z]/', $new_password) ||
        !preg_match('/[0-9]/', $new_password) ||
        !preg_match('/[\W]/', $new_password)
    ) {
        $_SESSION['reset_error'] = "Password must be at least 8 characters and include uppercase, lowercase, number, and special character.";
        header("Location: ../User/resetPassword.php?token=" . urlencode($token));
        exit;
    }

    $token_hash = hash('sha256', $token);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND reset_token = ? AND token_expire > NOW()");
    $stmt->execute([$email, $token_hash]);
    $user = $stmt->fetch();

    if (!$user) {
        $_SESSION['reset_error'] = "Invalid or expired token.";
        header("Location: ../User/ForgotPassword.php");
        exit;
    }

    // Update password
    $hashed = password_hash($new_password, PASSWORD_DEFAULT);
    $update = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, token_expire = NULL WHERE email = ?");
    $update->execute([$hashed, $email]);

    $_SESSION['reset_success'] = "Password updated successfully. Please log in.";
    header("Location: ../User/Login.php");
    exit;
}
