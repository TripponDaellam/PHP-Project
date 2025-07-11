<?php
session_start();
require_once 'DBConnection/DBConnector.php';

$isLoggedIn = isset($_SESSION['user_id']);
$username = $isLoggedIn ? $_SESSION['username'] : '';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  echo "Invalid question ID.";
  exit;
}

$question_id = intval($_GET['id']);

// Get question and user
$stmt = $pdo->prepare("SELECT q.*, u.username, u.profile_image, q.upvotes, q.downvotes FROM questions q JOIN users u ON q.user_id = u.id WHERE q.id = :id");
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

// Get user info for comments
$userMap = [];
if ($allComments) {
  $userIds = array_unique(array_column($allComments, 'user_id'));
  $placeholders = implode(',', array_fill(0, count($userIds), '?'));
  $userStmt = $pdo->prepare("SELECT id, username, profile_image FROM users WHERE id IN ($placeholders)");
  $userStmt->execute($userIds);
  foreach ($userStmt->fetchAll(PDO::FETCH_ASSOC) as $user) {
    $userMap[$user['id']] = $user;
  }
}

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

// --- Enhanced Image Caching with Mime Detection ---
$cacheDir = __DIR__ . '/cache_images';
$cacheExpire = 86400; // 1 day in seconds

if (!is_dir($cacheDir)) {
  mkdir($cacheDir, 0755, true);
}

$cacheFile = $cacheDir . "/image_{$question_id}.txt";
$cacheMimeFile = $cacheDir . "/image_{$question_id}_mime.txt";

$base64Image = null;
$imageMime = 'image/jpeg'; // default mime type fallback

if (!empty($question['image'])) {
  $cacheValid = file_exists($cacheFile) && file_exists($cacheMimeFile) &&
    (time() - filemtime($cacheFile) < $cacheExpire);

  if ($cacheValid) {
    $base64Image = file_get_contents($cacheFile);
    $imageMime = file_get_contents($cacheMimeFile);
  } else {
    $detectedMime = 'image/jpeg'; // fallback until FileInfo is enabled

    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (in_array($detectedMime, $allowedMimes)) {
      $imageMime = $detectedMime;
    }

    $base64Image = base64_encode($question['image']);
    file_put_contents($cacheFile, $base64Image);
    file_put_contents($cacheMimeFile, $imageMime);
  }
} else {
  $base64Image = null;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($question['title']) ?> - Question Details</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 pt-16 px-4">
  <?php include 'Partials/nav.php'; ?>
  <div class="flex flex-col lg:flex-row min-h-screen bg-gray-100">
    <aside class="hidden lg:block fixed top-16 left-0 h-[calc(100%-0rem)] w-[200px] bg-white z-10 shadow">
      <?php include 'Partials/left_nav.php'; ?>
    </aside>

    <main class="flex-1 min-w-full md:min-w-[500px] max-w-screen-full ml-[220px] lg:mr-10 p-4 overflow-x-auto bg-white">
      <div class="flex items-center mb-6">
        <img src="<?= $question['profile_image'] ? htmlspecialchars($question['profile_image']) : 'assets/default-user.png' ?>" class="w-12 h-12 rounded-full mr-4" alt="User">
        <div>
          <div class="font-semibold text-lg"><?= htmlspecialchars($question['username']) ?></div>
          <div class="text-gray-500 text-sm">Posted on <?= date('F j, Y H:i', strtotime($question['created_at'])) ?></div>
        </div>
      </div>

      <h1 class="text-3xl font-bold mb-4 text-orange-600"><?= htmlspecialchars($question['title']) ?></h1>
      <p class="mb-6 text-gray-700 whitespace-pre-line"><?= htmlspecialchars($question['description']) ?></p>

      <?php if ($base64Image): ?>
        <div class="mb-6">
          <div class="max-w-full sm:max-w-[600px] md:max-w-[800px] border rounded-xl overflow-hidden">
            <img src="data:<?= htmlspecialchars($imageMime) ?>;base64,<?= $base64Image ?>"
              class="w-full h-auto object-contain"
              alt="Question Image">
          </div>
        </div>
      <?php endif; ?>

      <div class="mb-4 text-sm text-gray-600">
        Upvotes: <?= $question['upvotes'] ?? 0 ?> | Downvotes: <?= $question['downvotes'] ?? 0 ?>
      </div>

      <div class="mb-8 flex flex-wrap gap-2">
        <?php foreach (explode(',', $question['tags']) as $tag): ?>
          <span class="bg-orange-100 text-orange-700 text-xs px-2 py-1 rounded"><?= htmlspecialchars(trim($tag)) ?></span>
        <?php endforeach; ?>
      </div>

      <?php if ($isLoggedIn): ?>
        <form action="../Controller/commentController.php" method="POST" class="mb-10">
          <input type="hidden" name="question_id" value="<?= $question_id ?>">
          <textarea name="comment" rows="4" required class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-orange-500" placeholder="Write your comment..."></textarea>
          <button type="submit" class="mt-2 px-4 py-2 bg-orange-600 text-white rounded hover:bg-orange-700">Post Comment</button>
        </form>
      <?php else: ?>
        <p class="text-sm text-gray-600">Please <a href="User/Login.php" class="text-orange-600 underline">log in</a> to comment.</p>
      <?php endif; ?>

      <section>
        <h2 class="text-xl font-semibold mb-6">Answers</h2>
        <?php if ($comments): ?>
          <?php foreach ($comments as $comment): ?>
            <div class="mb-8 flex items-start space-x-4">
              <img src="<?= $comment['profile_image'] ? htmlspecialchars($comment['profile_image']) : 'assets/default-user.png' ?>" class="w-10 h-10 rounded-full mt-1" alt="User">
              <div class="flex-1">
                <div class="text-sm font-semibold"><?= htmlspecialchars($comment['username']) ?></div>
                <div class="text-xs text-gray-500 mb-1">Posted on <?= date('F j, Y H:i', strtotime($comment['created_at'])) ?></div>
                <div class="bg-gray-100 p-3 rounded text-sm whitespace-pre-line"><?= htmlspecialchars($comment['content']) ?></div>
                <button onclick="toggleReplyForm(<?= $comment['id'] ?>)" class="text-orange-500 text-xs mt-1 hover:underline">Reply</button>

                <form action="../Controller/addReply.php" method="POST" id="reply-form-<?= $comment['id'] ?>" class="hidden mt-2">
                  <input type="hidden" name="question_id" value="<?= $question_id ?>">
                  <input type="hidden" name="parent_comment_id" value="<?= $comment['id'] ?>">
                  <textarea name="reply_content" rows="2" class="w-full p-2 border rounded text-sm mb-1" placeholder="Write a reply..."></textarea>
                  <button type="submit" class="bg-orange-500 text-white text-xs px-3 py-1 rounded hover:bg-orange-600">Submit</button>
                </form>

                <?php if (!empty($comment['replies'])): ?>
                  <div class="ml-6 mt-3 space-y-3">
                    <?php foreach ($comment['replies'] as $reply): ?>
                      <div class="bg-gray-50 p-3 rounded text-sm whitespace-pre-line">
                        <div class="font-semibold text-xs"><?= htmlspecialchars($reply['username']) ?></div>
                        <div class="text-xs text-gray-500 mb-1">Replied on <?= date('F j, Y H:i', strtotime($reply['created_at'])) ?></div>
                        <?= htmlspecialchars($reply['content']) ?>
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
      </section>
    </main>
  </div>

  <script>
    function toggleReplyForm(commentId) {
      const form = document.getElementById('reply-form-' + commentId);
      form.classList.toggle('hidden');
    }
  </script>
</body>

</html>