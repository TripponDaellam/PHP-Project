<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
if (!$isLoggedIn) {
    header("Location: Login.php");
    exit;
}

require_once 'DBConnection/DBConnector.php';
require_once 'Partials/Limiter.php';

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT q.id, q.title, q.description, q.tags, q.created_at, q.upvotes, q.downvotes, q.answer, sq.created_at AS saved_at
    FROM savedQuestions sq
    JOIN questions q ON sq.question_id = q.id
    WHERE sq.user_id = ? AND q.banned = 0
    ORDER BY sq.created_at DESC
");
$stmt->execute([$user_id]);
$savedQuestions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper function for description truncation
function word_limiter($string, $limit)
{
    $words = explode(' ', strip_tags($string));
    if (count($words) <= $limit) {
        return htmlspecialchars($string);
    }
    return htmlspecialchars(implode(' ', array_slice($words, 0, $limit))) . '...';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Saved Questions</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 text-black pt-16">
    <?php include 'Partials/nav.php'; ?>

    <aside class="hidden lg:block fixed top-16 left-0 h-[calc(100%-4rem)] w-[200px] bg-white z-10 shadow overflow-auto">
        <?php include 'Partials/left_nav.php'; ?>
    </aside>

    <main class="ml-0 lg:ml-[220px] mr-4 px-4 pt-6">
        <h1 class="text-2xl font-bold mb-4">Saved Questions</h1>

        <div id="saved-questions-container">
            <?php if (empty($savedQuestions)) : ?>
                <p class="text-gray-600">You haven't saved any questions yet.</p>
            <?php else : ?>
                <div class="space-y-4">
                    <?php foreach ($savedQuestions as $q) : ?>
                        <?php
                        $tags = array_filter(array_map('trim', explode(',', $q['tags'])));
                        ?>
                        <div class="bg-white shadow p-4 px-6 rounded relative">
                            <a href="questionDetails.php?id=<?= $q['id'] ?>" class="text-orange-600 text-lg font-semibold hover:underline">
                                <?= htmlspecialchars($q['title']) ?>
                            </a>
                            <p class="text-sm text-gray-600 mt-1"><?= word_limiter($q['description'], 100) ?></p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                <?php foreach ($tags as $tag) : ?>
                                    <span class="bg-orange-100 text-orange-700 text-xs px-2 py-1 rounded"><?= htmlspecialchars($tag) ?></span>
                                <?php endforeach; ?>
                            </div>
                            <button onclick="unsaveQuestion(<?= $q['id'] ?>, this)" class="absolute top-4 right-6 text-sm text-red-600 hover:underline">
                                Unsave
                            </button>
                            <div class="text-xs text-gray-400 text-right mt-4">
                                Saved on <?= date("M j, Y g:i a", strtotime($q['saved_at'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        function unsaveQuestion(questionId, btn) {
            fetch('Actions/unsave.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        question_id: questionId
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Remove the question card from DOM
                        btn.closest('.bg-white').remove();

                        // If no questions remain, show "no questions" message
                        if (document.querySelectorAll('#saved-questions-container .bg-white').length === 0) {
                            document.getElementById('saved-questions-container').innerHTML = '<p class="text-gray-600">You haven\'t saved any questions yet.</p>';
                        }
                    } else {
                        alert(data.message || 'Unsave failed');
                    }
                })
                .catch(() => alert('Error unsaving'));
        }
    </script>
</body>

</html>