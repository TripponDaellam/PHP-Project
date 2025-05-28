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
  <meta charset="UTF-8">
  <title>Responsive Stack Overflow Navbar</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/alpinejs" defer></script>
</head>

<body class="bg-white text-black">

  <nav x-data="{ open: false }" class="fixed bg-white top-0 left-0 w-full shadow px-6 py-4 z-50">
    <div class="flex items-center justify-between">

      <!-- Logo -->
      <div class="flex items-center space-x-3">
        <img src="" alt="Logo" class="w-8 h-8">
        <span class="text-lg font-semibold">Method Flow</span>
      </div>

      <!-- Desktop Menu -->
      <!-- <div class="hidden md:flex items-center space-x-3">
        <a href="../index.php" class="text-orange-600 text-sm px-2">Home</a>
        <a href="../Question.php" class="text-orange-600 text-sm px-2">Questions</a>
        <a href="../tag.php" class="text-orange-600 text-sm px-2">Tags</a>
        <a href="#" class="text-orange-600 text-sm px-2">Saved</a>
        <a href="#" class="text-orange-600 text-sm px-2">Community</a>
        <a href="#" class="text-orange-600 text-sm px-2">About</a>
      </div> -->

      <!-- Right Side (Ask, Icons, Auth Buttons) -->
      <div class="hidden md:flex items-center space-x-6">
        <!-- Search Bar (Desktop) -->
        <div class="relative hidden md:block">
          <input
            type="text"
            placeholder="Search..."
            class="w-[200px] lg:w-[500px] px-4 py-1.5 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-400 text-sm">
          <svg xmlns="http://www.w3.org/2000/svg"
            class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4"
            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1 0 6.5 6.5a7.5 7.5 0 0 0 10.6 10.6Z" />
          </svg>
        </div>
        <a href="#" class="bg-orange-500 text-white px-4 py-1.5 rounded hover:bg-orange-600 text-sm">Ask</a>

        <!-- Notifications Icon -->
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

        <!-- Settings Icon -->
        <a href="#" class="text-gray-700 hover:text-orange-500">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
          </svg>
        </a>

        <!-- Auth Profile / Login -->
        <?php if (isset($_SESSION['user_id'])): ?>
          <a href="../User/profile.php">
            <!-- <div class="flex items-center space-x-2 hover:text-orange-500 bg-black"> -->
            <?php if (empty($profileImage)): ?>
              <!-- Default Profile Icon -->
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 
              7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 
              0-5.216-.584-7.499-1.632Z" />
              </svg>
            <?php else: ?>
              <img src="<?php echo htmlspecialchars($profileImage); ?>" class="w-8 h-8 rounded-full border-2 border-orange-500" alt="Profile">
            <?php endif; ?>
            <!-- </div> -->
          </a>
        <?php else: ?>
          <div class="pl-3">
            <a href="User/Login.php" class="text-orange-600 text-sm hover:underline pr-2">Login</a>
            <a href="User/SignUp.php" class="bg-orange-500 text-white px-3.5 py-2 rounded hover:bg-orange-600 text-sm">Sign Up</a>
          </div>
        <?php endif; ?>
      </div>

      <!-- Mobile Toggle Button -->
      <div class="md:hidden">
        <button @click="open = !open" class="text-gray-700 focus:outline-none">
          <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" stroke="currentColor"
            viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
          <svg x-show="open" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" stroke="currentColor"
            viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Mobile Dropdown Menu -->
    <div x-show="open" x-transition class="md:hidden mt-4 px-6 space-y-4">
      <a href="../index.php" class="block text-orange-600 text-sm">Home</a>
      <a href="../Question.php" class="block text-orange-600 text-sm">Questions</a>
      <a href="../tag.php" class="block text-orange-600 text-sm">Tags</a>
      <a href="#" class="block text-orange-600 text-sm">Saved</a>
      <a href="#" class="block text-orange-600 text-sm">Community</a>
      <a href="#" class="block text-orange-600 text-sm">About</a>

      <a href="#" class="block bg-orange-500 text-white text-center py-2 rounded hover:bg-orange-600">Ask</a>

      <div class="flex space-x-4 items-center">
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
        <a href="#" class="text-gray-700 hover:text-orange-500">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
          </svg>
        </a>
      </div>

      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="../User/profile.php" class="block text-gray-700 hover:text-orange-500">Profile</a>
      <?php else: ?>
        <a href="User/Login.php" class="block text-orange-600 hover:underline">Login</a>
        <a href="User/SignUp.php" class="block bg-orange-500 text-white text-center py-2 rounded hover:bg-orange-600">Sign Up</a>
      <?php endif; ?>
    </div>
  </nav>

</body>

</html>