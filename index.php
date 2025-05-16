<?php
require_once 'DBConnection/DBConnector.php';

$query = "SELECT id, title, description, tags, created_at FROM questions ORDER BY created_at DESC LIMIT 10";
$stmt = $pdo->prepare($query);
$stmt->execute();
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Method Flow - Home</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/alpinejs" defer></script>
</head>
<body class="flex flex-col min-h-screen bg-gray-50 text-black pt-20 overflow-x-hidden">

  <!-- Top Navigation -->
  <?php include 'Partials/nav.php'; ?>

  <!-- Left Sidebar -->
  <aside class="fixed top-20 left-0 h-[calc(100%-10rem)] w-[200px] bg-white z-10 hidden md:block shadow">
    <?php include 'Partials/left_nav.php'; ?>
  </aside>

  <aside class="fixed top-20 right-0 h-[calc(100%-10rem)] w-60 bg-white shadow px-4 py-6 z-10 hidden lg:block">
    <?php include 'Partials/filter.php'; ?>
  </aside>
  <main class="flex-1 max-w-screen-2xl mx-auto px-4 py-6 md:ml-60 lg:mr-60">
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold">Recent Questions</h1>
      <a href="ask.php" class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600">Ask a Question</a>
    </div>
    <div class="space-y-4">
      <?php if (!empty($questions)): ?>
        <?php foreach ($questions as $q): ?>
          <div class="bg-white shadow p-4 rounded">
            <a href="/question/<?php echo $q['id']; ?>" class="text-xl font-semibold text-orange-600 hover:underline">
              <?php echo htmlspecialchars($q['title']); ?>
            </a>
            <p class="text-gray-700 mt-2">
              <?php echo htmlspecialchars($q['description']); ?>
            </p>
            <div class="mt-2 space-x-2">
              <?php foreach (explode(',', $q['tags']) as $tag): ?>
                <span class="inline-block bg-orange-100 text-orange-700 text-sm px-2 py-1 rounded">
                  <?php echo trim($tag); ?>
                </span>
              <?php endforeach; ?>
            </div>
            <p class="text-xs text-gray-500 mt-1">
              Asked on <?= date('F j, Y', strtotime($q['created_at'])) ?>
            </p>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="text-gray-500">No questions found.</p>
      <?php endif; ?>
    </div>
  </main>

  <!-- Footer -->
  <div class="mt-auto z-10">
    <?php include 'Partials/footer.php'; ?>
  </div>

</body>
</html>
