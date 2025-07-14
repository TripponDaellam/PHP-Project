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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="bg-gray-50 pt-16">
  <?php include 'Partials/nav.php'; ?>
  
  <aside class="hidden lg:block fixed top-16 left-0 h-[calc(100vh-4rem)] w-[200px] bg-white z-10 shadow-lg overflow-auto">
    <?php include 'Partials/left_nav.php'; ?>
  </aside>

  <main class="ml-0 lg:ml-[220px] p-6">
    <div class="max-w-6xl mx-auto">
      <!-- Question Card -->
      <div class="bg-white rounded-xl shadow-md border border-gray-200 mb-8">
        <!-- Question Header -->
        <div class="p-6 border-b border-gray-100">
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-4">
              <div class="w-12 h-12 bg-gradient-to-r from-orange-400 to-orange-500 rounded-full flex items-center justify-center text-white text-lg font-semibold">
                <?= strtoupper(substr($question['username'], 0, 1)) ?>
              </div>
              <div>
                <div class="font-semibold text-lg"><?= htmlspecialchars($question['username']) ?></div>
                <div class="text-sm text-gray-500"><?= date('M j, Y', strtotime($question['created_at'])) ?></div>
              </div>
            </div>
            <div class="flex items-center space-x-6 text-base text-gray-600">
              <div class="flex items-center space-x-2">
                <i class="fas fa-thumbs-up text-green-500 text-lg"></i>
                <span class="font-semibold"><?= $question['upvotes'] ?? 0 ?></span>
              </div>
              <div class="flex items-center space-x-2">
                <i class="fas fa-thumbs-down text-red-500 text-lg"></i>
                <span class="font-semibold"><?= $question['downvotes'] ?? 0 ?></span>
              </div>
            </div>
          </div>
          <h1 class="text-3xl font-bold text-gray-800"><?= htmlspecialchars($question['title']) ?></h1>
        </div>

        <!-- Question Content -->
        <div class="p-6">
          <p class="text-gray-700 text-base leading-relaxed mb-6"><?= htmlspecialchars($question['description']) ?></p>

          <?php if ($base64Image): ?>
            <div class="mb-6">
              <div class="max-w-2xl border border-gray-200 rounded-xl overflow-hidden">
                <img src="data:<?= htmlspecialchars($imageMime) ?>;base64,<?= $base64Image ?>"
                  class="w-full h-auto object-contain"
                  alt="Question Image">
              </div>
            </div>
          <?php endif; ?>

          <!-- Tags -->
          <div class="flex flex-wrap gap-2 mb-6">
            <?php foreach (explode(',', $question['tags']) as $tag): ?>
              <span class="bg-orange-50 text-orange-600 text-sm px-3 py-2 rounded-lg border border-orange-200 font-medium">
                <?= htmlspecialchars(trim($tag)) ?>
              </span>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Comment Form -->
      <?php if ($isLoggedIn): ?>
        <div class="bg-white rounded-xl shadow-md border border-gray-200 mb-8">
          <div class="p-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Add Your Answer</h3>
            <form action="../Controller/commentController.php" method="POST">
              <input type="hidden" name="question_id" value="<?= $question_id ?>">
              <textarea name="comment" rows="5" required 
                class="w-full border border-gray-300 rounded-lg px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" 
                placeholder="Write your answer..."></textarea>
              <div class="mt-4 flex justify-end">
                <button type="submit" class="bg-orange-500 text-white text-base px-6 py-3 rounded-lg hover:bg-orange-600 transition-colors font-medium">
                  <i class="fas fa-paper-plane mr-2"></i>Post Answer
                </button>
              </div>
            </form>
          </div>
        </div>
      <?php else: ?>
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-8">
          <p class="text-base text-blue-700">
            <i class="fas fa-info-circle mr-2 text-lg"></i>
            Please <a href="User/Login.php" class="text-blue-600 underline font-medium">log in</a> to add an answer.
          </p>
        </div>
      <?php endif; ?>

      <!-- Answers Section -->
      <div class="bg-white rounded-xl shadow-md border border-gray-200">
        <div class="p-6 border-b border-gray-100">
          <h2 class="text-2xl font-semibold text-gray-800">
            <i class="fas fa-comments mr-3 text-orange-500 text-xl"></i>
            Answers (<?= count($comments) ?>)
          </h2>
        </div>

        <div class="divide-y divide-gray-100">
          <?php if ($comments): ?>
            <?php foreach ($comments as $comment): ?>
              <div class="p-6">
                <div class="flex items-start space-x-4">
                  <div class="w-12 h-12 bg-gradient-to-r from-blue-400 to-blue-500 rounded-full flex items-center justify-center text-white text-lg font-semibold flex-shrink-0">
                    <?= strtoupper(substr($comment['username'], 0, 1)) ?>
                  </div>
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-3">
                      <div class="font-semibold text-lg text-gray-800"><?= htmlspecialchars($comment['username']) ?></div>
                      <div class="text-sm text-gray-500"><?= date('M j, Y', strtotime($comment['created_at'])) ?></div>
                    </div>
                    <div class="text-base text-gray-700 leading-relaxed mb-3"><?= htmlspecialchars($comment['content']) ?></div>
                    
                    <?php if ($isLoggedIn): ?>
                      <button onclick="toggleReplyForm(<?= $comment['id'] ?>)" 
                        class="text-orange-500 text-base hover:text-orange-600 transition-colors font-medium">
                        <i class="fas fa-reply mr-2"></i>Reply
                      </button>
                    <?php endif; ?>

                    <!-- Reply Form -->
                    <form action="../Controller/addReply.php" method="POST" id="reply-form-<?= $comment['id'] ?>" class="hidden mt-4">
                      <input type="hidden" name="question_id" value="<?= $question_id ?>">
                      <input type="hidden" name="parent_comment_id" value="<?= $comment['id'] ?>">
                      <textarea name="reply_content" rows="3" required
                        class="w-full border border-gray-300 rounded-lg px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" 
                        placeholder="Write a reply..."></textarea>
                      <div class="mt-3 flex justify-end">
                        <button type="submit" class="bg-orange-500 text-white text-base px-4 py-2 rounded-lg hover:bg-orange-600 transition-colors font-medium">
                          Submit Reply
                        </button>
                      </div>
                    </form>

                    <!-- Replies -->
                    <?php if (!empty($comment['replies'])): ?>
                      <div class="mt-4 space-y-3">
                        <?php foreach ($comment['replies'] as $reply): ?>
                          <div class="bg-gray-50 rounded-lg p-4 ml-6">
                            <div class="flex items-center justify-between mb-2">
                              <div class="font-semibold text-base text-gray-800"><?= htmlspecialchars($reply['username']) ?></div>
                              <div class="text-sm text-gray-500"><?= date('M j, Y', strtotime($reply['created_at'])) ?></div>
                            </div>
                            <div class="text-base text-gray-700"><?= htmlspecialchars($reply['content']) ?></div>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="p-12 text-center">
              <div class="text-gray-400 mb-4">
                <i class="fas fa-comments text-5xl"></i>
              </div>
              <p class="text-gray-500 text-lg">No answers yet. Be the first to help!</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
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