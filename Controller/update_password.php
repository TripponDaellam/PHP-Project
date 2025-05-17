<?php
require '../DBConnection/DBConnector.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'] ?? '';
    $token = $_POST['token'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($new_password !== $confirm_password) {
        die("Passwords do not match.");
    }

    $token_hash = hash('sha256', $token);

    // Validate token
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND reset_token = ? AND token_expire > NOW()");
    $stmt->execute([$email, $token_hash]);
    $user = $stmt->fetch();

    if (!$user) {
        die("Invalid or expired token.");
    }

    // Update password
    $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, token_expire = NULL WHERE email = ?");
    $stmt->execute([$new_password_hashed, $email]);

    echo "Password updated successfully. <a href='login.php'>Login here</a>";
}
?>
