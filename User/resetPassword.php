<?php
require '../DBConnection/DBConnector.php';
session_start();

$token = $_GET['token'] ?? '';
$token_hash = hash('sha256', $token);

// Check if token is valid and not expired
$stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token = ? AND token_expire > NOW()");
$stmt->execute([$token_hash]);
$user = $stmt->fetch();

if (!$user) {
    echo "Invalid or expired token.";
    exit();
}

$email = $user['email']; // get email to use in form
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
</head>
<body>
<h2>Reset Your Password</h2>
<form method="POST" action="Controller/update_password.php">
    <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
    <label>New Password:</label><br>
    <input type="password" name="new_password" required><br><br>
    <label>Confirm Password:</label><br>
    <input type="password" name="confirm_password" required><br><br>
    <button type="submit">Update Password</button>
</form>
</body>
</html>
