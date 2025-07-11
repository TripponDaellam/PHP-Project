<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sign Up</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
  <div class="bg-white p-10 rounded-lg shadow-lg w-full max-w-lg">
    <h2 class="text-2xl font-bold text-center text-orange-500 mb-6">Create Your Account</h2>

    <form method="POST" action="../Controller/adminSignupController.php" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
        <input type="text" name="username" class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-orange-400" required>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
        <input type="email" name="email" class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-orange-400" required>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
        <input type="password" name="password" class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-orange-400" required>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
        <input type="password" name="confirm_password" class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-orange-400" required>
      </div>    

      <button type="submit" class="bg-orange-500 text-white w-full py-2 rounded hover:bg-orange-600 transition duration-200">
        Sign Up
      </button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-600">
      Already have an account? <a href="login.php" class="text-orange-500 hover:underline">Log in</a>
    </p>
  </div>
  <script>
  function togglePassword() {
    const passwordInput = document.getElementById("password");
    const toggleButton = event.currentTarget;

    if (passwordInput.type === "password") {
      passwordInput.type = "text";
      toggleButton.textContent = "Hide";
    } else {
      passwordInput.type = "password";
      toggleButton.textContent = "Show";
    }
  }
</script>
</body>
</html>
