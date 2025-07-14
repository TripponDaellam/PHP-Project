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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="bg-gray-50 text-gray-800 pt-16">
  <?php include 'Partials/nav.php'; ?>

  <!-- Left Sidebar -->
  <aside class="hidden lg:block fixed top-16 left-0 h-[calc(100vh-4rem)] w-[200px] bg-white z-10 shadow-lg overflow-auto">
    <?php include 'Partials/left_nav.php'; ?>
  </aside>

  <!-- Main Content -->
  <main class="ml-0 lg:ml-[5px] p-6">
    <div class="max-w-4xl mx-auto">
      
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center space-x-3 mb-2">
          <div class="w-10 h-10 bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg flex items-center justify-center">
            <i class="fas fa-question text-white text-lg"></i>
          </div>
          <h1 class="text-3xl font-bold text-gray-900">Ask a Question</h1>
        </div>
        <p class="text-gray-600">Share your knowledge and help the community grow</p>
      </div>

      <!-- Error Message -->
      <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-lg mb-6 flex items-center space-x-2">
          <i class="fas fa-exclamation-circle text-red-500"></i>
          <span><?= htmlspecialchars($_SESSION['error']) ?></span>
        </div>
        <?php unset($_SESSION['error']); ?>
      <?php endif; ?>

      <!-- Form Card -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <form action="../Controller/post_question.php" method="POST" enctype="multipart/form-data" class="p-8" onsubmit="return validateForm()">

          <!-- Title Section -->
          <div class="mb-8">
            <label class="block text-lg font-semibold text-gray-900 mb-3">
              <i class="fas fa-heading mr-2 text-orange-500"></i>
              Question Title
            </label>
            <input type="text" name="title" required placeholder="What's your question? Be specific."
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent text-base transition-all duration-200 hover:border-gray-400">
            <p class="text-sm text-gray-500 mt-2">Make it clear and concise so others can understand your question</p>
          </div>

          <!-- Description Section -->
          <div class="mb-8">
            <label class="block text-lg font-semibold text-gray-900 mb-3">
              <i class="fas fa-align-left mr-2 text-orange-500"></i>
              Question Details
            </label>
            <textarea name="description" rows="8" required placeholder="Provide more context about your question. Include any relevant code, error messages, or examples."
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent text-base transition-all duration-200 hover:border-gray-400 resize-vertical"></textarea>
            <p class="text-sm text-gray-500 mt-2">The more details you provide, the better answers you'll receive</p>
          </div>

          <!-- Tags Section -->
          <div class="mb-8">
            <label class="block text-lg font-semibold text-gray-900 mb-3">
              <i class="fas fa-tags mr-2 text-orange-500"></i>
              Tags
            </label>
            <div id="tagInputContainer"
              class="flex flex-wrap items-center gap-2 min-h-[50px] px-4 py-3 border border-gray-300 rounded-lg bg-white cursor-text transition-all duration-200 hover:border-gray-400 focus-within:ring-2 focus-within:ring-orange-500 focus-within:border-transparent"
              onclick="tagInput.focus()">
              <input id="tagInput" type="text" placeholder="Type to search tags..." autocomplete="off"
                class="flex-grow min-w-[150px] focus:outline-none text-base"
                onkeyup="filterTags()" onkeydown="handleBackspace(event)">
            </div>

            <div id="suggestions" class="border border-gray-200 rounded-lg bg-white max-h-48 overflow-y-auto mt-2 hidden shadow-lg z-10"></div>

            <input type="hidden" id="selectedTagsInput" name="selected_tags" value="">
            <p class="text-sm text-gray-500 mt-2">Add relevant tags to help others find your question</p>
          </div>

          <!-- Image Upload Section -->
          <div class="mb-8">
            <label class="block text-lg font-semibold text-gray-900 mb-3">
              <i class="fas fa-image mr-2 text-orange-500"></i>
              Add Image (Optional)
            </label>
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-orange-400 transition-colors duration-200">
              <input type="file" name="image" accept="image/*" id="imageInput" class="hidden" onchange="previewImage(event)">
              <label for="imageInput" class="cursor-pointer">
                <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-3"></i>
                <p class="text-gray-600 mb-1">Click to upload an image</p>
                <p class="text-sm text-gray-500">PNG, JPG, GIF up to 5MB</p>
              </label>
            </div>
            <div id="imagePreview" class="mt-4 hidden">
              <img id="preview" class="max-w-md rounded-lg border border-gray-200" alt="Preview">
              <button type="button" onclick="removeImage()" class="mt-2 text-red-500 hover:text-red-700 text-sm">
                <i class="fas fa-trash mr-1"></i>Remove Image
              </button>
            </div>
          </div>

          <!-- Submit Section -->
          <div class="flex items-center justify-between pt-6 border-t border-gray-200">
            <div class="text-sm text-gray-500">
              <i class="fas fa-info-circle mr-1"></i>
              Your question will be visible to the community
            </div>
            <button type="submit"
              class="bg-gradient-to-r from-orange-500 to-orange-600 text-white px-8 py-3 rounded-lg hover:from-orange-600 hover:to-orange-700 transition-all duration-200 font-semibold shadow-sm hover:shadow-md">
              <i class="fas fa-paper-plane mr-2"></i>
              Post Question
            </button>
          </div>
        </form>
      </div>
    </div>
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
        suggestionsDiv.innerHTML = '<div class="p-4 text-gray-500 text-center">No matching tags found</div>';
      } else {
        filtered.forEach(tag => {
          const div = document.createElement('div');
          div.textContent = tag;
          div.className = 'p-3 hover:bg-orange-50 hover:text-orange-600 cursor-pointer transition-colors duration-200 border-b border-gray-100 last:border-b-0';
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
      tagPill.className = 'flex items-center bg-orange-100 text-orange-700 rounded-full px-3 py-1.5 text-sm font-medium shadow-sm';
      tagPill.innerHTML = `
        <span>${tag}</span>
        <button class="ml-2 text-orange-500 hover:text-orange-700 font-bold text-lg leading-none" onclick="removeTag('${tag}', this.parentElement)">&times;</button>
      `;

      tagInputContainer.insertBefore(tagPill, tagInput);

      tagInput.value = '';
      suggestionsDiv.classList.add('hidden');
      updateSelectedTagsInput();
    }

    function removeTag(tag, element) {
      tagInputContainer.removeChild(element);
      selectedTags = selectedTags.filter(t => t !== tag);
      updateSelectedTagsInput();
    }

    function updateSelectedTagsInput() {
      selectedTagsInput.value = selectedTags.join(',');
    }

    function handleBackspace(event) {
      if (event.key === 'Backspace' && tagInput.value === '' && selectedTags.length > 0) {
        const tagPills = tagInputContainer.querySelectorAll('div.flex.items-center');
        if (tagPills.length > 0) {
          const lastTag = tagPills[tagPills.length - 1];
          const tagText = lastTag.querySelector('span').textContent;
          removeTag(tagText, lastTag);
        }
      }
    }

    function previewImage(event) {
      const file = event.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          document.getElementById('preview').src = e.target.result;
          document.getElementById('imagePreview').classList.remove('hidden');
        }
        reader.readAsDataURL(file);
      }
    }

    function removeImage() {
      document.getElementById('imageInput').value = '';
      document.getElementById('imagePreview').classList.add('hidden');
    }

    function validateForm() {
      if (selectedTags.length === 0) {
        alert("Please select at least one tag to help categorize your question.");
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