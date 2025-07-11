<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);

require_once 'DBConnection/DBConnector.php';
require_once 'Partials/Limiter.php';

if (!$pdo) {
  die("❌ Database connection failed. Check DBConnector.php.");
}

$search = $_GET['search'] ?? '';
$searchParam = '%' . $search . '%';
$tag = $_GET['tag'] ?? null;

if (!$tag) {
  if (!empty($search)) {
    $stmt = $pdo->prepare("
            SELECT 
        t.tag_name, 
        t.description,
        (SELECT COUNT(*) FROM questions q WHERE FIND_IN_SET(t.tag_name, q.tags) AND q.banned = 0) AS question_count
      FROM tags t
      WHERE t.tag_name LIKE :search
      ORDER BY question_count
        ");
    $stmt->bindValue(':search', $searchParam, PDO::PARAM_STR);
  } else {
    $stmt = $pdo->prepare("
            SELECT 
        t.tag_name, 
        t.description,
        (SELECT COUNT(*) FROM questions q WHERE FIND_IN_SET(t.tag_name, q.tags) AND q.banned = 0) AS question_count
      FROM tags t
      ORDER BY question_count
        ");
  }
  $stmt->execute();
  $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$questions = [];
if ($tag) {
  $stmt = $pdo->prepare("
        SELECT id, title, description, tags, created_at, upvotes, downvotes, answer 
        FROM questions 
        WHERE FIND_IN_SET(:tag, tags)
        ORDER BY created_at DESC
    ");
  $stmt->execute(['tag' => $tag]);
  $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Tags</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 text-black min-h-screen pt-16 flex flex-col">
  <?php include 'Partials/nav.php'; ?>

  <!-- Sidebar (desktop only) -->
  <aside class="hidden lg:block fixed top-16 left-0 h-full w-[200px] bg-white shadow">
    <?php include 'Partials/left_nav.php'; ?>
  </aside>

  <main class="flex-1 min-w-[500px] md:min-w-[600px] max-w-screen-fullml-0 lg:ml-[220px] mr-0 lg:mr-10 p-4 overflow-x-auto transition-all duration-300 ease-in-out">
    <!-- <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-gray-800"><?= $tag ? 'Questions with Your tags' : 'Tags' ?></h1>
    </div> -->

    <?php if (!$tag): ?>
      <!-- Tag Search -->
      <div class="mb-6">
        <form method="GET" action="tag.php" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
          <input
            type="text"
            name="search"
            placeholder="Search by tags"
            value="<?= htmlspecialchars($search) ?>"
            class="w-full sm:w-72 px-3.5 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
          <button type="submit" class="px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600 text-sm">
            Search
          </button>
        </form>
      </div>

      <!-- Tags Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php if (!empty($tags)): ?>
          <?php foreach ($tags as $tagItem): ?>
            <a href="tag.php?tag=<?= urlencode($tagItem['tag_name']) ?>" class="block bg-white shadow rounded-lg p-4 hover:shadow-md transition">
              <span class="inline-block bg-orange-100 text-orange-600 text-xs font-semibold px-2 py-1 rounded mb-2">
                <?= htmlspecialchars($tagItem['tag_name']) ?>
              </span>
              <p class="text-gray-700 text-sm mb-1 line-clamp-3">
                <?= htmlspecialchars($tagItem['description']) ?>
              </p>
              <p class="text-xs text-gray-500">
                <?= (int)$tagItem['question_count'] ?> questions
              </p>
            </a>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="text-gray-500 col-span-full">No tags found.</p>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <?php if ($tag): ?>
      <!-- Questions List -->
      <div class="mt-4 space-y-4">
        <div class="flex justify-between items-center mb-4 flex-wrap gap-2">
          <h2 class="text-2xl font-bold text-orange-600">Showing results for tag:
            <span class="bg-orange-100 px-2 py-1 rounded"><?= htmlspecialchars($tag) ?></span>
          </h2>
          <a href="tag.php" class="text-blue-600 text-sm">&larr; Back to All Tags</a>
        </div>

        <?php if (empty($questions)): ?>
          <p class="text-gray-500">No questions found for <strong><?= htmlspecialchars($tag) ?></strong>.</p>
        <?php else: ?>
          <?php foreach ($questions as $q):
            $qTags = array_filter(array_map('trim', explode(',', $q['tags'])));
          ?>
            <div class="bg-white shadow p-4 rounded flex flex-col md:flex-row gap-4">
              <div class="flex flex-row md:flex-col justify-between md:justify-start text-sm text-center w-full md:w-16 text-gray-500 shrink-0">
                <div><strong><?= (int)$q['upvotes']; ?></strong><br class="hidden md:block">upvotes</div>
                <div><strong><?= (int)$q['downvotes']; ?></strong><br class="hidden md:block">downvotes</div>
                <div><strong><?= (int)$q['answer']; ?></strong><br class="hidden md:block">answers</div>
              </div>
              <div class="flex-1">
                <a href="questionDetails.php?id=<?= $q['id'] ?>" class="text-lg font-semibold text-orange-600 hover:underline">
                  <?= htmlspecialchars($q['title']); ?>
                </a>
                <p class="text-sm text-gray-600 mt-1">
                  <?= word_limiter($q['description'], 100); ?>
                </p>
                <div class="mt-2 flex flex-wrap gap-2">
                  <?php foreach ($qTags as $qt): ?>
                    <a href="tag.php?tag=<?= urlencode($qt) ?>" class="inline-block bg-orange-100 text-orange-700 text-sm px-2 py-1 rounded hover:bg-orange-200">
                      <?= htmlspecialchars($qt); ?>
                    </a>
                  <?php endforeach; ?>
                </div>
                <div class="text-xs text-gray-400 text-right mt-2">
                  asked <?= date("M j, Y g:i a", strtotime($q['created_at'])); ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </main>

  <footer class="mt-auto">
    <?php include 'Partials/footer.php'; ?>
  </footer>
</body>

</html>