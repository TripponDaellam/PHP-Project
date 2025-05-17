<?php
require '../DBConnection/DBConnector.php'; // Your DB connection
session_start();

// Get token and email from URL
$token = $_GET['token'] ?? '';
$email = $_GET['email'] ?? '';

// Check if token and email are valid
$stmt = $pdo->prepare("SELECT * FROM password_resets WHERE email = ? AND token = ? AND expires_at > NOW()");
$stmt->execute([$email, $token]);
$user = $stmt->fetch();

if (!$user) {
    echo "Invalid or expired token.";
    exit();
}
?>

<!-- HTML Password Reset Form -->
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
