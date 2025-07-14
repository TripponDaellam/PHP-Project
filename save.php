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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="bg-gradient-to-br from-gray-50 to-gray-100 text-gray-800 pt-16 min-h-screen">

    <?php include 'Partials/nav.php'; ?>

    <aside class="hidden lg:block fixed top-16 left-0 h-[calc(100%-4rem)] w-[200px] bg-white z-10 shadow-lg overflow-auto">
        <?php include 'Partials/left_nav.php'; ?>
    </aside>

    <main class="ml-0 lg:ml-[220px] mr-4 px-6 pt-6">

        <!-- Header Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-gradient-to-r from-orange-400 to-orange-600 rounded-full flex items-center justify-center text-white text-xl">
                        <i class="fas fa-bookmark"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-2">Saved Questions</h1>
                        <p class="text-gray-600">Your collection of bookmarked questions for later reference</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <i class="fas fa-bookmark text-orange-500 text-xl"></i>
                    <span class="text-sm text-gray-500"><?= count($savedQuestions) ?> saved</span>
                </div>
            </div>
        </div>

        <!-- Saved Questions Container -->
        <div id="saved-questions-container">
            <?php if (empty($savedQuestions)) : ?>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                    <div class="w-24 h-24 bg-gradient-to-r from-orange-100 to-orange-200 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-bookmark text-4xl text-orange-500"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No Saved Questions</h3>
                    <p class="text-gray-500 mb-6">You haven't saved any questions yet. Start exploring and bookmark interesting questions!</p>
                    <a href="index.php" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:from-orange-600 hover:to-orange-700 transition-all duration-200 font-medium shadow-md hover:shadow-lg">
                        <i class="fas fa-search mr-2"></i>
                        Browse Questions
                    </a>
                </div>
            <?php else : ?>
                <div class="space-y-6">
                    <?php foreach ($savedQuestions as $q) : ?>
                        <?php
                        $tags = array_filter(array_map('trim', explode(',', $q['tags'])));
                        ?>
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200 relative">

                            <!-- Header with Unsave Button -->
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex-1">
                                    <a href="questionDetails.php?id=<?= $q['id'] ?>" class="text-xl font-bold text-gray-800 hover:text-orange-600 transition-colors duration-200 block mb-2">
                                        <?= htmlspecialchars($q['title']) ?>
                                    </a>
                                    <p class="text-gray-600 text-sm leading-relaxed">
                                        <?= word_limiter($q['description'], 100) ?>
                                    </p>
                                </div>

                                <!-- Unsave Button -->
                                <button onclick="unsaveQuestion(<?= $q['id'] ?>, this)"
                                    class="ml-4 p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors duration-200 group">
                                    <i class="fas fa-bookmark text-lg group-hover:scale-110 transition-transform duration-200"></i>
                                </button>
                            </div>

                            <!-- Tags -->
                            <div class="flex flex-wrap gap-2 mb-4">
                                <?php foreach ($tags as $tag) : ?>
                                    <span class="bg-gradient-to-r from-orange-100 to-orange-200 text-orange-700 text-xs px-3 py-1 rounded-full font-medium">
                                        <?= htmlspecialchars($tag) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>

                            <!-- Stats and Meta -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-6">
                                    <!-- Stats -->
                                    <div class="flex items-center space-x-4">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-arrow-up text-green-500"></i>
                                            <span class="text-sm font-bold text-gray-700"><?= (int)$q['upvotes'] ?></span>
                                            <span class="text-xs text-gray-500">upvotes</span>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-arrow-down text-red-500"></i>
                                            <span class="text-sm font-bold text-gray-700"><?= (int)$q['downvotes'] ?></span>
                                            <span class="text-xs text-gray-500">downvotes</span>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-comments text-blue-500"></i>
                                            <span class="text-sm font-bold text-gray-700"><?= (int)$q['answer'] ?></span>
                                            <span class="text-xs text-gray-500">answers</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Save Date -->
                                <div class="text-xs text-gray-500 flex items-center">
                                    <i class="fas fa-calendar mr-1"></i>
                                    Saved on <?= date("M j, Y g:i a", strtotime($q['saved_at'])) ?>
                                </div>
                            </div>

                            <!-- Quick Actions -->
                            <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <a href="questionDetails.php?id=<?= $q['id'] ?>"
                                        class="text-sm text-blue-600 hover:text-blue-700 font-medium flex items-center">
                                        <i class="fas fa-eye mr-1"></i>
                                        View Question
                                    </a>
                                    <span class="text-gray-300">|</span>
                                    <span class="text-sm text-gray-500">
                                        Asked <?= date("M j, Y", strtotime($q['created_at'])) ?>
                                    </span>
                                </div>

                                <div class="flex items-center space-x-2">
                                    <span class="text-xs text-gray-400">Click bookmark to unsave</span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="mt-auto">
        <?php include 'Partials/footer.php'; ?>
    </footer>

    <script>
        function unsaveQuestion(questionId, btn) {
            // Add loading state
            const icon = btn.querySelector('i');
            const originalIcon = icon.className;
            icon.className = 'fas fa-spinner fa-spin text-lg';
            btn.disabled = true;

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
                        // Add fade out animation
                        const questionCard = btn.closest('.bg-white');
                        questionCard.style.transition = 'all 0.3s ease';
                        questionCard.style.opacity = '0';
                        questionCard.style.transform = 'translateX(20px)';

                        setTimeout(() => {
                            questionCard.remove();

                            // Update saved count
                            const savedCount = document.querySelector('.text-gray-500');
                            const currentCount = parseInt(savedCount.textContent.match(/\d+/)[0]);
                            savedCount.textContent = `${currentCount - 1} saved`;

                            // If no questions remain, show "no questions" message
                            if (document.querySelectorAll('#saved-questions-container .bg-white').length === 0) {
                                document.getElementById('saved-questions-container').innerHTML = `
                                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                                        <div class="w-24 h-24 bg-gradient-to-r from-orange-100 to-orange-200 rounded-full flex items-center justify-center mx-auto mb-6">
                                            <i class="fas fa-bookmark text-4xl text-orange-500"></i>
                                        </div>
                                        <h3 class="text-xl font-semibold text-gray-700 mb-2">No Saved Questions</h3>
                                        <p class="text-gray-500 mb-6">You haven't saved any questions yet. Start exploring and bookmark interesting questions!</p>
                                        <a href="index.php" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:from-orange-600 hover:to-orange-700 transition-all duration-200 font-medium shadow-md hover:shadow-lg">
                                            <i class="fas fa-search mr-2"></i>
                                            Browse Questions
                                        </a>
                                    </div>
                                `;
                            }
                        }, 300);
                    } else {
                        // Reset button state on error
                        icon.className = originalIcon;
                        btn.disabled = false;
                        alert(data.message || 'Unsave failed');
                    }
                })
                .catch(() => {
                    // Reset button state on error
                    icon.className = originalIcon;
                    btn.disabled = false;
                    alert('Error unsaving question');
                });
        }
    </script>
</body>

</html>