<?php
require '../DBConnection/DBConnector.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $token = $_POST['token'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($newPassword !== $confirmPassword) {
        echo "Passwords do not match.";
        exit();
    }

    // Recheck token
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE email = ? AND token = ? AND expires_at > NOW()");
    $stmt->execute([$email, $token]);
    $user = $stmt->fetch();

    if (!$user) {
        echo "Invalid or expired token.";
        exit();
    }

    // Hash the new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update password in your users table
    $update = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
    $update->execute([$hashedPassword, $email]);

    // Delete the reset token
    $pdo->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);

    echo "Password updated successfully. You can now <a href='login.php'>login</a>.";
}
