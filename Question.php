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
  <style>
    /* For better word wrapping in tags and question titles */
    .break-word {
      word-break: break-word;
    }
  </style>
</head>

<body class="flex flex-col min-h-screen bg-gray-50 text-black pt-16 overflow-x-hidden">

  <?php include 'Partials/nav.php'; ?>

  <!-- Sidebar - hidden below lg -->
  <aside class="hidden lg:block fixed top-16 left-0 h-[calc(100%-4rem)] w-[200px] bg-white z-10 shadow overflow-auto">
    <?php include 'Partials/left_nav.php'; ?>
  </aside>

  <main class="flex-1 min-w-[500px] md:min-w-[600px] max-w-screen-full ml-0 lg:ml-[220px] mr-0 lg:mr-10 p-4 overflow-x-auto transition-all duration-300 ease-in-out">

    <!-- Sorting and filter controls -->
    <div class="flex flex-wrap gap-2 items-center relative text-sm">

      <!-- Sorting buttons -->
      <button onclick="sortBy('upvotes')" class="px-3 py-1 border border-gray-300 rounded text-black hover:bg-gray-200 whitespace-nowrap">
        Upvotes
      </button>
      <button onclick="sortBy('downvotes')" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-200 whitespace-nowrap">
        Downvotes
      </button>
      <button onclick="sortBy('answers')" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-200 whitespace-nowrap">
        Answers
      </button>
      <button onclick="sortBy('in 24 hours')" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-200 whitespace-nowrap">
        In 24 hours
      </button>

      <!-- More Dropdown -->
      <div class="relative">
        <button onclick="toggleMore()" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-200 whitespace-nowrap" id="moreBtn" type="button">
          More
        </button>
        <div id="moreMenu" class="absolute hidden bg-white border border-gray-300 rounded shadow mt-1 w-48 z-50">
          <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:bg-gray-200">Trending</a>
          <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:bg-gray-200">Most frequent</a>
          <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:bg-gray-200">Most activity</a>
        </div>
      </div>

      <!-- Filter Button -->
      <button onclick="toggleFilter()" class="px-2 py-0 border border-blue-500 text-blue-500 rounded hover:bg-blue-50 whitespace-nowrap" type="button" aria-expanded="false" aria-controls="filterPanel">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 inline-block align-middle">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
        </svg>
        <span class="sr-only">Toggle filter panel</span>
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
          <input type="text" placeholder="Days old" class="mt-2 px-2 py-1 border rounded w-full" />
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
            <input type="text" class="mt-1 px-2 py-1 border rounded w-full" placeholder="e.g. javascript or python" />
          </label>
        </div>
      </form>

      <!-- Action buttons -->
      <div class="mt-6 flex flex-col sm:flex-row justify-between gap-4">
        <div>
          <button class="px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600">Apply filter</button>
          <button class="ml-2 px-4 py-2 bg-gray-100 text-black rounded hover:bg-gray-200">Save custom filter</button>
        </div>
        <button onclick="toggleFilter()" type="button" class="text-blue-500 hover:underline text-sm self-start sm:self-center">Cancel</button>
      </div>
    </div>

    <!-- Question List -->
    <div class="mt-4 space-y-4">
      <?php foreach ($questions as $q):
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $q['title'])));
        $tags = array_filter(array_map('trim', explode(',', $q['tags'])));
      ?>
        <div class=" relative bg-white shadow p-4 rounded flex flex-col sm:flex-row gap-4">
          <div class="flex sm:flex-col text-sm text-center w-full sm:w-16 text-gray-500 gap-4">
            <div class=""><strong><?php echo (int)$q['upvotes']; ?></strong><br class="hidden sm:block"> upvotes</div>
            <div class=""><strong><?php echo (int)$q['downvotes']; ?></strong><br class="hidden sm:block"> downvotes</div>
            <div class=""><strong><?php echo (int)$q['answer']; ?></strong><br class="hidden sm:block"> answers</div>
          </div>
          <div class="flex-1 break-word">
            <a href="questionDetails.php?id=<?= $q['id'] ?>" class="text-xl font-semibold text-orange-600 pointer hover:text-orange-700 break-word">
              <?php echo htmlspecialchars($q['title']); ?>
            </a>
            <p class="text-sm text-gray-600 mt-1">
              <?php echo '<p class="text-md text-gray-600 mt-1">' . word_limiter($q['description'], 100) . '</p>'; ?>
            </p>
            <div class="mt-2 flex flex-wrap gap-2">
              <?php foreach ($tags as $tag): ?>
                <span class="inline-block bg-orange-100 text-orange-700 text-sm px-2 py-1 rounded break-word"><?php echo htmlspecialchars($tag); ?></span>
              <?php endforeach; ?>
            </div>
            <!-- Options Dropdown -->
            <div class="absolute top-2 right-4 flex flex-wrap gap-1 max-w-[50%] justify-end">
              <button onclick="toggleDropdown(this)" class="p-1 rounded hover:bg-gray-200" aria-label="Options">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-700" fill="none" viewBox="0 0 24 24"
                  stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 6.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 12.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 18.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5Z" />
                </svg>
              </button>
              <div class="dropdown absolute right-0 top-full mt-2 hidden bg-white shadow rounded text-sm z-50 w-36 md:w-32">
                <a href="../Actions/save.php?id=<?= $q['id'] ?>" class="block px-4 py-2 hover:bg-gray-100">Save</a>
                <button
                  class="block w-full text-left px-4 py-2 hover:bg-gray-100"
                  onclick="openReportModal(<?= $q['id'] ?>)">
                  Report
                </button>
              </div>
            </div>
            <div class="text-xs text-gray-400 text-right mt-10">
              asked <?php echo date("M j, Y g:i a", strtotime($q['created_at'])); ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

  </main>

  <!-- Report Modal -->
  <div id="reportModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg max-w-lg w-full p-6 relative">
      <button id="closeReportModal" class="absolute top-2 right-2 text-gray-500 hover:text-gray-900 text-xl font-bold">&times;</button>
      <h2 class="text-xl font-semibold mb-4">Report Question</h2>
      <form id="reportForm" class="space-y-4">
        <textarea name="reason" placeholder="Reason for reporting..." required rows="5"
          class="w-full p-3 border border-gray-300 rounded resize-none focus:outline-none focus:ring focus:border-blue-300"></textarea>
        <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded shadow transition">
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