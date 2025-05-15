<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Responsive Stack Overflow Navbar</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/alpinejs" defer></script>
</head>
<body class="bg-white text-black">

  <nav x-data="{ open: false }" class="fixed bg-white top-0 left-0 w-full shadow px-6 py-3">
    <div class="flex items-center justify-between">

      <div class="flex items-center space-x-3">
        <img src="" alt="Logo" class="w-8 h-8">
        <span class="text-xl font-semibold">Method Flow</span>
      </div>

      <div class="hidden md:flex flex-1 items-center mx-6 space-x-6">
        <input
          type="text"
          placeholder="Search..."
          class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-400"
        >
        <a href="#" class="text-gray-700 hover:text-orange-500">About</a>
        <a href="#" class="text-gray-700 hover:text-orange-500">Subjects</a>
        <a href="#" class="text-gray-700 hover:text-orange-500">Ask</a>
      </div>

  
    <div class="hidden md:flex items-center space-x-4">
  <?php if (isset($_SESSION['user_id'])): ?>
    <img src="https://via.placeholder.com/32" class="rounded-full w-8 h-8" alt="User">
  <?php else: ?>
    <a href="User/Login.php" class="text-orange-600 hover:underline">Login</a>
    <a href="User/SignUp.php" class="bg-orange-500 text-white px-3 py-1 rounded hover:bg-orange-600">Sign Up</a>
  <?php endif; ?>
</div>

 
      <div class="md:hidden">
        <button @click="open = !open" class="text-gray-700 focus:outline-none">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2"
               viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
            <path x-show="!open" d="M4 6h16M4 12h16M4 18h16" />
            <path x-show="open" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>

 
    <div x-show="open" class="mt-3 md:hidden space-y-3">
      <input
        type="text"
        placeholder="Search..."
        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-400"
      >
      <a href="#" class="block text-gray-700 hover:text-orange-500">About</a>
      <a href="#" class="block text-gray-700 hover:text-orange-500">Products</a>
      <a href="#" class="block text-gray-700 hover:text-orange-500">Teams</a>
      <div>
        <img src="" class="rounded-full w-8 h-8" alt="User">
      </div>
    </div>
  </nav>

</body>
</html>
