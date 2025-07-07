<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: ../User/Login.php');
  exit;
}

require_once 'DBConnection/DBConnector.php';
$stmt = $pdo->query("SELECT tag_name FROM tags");
$tags = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>Ask a Question - Method Flow</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 text-black pt-16">
  <?php include 'Partials/nav.php'; ?>

  <!-- Left Sidebar -->
  <aside class="hidden lg:block fixed top-16 left-0 h-[calc(100%-0rem)] w-[200px] bg-white z-10 shadow">
    <?php include 'Partials/left_nav.php'; ?>
  </aside>

  <!-- Main Content -->
  <main class="p-6 max-w-4xl mx-auto lg:ml-[210px]">
    <h1 class="text-3xl font-bold mb-6">Ask a New Question</h1>

    <!-- Error Message -->
    <?php if (isset($_SESSION['error'])): ?>
      <div class="bg-red-100 text-red-700 p-3 rounded mb-4 border border-red-300">
        <?= htmlspecialchars($_SESSION['error']) ?>
      </div>
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Form -->
    <form action="../Controller/post_question.php" method="POST" enctype="multipart/form-data" class="space-y-6 bg-white p-6 rounded shadow" onsubmit="return validateForm()">

      <!-- Title -->
      <div>
        <label class="block text-lg font-medium mb-1">Title</label>
        <input type="text" name="title" required
          class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-orange-400 outline-none">
      </div>

      <!-- Description -->
      <div>
        <label class="block text-lg font-medium mb-1">Description</label>
        <textarea name="description" rows="6" required
          class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-orange-400 outline-none"></textarea>
      </div>

      <!-- Tags -->
      <div>
        <label class="block text-lg font-medium mb-1">Tags</label>
        <div id="tagInputContainer"
          class="flex flex-wrap items-center gap-2 min-h-[40px] px-3 py-2 border border-gray-300 rounded bg-white cursor-text"
          onclick="tagInput.focus()">
          <input id="tagInput" type="text" placeholder="Type and select tags..." autocomplete="off"
            class="flex-grow min-w-[100px] focus:outline-none"
            onkeyup="filterTags()" onkeydown="handleBackspace(event)">
        </div>

        <div id="suggestions" class="border border-gray-300 rounded bg-white max-h-48 overflow-y-auto mt-2 hidden shadow-lg"></div>

        <input type="hidden" id="selectedTagsInput" name="selected_tags" value="">
      </div>

      <!-- Image Upload -->
      <div>
        <label class="block text-lg font-medium mb-1">Upload an Image (optional)</label>
        <input type="file" name="image" accept="image/*"
          class="w-full px-4 py-2 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-orange-400 outline-none">
      </div>

      <!-- Submit -->
      <div>
        <button type="submit"
          class="bg-orange-600 text-white px-6 py-2 rounded hover:bg-orange-700 transition">
          Post Question
        </button>
      </div>
    </form>
  </main>

  <script>
    const tags = <?php echo json_encode($tags); ?>;
    const tagInput = document.getElementById('tagInput');
    const tagInputContainer = document.getElementById('tagInputContainer');
    const suggestionsDiv = document.getElementById('suggestions');
    const selectedTagsInput = document.getElementById('selectedTagsInput');
    let selectedTags = [];

    function filterTags() {
      const input = tagInput.value.toLowerCase().trim();
      if (input === '') {
        suggestionsDiv.innerHTML = '';
        suggestionsDiv.classList.add('hidden');
        return;
      }

      const filtered = tags.filter(tag =>
        tag.toLowerCase().includes(input) &&
        !selectedTags.includes(tag)
      );

      suggestionsDiv.innerHTML = '';
      if (filtered.length === 0) {
        suggestionsDiv.innerHTML = '<div class="p-2 text-gray-500">No matches</div>';
      } else {
        filtered.forEach(tag => {
          const div = document.createElement('div');
          div.textContent = tag;
          div.className = 'p-2 hover:bg-gray-100 cursor-pointer';
          div.onclick = () => addTag(tag);
          suggestionsDiv.appendChild(div);
        });
      }
      suggestionsDiv.classList.remove('hidden');
    }

    function addTag(tag) {
      if (selectedTags.includes(tag)) return;
      selectedTags.push(tag);

      const tagPill = document.createElement('div');
      tagPill.className = 'flex items-center bg-blue-100 text-blue-700 rounded-full px-3 py-1 text-sm shadow';
      tagPill.textContent = tag;

      const closeBtn = document.createElement('button');
      closeBtn.className = 'ml-2 text-red-500 font-bold hover:text-red-700';
      closeBtn.innerHTML = '&times;';
      closeBtn.onclick = () => {
        tagInputContainer.removeChild(tagPill);
        selectedTags = selectedTags.filter(t => t !== tag);
        updateSelectedTagsInput();
      };

      tagPill.appendChild(closeBtn);
      tagInputContainer.insertBefore(tagPill, tagInput);

      tagInput.value = '';
      suggestionsDiv.classList.add('hidden');
      updateSelectedTagsInput();
    }

    function updateSelectedTagsInput() {
      selectedTagsInput.value = selectedTags.join(',');
    }

    function handleBackspace(event) {
      if (event.key === 'Backspace' && tagInput.value === '' && selectedTags.length > 0) {
        const tagPills = tagInputContainer.querySelectorAll('div.flex.items-center');
        if (tagPills.length > 0) {
          tagInputContainer.removeChild(tagPills[tagPills.length - 1]);
          selectedTags.pop();
          updateSelectedTagsInput();
        }
      }
    }

    function validateForm() {
      if (selectedTags.length === 0) {
        alert("Please select at least one valid tag.");
        return false;
      }
      return true;
    }

    // Hide suggestions when clicking outside
    document.addEventListener('click', (e) => {
      if (!tagInputContainer.contains(e.target) && !suggestionsDiv.contains(e.target)) {
        suggestionsDiv.classList.add('hidden');
      }
    });
  </script>
</body>

</html>