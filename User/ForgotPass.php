<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../DBConnection/DBConnector.php';
require '../vendor/autoload.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"];
    $token = bin2hex(random_bytes(16));
    $token_hash = hash("sha256", $token);
    $expiry = date("Y-m-d H:i:s", time() + 60 * 30);

    // Save token to DB
    $sql = "UPDATE users SET reset_token = ?, token_expire = ? WHERE email = ?"; 
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$token_hash, $expiry, $email]);

    if ($stmt->rowCount() > 0) {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'methodflow12@gmail.com';
            $mail->Password = 'qktravxqntevamnt';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;
            $mail->SMTPDebug = 0;

            $mail->setFrom('methodflow12@gmail.com', 'Method Flow');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Reset your Method Flow password';
           $mail->Body = "Click the link below to reset your password:<br><br>
<a href='http://localhost/User/resetPassword.php?token=$token&email=$email'>
Reset Password</a>";


            $mail->send();
            $message = "<p class='text-green-600'>Reset link sent to your email.</p>";
        } catch (Exception $e) {
            $message = "<p class='text-red-600'>Mailer Error: {$mail->ErrorInfo}</p>";
        }
    } else {
        $message = "<p class='text-red-600'>Email not found in our system.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Forgot Password - Method Flow</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
  <div class="bg-white p-6 rounded shadow-md w-full max-w-sm">
    <h2 class="text-xl font-bold mb-4 text-center">Forgot Password</h2>

    <?php if (!empty($message)) echo $message; ?>

    <form method="POST" class="space-y-4">
      <div>
        <label class="block text-sm font-medium">Email</label>
        <input type="email" name="email" class="w-full px-3 py-2 border rounded" required>
      </div>

      <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white py-2 w-full rounded">
        Send Reset Link
      </button>
    </form>
  </div>
</body>
</html>
