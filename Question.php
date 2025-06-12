<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
require_once 'DBConnection/DBConnector.php'; // Remote DB (questions, users)
require_once 'Partials/Limiter.php';
try {
  $sort = $_GET['sort'] ?? 'newest';
  $order = strtoupper($_GET['order'] ?? 'DESC');
  $order = $order === 'ASC' ? 'ASC' : 'DESC'; // prevent SQL injection

  switch ($sort) {
    // case 'active':
    //   $orderBy = "ORDER BY updated_at $order";
    //   break;
    case 'newest':
    default:
      $orderBy = "ORDER BY created_at $order";
      break;
  }

  $query = "SELECT id, title, description, tags, created_at, upvotes, downvotes, answer FROM questions $orderBy";
  $stmt = $pdo->query($query);
  $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  echo "<div class='text-red-500'>Error fetching questions: " . $e->getMessage() . "</div>";
  $questions = [];
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Questions</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="flex flex-col min-h-screen bg-gray-50 text-black pt-20 overflow-x-hidden">
  <?php include 'Partials/nav.php'; ?>
  <aside class="hidden lg:block fixed top-0 left-0 h-[calc(100%-0rem)] w-[200px] bg-white z-10 shadow">
    <?php include 'Partials/left_nav.php'; ?>
  </aside>

  <main class="flex-1 min-w-full md:min-w-[500px] max-w-screen-full ml-[220px] lg:mr-10 p-4 overflow-x-auto">
    <div class="flex flex-wrap gap-2 items-center relative text-sm">
      <button onclick="sortBy('newest')" class="px-3 py-1 border border-gray-300 rounded text-black hover:bg-gray-200">Newest</button>
      <button onclick="sortBy('active')" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-200">Active</button>
      <button class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-200">Bountied</button>
      <button class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-200">Unanswered</button>

      <!-- More Dropdown -->
      <div class="relative">
        <button onclick="toggleMore()" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-200" id="moreBtn">More</button>
        <div id="moreMenu" class="absolute hidden bg-white border border-gray-300 rounded shadow mt-1 w-48 z-50">
          <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:bg-gray-200">Trending</a>
          <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:bg-gray-200">Most frequent</a>
          <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:bg-gray-200">Most activity</a>
        </div>
      </div>

      <!-- Filter Button -->
      <button onclick="toggleFilter()" class="ml-auto px-2 py-0 border border-blue-500 text-blue-500 rounded hover:bg-blue-50">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
        </svg>

      </button>
    </div>

    <!-- Filter Panel -->
    <div id="filterPanel" class="hidden bg-white text-black p-6 mt-4 rounded-md shadow-md max-w-4xl mx-auto">
      <form class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Filter by -->
        <div>
          <h3 class="font-semibold mb-2">Filter by</h3>
          <label class="block"><input type="checkbox" class="mr-2"> No answers</label>
          <label class="block"><input type="checkbox" class="mr-2"> No accepted answer</label>
          <label class="block"><input type="checkbox" class="mr-2"> No Staging Ground</label>
          <label class="block"><input type="checkbox" class="mr-2"> Has bounty</label>
          <input type="text" placeholder="Days old" class="mt-2 px-2 py-1 border rounded w-full">
        </div>

        <!-- Sorted by -->
        <div>
          <h3 class="font-semibold mb-2">Sorted by</h3>
          <label class="block"><input type="radio" name="sort" class="mr-2" checked> Newest</label>
          <label class="block"><input type="radio" name="sort" class="mr-2"> Recent activity</label>
          <label class="block"><input type="radio" name="sort" class="mr-2"> Highest score</label>
          <label class="block"><input type="radio" name="sort" class="mr-2"> Most frequent</label>
        </div>

        <!-- Tagged with -->
        <div>
          <h3 class="font-semibold mb-2">Tagged with</h3>
          <label class="block"><input type="radio" name="tags" class="mr-2"> My watched tags</label>
          <label class="block mt-2">
            <input type="radio" name="tags" class="mr-2" checked> The following tags:
            <input type="text" class="mt-1 px-2 py-1 border rounded w-full" placeholder="e.g. javascript or python">
          </label>
        </div>
      </form>

      <!-- Action buttons -->
      <div class="mt-6 flex flex-col sm:flex-row justify-between gap-4">
        <div>
          <button class="px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600">Apply filter</button>
          <button class="ml-2 px-4 py-2 bg-gray-100 text-black rounded hover:bg-gray-200">Save custom filter</button>
        </div>
        <button onclick="toggleFilter()" class="text-blue-500 hover:underline text-sm">Cancel</button>
      </div>
    </div>

    <!-- Question List -->
    <div class="mt-4 space-y-4">
      <?php foreach ($questions as $q):
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $q['title'])));
        $tags = array_filter(array_map('trim', explode(',', $q['tags'])));
      ?>
        <div class="bg-white shadow p-4 rounded flex flex-col sm:flex-row gap-4">
          <div class="flex sm:flex-col text-sm text-center w-full sm:w-16 text-gray-500">
            <div class="sm:mb-2">
              <strong><?php echo (int)$q['upvotes']; ?></strong>
              <br class="hidden sm:block">upvotes
            </div>
            <div class="sm:mb-2"><strong><?php echo (int)$q['downvotes']; ?></strong><br class="hidden sm:block">downvotes</div>
            <div class="sm:mb-2"><strong><?php echo (int)$q['answer']; ?></strong><br class="hidden sm:block">answers</div>

          </div>
          <div class="flex-1">
            <a href="questionDetails.php?id=<?= $q['id'] ?>" class="text-lg font-semibold text-orange-600 hover:underline">
              <?php echo htmlspecialchars($q['title']); ?>
            </a>
            <p class="text-sm text-gray-600 mt-1">
              <?php echo '<p class="text-sm text-gray-600 mt-1">' . word_limiter($q['description'], 100) . '</p>'; ?>
            </p>
            <div class="mt-2 flex flex-wrap gap-2">
              <?php foreach ($tags as $tag): ?>
                <span class="inline-block bg-orange-100 text-orange-700 text-sm px-2 py-1 rounded"><?php echo htmlspecialchars($tag); ?></span>
              <?php endforeach; ?>
            </div>
            <div class="text-xs text-gray-400 text-right mt-2">
              asked <?php echo date("M j, Y g:i a", strtotime($q['created_at'])); ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

  </main>

  <!-- Script for toggling -->
  <script>
    function sortBy(type) {
      const url = new URL(window.location.href);
      const currentSort = url.searchParams.get('sort');
      const currentOrder = url.searchParams.get('order') || 'desc';

      // If clicking the same sort type again, toggle the order
      if (currentSort === type) {
        const newOrder = currentOrder === 'desc' ? 'asc' : 'desc';
        url.searchParams.set('order', newOrder);
      } else {
        url.searchParams.set('order', 'desc'); // default to DESC on new sort type
      }

      url.searchParams.set('sort', type);
      window.location.href = url.toString();
    }

    function toggleFilter() {
      const panel = document.getElementById("filterPanel");
      panel.classList.toggle("hidden");
    }

    function toggleMore() {
      const menu = document.getElementById("moreMenu");
      menu.classList.toggle("hidden");

      document.addEventListener('click', function handler(e) {
        if (!e.target.closest('#moreBtn')) {
          menu.classList.add("hidden");
          document.removeEventListener('click', handler);
        }
      });
    }
  </script>
  <div class="mt-auto z-10">
    <?php include 'Partials/footer.php'; ?>
  </div>
</body>

</html>