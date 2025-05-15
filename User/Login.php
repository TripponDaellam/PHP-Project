<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - Method Flow</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
  <div class="bg-white shadow p-6 rounded w-full max-w-sm">
    <h2 class="text-xl font-bold mb-4">Login</h2>
  <form method="POST" action="login_process.php">
  <input type="text" name="username" placeholder="Username" class="w-full mb-3 px-4 py-2 border rounded" required>
  <input type="password" name="password" placeholder="Password" class="w-full mb-1 px-4 py-2 border rounded" required>

  <p class="text-right text-sm mb-3">
    <a href="ForgotPass.php" class="text-orange-500 hover:underline">Forgot Password?</a>
  </p>

  <button type="submit" class="bg-orange-500 text-white px-4 py-2 w-full rounded hover:bg-orange-600">Login</button>
</form>
    <p class="mt-4 text-sm text-gray-600">Don't have an account? <a href="signup.php" class="text-orange-500 hover:underline">Sign up</a></p>
  </div>
</body>
</html>
