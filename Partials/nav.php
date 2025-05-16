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
  <a href="#" class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600">Ask</a>
  <a href="/notifications" class="text-gray-700 hover:text-orange-500">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
      stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
      <path stroke-linecap="round" stroke-linejoin="round"
        d="M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 
           2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 
           2.25 0 0 1 2.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 0 0 2.25 
           2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 
           5.338a2.25 2.25 0 0 0-2.15-1.588H6.911a2.25 2.25 0 0 0-2.15 
           1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661Z" />
</svg>

  </a>
  <a href="" class="text-gray-700 hover:text-orange-500">  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
   <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
   <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
</svg></a>
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

 
    <div x-show="open" x-transition class="mt-3 md:hidden space-y-3">
  <input
    type="text"
    placeholder="Search..."
    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-400"
  >

  <a href="#" class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600 block w-full text-center">Ask</a>

  <a href="/notifications" class="flex items-center text-gray-700 hover:text-orange-500 space-x-2">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
      stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
      <path stroke-linecap="round" stroke-linejoin="round"
        d="M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 
           2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 
           2.25 0 0 1 2.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 0 0 2.25 
           2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 
           5.338a2.25 2.25 0 0 0-2.15-1.588H6.911a2.25 2.25 0 0 0-2.15 
           1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661Z" />
    </svg>
    <span>Notifications</span>
  </a>

  <a href="#" class="flex items-center text-gray-700 hover:text-orange-500 space-x-2">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
      stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
      <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
      <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
    </svg>
    <span>Settings</span>
  </a>

  <?php if (isset($_SESSION['user_id'])): ?>
    <div class="flex items-center space-x-2">
      <img src="https://via.placeholder.com/32" class="rounded-full w-8 h-8" alt="User">
      <span class="text-gray-700">Profile</span>
    </div>
  <?php else: ?>
    <a href="User/Login.php" class="block text-orange-600 hover:underline">Login</a>
    <a href="User/SignUp.php" class="block bg-orange-500 text-white px-3 py-1 rounded hover:bg-orange-600 text-center">Sign Up</a>
  <?php endif; ?>
</div>

  </nav>

</body>
</html>
