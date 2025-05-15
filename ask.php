<!-- ask.php -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Ask a Question - Method Flow</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-black">
  <div class="max-w-3xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-6">Ask a New Question</h1>

    <form action="post_question.php" method="POST" class="space-y-4">
      <div>
        <label class="block text-sm font-medium">Title</label>
        <input type="text" name="title" required
          class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-orange-400">
      </div>

      <div>
        <label class="block text-sm font-medium">Description</label>
        <textarea name="description" rows="6" required
          class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-orange-400"></textarea>
      </div>

      <div>
        <label class="block text-sm font-medium">Tags (comma-separated)</label>
        <input type="text" name="tags"
          class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-orange-400">
      </div>

      <button type="submit" class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600">
        Post Question
      </button>
    </form>
  </div>
</body>
</html>
