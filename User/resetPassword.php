<?php
require '../DBConnection/DBConnector.php';
session_start();

$token = $_GET['token'] ?? '';
$token_hash = hash('sha256', $token);

$stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token = ? AND token_expire > NOW()");
$stmt->execute([$token_hash]);
$user = $stmt->fetch();

if (!$user) {
    echo "Invalid or expired token.";
    exit();
}

$email = $user['email'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100 text-gray-900">

    <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold mb-6 text-center text-orange-600">Reset Your Password</h2>

        <?php if (isset($_SESSION['reset_error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-sm">
                <?= htmlspecialchars($_SESSION['reset_error']) ?>
                <?php unset($_SESSION['reset_error']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="../Controller/update_password.php" class="space-y-4">
            <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

            <div>
                <label class="block text-sm font-medium mb-1">New Password</label>
                <input type="password" name="new_password" required 
                       class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Confirm Password</label>
                <input type="password" name="confirm_password" required 
                       class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>

            <button type="submit" 
                    class="w-full bg-orange-500 hover:bg-orange-600 text-white py-2 px-4 rounded transition">
                Update Password
            </button>
        </form>
    </div>

</body>
</html>
