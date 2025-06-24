<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$username = $isLoggedIn ? $_SESSION['username'] : '';

require_once 'DBConnection/DBConnector.php';
require_once 'DBConnection/DBLocal.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  echo "Invalid question ID.";
  exit;
}

$question_id = intval($_GET['id']);

// Fetch question and user
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

// Fetch comments
$commentStmt = $pdo->prepare("SELECT * FROM comments WHERE question_id = :qid ORDER BY created_at ASC");
$commentStmt->execute(['qid' => $question_id]);
$allComments = $commentStmt->fetchAll(PDO::FETCH_ASSOC);

// Organize comments and replies
$comments = [];
$repliesMap = [];

foreach ($allComments as $comment) {
  if ($comment['parent_id'] === null) {
    $comments[] = $comment;
  } else {
    $repliesMap[$comment['parent_id']][] = $comment;
  }
}

// Get comment users
$userMap = [];
if (!empty($allComments)) {
  $userIds = array_unique(array_column($allComments, 'user_id'));
  $placeholders = implode(',', array_fill(0, count($userIds), '?'));

  $userStmt = $pdo->prepare("SELECT id, username, profile_image FROM users WHERE id IN ($placeholders)");
  $userStmt->execute($userIds);
  foreach ($userStmt->fetchAll(PDO::FETCH_ASSOC) as $user) {
    $userMap[$user['id']] = $user;
  }
}

// Attach user and replies
foreach ($comments as &$comment) {
  $uid = $comment['user_id'];
  $comment['username'] = $userMap[$uid]['username'] ?? 'Unknown';
  $comment['profile_image'] = $userMap[$uid]['profile_image'] ?? null;
  $comment['replies'] = [];

  if (!empty($repliesMap[$comment['id']])) {
    foreach ($repliesMap[$comment['id']] as $reply) {
      $rid = $reply['user_id'];
      $reply['username'] = $userMap[$rid]['username'] ?? 'Unknown';
      $reply['profile_image'] = $userMap[$rid]['profile_image'] ?? null;
      $comment['replies'][] = $reply;
    }
  }
}
unset($comment);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($question['title']) ?> - Method Flow</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 pt-16 text-black">
  <?php include 'Partials/nav.php'; ?>

  <!-- Sidebar (Desktop only) -->
  <aside class="hidden lg:block fixed top-16 left-0 h-full w-[200px] bg-white z-10 shadow">
    <?php include 'Partials/left_nav.php'; ?>
  </aside>

  <main class="flex-1 min-w-[500px] md:min-w-[600px] max-w-screen-full ml-0 lg:ml-[220px] mr-0 lg:mr-10 p-10 overflow-x-auto transition-all duration-300 ease-in-out bg-white shadow-md rounded-lg">
    <!-- Question header -->
    <div class="flex items-center mb-4">
      <img src="<?= $question['profile_image'] ? htmlspecialchars($question['profile_image']) : 'assets/default-user.png' ?>" class="w-10 h-10 rounded-full mr-3" alt="User">
      <div>
        <div class="text-sm font-medium"><?= htmlspecialchars($question['username']) ?></div>
        <div class="text-xs text-gray-500">Posted on <?= date('F j, Y H:i', strtotime($question['created_at'])) ?></div>
      </div>
    </div>

    <h1 class="text-2xl font-bold text-orange-600 mb-2"><?= htmlspecialchars($question['title']) ?></h1>
    <p class="text-gray-700 mb-4"><?= nl2br(htmlspecialchars($question['description'])) ?></p>

    <!-- Vote Info -->
    <div class="mb-4 text-sm text-gray-500">
      Upvotes: <?= $question['upvotes'] ?? 0 ?> | Downvotes: <?= $question['downvotes'] ?? 0 ?>
    </div>

    <!-- Tags -->
    <div class="mb-6 flex flex-wrap gap-2">
      <?php foreach (explode(',', $question['tags']) as $tag): ?>
        <span class="bg-orange-100 text-orange-700 text-xs px-2 py-1 rounded"><?= htmlspecialchars(trim($tag)) ?></span>
      <?php endforeach; ?>
    </div>

    <!-- Comment form -->
    <?php if ($isLoggedIn): ?>
      <form action="../Controller/commentController.php" method="POST" class="mb-8">
        <input type="hidden" name="question_id" value="<?= $question_id ?>">
        <textarea name="comment" rows="4" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500" placeholder="Write your answer or comment here..."></textarea>
        <button type="submit" class="mt-2 px-4 py-2 bg-orange-600 text-white rounded hover:bg-orange-700">Post Comment</button>
      </form>
    <?php else: ?>
      <p class="text-sm text-gray-500">Please <a href="User/Login.php" class="text-orange-600 underline">log in</a> to comment.</p>
    <?php endif; ?>

    <!-- Comments Section -->
    <div>
      <h2 class="text-lg font-semibold mb-3">Answers</h2>

      <?php if (count($comments) > 0): ?>
        <?php foreach ($comments as $comment): ?>
          <div class="mb-6 flex items-start">
            <img src="<?= $comment['profile_image'] ? htmlspecialchars($comment['profile_image']) : 'assets/default-user.png' ?>" class="w-8 h-8 rounded-full mr-3 mt-1" alt="User">
            <div class="flex-1">
              <div class="text-sm font-medium"><?= htmlspecialchars($comment['username']) ?></div>
              <div class="text-xs text-gray-500 mb-1">Posted on <?= date('F j, Y H:i', strtotime($comment['created_at'])) ?></div>
              <div class="bg-gray-100 p-3 rounded text-sm"><?= nl2br(htmlspecialchars($comment['content'])) ?></div>
              <button onclick="toggleReplyForm(<?= $comment['id'] ?>)" class="text-orange-500 text-xs mt-1 hover:underline">Reply</button>

              <!-- Reply Form -->
              <form method="POST" action="../Controller/addReply.php" class="mt-2 hidden" id="reply-form-<?= $comment['id'] ?>">
                <input type="hidden" name="question_id" value="<?= $question_id ?>">
                <input type="hidden" name="parent_comment_id" value="<?= $comment['id'] ?>">
                <textarea name="reply_content" rows="2" class="w-full p-2 border rounded text-sm mb-1" placeholder="Write a reply..."></textarea>
                <button type="submit" class="bg-orange-500 text-white text-xs px-3 py-1 rounded hover:bg-orange-600">Submit</button>
              </form>

              <!-- Replies -->
              <?php if (!empty($comment['replies'])): ?>
                <div class="ml-6 mt-3 space-y-3">
                  <?php foreach ($comment['replies'] as $reply): ?>
                    <div class="bg-gray-50 p-3 rounded text-sm">
                      <div class="font-medium text-xs"><?= htmlspecialchars($reply['username']) ?></div>
                      <div class="text-xs text-gray-500 mb-1">Replied on <?= date('F j, Y H:i', strtotime($reply['created_at'])) ?></div>
                      <?= nl2br(htmlspecialchars($reply['content'])) ?>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="text-gray-500">No answers yet. Be the first to comment!</p>
      <?php endif; ?>
    </div>
  </main>

  <script>
    function toggleReplyForm(commentId) {
      const form = document.getElementById('reply-form-' + commentId);
      form.classList.toggle('hidden');
    }
  </script>
</body>

</html>