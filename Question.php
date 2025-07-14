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
    case 'upvotes':
      $orderBy = "ORDER BY upvotes $order";
      break;
    case 'downvotes':
      $orderBy = "ORDER BY downvotes $order";
      break;
    case 'answers':
      $orderBy = "ORDER BY answer $order";
      break;
    case 'in 24 hours':
      $orderBy = "AND created_at >= NOW() - INTERVAL 1 DAY
                  ORDER BY created_at $order";
      break;
    case 'newest':
    default:
      $orderBy = "ORDER BY created_at $order";
      break;
  }

  $query = "SELECT id, title, description, tags, created_at, upvotes, downvotes, answer FROM questions WHERE banned = 0 $orderBy";
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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Questions</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    /* For better word wrapping in tags and question titles */
    .break-word {
      word-break: break-word;
    }
  </style>
</head>

<body class="flex flex-col min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 text-gray-800 pt-16 overflow-x-hidden">

  <?php include 'Partials/nav.php'; ?>

  <!-- Sidebar - hidden below lg -->
  <aside class="hidden lg:block fixed top-16 left-0 h-[calc(100%-4rem)] w-[200px] bg-white z-10 shadow-lg overflow-auto">
    <?php include 'Partials/left_nav.php'; ?>
  </aside>

  <main class="flex-1 min-w-[500px] md:min-w-[600px] max-w-screen-full ml-0 lg:ml-[220px] mr-0 lg:mr-10 p-6 overflow-x-auto transition-all duration-300 ease-in-out">

    <!-- Header Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
          <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-2">All Questions</h1>
          <p class="text-gray-600">Browse and filter questions from the community</p>
        </div>
        <div class="flex items-center space-x-2">
          <span class="text-sm text-gray-500">Sort by:</span>
          <div class="flex items-center space-x-1">
            <i class="fas fa-sort text-gray-400"></i>
          </div>
        </div>
      </div>

      <!-- Sorting and filter controls -->
      <div class="flex flex-wrap gap-3 items-center">
        <!-- Sorting buttons -->
        <button onclick="sortBy('newest')" class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 <?= $sort === 'newest' ? 'bg-orange-500 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
          <i class="fas fa-clock mr-2"></i>Newest
        </button>
        <button onclick="sortBy('upvotes')" class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 <?= $sort === 'upvotes' ? 'bg-orange-500 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
          <i class="fas fa-arrow-up mr-2"></i>Upvotes
        </button>
        <button onclick="sortBy('downvotes')" class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 <?= $sort === 'downvotes' ? 'bg-orange-500 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
          <i class="fas fa-arrow-down mr-2"></i>Downvotes
        </button>
        <button onclick="sortBy('answers')" class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 <?= $sort === 'answers' ? 'bg-orange-500 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
          <i class="fas fa-comments mr-2"></i>Answers
        </button>
        <button onclick="sortBy('in 24 hours')" class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 <?= $sort === 'in 24 hours' ? 'bg-orange-500 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
          <i class="fas fa-fire mr-2"></i>24 Hours
        </button>

        <!-- More Dropdown -->
        <!-- <div class="relative">
          <button onclick="toggleMore()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-all duration-200 text-sm font-medium" id="moreBtn" type="button">
            <i class="fas fa-ellipsis-h mr-2"></i>More
          </button>
          <div id="moreMenu" class="absolute hidden bg-white border border-gray-200 rounded-lg shadow-lg mt-2 w-48 z-50">
            <a href="#" class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-200">
              <i class="fas fa-trending-up mr-2 text-green-500"></i>Trending
            </a>
            <a href="#" class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-200">
              <i class="fas fa-chart-line mr-2 text-blue-500"></i>Most frequent
            </a>
            <a href="#" class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-200">
              <i class="fas fa-activity mr-2 text-purple-500"></i>Most activity
            </a>
          </div>
        </div> -->

        <!-- Filter Button -->
        <!-- <button onclick="toggleFilter()" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-all duration-200 text-sm font-medium" type="button" aria-expanded="false" aria-controls="filterPanel">
          <i class="fas fa-filter mr-2"></i>Filter
        </button> -->
      </div>
    </div>

    <!-- Filter Panel -->
    <div id="filterPanel" class="hidden bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
      <div class="mb-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
          <i class="fas fa-filter text-blue-500 mr-3"></i>
          Advanced Filters
        </h3>
      </div>
      
      <form class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Filter by -->
        <div class="space-y-3">
          <h4 class="font-semibold text-gray-700 mb-3">Filter by</h4>
          <label class="flex items-center p-2 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors duration-200">
            <input type="checkbox" class="mr-3 text-orange-500">
            <span class="text-gray-700">No answers</span>
          </label>
          <label class="flex items-center p-2 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors duration-200">
            <input type="checkbox" class="mr-3 text-orange-500">
            <span class="text-gray-700">No accepted answer</span>
          </label>
          <label class="flex items-center p-2 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors duration-200">
            <input type="checkbox" class="mr-3 text-orange-500">
            <span class="text-gray-700">Has bounty</span>
          </label>
          <div class="mt-4">
            <input type="text" placeholder="Days old" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" />
          </div>
        </div>

        <!-- Sorted by -->
        <div class="space-y-3">
          <h4 class="font-semibold text-gray-700 mb-3">Sorted by</h4>
          <label class="flex items-center p-2 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors duration-200">
            <input type="radio" name="sort" class="mr-3 text-orange-500" checked>
            <span class="text-gray-700">Newest</span>
          </label>
          <label class="flex items-center p-2 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors duration-200">
            <input type="radio" name="sort" class="mr-3 text-orange-500">
            <span class="text-gray-700">Recent activity</span>
          </label>
          <label class="flex items-center p-2 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors duration-200">
            <input type="radio" name="sort" class="mr-3 text-orange-500">
            <span class="text-gray-700">Highest score</span>
          </label>
          <label class="flex items-center p-2 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors duration-200">
            <input type="radio" name="sort" class="mr-3 text-orange-500">
            <span class="text-gray-700">Most frequent</span>
          </label>
        </div>

        <!-- Tagged with -->
        <div class="space-y-3">
          <h4 class="font-semibold text-gray-700 mb-3">Tagged with</h4>
          <label class="flex items-center p-2 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors duration-200">
            <input type="radio" name="tags" class="mr-3 text-orange-500">
            <span class="text-gray-700">My watched tags</span>
          </label>
          <label class="block">
            <input type="radio" name="tags" class="mr-3 text-orange-500" checked>
            <span class="text-gray-700">The following tags:</span>
            <input type="text" class="mt-2 w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="e.g. javascript or python" />
          </label>
        </div>
      </form>

      <!-- Action buttons -->
      <div class="mt-6 flex flex-col sm:flex-row justify-between gap-4 pt-4 border-t border-gray-200">
        <div class="flex flex-col sm:flex-row gap-3">
          <button class="px-6 py-2 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:from-orange-600 hover:to-orange-700 transition-all duration-200 font-medium">
            <i class="fas fa-check mr-2"></i>Apply filter
          </button>
          <button class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-all duration-200 font-medium">
            <i class="fas fa-save mr-2"></i>Save custom filter
          </button>
        </div>
        <button onclick="toggleFilter()" type="button" class="text-blue-500 hover:text-blue-700 transition-colors duration-200 text-sm font-medium self-start sm:self-center">
          <i class="fas fa-times mr-1"></i>Cancel
        </button>
      </div>
    </div>

    <!-- Question List -->
    <div class="space-y-4">
      <?php if (!empty($questions)): ?>
        <?php foreach ($questions as $q):
          $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $q['title'])));
          $tags = array_filter(array_map('trim', explode(',', $q['tags'])));
        ?>
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
            
            <!-- Header with Options -->
            <div class="flex justify-between items-start mb-4">
              <div class="flex-1">
                <a href="questionDetails.php?id=<?= $q['id'] ?>" class="text-xl font-bold text-gray-800 hover:text-orange-600 transition-colors duration-200 block mb-2 break-word">
                  <?php echo htmlspecialchars($q['title']); ?>
                </a>
                <p class="text-gray-600 text-sm leading-relaxed">
                  <?php echo word_limiter($q['description'], 100); ?>
                </p>
              </div>
              
              <!-- Options Dropdown -->
              <div class="relative ml-4">
                <button onclick="toggleDropdown(this)" class="p-2 rounded-lg hover:bg-gray-100 transition-colors duration-200" aria-label="Options">
                  <i class="fas fa-ellipsis-v text-gray-500"></i>
                </button>
                <div class="dropdown absolute right-0 top-full mt-2 hidden bg-white shadow-lg rounded-lg text-sm z-50 w-36 border border-gray-200">
                  <a href="../Actions/save.php?id=<?= $q['id'] ?>" class="block px-4 py-3 hover:bg-gray-50 rounded-t-lg transition-colors duration-200">
                    <i class="fas fa-bookmark mr-2 text-blue-500"></i>Save
                  </a>
                  <button class="block w-full text-left px-4 py-3 hover:bg-gray-50 rounded-b-lg transition-colors duration-200"
                    onclick="openReportModal(<?= $q['id'] ?>)">
                    <i class="fas fa-flag mr-2 text-red-500"></i>Report
                  </button>
                </div>
              </div>
            </div>

            <!-- Tags -->
            <div class="flex flex-wrap gap-2 mb-4">
              <?php foreach ($tags as $tag): ?>
                <span class="bg-gradient-to-r from-orange-100 to-orange-200 text-orange-700 text-xs px-3 py-1 rounded-full font-medium break-word">
                  <?php echo htmlspecialchars($tag); ?>
                </span>
              <?php endforeach; ?>
            </div>

            <!-- Stats and Meta -->
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-6">
                <!-- Stats -->
                <div class="flex items-center space-x-4">
                  <div class="flex items-center space-x-2">
                    <i class="fas fa-arrow-up text-green-500"></i>
                    <span class="text-sm font-bold text-gray-700"><?php echo (int)$q['upvotes']; ?></span>
                    <span class="text-xs text-gray-500">upvotes</span>
                  </div>
                  <div class="flex items-center space-x-2">
                    <i class="fas fa-arrow-down text-red-500"></i>
                    <span class="text-sm font-bold text-gray-700"><?php echo (int)$q['downvotes']; ?></span>
                    <span class="text-xs text-gray-500">downvotes</span>
                  </div>
                  <div class="flex items-center space-x-2">
                    <i class="fas fa-comments text-blue-500"></i>
                    <span class="text-sm font-bold text-gray-700"><?php echo (int)$q['answer']; ?></span>
                    <span class="text-xs text-gray-500">answers</span>
                  </div>
                </div>
              </div>

              <!-- Date -->
              <div class="text-xs text-gray-500 flex items-center">
                <i class="fas fa-calendar mr-1"></i>
                asked <?php echo date("M j, Y g:i a", strtotime($q['created_at'])); ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
          <i class="fas fa-question-circle text-6xl text-gray-300 mb-4"></i>
          <h3 class="text-xl font-semibold text-gray-700 mb-2">No Questions Found</h3>
          <p class="text-gray-500">No questions match your current filters.</p>
        </div>
      <?php endif; ?>
    </div>

  </main>

  <!-- Report Modal -->
  <div id="reportModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl shadow-xl max-w-lg w-full p-6 relative mx-4">
      <button id="closeReportModal" class="absolute top-4 right-4 text-gray-500 hover:text-gray-900 text-xl font-bold p-2 hover:bg-gray-100 rounded-lg transition-colors duration-200">
        <i class="fas fa-times"></i>
      </button>
      <h2 class="text-xl font-bold mb-6 flex items-center">
        <i class="fas fa-flag text-red-500 mr-3"></i>
        Report Question
      </h2>
      <form id="reportForm" class="space-y-6">
        <textarea name="reason" placeholder="Please explain the reason for reporting..." required rows="5"
          class="w-full p-4 border border-gray-300 rounded-lg resize-none focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"></textarea>
        <button type="submit" class="w-full px-6 py-3 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-lg shadow-md hover:shadow-lg transition-all duration-200 font-medium">
          <i class="fas fa-paper-plane mr-2"></i>
          Submit Report
        </button>
      </form>
      <div id="reportMessage" class="mt-4 text-center"></div>
    </div>
  </div>

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

    function toggleDropdown(button) {
      const dropdown = button.parentElement.querySelector('.dropdown');
      dropdown.classList.toggle('hidden');
    }
    document.addEventListener('click', function(e) {
      document.querySelectorAll('.dropdown').forEach(d => {
        if (!d.parentElement.contains(e.target)) d.classList.add('hidden');
      });
    });

    let currentReportQuestionId = null;

    function openReportModal(questionId) {
      currentReportQuestionId = questionId;
      document.getElementById('reportModal').classList.remove('hidden');
      document.getElementById('reportMessage').textContent = '';
      document.getElementById('reportForm').reset();
    }

    document.getElementById('closeReportModal').addEventListener('click', () => {
      document.getElementById('reportModal').classList.add('hidden');
    });

    // Optional: close modal on clicking outside modal content
    document.getElementById('reportModal').addEventListener('click', (e) => {
      if (e.target.id === 'reportModal') {
        document.getElementById('reportModal').classList.add('hidden');
      }
    });

    document.getElementById('reportForm').addEventListener('submit', async function(e) {
      e.preventDefault();
      const reason = this.reason.value.trim();
      const messageDiv = document.getElementById('reportMessage');
      messageDiv.textContent = '';
      messageDiv.className = '';

      if (!reason) {
        messageDiv.textContent = 'Please provide a reason for reporting.';
        messageDiv.className = 'text-red-600 mt-2';
        return;
      }

      try {
        const response = await fetch(`../Actions/report.php?id=${currentReportQuestionId}`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: new URLSearchParams({
            reason
          })
        });

        const text = await response.text();

        if (response.ok) {
          messageDiv.textContent = 'Report submitted successfully. Closing modal...';
          messageDiv.className = 'text-green-600 mt-2';
          setTimeout(() => {
            document.getElementById('reportModal').classList.add('hidden');
          }, 2000);
        } else {
          messageDiv.textContent = text || 'Error submitting report.';
          messageDiv.className = 'text-red-600 mt-2';
        }
      } catch (err) {
        messageDiv.textContent = 'Network error. Please try again later.';
        messageDiv.className = 'text-red-600 mt-2';
      }
    });
  </script>

  <div class="mt-auto z-10">
    <?php include 'Partials/footer.php'; ?>
  </div>
</body>

</html>