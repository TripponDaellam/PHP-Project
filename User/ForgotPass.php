<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../DBConnection/DBConnector.php';
require '../vendor/autoload.php';

session_start();

// Set your timezone explicitly (adjust as needed)
date_default_timezone_set('Asia/Yangon');

$message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);

    try {
        // Check if email exists
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $checkStmt->execute([$email]);
        $userExists = $checkStmt->fetchColumn();

        if (!$userExists) {
            $message = "<p class='text-red-600'>Email not found in our system.</p>";
        } else {
            // Generate token and expiry (1 hour)
            $token = bin2hex(random_bytes(16));
            $token_hash = hash("sha256", $token);
            $expiry = date("Y-m-d H:i:s", time() + 3600); // 1 hour

            // Save token and expiry
            $updateStmt = $pdo->prepare("UPDATE users SET reset_token = ?, token_expire = ? WHERE email = ?");
            $updateStmt->execute([$token_hash, $expiry, $email]);

            if ($updateStmt->rowCount() === 0) {
                $message = "<p class='text-red-600'>Failed to save reset token. Please try again later.</p>";
            } else {
                // Send reset email
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'methodflow12@gmail.com'; // Your SMTP email
                    $mail->Password = 'qktravxqntevamnt';       // Your SMTP password or app password
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;
                    $mail->setFrom('methodflow12@gmail.com', 'Method Flow');
                    $mail->addAddress($email);
                    $mail->isHTML(true);
                    $mail->Subject = 'Reset your Method Flow password';

                    $reset_link = "http://localhost/User/resetPassword.php?token=$token&email=" . urlencode($email);

                    $mail->Body = "
                    <html>
                    <head>
                      <style>
                        body { font-family: Arial, sans-serif; background-color: #f4f4f4; color: #333; padding: 20px; }
                        .email-container { background-color: #fff; padding: 30px; border-radius: 8px; max-width: 600px; margin: auto; box-shadow: 0 2px 4px rgba(0,0,0,0.1);}
                        .btn { display: inline-block; padding: 12px 24px; margin-top: 20px; background-color: #f97316; border-radius: 6px; font-weight: bold; color: #fff; text-decoration: none;}
                        .footer { font-size: 12px; color: #777; margin-top: 30px; text-align: center;}
                      </style>
                    </head>
                    <body>
                      <div class='email-container'>
                        <h2>Hello,</h2>
                        <p>We received a request to reset your password for your Method Flow account.</p>
                        <p>If you made this request, click the button below to reset your password. This link will expire in 1 hour for your security.</p>
                        <a href='$reset_link' class='btn'>Reset Your Password</a>
                        <p>If you didn’t request a password reset, please ignore this email.</p>
                        <div class='footer'>&copy; " . date('Y') . " Method Flow. All rights reserved.</div>
                      </div>
                    </body>
                    </html>";

                    $mail->send();
                    $message = "<p class='text-green-600'>Reset link sent to your email.</p>";
                } catch (Exception $e) {
                    $message = "<p class='text-red-600'>Mailer Error: {$mail->ErrorInfo}</p>";
                }
            }
        }
    } catch (Exception $ex) {
        $message = "<p class='text-red-600'>An error occurred: " . htmlspecialchars($ex->getMessage()) . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Forgot Password - Method Flow</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
  <div class="bg-white p-6 rounded shadow-md w-full max-w-sm">
    <h2 class="text-xl font-bold mb-4 text-center">Forgot Password</h2>

    <?= $message ?>

    <form method="POST" class="space-y-4" novalidate>
      <div>
        <label class="block text-sm font-medium">Email</label>
        <input type="email" name="email" class="w-full px-3 py-2 border rounded" required autofocus>
      </div>

      <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white py-2 w-full rounded">
        Send Reset Link
      </button>
    </form>
  </div>
</body>
</html>
