<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Forgot Password - Method Flow</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
  <div class="bg-white shadow p-6 rounded w-full max-w-sm">
    <h2 class="text-xl font-bold mb-4 text-center">Reset Your Password</h2>
    
    <form method="POST" action="forgot_password_process.php">
      <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
      <input
        type="email"
        name="email"
        id="email"
        placeholder="you@example.com"
        class="w-full mb-4 px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-orange-400"
        required
      >

      <button type="submit" class="bg-orange-500 text-white px-4 py-2 w-full rounded hover:bg-orange-600">
        Send Reset Link
      </button>
    </form>

    <p class="mt-4 text-sm text-gray-600 text-center">
      <a href="login.php" class="text-orange-500 hover:underline">Back to Login</a>
    </p>
  </div>
</body>
</html>
