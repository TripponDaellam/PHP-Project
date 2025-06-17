<?php
// Database connection
require_once 'DBConnection/DBConnector.php';

// Get all tag names
$stmt = $pdo->query("SELECT tag_name FROM tags");
$tags = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Tag Input Inside Input Box - Tailwind</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
  <div class="max-w-xl mx-auto bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold mb-4">Tag Input Inside Input Box (Tailwind)</h1>

    <!-- Tag input container -->
    <div id="tagInputContainer"
         tabindex="0"
         class="flex flex-wrap items-center gap-2 min-h-[40px] px-3 py-2 border border-gray-300 rounded cursor-text bg-white"
         onclick="document.getElementById('tagInput').focus()"
    >
      <!-- Tags will be inserted here before the input -->
      <input
        id="tagInput"
        type="text"
        placeholder="Type and select tags..."
        autocomplete="off"
        class="flex-grow min-w-[100px] focus:outline-none"
        onkeyup="filterTags(event)"
        onkeydown="handleBackspace(event)"
      />
    </div>

    <!-- Suggestions box -->
    <div id="suggestions" class="border border-gray-300 rounded bg-white max-h-48 overflow-y-auto mt-2 hidden shadow-lg"></div>
  </div>

  <script>
    const tags = <?php echo json_encode($tags); ?>;
    const suggestionsDiv = document.getElementById('suggestions');
    const tagInput = document.getElementById('tagInput');
    const tagInputContainer = document.getElementById('tagInputContainer');

    let selectedTags = [];

    function filterTags(event) {
      const input = tagInput.value.toLowerCase();

      if (input.length === 0) {
        suggestionsDiv.innerHTML = '';
        suggestionsDiv.classList.add('hidden');
        return;
      }

      const filtered = tags.filter(tag => tag.toLowerCase().includes(input) && !selectedTags.includes(tag));

      if (filtered.length === 0) {
        suggestionsDiv.innerHTML = '<div class="p-2 text-gray-500">No matches</div>';
        suggestionsDiv.classList.remove('hidden');
        return;
      }

      suggestionsDiv.innerHTML = '';
      filtered.forEach(tag => {
        const div = document.createElement('div');
        div.textContent = tag;
        div.className = 'p-2 hover:bg-gray-100 cursor-pointer';
        div.onclick = () => addTag(tag);
        suggestionsDiv.appendChild(div);
      });

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
        updateInputPlaceholder();
      };

      tagPill.appendChild(closeBtn);
      tagInputContainer.insertBefore(tagPill, tagInput);

      tagInput.value = '';
      suggestionsDiv.classList.add('hidden');
      updateInputPlaceholder();
      tagInput.focus();
    }

    function updateInputPlaceholder() {
      tagInput.placeholder = selectedTags.length === 0 ? 'Type and select tags...' : '';
    }

    function handleBackspace(event) {
      if (event.key === 'Backspace' && tagInput.value === '' && selectedTags.length > 0) {
        const lastTag = selectedTags.pop();
        const tagPills = tagInputContainer.querySelectorAll('div.flex.items-center');
        if (tagPills.length > 0) {
          tagInputContainer.removeChild(tagPills[tagPills.length - 1]);
        }
        updateInputPlaceholder();
      }
    }

    document.addEventListener('click', (e) => {
      if (!tagInputContainer.contains(e.target) && !suggestionsDiv.contains(e.target)) {
        suggestionsDiv.classList.add('hidden');
      }
    });
  </script>
</body>
</html>


