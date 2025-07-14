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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="bg-gradient-to-br from-gray-50 to-gray-100 text-gray-800 min-h-screen pt-16 flex flex-col">

  <?php include 'Partials/nav.php'; ?>

  <!-- Sidebar (desktop only) -->
  <aside class="hidden lg:block fixed top-16 left-0 h-full w-[200px] bg-white shadow-lg">
    <?php include 'Partials/left_nav.php'; ?>
  </aside>

  <main class="flex-1 min-w-[500px] md:min-w-[600px] max-w-screen-full ml-0 lg:ml-[220px] mr-0 lg:mr-10 p-6 overflow-x-auto transition-all duration-300 ease-in-out">
    
    <?php if (!$tag): ?>
      <!-- Header Section -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
          <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-2">Tags</h1>
            <p class="text-gray-600">Discover topics and find questions by category</p>
          </div>
          <div class="flex items-center space-x-2">
            <i class="fas fa-tags text-orange-500 text-xl"></i>
            <span class="text-sm text-gray-500"><?= $totalTags ?> tags available</span>
          </div>
        </div>

        <!-- Tag Search -->
        <form method="GET" action="tag.php" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
          <div class="relative flex-1">
            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
            <input
              type="text"
              name="search"
              placeholder="Search by tags..."
              value="<?= htmlspecialchars($search) ?>"
              class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
          </div>
          <button type="submit" class="px-6 py-3 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:from-orange-600 hover:to-orange-700 transition-all duration-200 font-medium shadow-md hover:shadow-lg">
            <i class="fas fa-search mr-2"></i>Search
          </button>
        </form>
      </div>

      <!-- Tags Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <?php if (!empty($tags)): ?>
          <?php foreach ($tags as $tagItem): ?>
            <a href="tag.php?tag=<?= urlencode($tagItem['tag_name']) ?>" class="group block bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md hover:border-orange-200 transition-all duration-200">
              <div class="flex items-center justify-between mb-3">
                <span class="inline-block bg-gradient-to-r from-orange-100 to-orange-200 text-orange-700 text-sm font-semibold px-3 py-1 rounded-full">
                  <?= htmlspecialchars($tagItem['tag_name']) ?>
                </span>
                <i class="fas fa-chevron-right text-gray-400 group-hover:text-orange-500 transition-colors duration-200"></i>
              </div>
              <p class="text-gray-600 text-sm mb-4 line-clamp-3 leading-relaxed">
                <?= htmlspecialchars($tagItem['description']) ?>
              </p>
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <i class="fas fa-question-circle text-blue-500"></i>
                  <span class="text-sm font-semibold text-gray-700"><?= (int)$tagItem['question_count'] ?></span>
                  <span class="text-xs text-gray-500">questions</span>
                </div>
                <div class="text-xs text-gray-400 group-hover:text-orange-500 transition-colors duration-200">
                  View details →
                </div>
              </div>
            </a>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-span-full bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
            <i class="fas fa-tags text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">No Tags Found</h3>
            <p class="text-gray-500">Try adjusting your search criteria.</p>
          </div>
        <?php endif; ?>
      </div>

      <!-- Pagination -->
      <?php if ($totalPages > 1): ?>
        <div class="flex justify-center mt-8">
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <div class="flex items-center space-x-2">
              <?php if ($page > 1): ?>
                <a href="tag.php?page=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"
                  class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-orange-500 hover:text-white transition-all duration-200">
                  <i class="fas fa-chevron-left mr-1"></i>Previous
                </a>
              <?php endif; ?>

              <?php
              $start = max(2, $page - 2);
              $end = min($totalPages - 1, $page + 2);

              if ($start > 2) {
                // show first page
                echo '<a href="tag.php?page=1' . (!empty($search) ? '&search=' . urlencode($search) : '') . '" class="px-3 py-2 rounded-lg ' . ($page == 1 ? 'bg-orange-500 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-orange-500 hover:text-white') . ' transition-all duration-200">1</a>';
                echo '<span class="px-2 text-gray-400">...</span>';
              } else {
                for ($i = 1; $i < $start; $i++) {
                  echo '<a href="tag.php?page=' . $i . (!empty($search) ? '&search=' . urlencode($search) : '') . '" class="px-3 py-2 rounded-lg ' . ($i == $page ? 'bg-orange-500 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-orange-500 hover:text-white') . ' transition-all duration-200">' . $i . '</a>';
                }
              }

              for ($i = $start; $i <= $end; $i++) {
                echo '<a href="tag.php?page=' . $i . (!empty($search) ? '&search=' . urlencode($search) : '') . '" class="px-3 py-2 rounded-lg ' . ($i == $page ? 'bg-orange-500 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-orange-500 hover:text-white') . ' transition-all duration-200">' . $i . '</a>';
              }

              if ($end < $totalPages - 1) {
                echo '<span class="px-2 text-gray-400">...</span>';
                echo '<a href="tag.php?page=' . $totalPages . (!empty($search) ? '&search=' . urlencode($search) : '') . '" class="px-3 py-2 rounded-lg ' . ($page == $totalPages ? 'bg-orange-500 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-orange-500 hover:text-white') . ' transition-all duration-200">' . $totalPages . '</a>';
              } else {
                for ($i = $end + 1; $i <= $totalPages; $i++) {
                  echo '<a href="tag.php?page=' . $i . (!empty($search) ? '&search=' . urlencode($search) : '') . '" class="px-3 py-2 rounded-lg ' . ($i == $page ? 'bg-orange-500 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-orange-500 hover:text-white') . ' transition-all duration-200">' . $i . '</a>';
                }
              }
              ?>

              <?php if ($page < $totalPages): ?>
                <a href="tag.php?page=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"
                  class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-orange-500 hover:text-white transition-all duration-200">
                  Next<i class="fas fa-chevron-right ml-1"></i>
                </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endif; ?>

    <?php else: ?>
      <!-- Questions List -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex justify-between items-center mb-6">
          <div class="flex items-center space-x-4">
            <div class="w-12 h-12 bg-gradient-to-r from-orange-400 to-orange-600 rounded-full flex items-center justify-center text-white text-xl font-bold">
              <i class="fas fa-tag"></i>
            </div>
            <div>
              <h2 class="text-2xl font-bold text-gray-800">Questions tagged with</h2>
              <div class="flex items-center space-x-2 mt-1">
                <span class="bg-gradient-to-r from-orange-100 to-orange-200 text-orange-700 px-3 py-1 rounded-full font-medium">
                  <?= htmlspecialchars($tag) ?>
                </span>
                <span class="text-sm text-gray-500"><?= count($questions) ?> questions</span>
              </div>
            </div>
          </div>
          <a href="tag.php" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-all duration-200 text-sm font-medium">
            <i class="fas fa-arrow-left mr-2"></i>Back to All Tags
          </a>
        </div>
      </div>

      <div class="space-y-4">
        <?php if (empty($questions)): ?>
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
            <i class="fas fa-question-circle text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">No Questions Found</h3>
            <p class="text-gray-500">No questions found for <strong><?= htmlspecialchars($tag) ?></strong>.</p>
          </div>
        <?php else: ?>
          <?php foreach ($questions as $q):
            $qTags = array_filter(array_map('trim', explode(',', $q['tags'])));
          ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
              
              <!-- Header with Stats -->
              <div class="flex justify-between items-start mb-4">
                <div class="flex-1">
                  <a href="questionDetails.php?id=<?= $q['id'] ?>" class="text-xl font-bold text-gray-800 hover:text-orange-600 transition-colors duration-200 block mb-2">
                    <?= htmlspecialchars($q['title']); ?>
                  </a>
                  <p class="text-gray-600 text-sm leading-relaxed">
                    <?= word_limiter($q['description'], 100); ?>
                  </p>
                </div>
              </div>

              <!-- Tags -->
              <div class="flex flex-wrap gap-2 mb-4">
                <?php foreach ($qTags as $qt): ?>
                  <a href="tag.php?tag=<?= urlencode($qt) ?>" class="inline-block bg-gradient-to-r from-orange-100 to-orange-200 text-orange-700 text-xs px-3 py-1 rounded-full font-medium hover:from-orange-200 hover:to-orange-300 transition-all duration-200">
                    <?= htmlspecialchars($qt); ?>
                  </a>
                <?php endforeach; ?>
              </div>

              <!-- Stats and Meta -->
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-6">
                  <!-- Stats -->
                  <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                      <i class="fas fa-arrow-up text-green-500"></i>
                      <span class="text-sm font-bold text-gray-700"><?= (int)$q['upvotes']; ?></span>
                      <span class="text-xs text-gray-500">upvotes</span>
                    </div>
                    <div class="flex items-center space-x-2">
                      <i class="fas fa-arrow-down text-red-500"></i>
                      <span class="text-sm font-bold text-gray-700"><?= (int)$q['downvotes']; ?></span>
                      <span class="text-xs text-gray-500">downvotes</span>
                    </div>
                    <div class="flex items-center space-x-2">
                      <i class="fas fa-comments text-blue-500"></i>
                      <span class="text-sm font-bold text-gray-700"><?= (int)$q['answer']; ?></span>
                      <span class="text-xs text-gray-500">answers</span>
                    </div>
                  </div>
                </div>

                <!-- Date -->
                <div class="text-xs text-gray-500 flex items-center">
                  <i class="fas fa-calendar mr-1"></i>
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