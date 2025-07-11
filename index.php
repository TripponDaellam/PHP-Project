<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$username = $isLoggedIn ? $_SESSION['username'] : '';

require_once 'DBConnection/DBConnector.php';
require_once 'DBConnection/DBLocal.php';

// Fetch questions with usernames
$query = "SELECT q.id, q.title, q.description, q.tags, q.created_at, q.upvotes, q.downvotes, u.username, u.profile_image
          FROM questions q
          JOIN users u ON q.user_id = u.id
          WHERE q.banned = 0 and q.is_approved = 1
          ORDER BY q.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute();
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch comments from local DB (only top-level, parent_id IS NULL)
$commentStmt = $pdo->query("
  SELECT c.*, u.username, u.profile_image
  FROM comments c
  JOIN users u ON c.user_id = u.id
  WHERE c.parent_id IS NULL
  ORDER BY c.created_at DESC
");

$allComments = [];
while ($c = $commentStmt->fetch(PDO::FETCH_ASSOC)) {
  $allComments[$c['question_id']][] = $c;
}

// Helper function to limit words
if (!function_exists('word_limiter')) {
  function word_limiter($text, $limit = 20)
  {
    $words = explode(' ', strip_tags($text));
    if (count($words) > $limit) {
      return implode(' ', array_slice($words, 0, $limit)) . '...';
    }
    return $text;
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>Method Flow - Home</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/alpinejs" defer></script>
</head>

<body class="bg-gray-100 text-black pt-16">

  <!-- Top Nav -->
  <?php include 'Partials/nav.php'; ?>

  <div class="flex flex-col lg:flex-row min-h-screen bg-gray-100">

    <!-- Left Sidebar -->
    <aside class="hidden lg:block lg:fixed top-16 left-0 h-[calc(100%-4rem)] w-[200px] bg-white z-10 shadow">
      <?php include 'Partials/left_nav.php'; ?>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 w-full lg:ml-[220px] lg:mr-5 p-4 overflow-x-auto min-w-full md:min-w-[500px] max-w-screen-full">
      <?php if ($isLoggedIn): ?>
        <div class="mb-6">
          <h2 class="text-2xl md:text-3xl font-bold text-gray-800">Welcome back, <?= htmlspecialchars($username) ?>!</h2>
          <p class="text-gray-600 text-sm mt-1">Ready to help others or ask your next question?</p>
        </div>
      <?php endif; ?>

      <div class="flex flex-row justify-between items-start mb-4 gap-2 flex-wrap">
        <h1 class="text-2xl sm:text-3xl font-bold">Recent Questions</h1>
        <?php if ($isLoggedIn): ?>
          <a href="ask.php" class="bg-orange-600 text-white px-4 py-2 rounded-md hover:bg-orange-700 text-sm">Ask a Question</a>
        <?php else: ?>
          <a href="../User/Login.php" class="bg-orange-600 text-white px-4 py-2 rounded-md hover:bg-orange-700 text-sm">Login to Ask</a>
        <?php endif; ?>
      </div>

      <div class="space-y-6">
        <?php if (!empty($questions)): ?>
          <?php foreach ($questions as $q): ?>
            <div class="relative bg-white shadow px-8 pt-4 pb-8 rounded-sm" data-question-id="<?= $q['id'] ?>">
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
                  <button
                    class="block w-full text-left px-4 py-2 hover:bg-gray-100"
                    onclick="saveQuestion(<?= $q['id'] ?>)">
                    Save
                  </button>
                  <button
                    class="block w-full text-left px-4 py-2 hover:bg-gray-100"
                    onclick="openReportModal(<?= $q['id'] ?>)">
                    Report
                  </button>
                </div>
              </div>

              <!-- Title & Description -->
              <a href="questionDetails.php?id=<?= $q['id'] ?>" class="text-xl font-semibold text-orange-600 hover:text-orange-700 block underline">
                <?= htmlspecialchars($q['title']); ?>
              </a>
              <p class="text-gray-700 mt-2 text-md">
                <?= htmlspecialchars(word_limiter($q['description'], 55)); ?>
              </p>

              <!-- Tags -->
              <div class="mt-1">
                <?php foreach (explode(',', $q['tags'] ?? '') as $tag): ?>
                  <span class="bg-orange-100 text-orange-700 text-xs px-2 py-1 rounded mr-1"><?= htmlspecialchars(trim($tag)); ?></span>
                <?php endforeach; ?>
              </div>

              <!-- Meta -->
              <p class="text-xs text-gray-500 mt-4">
                Asked by <span class="font-semibold text-orange-700"><?= htmlspecialchars($q['username']) ?></span>
                on <?= date('F j, Y', strtotime($q['created_at'])) ?>
              </p>

              <!-- Voting -->
              <div class="flex items-center gap-2 mt-2" data-question-id="<?= $q['id'] ?>">
                <button class="upvote text-orange-600 hover:text-orange-800 cursor-pointer" aria-label="Upvote" data-question-id="<?= $q['id'] ?>">▲</button>
                <span class="text-sm font-semibold text-gray-700 vote-count-up"><?= (int)$q['upvotes'] ?></span>

                <button class="downvote text-gray-600 hover:text-gray-800 cursor-pointer" aria-label="Downvote" data-question-id="<?= $q['id'] ?>">▼</button>
                <span class="text-sm font-semibold text-gray-700 vote-count-down"><?= (int)$q['downvotes'] ?></span>
              </div>

              <!-- Comments (Clickable to question detail) -->
              <?php if (isset($allComments[$q['id']])): ?>
                <div class="mt-4 border-t pt-3 cursor-pointer hover:bg-gray-50 rounded"
                  onclick="window.location.href='questionDetails.php?id=<?= $q['id'] ?>'">
                  <h3 class="text-sm font-semibold text-gray-700 mb-2">Answers:</h3>
                  <?php foreach (array_slice($allComments[$q['id']], 0, 2) as $comment): ?>
                    <div class="mb-2 bg-gray-100 p-2 rounded text-sm flex items-start">
                      <img src="<?= $comment['profile_image'] ? htmlspecialchars($comment['profile_image']) : 'assets/default-user.png' ?>"
                        class="w-8 h-8 rounded-full mr-3" alt="User profile image">
                      <div>
                        <div class="font-medium text-xs mb-1"><?= htmlspecialchars($comment['username']) ?></div>
                        <div><?= htmlspecialchars($comment['content']) ?></div>
                        <div class="text-xs text-gray-500 mt-1">
                          Posted on <?= date('F j, Y H:i', strtotime($comment['created_at'])) ?>
                        </div>
                      </div>
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

    <!-- Right Sidebar -->
    <aside class="hidden lg:block w-full lg:w-72 px-4 py-6 bg-white rounded shadow mt-10 lg:mt-[75px] lg:mr-6 h-fit sticky top-24">
      <h3 class="text-xl font-bold text-gray-900 mb-4">Tags</h3>
      <div class="flex flex-wrap gap-2">
        <?php
        $tagStmt = $pdo->query("SELECT DISTINCT tags FROM questions");
        $allTags = [];
        while ($row = $tagStmt->fetch(PDO::FETCH_ASSOC)) {
          foreach (explode(',', $row['tags'] ?? '') as $tag) {
            $tag = trim($tag);
            if ($tag !== '') {
              $allTags[$tag] = ($allTags[$tag] ?? 0) + 1;
            }
          }
        }
        if (empty($allTags)): ?>
          <p class="text-sm text-gray-500">No tags found.</p>
          <?php else:
          arsort($allTags);
          foreach (array_slice($allTags, 0, 12) as $tag => $count): ?>
            <a href="tag.php?tag=<?= urlencode($tag) ?>"
              class="inline-flex items-center gap-1 bg-gray-100 text-black text-xs px-3 py-1 rounded-full shadow hover:bg-gray-200">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
              </svg>
              <?= htmlspecialchars($tag) ?>
            </a>
        <?php endforeach;
        endif; ?>
      </div>

    </aside>

  </div>

  <!-- Footer -->
  <footer class="mt-10">
    <?php include 'Partials/footer.php'; ?>
  </footer>

  <script>
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

    function toggleDropdown(button) {
      const dropdown = button.parentElement.querySelector('.dropdown');
      dropdown.classList.toggle('hidden');
    }
    document.addEventListener('click', function(e) {
      document.querySelectorAll('.dropdown').forEach(d => {
        if (!d.parentElement.contains(e.target)) d.classList.add('hidden');
      });
    });

    // AJAX Voting
    document.querySelectorAll('.upvote, .downvote').forEach(button => {
      button.addEventListener('click', function() {
        const questionId = this.dataset.questionId;
        const voteType = this.classList.contains('upvote') ? 'up' : 'down';

        fetch('Controller/voteController.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `question_id=${encodeURIComponent(questionId)}&vote_type=${encodeURIComponent(voteType)}`
          })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              const container = document.querySelector(`[data-question-id="${questionId}"]`);
              if (container) {
                container.querySelector('.vote-count-up').textContent = data.upvotes;
                container.querySelector('.vote-count-down').textContent = data.downvotes;
              }
            } else {
              alert(data.error || 'Failed to vote');
            }
          })
          .catch(() => alert('Failed to connect to server.'));
      });
    });

    function saveQuestion(questionId) {
      fetch(`../Actions/save.php?id=${questionId}`)
        .then(response => response.json())
        .then(data => {
          alert(data.message);
        })
        .catch(() => {
          alert('Network error. Please try again later.');
        });
    }
  </script>

</body>

</html>