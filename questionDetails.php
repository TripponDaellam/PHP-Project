<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$username = $isLoggedIn ? $_SESSION['username'] : '';

require_once 'DBConnection/DBConnector.php'; // Remote DB (questions, users)
require_once 'DBConnection/DBLocal.php';     // Local DB (comments)

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  echo "Invalid question ID.";
  exit;
}

$question_id = intval($_GET['id']);

// Fetch question + user info
$stmt = $pdo->prepare("
    SELECT q.*, u.username, u.profile_image, q.upvotes, q.downvotes
    FROM questions q
    JOIN users u ON q.user_id = u.id
    WHERE q.id = :id
");
$stmt->execute(['id' => $question_id]);
$question = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$question) {
  echo "Question not found.";
  exit;
}

// Fetch comments from local DB
$commentStmt = $pdo->prepare("SELECT * FROM comments WHERE question_id = :qid ORDER BY created_at DESC");
$commentStmt->execute(['qid' => $question_id]);
$comments = $commentStmt->fetchAll(PDO::FETCH_ASSOC);

// Collect user IDs from comments to fetch their info
$userMap = [];
if (!empty($comments)) {
  $userIds = array_unique(array_column($comments, 'user_id'));
  $placeholders = implode(',', array_fill(0, count($userIds), '?'));

  $userStmt = $pdo->prepare("SELECT id, username, profile_image FROM users WHERE id IN ($placeholders)");
  $userStmt->execute($userIds);
  $users = $userStmt->fetchAll(PDO::FETCH_ASSOC);

  foreach ($users as $user) {
    $userMap[$user['id']] = $user;
  }

  foreach ($comments as &$comment) {
    $userId = $comment['user_id'];
    $comment['username'] = $userMap[$userId]['username'] ?? 'Unknown';
    $comment['profile_image'] = $userMap[$userId]['profile_image'] ?? null;
  }
  unset($comment); // Clean ref
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($question['title']) ?> - Method Flow</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 pt-20">
  <?php include 'Partials/nav.php'; ?>
  <aside class="hidden lg:block fixed top-[60px] left-0 h-[calc(100%-4rem)] w-[200px] bg-white z-10 shadow">
    <?php include 'Partials/left_nav.php'; ?>
  </aside>

  <main class="max-w-4xl mx-auto p-6 bg-white rounded shadow mt-8">
    <div class="flex items-center mb-4">
      <img src="<?= $question['profile_image'] ? htmlspecialchars($question['profile_image']) : 'assets/default-user.png' ?>"
        class="w-10 h-10 rounded-full mr-3" alt="User">
      <div>
        <div class="text-sm font-medium"><?= htmlspecialchars($question['username']) ?></div>
        <div class="text-xs text-gray-500">Posted on <?= date('F j, Y H:i', strtotime($question['created_at'])) ?></div>
      </div>
    </div>

    <h1 class="text-3xl font-bold text-orange-600 mb-2"><?= htmlspecialchars($question['title']) ?></h1>
    <p class="text-gray-700 mb-4"><?= htmlspecialchars($question['description']) ?></p>

    <div class="mb-4 text-sm text-gray-500">
      Upvotes: <?= $question['upvotes'] ?? 0 ?> | Downvotes: <?= $question['downvotes'] ?? 0 ?>
    </div>

    <!-- Tags -->
    <div class="mb-4">
      <?php foreach (explode(',', $question['tags']) as $tag): ?>
        <span class="bg-orange-100 text-orange-700 text-xs px-2 py-1 rounded mr-2"><?= htmlspecialchars(trim($tag)) ?></span>
      <?php endforeach; ?>
    </div>

    <!-- Comment form -->
    <?php if ($isLoggedIn): ?>
      <form action="../Controller/commentController.php" method="POST" class="mb-6">
        <input type="hidden" name="question_id" value="<?= $question_id ?>">
        <textarea name="comment" rows="3" required
          class="w-full h-60 border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500"
          placeholder="Write your answer or comment here..."></textarea>
        <button type="submit" class="mt-2 px-4 py-2 bg-orange-600 text-white rounded hover:bg-orange-700">Post Comment</button>
      </form>
    <?php else: ?>
      <p class="text-gray-500 text-sm">Please <a href="User/Login.php" class="text-orange-600 underline">log in</a> to comment.</p>
    <?php endif; ?>

    <!-- Show comments -->
    <div>
      <h2 class="text-lg font-semibold mb-2">Answers</h2>
      <?php if (count($comments) > 0): ?>
        <?php foreach ($comments as $comment): ?>
          <div class="mb-4 flex items-start">
            <img src="<?= $comment['profile_image'] ? htmlspecialchars($comment['profile_image']) : 'assets/default-user.png' ?>"
              class="w-8 h-8 rounded-full mr-3" alt="User">
            <div>
              <div class="text-sm font-medium"><?= htmlspecialchars($comment['username']) ?></div>
              <div class="text-xs text-gray-500 mb-1">Posted on <?= date('F j, Y H:i', strtotime($comment['created_at'])) ?></div>
              <div class="bg-gray-100 p-2 rounded text-sm"><?= htmlspecialchars($comment['content']) ?></div>
            </div>
          </div>
        <?php endforeach; ?>

      <?php else: ?>
        <p class="text-gray-500">No answers yet. Be the first to comment!</p>
      <?php endif; ?>
    </div>
  </main>
</body>

</html>