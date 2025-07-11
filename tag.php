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

// Pagination setup
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$tagsPerPage = 8;
$offset = ($page - 1) * $tagsPerPage;

if (!$tag) {
  // Count total tags
  $countSql = "SELECT COUNT(*) FROM tags" . (!empty($search) ? " WHERE tag_name LIKE :search" : "");
  $totalTagsStmt = $pdo->prepare($countSql);
  if (!empty($search)) {
    $totalTagsStmt->bindValue(':search', $searchParam, PDO::PARAM_STR);
  }
  $totalTagsStmt->execute();
  $totalTags = $totalTagsStmt->fetchColumn();
  $totalPages = ceil($totalTags / $tagsPerPage);

  // Fetch paginated tags
  $query = "
    SELECT 
      t.tag_name, 
      t.description,
      (SELECT COUNT(*) FROM questions q WHERE FIND_IN_SET(t.tag_name, q.tags) AND q.banned = 0) AS question_count
    FROM tags t
    " . (!empty($search) ? " WHERE t.tag_name LIKE :search" : "") . "
    ORDER BY question_count
    LIMIT :limit OFFSET :offset
  ";
  $stmt = $pdo->prepare($query);
  if (!empty($search)) {
    $stmt->bindValue(':search', $searchParam, PDO::PARAM_STR);
  }
  $stmt->bindValue(':limit', $tagsPerPage, PDO::PARAM_INT);
  $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
  $stmt->execute();
  $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
  // Fetch questions by tag
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

  <main class="flex-1 min-w-[500px] md:min-w-[600px] max-w-screen-full ml-0 lg:ml-[220px] mr-0 lg:mr-10 p-4 overflow-x-auto transition-all duration-300 ease-in-out">
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
          <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded hover:bg-orange-700 text-sm">
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

      <!-- Pagination -->
      <?php if ($totalPages > 1): ?>
        <div class="fixed bottom-28 left-1/2 flex justify-center mt-6 space-x-2">
          <?php if ($page > 1): ?>
            <a href="tag.php?page=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"
              class="px-3 py-1 rounded bg-gray-200 text-gray-700 hover:bg-orange-500 hover:text-white">
              &laquo;
            </a>
          <?php endif; ?>

          <?php
          $start = max(2, $page - 2);
          $end = min($totalPages - 1, $page + 2);

          if ($start > 2) {
            // show first page
            echo '<a href="tag.php?page=1' . (!empty($search) ? '&search=' . urlencode($search) : '') . '" class="px-3 py-1 rounded ' . ($page == 1 ? 'bg-orange-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-orange-500 hover:text-white') . '">1</a>';
            echo '<span class="px-2">...</span>';
          } else {
            for ($i = 1; $i < $start; $i++) {
              echo '<a href="tag.php?page=' . $i . (!empty($search) ? '&search=' . urlencode($search) : '') . '" class="px-3 py-1 rounded ' . ($i == $page ? 'bg-orange-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-orange-500 hover:text-white') . '">' . $i . '</a>';
            }
          }

          for ($i = $start; $i <= $end; $i++) {
            echo '<a href="tag.php?page=' . $i . (!empty($search) ? '&search=' . urlencode($search) : '') . '" class="px-3 py-1 rounded ' . ($i == $page ? 'bg-orange-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-orange-500 hover:text-white') . '">' . $i . '</a>';
          }

          if ($end < $totalPages - 1) {
            echo '<span class="px-2">...</span>';
            echo '<a href="tag.php?page=' . $totalPages . (!empty($search) ? '&search=' . urlencode($search) : '') . '" class="px-3 py-1 rounded ' . ($page == $totalPages ? 'bg-orange-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-orange-500 hover:text-white') . '">' . $totalPages . '</a>';
          } else {
            for ($i = $end + 1; $i <= $totalPages; $i++) {
              echo '<a href="tag.php?page=' . $i . (!empty($search) ? '&search=' . urlencode($search) : '') . '" class="px-3 py-1 rounded ' . ($i == $page ? 'bg-orange-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-orange-500 hover:text-white') . '">' . $i . '</a>';
            }
          }
          ?>

          <?php if ($page < $totalPages): ?>
            <a href="tag.php?page=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"
              class="px-3 py-1 rounded bg-gray-200 text-gray-700 hover:bg-orange-500 hover:text-white">
              &raquo;
            </a>
          <?php endif; ?>
        </div>
      <?php endif; ?>

    <?php else: ?>
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