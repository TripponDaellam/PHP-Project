<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: Login.php");
  exit();
}

require_once '../DBConnection/DBConnector.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en" class="">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>My Profile</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .tab-content {
      display: none;
    }

    .tab-content.active {
      display: block;
    }
  </style>
</head>

<body class="bg-gray-50 text-black min-h-screen pt-20 overflow-x-hidden">

  <?php include '../Partials/nav.php'; ?>
  <aside class="hidden lg:block fixed top-[65px] left-0 h-[calc(100%-4rem)] w-[200px] bg-white z-10 shadow">
    <?php include '../Partials/left_nav.php'; ?>
  </aside>

  <main class="flex flex-col md:ml-[220px] px-4 sm:px-10 py-8 gap-8">

    <!-- Profile Header -->
    <section class="bg-white shadow-lg rounded-xl p-6 flex flex-col items-center gap-4">
      <!-- Avatar Upload -->
      <form id="avatarForm" action="../Controller/uploadPhoto.php" method="POST" enctype="multipart/form-data">
        <input type="file" id="profileInput" name="profile_image" accept="image/*" class="hidden" onchange="document.getElementById('avatarForm').submit();">
        <div class="cursor-pointer" onclick="document.getElementById('profileInput').click();">
          <?php if (!empty($user['profile_image'])): ?>
            <img src="../uploads/<?= htmlspecialchars($user['profile_image']) ?>"
              alt="Profile" class="w-28 h-28 rounded-full object-cover border-4 border-yellow-400 shadow" />
          <?php else: ?>
            <div class="w-28 h-28 rounded-full bg-yellow-400 flex items-center justify-center border-4 border-white shadow">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke-width="1.5" stroke="white" class="w-14 h-14">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 
                         0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 
                         9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
              </svg>
            </div>
          <?php endif; ?>
        </div>
      </form>

      <h2 class="text-xl font-semibold text-gray-800"><?= htmlspecialchars($user['username']) ?></h2>

      <!-- Tabs -->
      <div class="overflow-x-auto">
        <div class="flex flex-nowrap md:flex-wrap justify-start md:justify-center space-x-4 mt-4 border-b w-full px-4">
          <button class="tab-button whitespace-nowrap text-gray-600 py-2 px-4 border-b-2 border-transparent hover:border-orange-500" onclick="showTab('info')">Profile Info</button>
          <button class="tab-button whitespace-nowrap text-gray-600 py-2 px-4 border-b-2 border-transparent hover:border-orange-500" onclick="showTab('post')">Post</button>
          <button class="tab-button whitespace-nowrap text-gray-600 py-2 px-4 border-b-2 border-transparent hover:border-orange-500" onclick="showTab('save')">Save</button>
          <button class="tab-button whitespace-nowrap text-gray-600 py-2 px-4 border-b-2 border-transparent hover:border-orange-500" onclick="showTab('about')">About Me</button>
          <button class="tab-button whitespace-nowrap text-gray-600 py-2 px-4 border-b-2 border-transparent hover:border-orange-500" onclick="showTab('links')">Links</button>
          <button class="tab-button whitespace-nowrap text-gray-600 py-2 px-4 border-b-2 border-transparent hover:border-orange-500" onclick="showTab('edit')">Edit Profile</button>
          <button class="tab-button whitespace-nowrap text-gray-600 py-2 px-4 border-b-2 border-transparent hover:border-orange-500" onclick="showTab('setting')">Setting</button>
        </div>
      </div>

    </section>

    <!-- Tabs Content -->
    <div id="info" class="tab-content active bg-white shadow rounded-xl p-6 space-y-4">
      <h3 class="text-xl font-semibold text-gray-800 mb-4">Profile Information</h3>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-red-50 p-4 rounded-md border">
        <div>
          <p class="text-sm text-gray-500">Full Name</p>
          <p class="font-medium text-gray-800"><?= htmlspecialchars($user['full_name'] ?? 'N/A') ?></p>
        </div>
        <div>
          <p class="text-sm text-gray-500">Username</p>
          <p class="font-medium text-gray-800"><?= htmlspecialchars($user['username']) ?></p>
        </div>
        <div>
          <p class="text-sm text-gray-500">Email</p>
          <p class="font-medium text-gray-800"><?= htmlspecialchars($user['email']) ?></p>
        </div>
        <div>
          <p class="text-sm text-gray-500">Phone</p>
          <p class="font-medium text-gray-800"><?= htmlspecialchars($user['phone'] ?? 'N/A') ?></p>
        </div>
        <div>
          <p class="text-sm text-gray-500">Password</p>
          <p class="font-medium text-gray-800">************</p>
        </div>
      </div>
      <a href="change_password.php" class="inline-block bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded">Change Password</a>
    </div>

    <div id="about" class="tab-content bg-white shadow rounded-xl p-6">
      <h3 class="text-xl font-semibold text-gray-800 mb-4">About Me</h3>
      <p class="text-gray-700 text-sm leading-relaxed">
        <?= nl2br(htmlspecialchars($user['about_me'] ?? 'No bio provided.')) ?>
      </p>
    </div>

    <div id="links" class="tab-content bg-white shadow rounded-xl p-6 space-y-4">
      <h3 class="text-xl font-semibold text-gray-800 mb-4">Links</h3>
      <div>
        <p class="text-sm text-gray-500">Website</p>
        <p class="font-medium text-blue-600 underline"><?= htmlspecialchars($user['website'] ?? 'N/A') ?></p>
      </div>
      <div>
        <p class="text-sm text-gray-500">GitHub</p>
        <p class="font-medium text-blue-600 underline"><?= htmlspecialchars($user['github'] ?? 'N/A') ?></p>
      </div>
    </div>

    <div id="edit" class="tab-content bg-white shadow rounded-xl p-6">
      <h3 class="text-xl font-semibold text-gray-800 mb-4">Edit Profile</h3>
      <form action="../Controller/updateProfileInfo.php" method="POST" class="space-y-4">
        <div>
          <label class="block text-sm font-medium">Full Name</label>
          <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name'] ?? 'N/A') ?>" class="w-full border rounded px-3 py-2 mt-1" />
        </div>
        <div>
          <label class="block text-sm font-medium">Phone</label>
          <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? 'N/A') ?>" class="w-full border rounded px-3 py-2 mt-1" />
        </div>
        <div>
          <label class="block text-sm font-medium">About Me</label>
          <textarea name="about_me" rows="4" class="w-full border rounded px-3 py-2 mt-1"><?= htmlspecialchars($user['about_me'] ?? 'N/A') ?></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium">Website</label>
          <input type="url" name="website" value="<?= htmlspecialchars($user['website'] ?? 'N/A') ?>" class="w-full border rounded px-3 py-2 mt-1" />
        </div>
        <div>
          <label class="block text-sm font-medium">GitHub</label>
          <input type="url" name="github" value="<?= htmlspecialchars($user['github'] ?? 'N/A') ?>" class="w-full border rounded px-3 py-2 mt-1" />
        </div>
        <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded">Save Changes</button>
      </form>
    </div>
    <div id="setting" class="tab-content bg-white shadow rounded-xl p-6">
  
      <!-- Theme Toggle -->
      <div class="flex items-center justify-between p-4 border rounded bg-white dark:bg-gray-800">
        <div>
          <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200">Theme</h3>
          <p class="text-sm text-gray-500 dark:text-gray-400">Toggle between Light and Dark mode</p>
        </div>
        <!-- Light Mode / Dark Mode -->
        <label class="relative inline-flex items-center cursor-pointer">
          <input type="checkbox" value="" id="theme-toggle" class="sr-only peer">
          <div class="w-16 h-8 bg-gray-300 rounded-full peer-focus:outline-none dark:bg-gray-700 peer-checked:bg-yellow-400 transition-colors duration-300 relative">
          </div>
          <!-- Sun Icon -->
          <svg class="absolute left-1 top-1 w-6 h-6 text-yellow-500 transition-opacity 
             duration-300 peer-checked:opacity-0" xmlns="http://www.w3.org/2000/svg" fill="none"
            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
          </svg>

          <!-- Moon Icon -->
          <svg class="absolute right-1 top-1 w-6 h-6 text-gray-100 opacity-0 peer-checked:opacity-100 transition-opacity duration-300" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
            <path d="M17.293 13.293a8 8 0 01-10.586-10.586A8 8 0 1017.293 13.293z" />
          </svg>
        </label>
      </div>
      <!-- Logout -->
      <div class="p-4 border rounded bg-white dark:bg-gray-800">
        <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200 mb-2">Account</h3>
        <a href="#"
          onclick="confirmLogout(event)"
          class="block w-full text-center text-white bg-red-600 hover:bg-red-700 px-4 py-2 rounded transition">
          Log Out
        </a>
      </div>


      <!-- Delete -->
      <div>
        <form action="../Controller/deleteAccount.php" method="POST" onsubmit="return confirm('Are you sure you want to delete your account?');">
          <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded w-full sm:w-auto">
            Delete Account
          </button>
        </form>
      </div>

  </main>
  <!-- Tabs Script -->
  <script>
    function showTab(tabId) {
      document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
      document.getElementById(tabId).classList.add('active');

      document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('border-orange-500', 'text-orange-600'));
      event.target.classList.add('border-orange-500', 'text-orange-600');
    }
    const toggle = document.getElementById('theme-toggle');
    const html = document.documentElement;

    // Initialize based on localStorage
    if (localStorage.getItem('theme') === 'dark') {
      html.classList.add('dark');
      toggle.checked = true;
    }

    toggle.addEventListener('change', () => {
      if (toggle.checked) {
        html.classList.add('dark');
        localStorage.setItem('theme', 'dark');
      } else {
        html.classList.remove('dark');
        localStorage.setItem('theme', 'light');
      }
    });

    function confirmLogout(event) {
      event.preventDefault();
      if (confirm("Are you sure you want to log out?")) {
        window.location.href = "../Controller/logout.php";
      }
    }
  </script>
</body>

</html>