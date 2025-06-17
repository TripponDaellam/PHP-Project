<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);

require_once 'DBConnection/DBConnector.php'; // Remote DB (questions, users)
require_once 'Partials/Limiter.php';

if (!$pdo) {
    die("❌ Database connection failed. Check DBConnector.php.");
}

$search = $_GET['search'] ?? '';
$searchParam = '%' . $search . '%';

if (!empty($search)) {
    $query = "
        SELECT tag_name, description, question_count 
        FROM tags 
        WHERE tag_name LIKE :search 
        ORDER BY question_count DESC 
        LIMIT 8";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':search', $searchParam, PDO::PARAM_STR);
} else {
    $query = "
        SELECT tag_name, description, question_count 
        FROM tags 
        ORDER BY question_count DESC 
        LIMIT 8";
    $stmt = $pdo->prepare($query);
}

$stmt->execute();
$tags = $stmt->fetchAll(PDO::FETCH_ASSOC);

$tag = $_GET['tag'] ?? null;
$questions = [];

if ($tag) {
    $stmt = $pdo->prepare("SELECT id, title, description, tags, created_at, upvotes, downvotes, answer FROM questions WHERE tags LIKE ?");
    $stmt->execute(["%$tag%"]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tags</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="flex flex-col min-h-screen bg-gray-50 text-black pt-20 overflow-x-hidden">
    <?php include 'Partials/nav.php'; ?>
    <aside class="hidden lg:block fixed top-0 left-0 h-full w-[200px] bg-white shadow">
        <?php include 'Partials/left_nav.php'; ?>
    </aside>

    <main class="flex-1 min-w-full md:min-w-[500px] max-w-screen-full ml-[220px] p-4">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Tags</h1>
        </div>

        <div class="mb-6">
            <form method="GET" action="tag.php" class="flex items-center space-x-2">
                <input type="text" name="search" placeholder="Search by tags" value="<?= htmlspecialchars($search) ?>" class="px-3.5 py-1.5 border border-gray-300 rounded w-72 focus:outline-none focus:ring-2 focus:ring-orange-400">
                <button type="submit" class="px-3.5 py-2.5 bg-orange-500 text-white rounded hover:bg-orange-600">Search</button>
            </form>
        </div>

        <?php if (!$tag): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php if (!empty($tags)): ?>
                    <?php foreach ($tags as $tagItem): ?>
                        <a href="tag.php?tag=<?= urlencode($tagItem['tag_name']) ?>" class="block bg-white shadow rounded-lg p-4 hover:shadow-md transition">
                            <span class="inline-block bg-orange-100 text-orange-600 text-xs font-semibold px-2 py-1 rounded mb-2">
                                <?= htmlspecialchars($tagItem['tag_name']) ?>
                            </span>
                            <p class="text-gray-700 text-sm mb-1 line-clamp-3">
                                <?= htmlspecialchars($tagItem['description']) ?>
                            </p>
                            <p class="text-xs text-gray-500">
                                <?= htmlspecialchars($tagItem['question_count']) ?> questions
                            </p>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-gray-500 col-span-full">No tags found.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($tag): ?>
            <div class="mt-4 space-y-4">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-bold text-orange-600">Showing results for tag: <?= htmlspecialchars($tag) ?></h2>
                    <a href="tag.php" class="text-blue-600">&larr; Back to Tags</a>
                </div>

                <?php if (empty($questions)): ?>
                    <p class="text-gray-500">No questions found for <strong><?= htmlspecialchars($tag) ?></strong>.</p>
                <?php else: ?>
                    <?php foreach ($questions as $q):
                        $qTags = array_filter(array_map('trim', explode(',', $q['tags'])));
                    ?>
                        <div class="bg-white shadow p-4 rounded flex flex-col sm:flex-row gap-4">
                            <div class="flex sm:flex-col text-sm text-center w-full sm:w-16 text-gray-500">
                                <div class="sm:mb-2">
                                    <strong><?= (int)$q['upvotes']; ?></strong><br class="hidden sm:block">upvotes
                                </div>
                                <div class="sm:mb-2">
                                    <strong><?= (int)$q['downvotes']; ?></strong><br class="hidden sm:block">downvotes
                                </div>
                                <div class="sm:mb-2">
                                    <strong><?= (int)$q['answer']; ?></strong><br class="hidden sm:block">answers
                                </div>
                            </div>
                            <div class="flex-1">
                                <a href="questionDetails.php?id=<?= $q['id'] ?>" class="text-lg font-semibold text-orange-600 hover:underline">
                                    <?= htmlspecialchars($q['title']); ?>
                                </a>
                                <p class="text-sm text-gray-600 mt-1">
                                    <?= word_limiter($q['description'], 100); ?>
                                </p>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <?php foreach ($qTags as $qt): ?>
                                        <span class="inline-block bg-orange-100 text-orange-700 text-sm px-2 py-1 rounded">
                                            <?= htmlspecialchars($qt); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                                <div class="text-xs text-gray-400 text-right mt-2">
                                    asked <?= date("M j, Y g:i a", strtotime($q['created_at'])); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>

    <div class="mt-auto">
        <?php include 'Partials/footer.php'; ?>
    </div>
</body>

</html>