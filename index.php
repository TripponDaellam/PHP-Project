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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="bg-gradient-to-br from-gray-50 to-gray-100 text-gray-800 pt-16 min-h-screen">

  <!-- Top Nav -->
  <?php include 'Partials/nav.php'; ?>

  <div class="flex flex-col lg:flex-row min-h-screen">

    <!-- Left Sidebar -->
    <aside class="hidden lg:block lg:fixed top-16 left-0 h-[calc(100%-4rem)] w-[200px] bg-white z-10 shadow-lg">
      <?php include 'Partials/left_nav.php'; ?>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 w-full lg:ml-[220px] lg:mr-5 p-6 overflow-x-auto min-w-full md:min-w-[500px] max-w-screen-full">
      
      <!-- Welcome Section -->
      <?php if ($isLoggedIn): ?>
        <div class="bg-gradient-to-r from-orange-50 to-orange-100 rounded-xl p-6 mb-8 border border-orange-200">
          <div class="flex items-center space-x-4">
            <div class="w-12 h-12 bg-gradient-to-r from-orange-400 to-orange-600 rounded-full flex items-center justify-center text-white text-xl font-bold">
              <?= strtoupper(substr($username, 0, 1)) ?>
            </div>
            <div>
              <h2 class="text-2xl md:text-3xl font-bold text-gray-800">Welcome back, <?= htmlspecialchars($username) ?>!</h2>
              <p class="text-gray-600 mt-1">Ready to help others or ask your next question?</p>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <!-- Header Section -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-2">Recent Questions</h1>
            <p class="text-gray-600">Discover and answer questions from the community</p>
          </div>
          <?php if ($isLoggedIn): ?>
            <a href="ask.php" class="bg-gradient-to-r from-orange-500 to-orange-600 text-white px-6 py-3 rounded-lg hover:from-orange-600 hover:to-orange-700 transition-all duration-200 shadow-md hover:shadow-lg flex items-center space-x-2">
              <i class="fas fa-plus"></i>
              <span>Ask a Question</span>
            </a>
          <?php else: ?>
            <a href="../User/Login.php" class="bg-gradient-to-r from-orange-500 to-orange-600 text-white px-6 py-3 rounded-lg hover:from-orange-600 hover:to-orange-700 transition-all duration-200 shadow-md hover:shadow-lg flex items-center space-x-2">
              <i class="fas fa-sign-in-alt"></i>
              <span>Login to Ask</span>
            </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Questions Section -->
      <div class="space-y-6">
        <?php if (!empty($questions)): ?>
          <?php foreach ($questions as $q): ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200" data-question-id="<?= $q['id'] ?>">
              
              <!-- Header with Options -->
              <div class="flex justify-between items-start mb-4">
                <div class="flex-1">
                  <a href="questionDetails.php?id=<?= $q['id'] ?>" class="text-xl font-bold text-gray-800 hover:text-orange-600 transition-colors duration-200 block mb-2">
                    <?= htmlspecialchars($q['title']); ?>
                  </a>
                  <p class="text-gray-600 text-sm leading-relaxed">
                    <?= htmlspecialchars(word_limiter($q['description'], 55)); ?>
                  </p>
                </div>
                
                <!-- Options Dropdown -->
                <div class="relative ml-4">
                  <button onclick="toggleDropdown(this)" class="p-2 rounded-lg hover:bg-gray-100 transition-colors duration-200" aria-label="Options">
                    <i class="fas fa-ellipsis-v text-gray-500"></i>
                  </button>
                  <div class="dropdown absolute right-0 top-full mt-2 hidden bg-white shadow-lg rounded-lg text-sm z-50 w-36 border border-gray-200">
                    <button class="block w-full text-left px-4 py-3 hover:bg-gray-50 rounded-t-lg transition-colors duration-200"
                      onclick="saveQuestion(<?= $q['id'] ?>)">
                      <i class="fas fa-bookmark mr-2 text-blue-500"></i>Save
                    </button>
                    <button class="block w-full text-left px-4 py-3 hover:bg-gray-50 rounded-b-lg transition-colors duration-200"
                      onclick="openReportModal(<?= $q['id'] ?>)">
                      <i class="fas fa-flag mr-2 text-red-500"></i>Report
                    </button>
                  </div>
                </div>
              </div>

              <!-- Tags -->
              <div class="flex flex-wrap gap-2 mb-4">
                <?php foreach (explode(',', $q['tags'] ?? '') as $tag): ?>
                  <span class="bg-gradient-to-r from-orange-100 to-orange-200 text-orange-700 text-xs px-3 py-1 rounded-full font-medium">
                    <?= htmlspecialchars(trim($tag)); ?>
                  </span>
                <?php endforeach; ?>
              </div>

              <!-- Meta Information -->
              <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-4 text-sm text-gray-500">
                  <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-gradient-to-r from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white text-xs font-bold">
                      <?= strtoupper(substr($q['username'], 0, 1)) ?>
                    </div>
                    <span>Asked by <span class="font-semibold text-gray-700"><?= htmlspecialchars($q['username']) ?></span></span>
                  </div>
                  <div class="flex items-center space-x-1">
                    <i class="fas fa-calendar text-gray-400"></i>
                    <span><?= date('F j, Y', strtotime($q['created_at'])) ?></span>
                  </div>
                </div>

                <!-- Voting -->
                <div class="flex items-center space-x-4" data-question-id="<?= $q['id'] ?>">
                  <div class="flex items-center space-x-2">
                    <button class="upvote p-2 rounded-lg hover:bg-green-50 transition-colors duration-200" aria-label="Upvote" data-question-id="<?= $q['id'] ?>">
                      <i class="fas fa-arrow-up text-green-500"></i>
                    </button>
                    <span class="text-sm font-bold text-gray-700 vote-count-up min-w-[20px] text-center"><?= (int)$q['upvotes'] ?></span>
                  </div>

                  <div class="flex items-center space-x-2">
                    <button class="downvote p-2 rounded-lg hover:bg-red-50 transition-colors duration-200" aria-label="Downvote" data-question-id="<?= $q['id'] ?>">
                      <i class="fas fa-arrow-down text-red-500"></i>
                    </button>
                    <span class="text-sm font-bold text-gray-700 vote-count-down min-w-[20px] text-center"><?= (int)$q['downvotes'] ?></span>
                  </div>
                </div>
              </div>

              <!-- Comments Preview -->
              <?php if (isset($allComments[$q['id']])): ?>
                <div class="border-t border-gray-100 pt-4 cursor-pointer hover:bg-gray-50 rounded-lg -mx-2 px-2 transition-colors duration-200"
                  onclick="window.location.href='questionDetails.php?id=<?= $q['id'] ?>'">
                  <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-gray-700 flex items-center">
                      <i class="fas fa-comments text-blue-500 mr-2"></i>
                      Answers (<?= count($allComments[$q['id']]) ?>)
                    </h3>
                    <span class="text-xs text-gray-500">Click to view all</span>
                  </div>
                  <?php foreach (array_slice($allComments[$q['id']], 0, 2) as $comment): ?>
                    <div class="mb-3 bg-gray-50 p-3 rounded-lg">
                      <div class="flex items-start space-x-3">
                        <div class="w-8 h-8 bg-gradient-to-r from-purple-400 to-purple-600 rounded-full flex items-center justify-center text-white text-xs font-bold">
                          <?= strtoupper(substr($comment['username'], 0, 1)) ?>
                        </div>
                        <div class="flex-1">
                          <div class="font-medium text-xs text-gray-700 mb-1"><?= htmlspecialchars($comment['username']) ?></div>
                          <div class="text-sm text-gray-600"><?= htmlspecialchars($comment['content']) ?></div>
                          <div class="text-xs text-gray-500 mt-2 flex items-center">
                            <i class="fas fa-clock mr-1"></i>
                            <?= date('F j, Y H:i', strtotime($comment['created_at'])) ?>
                          </div>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>

            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
            <i class="fas fa-question-circle text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">No Questions Found</h3>
            <p class="text-gray-500">Be the first to ask a question and help build the community!</p>
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
          
          <div>
            <p class="font-medium mb-4 text-gray-700">Select a reason:</p>
            <div class="space-y-3">
              <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors duration-200">
                <input type="radio" name="reason" value="Spam or misleading" required class="mr-3 text-orange-500">
                <span class="text-gray-700">Spam or misleading</span>
              </label>
              <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors duration-200">
                <input type="radio" name="reason" value="Hate speech or abusive" class="mr-3 text-orange-500">
                <span class="text-gray-700">Hate speech or abusive</span>
              </label>
              <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors duration-200">
                <input type="radio" name="reason" value="Harassment or bullying" class="mr-3 text-orange-500">
                <span class="text-gray-700">Harassment or bullying</span>
              </label>
              <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors duration-200">
                <input type="radio" name="reason" value="Violence or harmful content" class="mr-3 text-orange-500">
                <span class="text-gray-700">Violence or harmful content</span>
              </label>
              <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors duration-200">
                <input type="radio" name="reason" value="Inappropriate content" class="mr-3 text-orange-500">
                <span class="text-gray-700">Inappropriate content</span>
              </label>
              <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors duration-200">
                <input type="radio" name="reason" value="Other" class="mr-3 text-orange-500" id="otherReasonOption">
                <span class="text-gray-700">Other (please explain)</span>
              </label>
            </div>
          </div>

          <div id="customReasonContainer" class="hidden">
            <textarea name="custom_reason" placeholder="Please explain the issue..." rows="4"
              class="w-full p-4 border border-gray-300 rounded-lg resize-none focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"></textarea>
          </div>

          <button type="submit" class="w-full px-6 py-3 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-lg shadow-md hover:shadow-lg transition-all duration-200 font-medium">
            <i class="fas fa-paper-plane mr-2"></i>
            Submit Report
          </button>
        </form>
        <div id="reportMessage" class="mt-4 text-center"></div>
      </div>
    </div>

    <!-- Right Sidebar -->
    <aside class="hidden lg:block w-full lg:w-80 px-6 py-6 bg-white rounded-xl shadow-sm border border-gray-200 mt-6 lg:mt-[75px] lg:mr-6 h-fit sticky top-24">
      <div class="mb-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
          <i class="fas fa-tags text-orange-500 mr-3"></i>
          Popular Tags
        </h3>
        <p class="text-gray-600 text-sm mb-4">Discover questions by topic</p>
      </div>
      
      <div class="flex flex-wrap gap-2">
        <?php
        $tagStmt = $pdo->query("SELECT tags FROM questions");
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
          <div class="text-center py-8">
            <i class="fas fa-tags text-3xl text-gray-300 mb-3"></i>
            <p class="text-sm text-gray-500">No tags found.</p>
          </div>
          <?php else:
          arsort($allTags);
          foreach (array_slice($allTags, 0, 15) as $tag => $count): ?>
            <a href="tag.php?tag=<?= urlencode($tag) ?>"
              class="inline-flex items-center gap-2 bg-gradient-to-r from-gray-100 to-gray-200 hover:from-orange-100 hover:to-orange-200 text-gray-700 hover:text-orange-700 text-xs px-3 py-2 rounded-full shadow-sm hover:shadow-md transition-all duration-200 font-medium">
              <i class="fas fa-tag text-xs"></i>
              <?= htmlspecialchars($tag) ?>
              <span class="bg-white text-gray-500 px-2 py-0.5 rounded-full text-xs"><?= $count ?></span>
            </a>
        <?php endforeach;
        endif; ?>
      </div>

      <!-- Quick Stats -->
      <!-- <div class="mt-8 pt-6 border-t border-gray-200">
        <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
          <i class="fas fa-chart-bar text-blue-500 mr-3"></i>
          Community Stats
        </h4>
        <div class="grid grid-cols-2 gap-4">
          <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-4 rounded-lg border border-blue-200">
            <div class="text-2xl font-bold text-blue-600"><?= count($questions) ?></div>
            <div class="text-blue-700 text-sm font-medium">Questions</div>
          </div>
          <div class="bg-gradient-to-br from-green-50 to-green-100 p-4 rounded-lg border border-green-200">
            <div class="text-2xl font-bold text-green-600"><?= count($allComments) ?></div>
            <div class="text-green-700 text-sm font-medium">Answers</div>
          </div>
        </div>
      </div> -->
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

    // Custom reason toggle
    const otherReasonOption = document.getElementById("otherReasonOption");
    const customReasonContainer = document.getElementById("customReasonContainer");
    const radioButtons = document.querySelectorAll('input[name="reason"]');

    radioButtons.forEach((radio) => {
      radio.addEventListener('change', () => {
        if (otherReasonOption.checked) {
          customReasonContainer.classList.remove("hidden");
        } else {
          customReasonContainer.classList.add("hidden");
        }
      });
    });
  </script>

</body>

</html>