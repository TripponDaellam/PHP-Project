<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$username = $isLoggedIn ? $_SESSION['username'] : '';

require_once 'DBConnection/DBConnector.php';
require_once 'DBConnection/DBLocal.php';

// Fetch questions with usernames
$query = "SELECT q.id, q.title, q.description, q.tags, q.created_at, u.username 
          FROM questions q 
          JOIN users u ON q.user_id = u.id 
          ORDER BY q.created_at DESC LIMIT 10";
$stmt = $pdo->prepare($query);
$stmt->execute();
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch comments from local DB
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
<body class="bg-gray-50 text-black pt-20">

  <!-- Top Nav -->
  <?php include 'Partials/nav.php'; ?>

  <div class="flex flex-col lg:flex-row min-h-screen">

    <!-- Left Sidebar -->
    <aside class="hidden lg:block fixed top-[60px] left-0 h-[calc(100%-4rem)] w-[200px] bg-white z-10 shadow">
      <?php include 'Partials/left_nav.php'; ?>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 w-full lg:ml-60 lg:mr-10 px-4 py-6 md:px-8 max-w-full">
      <?php if ($isLoggedIn): ?>
        <div class="mb-6">
          <h2 class="text-2xl font-bold text-gray-800">Welcome back, <?= htmlspecialchars($username) ?>!</h2>
          <p class="text-gray-600 text-sm mt-1">Ready to help others or ask your next question?</p>
        </div>
      <?php endif; ?>

      <div class="flex justify-between items-center mb-4 flex-wrap gap-2">
        <h1 class="text-3xl font-bold">Recent Questions</h1>
        <?php if ($isLoggedIn): ?>
          <a href="ask.php" class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600 text-sm">Ask a Question</a>
        <?php else: ?>
          <a href="../User/Login.php" class="bg-orange-600 text-white px-4 py-2 rounded hover:bg-orange-700 text-sm">Login to Ask</a>
        <?php endif; ?>
      </div>

      <div class="space-y-6">
        <?php if (!empty($questions)): ?>
          <?php foreach ($questions as $q): ?>
            <div class="relative bg-white shadow p-4 rounded">
              <!-- Options -->
              <div class="absolute top-[-5px] left-[-10px] relative">
                <button onclick="toggleDropdown(this)" class="p-1 rounded hover:bg-gray-200">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                       viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                       class="size-6 text-gray-700">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 6.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 12.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 18.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5Z" />
                  </svg>
                </button>
                <div class="dropdown absolute left-8 top-0 hidden bg-white shadow rounded text-sm z-20 w-32">
                  <a href="#" class="block px-4 py-2 hover:bg-gray-100">Save</a>
                  <a href="#" class="block px-4 py-2 hover:bg-gray-100">Report</a>
                </div>
              </div>

              <script>
                function toggleDropdown(button) {
                  const dropdown = button.parentElement.querySelector('.dropdown');
                  dropdown.classList.toggle('hidden');
                }
                document.addEventListener('click', function (e) {
                  document.querySelectorAll('.dropdown').forEach(d => {
                    if (!d.parentElement.contains(e.target)) d.classList.add('hidden');
                  });
                });
              </script>

              <!-- Tags -->
              <div class="absolute top-4 right-4 flex flex-wrap gap-1 max-w-[40%] justify-end">
                <?php foreach (explode(',', $q['tags']) as $tag): ?>
                  <span class="bg-orange-100 text-orange-700 text-xs px-2 py-1 rounded"><?= htmlspecialchars(trim($tag)); ?></span>
                <?php endforeach; ?>
              </div>

              <!-- Title -->
             <a href="questionDetails.php?id=<?= $q['id'] ?>" class="text-xl font-semibold text-orange-600 hover:underline block">
  <?= htmlspecialchars($q['title']); ?>
</a>

              <!-- Description -->
<?php
if (!function_exists('word_limiter')) {
    function word_limiter($text, $limit = 20) {
        $words = explode(' ', strip_tags($text));
        if (count($words) > $limit) {
            return implode(' ', array_slice($words, 0, $limit)) . '...';
        }
        return $text;
    }
}
?>
              <p class="text-gray-700 mt-2"><?= htmlspecialchars(word_limiter($q['description'],20)); ?></p>

              <!-- Asked by + Date -->
              <p class="text-xs text-gray-500 mt-4">
                Asked by <span class="font-semibold text-orange-700"><?= htmlspecialchars($q['username']) ?></span>
                on <?= date('F j, Y', strtotime($q['created_at'])) ?>
              </p>

              <!-- Voting -->
              <div class="flex items-center gap-2 mt-2">
                <form method="POST" action="vote.php">
                  <input type="hidden" name="question_id" value="<?= $q['id'] ?>">
                  <input type="hidden" name="vote_type" value="up">
                  <button type="submit" class="text-orange-600 hover:text-orange-800">▲ Up</button>
                </form>
                <form method="POST" action="vote.php">
                  <input type="hidden" name="question_id" value="<?= $q['id'] ?>">
                  <input type="hidden" name="vote_type" value="down">
                  <button type="submit" class="text-gray-600 hover:text-gray-800">▼ Down</button>
                </form>
              </div>

              <!-- Comments -->
              <?php if (isset($allComments[$q['id']])): ?>
                <div class="mt-4 border-t pt-3">
                  <h3 class="text-sm font-semibold text-gray-700 mb-2">Answers:</h3>
                  <?php foreach (array_slice($allComments[$q['id']], 0, 2) as $comment): ?>
                    <div class="mb-2 bg-gray-100 p-2 rounded text-sm">
                      <?= htmlspecialchars($comment['comment']) ?>
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

    <!-- Right Sidebar -->
    <aside class="hidden lg:block w-72 px-4 py-6 bg-gray-100 rounded shadow ml-auto mt-20 mr-6 h-fit sticky top-24">
      <h3 class="text-xl font-bold text-gray-900 mb-4">Your Tags</h3>
      <div class="flex flex-wrap gap-2">
        <?php
        $tagStmt = $pdo->query("SELECT tags FROM questions");
        $allTags = [];
        while ($row = $tagStmt->fetch(PDO::FETCH_ASSOC)) {
          foreach (explode(',', $row['tags']) as $tag) {
            $tag = trim($tag);
            if ($tag !== '') {
              $allTags[$tag] = ($allTags[$tag] ?? 0) + 1;
            }
          }
        }
        arsort($allTags);
        foreach (array_slice($allTags, 0, 12) as $tag => $count): ?>
          <a href="/tag/<?= urlencode($tag) ?>"
             class="inline-flex items-center gap-1 bg-orange-600 text-white text-xs px-3 py-1 rounded-full shadow hover:bg-orange-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            <?= htmlspecialchars($tag) ?>
          </a>
        <?php endforeach; ?>
      </div>
    </aside>
  </div>

  <!-- Footer -->
  <footer class="mt-10">
    <?php include 'Partials/footer.php'; ?>
  </footer>
</body>
</html>
