<?php
session_start();
require_once 'DBConnection/DBConnector.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: ../User/Login.php");
  exit();
}

$user_id = $_SESSION['user_id'];
$activeTab = $_GET['tab'] ?? 'all';

// Pending requests *you sent*
$pendingStmt = $pdo->prepare("
  SELECT u.id, u.username FROM friend_requests fr
  JOIN users u ON fr.receiver_id = u.id
  WHERE fr.sender_id = ? AND fr.status = 'pending'
");
$pendingStmt->execute([$user_id]);
$pending = $pendingStmt->fetchAll();

// Incoming friend requests *you received*
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
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Friends</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-50 text-black pt-20">

  <?php include 'Partials/nav.php'; ?>

  <aside class="hidden lg:block fixed top-16 left-0 h-[calc(100%-0rem)] w-[200px] bg-white z-10 shadow">
    <?php include 'Partials/left_nav.php'; ?>
  </aside>

  <main class="lg:ml-[220px] p-6 screen  -w-3xl mx-auto">
    <div class="mb-4 border-b border-gray-200">
      <h1 class="text-2xl font-bold mb-4">Friends</h1>
      <ul class="flex space-x-4 text-sm  text-gray-600 hover:">
        <li><a href="?tab=all" class="<?= $activeTab === 'all' ? 'text-orange-600' : 'hover:text-orange-600' ?>">All</a></li>
        <li><a href="?tab=pending" class="<?= $activeTab === 'pending' ? 'text-orange-600' : 'hover:text-orange-600' ?>">Pending</a></li>
        <li><a href="?tab=requests" class="<?= $activeTab === 'requests' ? 'text-orange-600' : 'hover:text-orange-600' ?>">Requests</a></li>
        <li><a href="?tab=friends" class="<?= $activeTab === 'friends' ? 'text-orange-600' : 'hover:text-orange-600' ?>">Friends</a></li>
        <li><a href="?tab=add" class="<?= $activeTab === 'add' ? 'text-orange-600' : 'hover:text-orange-600' ?>">Add Friends</a></li>
      </ul>
    </div>

    <?php if ($activeTab === 'pending'): ?>
      <h2 class="text-xl font-semibold mb-2">Pending Sent</h2>
      <ul>
        <?php foreach ($pending as $p): ?>
          <li class="bg-white p-3 mb-2 rounded shadow flex justify-between items-center">
            <span><?= htmlspecialchars($p['username']) ?> (Sent)</span>
            <a href="Controller/friendAction.php?action=cancel&id=<?= $p['id'] ?>&from=pending" class="text-red-600 hover:text-red-800">Cancel</a>
          </li>
        <?php endforeach; ?>
      </ul>


    <?php elseif ($activeTab === 'requests'): ?>
      <h2 class="text-xl font-semibold mb-2">Friend Requests</h2>
      <ul>
        <?php foreach ($requests as $r): ?>
          <li class="bg-white p-3 mb-2 rounded shadow flex justify-between">
            <span><?= htmlspecialchars($r['username']) ?></span>
            <div>
              <a href="Controller/friendAction.php?action=accept&id=<?= $r['id'] ?>" class="text-green-600">Accept</a>
              <a href="Controller/friendAction.php?action=decline&id=<?= $r['id'] ?>" class="text-red-600 ml-2">Decline</a>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>

    <?php elseif ($activeTab === 'friends'): ?>
      <h2 class="text-xl font-semibold mb-2">Your Friends</h2>
      <ul>
        <?php foreach ($friends as $f): ?>
          <li class="bg-white p-3 mb-2 rounded shadow"><?= htmlspecialchars($f['username']) ?></li>
        <?php endforeach; ?>
      </ul>

    <?php elseif ($activeTab === 'add'): ?>
      <h2 class="text-xl font-semibold mb-2">Add Friends</h2>
      <ul>
        <?php foreach ($addUsers as $u): ?>
          <li class="bg-white p-3 mb-2 rounded shadow flex justify-between">
            <span><?= htmlspecialchars($u['username']) ?></span><a href="../Controller/friendAction.php?action=request&id=<?= $u['id'] ?>&from=add" class="text-orange-600">Add Friend</a>
          </li>
        <?php endforeach; ?>
      </ul>

    <?php else: ?>
      <h2 class="text-xl font-semibold mb-2">All</h2>
      <p class="text-gray-700 mb-4">You have <?= count($friends) ?> friends, <?= count($pending) ?> pending requests, and <?= count($requests) ?> incoming requests.</p>
    <?php endif; ?>
  </main>


</body>

</html>