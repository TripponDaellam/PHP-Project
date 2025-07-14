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
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>My Profile - Method Flow</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    .tab-content {
      display: none;
    }

    .tab-content.active {
      display: block;
    }
  </style>
</head>

<body class="bg-gray-50 text-gray-800 pt-16">
  <?php include '../Partials/nav.php'; ?>
  
  <aside class="hidden lg:block fixed top-16 left-0 h-[calc(100vh-4rem)] w-[200px] bg-white z-10 shadow-lg overflow-auto">
    <?php include '../Partials/left_nav.php'; ?>
  </aside>

  <main class="ml-0 lg:ml-[220px] p-6">
    <div class="max-w-6xl mx-auto">
      
      <!-- Profile Header -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 mb-6">
        <div class="flex items-start space-x-6">
          <!-- Profile Image -->
          <form id="avatarForm" action="../Controller/uploadPhoto.php" method="POST" enctype="multipart/form-data">
            <input type="file" id="profileInput" name="profile_image" accept="image/*" class="hidden" onchange="document.getElementById('avatarForm').submit();">
            <div class="cursor-pointer group" onclick="document.getElementById('profileInput').click();">
              <?php if (!empty($user['profile_image'])): ?>
                <div class="relative">
                  <img src="../uploads/<?= htmlspecialchars($user['profile_image']) ?>" alt="Profile" class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-lg" />
                  <div class="absolute inset-0 bg-black bg-opacity-50 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                    <i class="fas fa-camera text-white text-lg"></i>
                  </div>
                </div>
              <?php else: ?>
                <div class="relative w-24 h-24 rounded-full bg-gradient-to-r from-orange-400 to-orange-600 flex items-center justify-center border-4 border-white shadow-lg group-hover:from-orange-500 group-hover:to-orange-700 transition-all duration-200">
                  <i class="fas fa-user text-white text-2xl"></i>
                  <div class="absolute inset-0 bg-black bg-opacity-50 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                    <i class="fas fa-camera text-white text-lg"></i>
                  </div>
                </div>
              <?php endif; ?>
            </div>
          </form>

          <!-- User Info -->
          <div class="flex-1">
            <h1 class="text-3xl font-bold text-gray-900 mb-2"><?= htmlspecialchars($user['username']) ?></h1>
            <p class="text-gray-600 mb-4"><?= htmlspecialchars($user['email'] ?? 'No email provided') ?></p>
            
            <!-- Stats -->
            <div class="flex space-x-6">
              <div class="text-center">
                <div class="text-2xl font-bold text-orange-600"><?= count($userPosts) ?></div>
                <div class="text-sm text-gray-500">Questions</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-orange-600"><?= count($savedQuestions) ?></div>
                <div class="text-sm text-gray-500">Saved</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-orange-600">0</div>
                <div class="text-sm text-gray-500">Answers</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Navigation Tabs -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
        <div class="border-b border-gray-200">
          <nav class="flex space-x-8 px-6">
            <button class="tab-button py-4 px-1 border-b-2 border-transparent text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-colors duration-200" onclick="showTab('info')">
              <i class="fas fa-user mr-2"></i>Profile Info
            </button>
            <button class="tab-button py-4 px-1 border-b-2 border-transparent text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-colors duration-200" onclick="showTab('edit')">
              <i class="fas fa-edit mr-2"></i>Edit Profile
            </button>
            <button class="tab-button py-4 px-1 border-b-2 border-transparent text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-colors duration-200" onclick="showTab('post')">
              <i class="fas fa-question-circle mr-2"></i>My Questions
            </button>
            <button class="tab-button py-4 px-1 border-b-2 border-transparent text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-colors duration-200" onclick="showTab('setting')">
              <i class="fas fa-cog mr-2"></i>Settings
            </button>
          </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
          
          <!-- Profile Info Tab -->
          <div id="info" class="tab-content active">
            <h3 class="text-xl font-semibold text-gray-900 mb-6">Profile Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-gray-900">
                  <?= htmlspecialchars($user['full_name'] ?? 'Not provided') ?>
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-gray-900">
                  <?= htmlspecialchars($user['email'] ?? 'Not provided') ?>
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-gray-900">
                  <?= htmlspecialchars($user['phone'] ?? 'Not provided') ?>
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Website</label>
                <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-gray-900">
                  <?= htmlspecialchars($user['website'] ?? 'Not provided') ?>
                </div>
              </div>
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">GitHub</label>
                <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-gray-900">
                  <?= htmlspecialchars($user['github'] ?? 'Not provided') ?>
                </div>
              </div>
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">About Me</label>
                <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-gray-900 min-h-[100px]">
                  <?= htmlspecialchars($user['about_me'] ?? 'No bio provided.') ?>
                </div>
              </div>
            </div>
          </div>

          <!-- Edit Profile Tab -->
          <div id="edit" class="tab-content">
            <h3 class="text-xl font-semibold text-gray-900 mb-6">Edit Profile</h3>
            <form action="../Controller/updateProfile.php" method="POST" class="space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                  <input type="text" name="username" value="<?= htmlspecialchars($user['username'] ?? '') ?>" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-200" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                  <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-200" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Website</label>
                  <input type="url" name="website" value="<?= htmlspecialchars($user['website'] ?? '') ?>" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-200" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">GitHub</label>
                  <input type="url" name="github" value="<?= htmlspecialchars($user['github'] ?? '') ?>" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-200" />
                </div>
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-2">About Me</label>
                  <textarea name="about_me" rows="4" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-200 resize-vertical"><?= htmlspecialchars($user['about_me'] ?? '') ?></textarea>
                </div>
              </div>
              <div class="flex justify-end">
                <button type="submit" class="bg-gradient-to-r from-orange-500 to-orange-600 text-white px-6 py-3 rounded-lg hover:from-orange-600 hover:to-orange-700 transition-all duration-200 font-semibold shadow-sm hover:shadow-md">
                  <i class="fas fa-save mr-2"></i>Save Changes
                </button>
              </div>
            </form>
          </div>

          <!-- My Questions Tab -->
          <div id="post" class="tab-content">
            <h3 class="text-xl font-semibold text-gray-900 mb-6">My Questions</h3>
            <?php if (count($userPosts) > 0): ?>
              <div class="space-y-4">
                <?php foreach ($userPosts as $post): ?>
                  <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow duration-200">
                    <div class="flex items-start justify-between">
                      <div class="flex-1">
                        <a href="../questionDetails.php?id=<?= $post['id'] ?>" class="text-lg font-semibold text-orange-600 hover:text-orange-700 transition-colors duration-200 block mb-2">
                          <?= htmlspecialchars($post['title']) ?>
                        </a>
                        <p class="text-gray-600 text-sm mb-3"><?= htmlspecialchars(substr($post['description'], 0, 150)) ?>...</p>
                        <div class="flex items-center text-xs text-gray-500">
                          <i class="fas fa-calendar mr-1"></i>
                          <?= date("F j, Y", strtotime($post['created_at'])) ?>
                        </div>
                      </div>
                      <form method="POST" action="../Controller/deletePostController.php" onsubmit="return confirm('Are you sure you want to delete this question?');">
                        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                        <button type="submit" class="text-red-500 hover:text-red-700 transition-colors duration-200 p-2">
                          <i class="fas fa-trash"></i>
                        </button>
                      </form>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <div class="text-center py-12">
                <i class="fas fa-question-circle text-4xl text-gray-300 mb-4"></i>
                <p class="text-gray-500 text-lg">No questions posted yet.</p>
                <a href="../ask.php" class="inline-block mt-4 bg-orange-500 text-white px-6 py-2 rounded-lg hover:bg-orange-600 transition-colors duration-200">
                  <i class="fas fa-plus mr-2"></i>Ask Your First Question
                </a>
              </div>
            <?php endif; ?>
          </div>

          <!-- Settings Tab -->
          <div id="setting" class="tab-content">
            <h3 class="text-xl font-semibold text-gray-900 mb-6">Account Settings</h3>
            <div class="space-y-4">
              <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
                <h4 class="text-lg font-medium text-gray-900 mb-4">Account Actions</h4>
                <div class="flex flex-wrap gap-4">
                  <a href="change_password.php" class="inline-flex items-center bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                    <i class="fas fa-key mr-2"></i>Change Password
                  </a>
                  <button onclick="confirmLogout(event)" class="inline-flex items-center bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                    <i class="fas fa-sign-out-alt mr-2"></i>Log Out
                  </button>
                  <form action="../Controller/deleteAccount.php" method="POST" onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone.');" class="inline">
                    <button type="submit" class="inline-flex items-center bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                      <i class="fas fa-trash mr-2"></i>Delete Account
                    </button>
                  </form>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </main>

  <script>
    function showTab(tabId) {
      // Hide all tab contents
      document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
      
      // Remove active state from all tab buttons
      document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('border-orange-500', 'text-orange-600');
        btn.classList.add('border-transparent', 'text-gray-500');
      });
      
      // Show selected tab content
      document.getElementById(tabId).classList.add('active');
      
      // Add active state to clicked button
      event.target.classList.remove('border-transparent', 'text-gray-500');
      event.target.classList.add('border-orange-500', 'text-orange-600');
    }

    function confirmLogout(event) {
      event.preventDefault();
      if (confirm("Are you sure you want to log out?")) {
        window.location.href = "../Controller/logout.php";
      }
    }

    // Set initial active tab
    document.addEventListener('DOMContentLoaded', function() {
      const firstTab = document.querySelector('.tab-button');
      if (firstTab) {
        firstTab.classList.remove('border-transparent', 'text-gray-500');
        firstTab.classList.add('border-orange-500', 'text-orange-600');
      }
    });
  </script>
</body>
</html>