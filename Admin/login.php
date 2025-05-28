<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
  <div class="bg-white p-8 rounded-xl shadow-xl w-full max-w-md">
    <div class="mb-6 text-center">
      <h1 class="text-3xl font-bold text-orange-500">Admin Portal</h1>
      <p class="text-sm text-gray-600 mt-1">Sign in to your admin dashboard</p>
    </div>

    <?php if (isset($_SESSION['login_error'])): ?>
      <p class="text-red-500 text-sm mb-4 text-center"><?php echo $_SESSION['login_error']; unset($_SESSION['login_error']); ?></p>
    <?php endif; ?>

    <form method="POST" action="../Controller/adminLogInController.php" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
        <input type="text" name="username" class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-orange-400" required>
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
        <input type="password" name="password" class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-orange-400" required>
      </div>

      <div class="text-right">
        <a href="ForgotPass.php" class="text-sm text-orange-500 hover:underline">Forgot password?</a>
      </div>

      <button type="submit" class="bg-orange-500 text-white w-full py-2 rounded hover:bg-orange-600 transition duration-200">
        Login
      </button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-600">
      Not an admin? <a href="signup.php" class="text-orange-500 hover:underline">Sign up</a>
    </p>
  </div>
</body>
</html>
