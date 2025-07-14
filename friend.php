<?php
session_start();
require_once 'DBConnection/DBConnector.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: ../User/Login.php");
  exit();
}

$user_id = $_SESSION['user_id'];
$activeTab = $_GET['tab'] ?? 'all';

// Pending requests you sent
$pendingStmt = $pdo->prepare("
  SELECT u.id, u.username FROM friend_requests fr
  JOIN users u ON fr.receiver_id = u.id
  WHERE fr.sender_id = ? AND fr.status = 'pending'
");
$pendingStmt->execute([$user_id]);
$pending = $pendingStmt->fetchAll();

// Incoming friend requests you received
$requestStmt = $pdo->prepare("
  SELECT u.id, u.username FROM friend_requests fr
  JOIN users u ON fr.sender_id = u.id
  WHERE fr.receiver_id = ? AND fr.status = 'pending'
");
$requestStmt->execute([$user_id]);
$requests = $requestStmt->fetchAll();

// Accepted friends
$friendsStmt = $pdo->prepare("
  SELECT u.id, u.username FROM friend_requests fr
  JOIN users u ON (u.id = fr.sender_id OR u.id = fr.receiver_id)
  WHERE fr.status = 'accepted' AND (fr.sender_id = ? OR fr.receiver_id = ?) AND u.id != ?
");
$friendsStmt->execute([$user_id, $user_id, $user_id]);
$friends = $friendsStmt->fetchAll();

// Add friends — all users excluding self, accepted, and pending/requested
$excludeIds = array_merge(
  [$user_id],
  array_column($pending, 'id'),
  array_column($requests, 'id'),
  array_column($friends, 'id')
);
$placeholders = implode(',', array_fill(0, count($excludeIds), '?'));
$addStmt = $pdo->prepare("SELECT id, username FROM users WHERE id NOT IN ($placeholders)");
$addStmt->execute($excludeIds);
$addUsers = $addStmt->fetchAll();

// Fetch questions from a selected friend
$friendPosts = [];
$selectedFriendId = $_GET['friend_id'] ?? null;

// Check if selected friend is actually your friend
if ($selectedFriendId && in_array($selectedFriendId, array_column($friends, 'id'))) {
  $postsStmt = $pdo->prepare("
        SELECT q.id, q.title, q.description, q.created_at, u.username
        FROM questions q
        JOIN users u ON q.user_id = u.id
        WHERE q.user_id = ?
        ORDER BY q.created_at DESC
    ");
  $postsStmt->execute([$selectedFriendId]);
  $friendPosts = $postsStmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Friends</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="bg-gradient-to-br from-gray-50 to-gray-100 text-gray-800 pt-16 min-h-screen">
  <?php include 'Partials/nav.php'; ?>

  <aside class="hidden lg:block fixed top-16 left-0 h-[calc(100%-0rem)] w-[200px] bg-white z-10 shadow-lg">
    <?php include 'Partials/left_nav.php'; ?>
  </aside>

  <main class="lg:ml-[220px] p-6 max-w-4xl mx-auto">

    <!-- Header Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h1 class="text-3xl font-bold text-gray-800 mb-2">Friends</h1>
          <p class="text-gray-600">Connect with developers and share knowledge</p>
        </div>
        <div class="flex items-center space-x-4">
          <div class="text-center">
            <div class="text-2xl font-bold text-orange-500"><?= count($friends) ?></div>
            <div class="text-xs text-gray-500">Friends</div>
          </div>
          <div class="text-center">
            <div class="text-2xl font-bold text-blue-500"><?= count($requests) ?></div>
            <div class="text-xs text-gray-500">Requests</div>
          </div>
        </div>
      </div>

      <!-- Navigation Tabs -->
      <div class="flex flex-wrap gap-2">
        <a href="?tab=all" class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 <?= $activeTab === 'all' ? 'bg-orange-500 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
          <i class="fas fa-users mr-2"></i>All
        </a>
        <a href="?tab=pending" class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 <?= $activeTab === 'pending' ? 'bg-orange-500 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
          <i class="fas fa-clock mr-2"></i>Pending
        </a>
        <a href="?tab=requests" class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 <?= $activeTab === 'requests' ? 'bg-orange-500 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
          <i class="fas fa-user-plus mr-2"></i>Requests
        </a>
        <a href="?tab=friends" class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 <?= $activeTab === 'friends' ? 'bg-orange-500 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
          <i class="fas fa-heart mr-2"></i>Friends
        </a>
        <a href="?tab=add" class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 <?= $activeTab === 'add' ? 'bg-orange-500 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
          <i class="fas fa-plus mr-2"></i>Add Friends
        </a>
      </div>
    </div>

    <!-- Content Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
      <?php if ($activeTab === 'pending'): ?>
        <div class="mb-6">
          <h2 class="text-xl font-semibold mb-4 flex items-center">
            <i class="fas fa-clock text-orange-500 mr-3"></i>
            Pending Sent Requests
          </h2>
          <?php if (!empty($pending)): ?>
            <div class="grid gap-3">
              <?php foreach ($pending as $p): ?>
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-200">
                  <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-r from-orange-400 to-orange-600 rounded-full flex items-center justify-center text-white font-semibold">
                      <?= strtoupper(substr($p['username'], 0, 1)) ?>
                    </div>
                    <div>
                      <div class="font-medium text-gray-800"><?= htmlspecialchars($p['username']) ?></div>
                      <div class="text-sm text-gray-500">Request sent</div>
                    </div>
                  </div>
                  <a href="Controller/friendAction.php?action=cancel&id=<?= $p['id'] ?>&from=pending"
                    class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors duration-200 text-sm">
                    <i class="fas fa-times mr-1"></i>Cancel
                  </a>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div class="text-center py-8">
              <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
              <p class="text-gray-500">No pending requests</p>
            </div>
          <?php endif; ?>
        </div>

      <?php elseif ($activeTab === 'requests'): ?>
        <div class="mb-6">
          <h2 class="text-xl font-semibold mb-4 flex items-center">
            <i class="fas fa-user-plus text-blue-500 mr-3"></i>
            Friend Requests
          </h2>
          <?php if (!empty($requests)): ?>
            <div class="grid gap-3">
              <?php foreach ($requests as $r): ?>
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-200">
                  <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-r from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white font-semibold">
                      <?= strtoupper(substr($r['username'], 0, 1)) ?>
                    </div>
                    <div>
                      <div class="font-medium text-gray-800"><?= htmlspecialchars($r['username']) ?></div>
                      <div class="text-sm text-gray-500">Wants to be your friend</div>
                    </div>
                  </div>
                  <div class="flex space-x-2">
                    <a href="Controller/friendAction.php?action=accept&id=<?= $r['id'] ?>"
                      class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors duration-200 text-sm">
                      <i class="fas fa-check mr-1"></i>Accept
                    </a>
                    <a href="Controller/friendAction.php?action=decline&id=<?= $r['id'] ?>"
                      class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors duration-200 text-sm">
                      <i class="fas fa-times mr-1"></i>Decline
                    </a>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div class="text-center py-8">
              <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
              <p class="text-gray-500">No friend requests</p>
            </div>
          <?php endif; ?>
        </div>

      <?php elseif ($activeTab === 'friends'): ?>
        <div class="mb-6">
          <h2 class="text-xl font-semibold mb-4 flex items-center">
            <i class="fas fa-heart text-green-500 mr-3"></i>
            Your Friends
          </h2>
          <?php if (!empty($friends)): ?>
            <div class="grid gap-3">
              <?php foreach ($friends as $f): ?>
                <div class="p-4 bg-gray-50 rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-200">
                  <a href="?tab=friends&friend_id=<?= $f['id'] ?>" class="flex items-center space-x-3 group">
                    <div class="w-10 h-10 bg-gradient-to-r from-green-400 to-green-600 rounded-full flex items-center justify-center text-white font-semibold">
                      <?= strtoupper(substr($f['username'], 0, 1)) ?>
                    </div>
                    <div class="flex-1">
                      <div class="font-medium text-gray-800 group-hover:text-orange-600 transition-colors duration-200">
                        <?= htmlspecialchars($f['username']) ?>
                      </div>
                      <div class="text-sm text-gray-500">Click to view posts</div>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400 group-hover:text-orange-500 transition-colors duration-200"></i>
                  </a>
                </div>
              <?php endforeach; ?>
            </div>

            <?php if ($selectedFriendId): ?>
              <?php
              $friendKey = array_search($selectedFriendId, array_column($friends, 'id'));
              $friendName = $friends[$friendKey]['username'];
              ?>
              <div class="mt-8 p-6 bg-gradient-to-r from-orange-50 to-orange-100 rounded-xl border border-orange-200">
                <h3 class="text-lg font-semibold mb-4 flex items-center">
                  <i class="fas fa-question-circle text-orange-500 mr-3"></i>
                  Questions by <?= htmlspecialchars($friendName) ?>
                </h3>
                <?php if (!empty($friendPosts)): ?>
                  <div class="grid gap-4">
                    <?php foreach ($friendPosts as $post): ?>
                      <div class="bg-white p-4 rounded-lg shadow-sm border border-orange-200">
                        <div class="font-semibold text-gray-800 mb-2"><?= htmlspecialchars($post['title']) ?></div>
                        <div class="text-sm text-gray-500 mb-3">
                          <i class="fas fa-calendar mr-1"></i>
                          Asked on <?= htmlspecialchars($post['created_at']) ?>
                        </div>
                        <div class="text-gray-700"><?= nl2br(htmlspecialchars($post['description'])) ?></div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php else: ?>
                  <div class="text-center py-6">
                    <i class="fas fa-question text-3xl text-orange-300 mb-3"></i>
                    <p class="text-gray-600">This friend hasn't posted any questions yet.</p>
                  </div>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          <?php else: ?>
            <div class="text-center py-8">
              <i class="fas fa-users text-4xl text-gray-300 mb-4"></i>
              <p class="text-gray-500">No friends yet. Start connecting with other developers!</p>
            </div>
          <?php endif; ?>
        </div>

      <?php elseif ($activeTab === 'add'): ?>
        <div class="mb-6">
          <h2 class="text-xl font-semibold mb-4 flex items-center">
            <i class="fas fa-plus text-purple-500 mr-3"></i>
            Add Friends
          </h2>
          <?php if (!empty($addUsers)): ?>
            <div class="grid gap-3">
              <?php foreach ($addUsers as $u): ?>
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-200">
                  <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-r from-purple-400 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold">
                      <?= strtoupper(substr($u['username'], 0, 1)) ?>
                    </div>
                    <div>
                      <div class="font-medium text-gray-800"><?= htmlspecialchars($u['username']) ?></div>

                    </div>
                  </div>
                  <a href="../Controller/friendAction.php?action=request&id=<?= $u['id'] ?>&from=add"
                    class="px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition-colors duration-200 text-sm">
                    <i class="fas fa-user-plus mr-1"></i>Add Friend
                  </a>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div class="text-center py-8">
              <i class="fas fa-users text-4xl text-gray-300 mb-4"></i>
              <p class="text-gray-500">No more users to add as friends</p>
            </div>
          <?php endif; ?>
        </div>

      <?php else: ?>
        <div class="text-center py-12">
          <div class="mb-6">
            <i class="fas fa-users text-6xl text-orange-400 mb-4"></i>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Friends Overview</h2>
            <p class="text-gray-600 mb-6">Manage your connections and discover new developers</p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-2xl mx-auto">
            <div class="bg-gradient-to-br from-green-50 to-green-100 p-6 rounded-xl border border-green-200">
              <div class="text-3xl font-bold text-green-600 mb-2"><?= count($friends) ?></div>
              <div class="text-green-700 font-medium">Friends</div>
            </div>
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-6 rounded-xl border border-blue-200">
              <div class="text-3xl font-bold text-blue-600 mb-2"><?= count($requests) ?></div>
              <div class="text-blue-700 font-medium">Requests</div>
            </div>
            <div class="bg-gradient-to-br from-orange-50 to-orange-100 p-6 rounded-xl border border-orange-200">
              <div class="text-3xl font-bold text-orange-600 mb-2"><?= count($pending) ?></div>
              <div class="text-orange-700 font-medium">Pending</div>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </main>

  <?php include 'Partials/footer.php'; ?>
</body>

</html>