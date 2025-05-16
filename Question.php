<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Question</title>
</head>
<body class="flex flex-col min-h-screen bg-gray-50 text-black pt-20 overflow-x-hidden">
    <?php include 'Partials/nav.php'; ?>
   <aside class="fixed top-20 left-0 h-[calc(100%-10rem)] w-[180px] bg-white z-10 hidden md:block shadow">
    <?php include 'Partials/left_nav.php'; ?>
  </aside>
  <main class="flex-1 w-screen-full mx-auto px-4 py-6 md:ml-60 lg:mr-10">
<div class="flex justify-between items-center mb-6">
  <div>
    <h1 class="text-3xl font-bold">Newest Questions</h1>
    <h2 class="text-xl">Post For You</h2>
    <div class="flex flex-wrap gap-4 mt-6 mb-3">
  <button class="bg-orange-500 text-white px-3 py-2 rounded hover:bg-orange-600">Java</button>
  <button class="bg-orange-500 text-white px-3 py-2 rounded hover:bg-orange-600">Bootstrap</button>
  <button class="bg-orange-500 text-white px-3 py-2 rounded hover:bg-orange-600">HTML</button>
  <button class="bg-orange-500 text-white px-3 py-2 rounded hover:bg-orange-600">CSS</button>
  <button class="bg-orange-500 text-white px-3 py-2 rounded hover:bg-orange-600">JavaScript</button>
  <button class="bg-orange-500 text-white px-3 py-2 rounded hover:bg-orange-600">FE</button>
  <button class="bg-orange-500 text-white px-3 py-2 rounded hover:bg-orange-600">PHP</button>
  <button class="bg-orange-500 text-white px-3 py-2 rounded hover:bg-orange-600">IP</button>
</div>
  </div>
</div>

    <!-- Question List -->
    <div class="space-y-4">
      <?php
      $questions = [
        ['id' => 1, 'title' => 'How to suck ur owndick?', 'slug' => 'how-to-solve-quadratic-equations', 'tags' => ['Math', 'Algebra']],
        ['id' => 2, 'title' => 'Can someone explain Newton’s 3rd law?', 'slug' => 'newtons-third-law', 'tags' => ['Physics']],
      ];
      foreach ($questions as $q): ?>
        <div class="bg-white shadow p-4 rounded">
          <a href="/question/<?php echo $q['id'] . '-' . $q['slug']; ?>" class="text-xl font-semibold text-orange-600 hover:underline">
            <?php echo htmlspecialchars($q['title']); ?>
          </a>
          <div class="mt-2 space-x-2">
            <?php foreach ($q['tags'] as $tag): ?>
              <span class="inline-block bg-orange-100 text-orange-700 text-sm px-2 py-1 rounded"><?php echo $tag; ?></span>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </main>


</body>
</html>