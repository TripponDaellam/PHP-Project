<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$username = $isLoggedIn ? $_SESSION['username'] : '';

require_once 'DBConnection/DBConnector.php'; 
require_once 'DBConnection/DBLocal.php';

$query = "SELECT id, title, description, tags, created_at FROM questions ORDER BY created_at DESC LIMIT 10";
$stmt = $pdo->prepare($query);
$stmt->execute();
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$commentStmt = $localPdo->query("SELECT * FROM comments ORDER BY created_at DESC");
$allComments = [];
while ($c = $commentStmt->fetch(PDO::FETCH_ASSOC)) {
    $allComments[$c['question_id']][] = $c;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Method Flow - Home</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/alpinejs" defer></script>
</head>
<body class="flex flex-col min-h-screen bg-gray-50 text-black pt-20 overflow-x-hidden">

  <?php include 'Partials/nav.php'; ?>

  <aside class="fixed top-20 left-0 h-[calc(100%-10rem)] w-[200px] bg-white z-10 hidden md:block shadow">
    <?php include 'Partials/left_nav.php'; ?>
  </aside>

  <!-- <aside class="fixed top-20 right-0 h-[calc(100%-10rem)] w-60 bg-white shadow px-4 py-6 z-10 hidden lg:block">
    <div class="space-y-4">
      <h3 class="font-semibold text-gray-800">Popular Tags</h3>
      <div class="flex flex-wrap gap-2">
        <?php 
        $tagStmt = $pdo->query("SELECT tags FROM questions");
        $allTags = [];
        while ($row = $tagStmt->fetch(PDO::FETCH_ASSOC)) {
          $tags = explode(',', $row['tags']);
          foreach ($tags as $tag) {
            $tag = trim($tag);
            if (!empty($tag)) {
              $allTags[$tag] = ($allTags[$tag] ?? 0) + 1;
            }
          }
        }
        arsort($allTags);
        $popularTags = array_slice($allTags, 0, 10, true);
        foreach ($popularTags as $tag => $count): ?>
          <a href="/tag/<?= urlencode($tag) ?>" class="inline-block bg-orange-100 text-orange-700 text-xs px-2 py-1 rounded hover:bg-orange-200">
            <?= htmlspecialchars($tag) ?> (<?= $count ?>)
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </aside> -->

  <main class="flex-1 min-w-[700px] max-w-screen-full mx-auto px-4 py-6 md:ml-60 lg:mr-10">
    <?php if ($isLoggedIn): ?>
      <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Welcome back, <?= htmlspecialchars($username) ?>!</h2>
        <p class="text-gray-600 text-sm mt-1">Ready to help others or ask your next question?</p>
      </div>
    <?php endif; ?>

    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold">Recent Questions</h1>
      <a href="ask.php" class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600">Ask a Question</a>
    </div>

    <div class="space-y-6">
      <?php if (!empty($questions)): ?>
        <?php foreach ($questions as $q): ?>
          <div class="relative bg-white shadow p-4 rounded">
            <!-- Tags Top Right (Not Fully Right) -->
            <div class="absolute top-4 right-4 flex flex-wrap gap-2">
              <?php foreach (explode(',', $q['tags']) as $tag): ?>
                <span class="inline-block bg-orange-100 text-orange-700 text-xs px-2 py-1 rounded">
                  <?= htmlspecialchars(trim($tag)); ?>
                </span>
              <?php endforeach; ?>
            </div>

            <!-- Question Title -->
            <a href="/question/<?php echo $q['id']; ?>" class="text-xl font-semibold text-orange-600 hover:underline">
              <?= htmlspecialchars($q['title']); ?>
            </a>

            <!-- Description -->
            <p class="text-gray-700 mt-2"><?= htmlspecialchars($q['description']); ?></p>

            <!-- Date -->
            <p class="text-xs text-gray-500 mt-4">Asked on <?= date('F j, Y', strtotime($q['created_at'])) ?></p>

            <!-- Answers -->
            <?php if (isset($allComments[$q['id']])): ?>
              <div class="mt-4 border-t pt-3">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">Answers:</h3>
                <?php 
                  $commentsToShow = array_slice($allComments[$q['id']], 0, 2); 
                  foreach ($commentsToShow as $comment): 
                ?>
                  <div class="mb-2 bg-gray-100 p-2 rounded text-sm">
                    <?= htmlspecialchars($comment['comment'] ?? 'No comment provided.') ?>
                    <div class="text-xs text-gray-500 mt-1">Posted on <?= date('F j, Y H:i', strtotime($comment['created_at'])) ?></div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="text-gray-500">No questions found.</p>
      <?php endif; ?>
    </div>
  </main>

  <div class="mt-auto z-10">
    <?php include 'Partials/footer.php'; ?>
  </div>
</body>
</html>
