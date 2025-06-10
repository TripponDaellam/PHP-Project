<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: ../User/Login.php');
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Ask a Question - Method Flow</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-black pt-16">

  <?php include 'Partials/nav.php'; ?>

  <!-- Left Sidebar for large screens -->
  <aside class="hidden lg:block fixed top-0 left-0 h-[calc(100%-0rem)] w-[200px] bg-white z-10 shadow">
    <?php include 'Partials/left_nav.php'; ?>
  </aside>

  <!-- Form Container -->
  <div class="min-w-full md:min-w-[500px] max-w-screen-full ml-[220px] lg:mr-10 p-4 overflow-x-auto">
    <h1 class="text-3xl font-bold mb-6">Ask a New Question</h1>

    <form action="../Controller/post_question.php" method="POST" enctype="multipart/form-data" class="space-y-6 bg-white p-6 rounded shadow">
      
      <!-- Title -->
      <div>
        <label class="block text-lg font-medium text-gray-700 mb-1">Title</label>
        <p class="text-sm text-gray-500 mb-2">Be specific and imagine you’re asking a question to another person</p>
        <input type="text" name="title" required
          class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-orange-400 focus:outline-none">
      </div>

      <!-- Description -->
      <div>
        <label class="block text-lg font-medium text-gray-700 mb-1">Description</label>
        <textarea name="description" rows="6" required
          class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-orange-400 focus:outline-none"></textarea>
      </div>

      <!-- Tags -->
      <div>
        <label class="block text-lg font-medium text-gray-700 mb-1">Tags</label>
        <p class="text-sm text-gray-500 mb-2">Add comma-separated tags (e.g. math, algebra, programming)</p>
        <input type="text" name="tags"
          class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-orange-400 focus:outline-none">
      </div>

      <!-- Optional Image Upload -->
      <div>
        <label class="block text-lg font-medium text-gray-700 mb-1">Upload an Image (optional)</label>
        <input type="file" name="image" accept="image/*"
          class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-orange-400 focus:outline-none text-sm">
      </div>

      <!-- Submit -->
      <div>
        <button type="submit"
          class="bg-orange-500 text-white px-6 py-2 rounded-md hover:bg-orange-600 transition">
          Post Question
        </button>
      </div>
    </form>
  </div>

</body>
</html>
