<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sign Up - Method Flow</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
  <div class="bg-white shadow p-6 rounded w-full max-w-sm">
    <h2 class="text-xl font-bold mb-4">Sign Up</h2>
    <form method="POST" action="signup_process.php">
      <input type="text" name="username" placeholder="Username" class="w-full mb-3 px-4 py-2 border rounded" required>
      <input type="email" name="email" placeholder="Email" class="w-full mb-3 px-4 py-2 border rounded" required>
      <input type="password" name="password" placeholder="Password" class="w-full mb-3 px-4 py-2 border rounded" required>
      <button type="submit" class="bg-orange-500 text-white px-4 py-2 w-full rounded hover:bg-orange-600">Sign Up</button>
    </form>
    <p class="mt-4 text-sm text-gray-600">Already have an account? <a href="login.php" class="text-orange-500 hover:underline">Login</a></p>
  </div>
</body>
</html>
