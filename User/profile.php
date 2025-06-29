<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: Login.php");
  exit();
}

require_once '../DBConnection/DBConnector.php';

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$savedStmt = $pdo->prepare("
    SELECT q.id, q.title, q.description, q.created_at
    FROM savedQuestions s
    JOIN questions q ON s.question_id = q.id
    WHERE s.user_id = ?
    ORDER BY s.created_at DESC
");
$savedStmt->execute([$user_id]);
$savedQuestions = $savedStmt->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM questions WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$userPosts = $stmt->fetchAll();

//save
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Fetch saved questions
$savedStmt = $pdo->prepare("
    SELECT q.id, q.title, q.description, q.created_at
    FROM savedQuestions s
    JOIN questions q ON s.question_id = q.id
    WHERE s.user_id = ?
    ORDER BY s.created_at DESC
");
$savedStmt->execute([$user_id]);
$savedQuestions = $savedStmt->fetchAll();

//posts
$user_id = $_SESSION['user_id'];

// Fetch user posts
$stmt = $pdo->prepare("SELECT * FROM questions WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$userPosts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en" class="">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>My Profile</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
    };
  </script>
  <style>
    .tab-content {
      display: none;
    }

    .tab-content.active {
      display: block;
    }
  </style>
</head>

<body class="bg-gray-100 text-black pt-20">
  <?php include '../Partials/nav.php'; ?>
  <div class="flex flex-col lg:flex-row min-h-screen bg-gray-100">
    <aside class="hidden lg:block fixed top-16 left-0 h-[calc(100%-0rem)] w-[200px] bg-white z-10 shadow">
      <?php include '../Partials/left_nav.php'; ?>
    </aside>

    <main class="flex-1 min-w-full md:min-w-[500px] max-w-screen-full ml-[230px] lg:mr-10 p-4 overflow-x-auto bg-white">

      <!-- Profile Header -->
      <section class="flex flex-col gap-4 pb-5 pt-5">
        <!-- Avatar Upload -->
        <div class="flex flex-row">
          <form id="avatarForm" action="../Controller/uploadPhoto.php" method="POST" enctype="multipart/form-data">
            <input type="file" id="profileInput" name="profile_image" accept="image/*" class="hidden" onchange="document.getElementById('avatarForm').submit();">
            <div class="cursor-pointer pl-5" onclick="document.getElementById('profileInput').click();">
              <?php if (!empty($user['profile_image'])): ?>
                <img src="../uploads/<?= htmlspecialchars($user['profile_image']) ?>"
                  alt="Profile" class="w-28 h-28 rounded-full object-cover border-4 border-gray-100 shadow" />
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

          <h2 class="text-2xl font-semibold text-gray-800 pt-10 pl-5"><?= htmlspecialchars($user['username']) ?></h2>
        </div>

        <!-- Tabs -->
        <div class="overflow-x-auto px-5">
          <div class="flex flex-nowrap md:flex-wrap justify-start md:justify-left space-x-4 mt-2 border-b w-full">
            <button class="tab-button whitespace-nowrap text-sm text-gray-600 py-2 px-4 border-b-2 border-transparent hover:border-orange-500" onclick="showTab('info')">Profile Info</button>
            <button class="tab-button whitespace-nowrap text-sm text-gray-600 py-2 px-4 border-b-2 border-transparent hover:border-orange-500" onclick="showTab('post')">Post</button>
            <button class="tab-button whitespace-nowrap text-sm text-gray-600 py-2 px-4 border-b-2 border-transparent hover:border-orange-500" onclick="showTab('save')">Save</button>
            <button class="tab-button whitespace-nowrap text-sm text-gray-600 py-2 px-4 border-b-2 border-transparent hover:border-orange-500" onclick="showTab('about')">About Me</button>
            <button class="tab-button whitespace-nowrap text-sm text-gray-600 py-2 px-4 border-b-2 border-transparent hover:border-orange-500" onclick="showTab('links')">Links</button>
            <button class="tab-button whitespace-nowrap text-sm text-gray-600 py-2 px-4 border-b-2 border-transparent hover:border-orange-500" onclick="showTab('edit')">Edit Profile</button>
            <button class="tab-button whitespace-nowrap text-sm text-gray-600 py-2 px-4 border-b-2 border-transparent hover:border-orange-500" onclick="showTab('setting')">Setting</button>
          </div>
        </div>
      </section>

      <!-- Tabs Content -->
      <div id="info" class="tab-content active bg-white shadow rounded-xl p-6 space-y-4 mx-5">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Profile Information</h3>
        <div class="bg-red-50 p-4 rounded-md border">
          <div>
            <label class="text-sm text-gray-500">Full Name</label>
            <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name'] ?? 'N/A') ?>" class="w-full border rounded px-3 py-2 mt-1 mb-3" />
          </div>
          <div>
            <label class="text-sm text-gray-500">Email</label>
            <input type="text" name="email" value="<?= htmlspecialchars($user['email'] ?? 'N/A') ?>" class="w-full border rounded px-3 py-2 mt-1 mb-3" />
          </div>
          <div>
            <label class="text-sm text-gray-500">Phone</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? 'N/A') ?>" class="w-full border rounded px-3 py-2 mt-1 mb-3" />
          </div>
          <div>
            <label class="text-sm text-gray-500">Password</label>
            <input type="text" name="password" value="" class="w-full border rounded px-3 py-2 mt-1 mb-3" />
          </div>
        </div>
        <a href="change_password.php" class="inline-block bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded">Change Password</a>
      </div>

      <div id="about" class="tab-content bg-white shadow rounded-xl p-6 space-y-4 mx-5">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">About Me</h3>
        <p class="text-gray-700 text-sm leading-relaxed">
          <?= nl2br(htmlspecialchars($user['about_me'] ?? 'No bio provided.')) ?>
        </p>
      </div>
      <div id="post" class="tab-content bg-white shadow rounded-xl p-6">
  <h3 class="text-xl font-semibold text-gray-800 mb-4">Your Posts</h3>

  <?php if (count($userPosts) > 0): ?>
    <ul class="space-y-4">
      <?php foreach ($userPosts as $post): ?>
        <li class="bg-gray-50 p-4 rounded shadow">
         <a href="../questionDetails.php?id=<?= $post['id'] ?>" class="text-lg font-semibold text-orange-600 hover:underline block">
  <?= htmlspecialchars($post['title']) ?>
</a>

          <p class="text-gray-600 text-sm mb-2"><?= htmlspecialchars(substr($post['description'], 0, 100)) ?>...</p>
          <div class="flex justify-between items-center text-sm">
            <span class="text-gray-500"><?= date("F j, Y", strtotime($post['created_at'])) ?></span>
            <form method="POST" action="../Controller/deletePostController.php" onsubmit="return confirm('Are you sure you want to delete this post?');">
              <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
              <button type="submit" class="text-red-600 hover:underline">Delete</button>
            </form>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <p class="text-gray-500 mt-4">No posts available yet.</p>
  <?php endif; ?>
</div>

<div id="save" class="tab-content bg-white shadow rounded-xl p-6">
  <h3 class="text-xl font-semibold text-gray-800 mb-4">Saved Items</h3>

  <?php if (count($savedQuestions) > 0): ?>
    <div class="space-y-4">
      <?php foreach ($savedQuestions as $question): ?>
        <div class="border border-gray-200 rounded-lg p-4 shadow-sm">
          <h4 class="text-lg font-medium text-orange-600"><?= htmlspecialchars($question['title']) ?></h4>
          <p class="text-gray-700 mt-1"><?= htmlspecialchars($question['description']) ?></p>
          <p class="text-gray-400 text-xs mt-2">Saved on <?= date('F j, Y', strtotime($question['created_at'])) ?></p>
          <a href="../questionDetails.php?id=<?= $question['id'] ?>" class="text-sm text-indigo-500 hover:underline mt-2 inline-block">View Question</a>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <p class="text-gray-500">No saved items available yet.</p>
  <?php endif; ?>
</div>

      <div id="links" class="tab-content bg-white shadow rounded-xl p-6 space-y-4 mx-5">
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

      <div id="edit" class="tab-content bg-white shadow rounded-xl p-6 space-y-4 mx-5">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Edit Profile</h3>
        <form action="../Controller/updateProfile.php" method="POST" class="space-y-4">
          <div>
            <label class="text-sm text-gray-500">Username</label>
            <input type="text" name="username" value="<?= htmlspecialchars($user['username'] ?? 'N/A') ?>" class="w-full border rounded px-3 py-2 mt-1" />
          </div>
          <div>
            <label class="text-sm text-gray-500">Full Name</label>
            <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name'] ?? 'N/A') ?>" class="w-full border rounded px-3 py-2 mt-1" />
          </div>
          <div>
            <label class="text-sm text-gray-500">Phone</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? 'N/A') ?>" class="w-full border rounded px-3 py-2 mt-1" disabled/>
          </div>
          <div>
            <label class="text-sm text-gray-500">Email</label>
            <input type="text" name="email" value="<?= htmlspecialchars($user['email'] ?? 'N/A') ?>" class="w-full border rounded px-3 py-2 mt-1" disabled/>
          </div>
          <div>
            <label class="text-sm text-gray-500">About Me</label>
            <textarea name="about_me" rows="4" class="w-full border rounded px-3 py-2 mt-1"><?= htmlspecialchars($user['about_me'] ?? 'N/A') ?></textarea>
          </div>
          <div>
            <label class="text-sm text-gray-500">Website</label>
            <input type="url" name="website" value="<?= htmlspecialchars($user['website'] ?? 'N/A') ?>" class="w-full border rounded px-3 py-2 mt-1" />
          </div>
          <div>
            <label class="text-sm text-gray-500">GitHub</label>
            <input type="url" name="github" value="<?= htmlspecialchars($user['github'] ?? 'N/A') ?>" class="w-full border rounded px-3 py-2 mt-1" />
          </div>
          <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded">Save Changes</button>
        </form>
      </div>

      <div id="setting" class="tab-content bg-white shadow rounded-xl p-6 space-y-4 mx-5">
        <!-- Theme Toggle -->
        <div class="flex items-center justify-between p-4 border rounded bg-white">
          <div>
            <h3 class="text-lg font-medium text-gray-800">Theme</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Toggle between Light and Dark mode</p>
          </div>

          <!-- Light Mode / Dark Mode -->
          <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" id="toggle-dark" class="sr-only peer" />
            <div class="w-16 h-9 bg-gray-300 rounded-full peer-focus:outline-none dark:bg-gray-700 peer-checked:bg-yellow-400 transition-colors duration-300 relative">
            </div>

            <!-- Sun Icon -->
            <svg class="absolute left-1 w-7 h-7 text-yellow-500 transition-opacity 
             duration-300 peer-checked:opacity-0" xmlns="http://www.w3.org/2000/svg" fill="none"
              viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M12 3v2m6.364.636l-1.414 1.414M21 12h-2M18.364 18.364l-1.414-1.414M12 21v-2m-4.95-.636l-1.414 1.414M3 12h2m1.222-6.364l1.414 1.414M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
            </svg>

            <!-- Moon Icon -->
            <svg class="absolute right-1 w-7 h-7 text-gray-100 opacity-0 peer-checked:opacity-100 transition-opacity duration-300" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
              viewBox="0 0 20 20">
              <path d="M17.293 13.293a8 8 0 01-10.586-10.586A8 8 0 1017.293 13.293z" />
            </svg>
          </label>
        </div>

        <!-- Delete -->
        <div class="flex flex-row space-x-4 mt-4">
          <form action="../Controller/deleteAccount.php" method="POST" onsubmit="return confirm('Are you sure you want to delete your account?');">
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded w-full sm:w-auto">
              Delete Account
            </button>
          </form>
          <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded w-full sm:w-auto" onclick="confirmLogout(event)">
            Log Out
          </button>
        </div>
    </main>
  </div>

  <!-- Tabs Script -->
  <script>
    function showTab(tabId, event) {
      document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
      document.getElementById(tabId).classList.add('active');

      document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('border-orange-500', 'text-orange-600'));
      if (event) {
        event.target.classList.add('border-orange-500', 'text-orange-600');
      }
    }

    // Add event listeners to tab buttons after DOM loaded
    document.querySelectorAll('.tab-button').forEach(btn => {
      btn.addEventListener('click', e => {
        showTab(btn.getAttribute('onclick').match(/'(\w+)'/)[1], e);
      });
    });

    // Dark mode toggle setup
    const toggle = document.getElementById('toggle-dark');
    const root = document.documentElement;

    // Initialize based on localStorage
    if (localStorage.theme === 'dark') {
      root.classList.add('dark');
      toggle.checked = true;
    } else {
      root.classList.remove('dark');
      toggle.checked = false;
    }

    toggle.addEventListener('change', () => {
      root.classList.toggle('dark');
      localStorage.theme = root.classList.contains('dark') ? 'dark' : 'light';
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
