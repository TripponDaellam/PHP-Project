<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/../DBConnection/DBConnector.php';

$userId = $_SESSION['user_id'] ?? null;
$profileImage = '';

if ($userId) {
  $stmt = $pdo->prepare("SELECT profile_image FROM users WHERE id = :id");
  $stmt->execute(['id' => $userId]);
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
  $profileImage = $result['profile_image'] ?? '';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>Stack Overflow Navbar</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/alpinejs" defer></script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <style>
    [x-cloak] {
      display: none !important;
    }
  </style>
</head>

<body class="bg-white text-black font-poppins">

  <nav x-data="{ open: false }" class="fixed bg-white top-0 left-0 w-full px-6 py-2 z-50 h-14 shadow flex items-center justify-between">
    <!-- Logo -->
    <div class="flex items-center">
      <img src="../Logo_Light-removebg-preview.png" alt="Logo" class="w-16 h-16" />
      <span class="text-2xl font-semibold pl-2">Method Flow</span>
    </div>

    <!-- Desktop Right Side -->
    <div class="hidden md:flex items-center space-x-6">

      <!-- Search Bar (Desktop) -->
      <div class="relative">
        <input
          type="text"
          placeholder="Search..."
          class="w-[200px] lg:w-[500px] px-3 py-2 border border-gray-200 rounded-md focus:outline-none focus:border-orange-400 text-xs bg-white placeholder-gray-700" />
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-700 w-4 h-4"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
          stroke-width="2">
          <circle cx="11" cy="11" r="7" stroke-linecap="round" stroke-linejoin="round" />
          <line x1="21" y1="21" x2="16.65" y2="16.65" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      </div>

      <a href="ask.php" class="bg-orange-600 text-white px-4 py-1.5 rounded-md hover:bg-orange-700 text-sm">Ask</a>

      <!-- Notification Dropdown -->
      <div class="relative" x-data="{ open: false }">
        <button @click="open = !open" class="cursor-pointer text-gray-700 hover:text-orange-500 pt-1" aria-label="Toggle Notifications">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 
              2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 
              2.25 0 0 1 2.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 0 0 2.25 
              2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 
              5.338a2.25 2.25 0 0 0-2.15-1.588H6.911a2.25 2.25 0 0 0-2.15 
              1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661Z" />
          </svg>
        </button>

        <div
          x-show="open"
          x-cloak
          @click.outside="open = false"
          x-transition
          class="absolute right-0 mt-2 w-64 bg-white border border-gray-200 rounded-lg shadow-lg z-50">
          <div class="p-4 border-b font-semibold text-gray-800">Notifications</div>
          <ul class="max-h-60 overflow-y-auto">
            <li class="px-4 py-2 hover:bg-gray-100 cursor-pointer text-sm text-gray-700">🔔 New comment on your post</li>
            <li class="px-4 py-2 hover:bg-gray-100 cursor-pointer text-sm text-gray-700">📦 Your order has been shipped</li>
            <li class="px-4 py-2 hover:bg-gray-100 cursor-pointer text-sm text-gray-700">✅ Your profile was updated</li>
          </ul>
          <div class="text-center p-2 border-t">
            <a href="/notifications" class="text-sm text-orange-500 hover:underline">View all</a>
          </div>
        </div>
      </div>

      <!-- Settings Icon -->
      <a href="../User/profile.php#setting" class="text-gray-700 hover:text-orange-500" aria-label="Settings">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
        </svg>
      </a>

      <!-- Auth Profile / Login -->
      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="../User/profile.php">
          <?php if (empty($profileImage)): ?>
            <!-- Default Profile Icon -->
            <svg
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke-width="1.5"
              stroke="currentColor"
              class="size-6 w-10 h-10">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 
              7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 
              0-5.216-.584-7.499-1.632Z" />
            </svg>
          <?php else: ?>
            <img src="<?php echo htmlspecialchars($profileImage); ?>" class="w-10 h-10 rounded-full border-2 border-gray-100" alt="Profile" />
          <?php endif; ?>
        </a>
      <?php else: ?>
        <div class="pl-3">
          <a href="User/Login.php" class="text-orange-600 text-sm hover:underline pr-5">Login</a>
          <a
            href="User/SignUp.php"
            class="border-2 border-gray-300 text-black px-4 py-1.5 rounded-md hover:bg-orange-600 hover:text-white hover:border-orange-600 text-sm">Sign Up</a>
        </div>
      <?php endif; ?>
    </div>

    <!-- Mobile Icons Grouped: Search, Notification, Settings, Hamburger -->
    <div class="md:hidden flex items-center space-x-4">

      <!-- Mobile Search Dropdown -->
      <div x-data="{ openSearch: false }" class="relative">
        <button @click="openSearch = !openSearch" class="text-gray-700 focus:outline-none pt-2" aria-label="Toggle Search">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
            <circle cx="11" cy="11" r="7" stroke-linecap="round" stroke-linejoin="round" />
            <line x1="21" y1="21" x2="16.65" y2="16.65" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </button>

        <div
          x-show="openSearch"
          x-cloak
          x-transition
          @click.outside="openSearch = false"
          class="absolute -right-[210px] mt-3 min-w-[470px] p-2 bg-white border border-gray-300 rounded shadow-lg"
          style="display: none">
          <input
            type="text"
            placeholder="Search..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-400 text-sm" />
        </div>
      </div>

      <a href="ask.php" class="bg-orange-600 text-white px-4 py-1.5 rounded-md hover:bg-orange-700 text-sm">Ask</a>

      <!-- Mobile Notification Dropdown -->
      <div x-data="{ openNoti: false }" class="relative">
        <button @click="openNoti = !openNoti" class="text-gray-700 focus:outline-none pt-2" aria-label="Toggle Notifications">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 
              2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 
              2.25 0 0 1 2.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 0 0 2.25 
              2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 
              5.338a2.25 2.25 0 0 0-2.15-1.588H6.911a2.25 2.25 0 0 0-2.15 
              1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661Z" />
          </svg>
        </button>

        <div
          x-show="openNoti"
          x-cloak
          @click.outside="openNoti = false"
          x-transition
          class="absolute right-0 mt-3 w-64 bg-white border border-gray-300 rounded shadow-lg z-50"
          style="display: none">
          <div class="p-4 border-b font-semibold text-gray-800">Notifications</div>
          <ul class="max-h-60 overflow-y-auto">
            <li class="px-4 py-2 hover:bg-gray-100 cursor-pointer text-sm text-gray-700">🔔 New comment on your post</li>
            <li class="px-4 py-2 hover:bg-gray-100 cursor-pointer text-sm text-gray-700">📦 Your order has been shipped</li>
            <li class="px-4 py-2 hover:bg-gray-100 cursor-pointer text-sm text-gray-700">✅ Your profile was updated</li>
          </ul>
          <div class="text-center p-2 border-t">
            <a href="/notifications" class="text-sm text-orange-500 hover:underline">View all</a>
          </div>
        </div>
      </div>

      <!-- Mobile Settings Icon -->
      <a href="../User/profile.php#setting" class="text-gray-700 hover:text-orange-500 pt-1" aria-label="Settings">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
        </svg>
      </a>

      <!-- Mobile Hamburger -->
      <button @click="open = !open" class="text-gray-700 focus:outline-none pt-1" aria-label="Toggle Menu">
        <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
        <svg x-show="open" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 left-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

    <!-- Mobile Slide-in Sidebar -->
    <div
      x-show="open"
      x-transition:enter="transition transform duration-300"
      x-transition:enter-start="translate-x-full"
      x-transition:enter-end="translate-x-0"
      x-transition:leave="transition transform duration-300"
      x-transition:leave-start="translate-x-0"
      x-transition:leave-end="translate-x-full"
      x-cloak
      class="fixed top-0 right-0 h-full w-64 bg-white shadow-lg z-50 md:hidden">
      <div class="p-6 space-y-4 text-md">
        <div class="flex justify-end">
          <button @click="open = false" class="mb-4 text-gray-500" aria-label="Close Menu">
            <!-- Close icon -->
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <a href="../index.php" class="text-orange-600 block pl-4">Home</a>
        <a href="../Question.php" class="text-orange-600 block pl-4">Questions</a>
        <a href="../tag.php" class="text-orange-600 block pl-4">Tags</a>
        <a href="#" class="text-orange-600 block pl-4">Saved</a>
        <a href="#" class="text-orange-600 block pl-4">Community</a>
        <a href="#" class="text-orange-600 block pb-4 border-b pl-4">About</a>

        <?php if ($userId): ?>
          <a href="../User/profile.php" class="text-gray-700 block text-md pl-4">Profile</a>
        <?php else: ?>
          <a href="User/Login.php" class="text-orange-600 flex justify-center">Login</a>
          <a href="User/SignUp.php" class="bg-orange-600 text-white text-center py-2 rounded block hover:bg-orange-700 text-sm">Sign Up</a>
        <?php endif; ?>
      </div>
    </div>
  </nav>
</body>

</html>