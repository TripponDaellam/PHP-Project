<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$username = $isLoggedIn ? $_SESSION['username'] : '';

require_once '../DBConnection/DBConnector.php';
require_once '../DBConnection/DBLocal.php';

// Fetch pending reports with question info
$stmt = $pdo->prepare("
    SELECT r.id AS report_id, r.reason, r.reported_at, q.id AS question_id, q.title
    FROM reportedQuestions r
    JOIN questions q ON r.question_id = q.id
    WHERE r.status = 'pending'
    ORDER BY r.reported_at DESC
");
$stmt->execute();
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Admin - Reported Posts</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs" defer></script>
</head>

<body class="bg-gray-100 text-black pt-16">

    <!-- Top Nav -->
    <?php include '../Partials/nav.php'; ?>

    <div class="flex flex-col lg:flex-row min-h-screen bg-gray-100">

        <!-- Left Sidebar -->
        <aside class="hidden lg:block lg:fixed top-16 left-0 h-[calc(100%-4rem)] w-[200px] bg-white z-10 shadow">
            <?php include '../Partials/left_nav.php'; ?>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 w-full lg:ml-[220px] lg:mr-5 p-4 overflow-x-auto min-w-full md:min-w-[500px] max-w-screen-full">
            <div class="mb-6">
                <h1 class="text-3xl font-bold">Reported Questions</h1>
                <p class="text-sm text-gray-600">Review and take action on reported posts.</p>
            </div>

            <?php if (empty($reports)): ?>
                <p class="text-gray-500 mt-4">No reports to review.</p>
            <?php else: ?>
                <div class="space-y-6">
                    <?php foreach ($reports as $report): ?>
                        <div class="bg-white shadow px-6 py-4 rounded">
                            <h2 class="text-lg font-semibold text-orange-600 underline mb-1">
                                <a href="../questionDetails.php?id=<?= $report['question_id'] ?>" target="_blank">
                                    <?= htmlspecialchars($report['title']) ?>
                                </a>
                            </h2>
                            <p class="text-gray-700"><strong>Reason:</strong> <?= nl2br(htmlspecialchars($report['reason'])) ?></p>
                            <p class="text-xs text-gray-500 mt-2">Reported at: <?= date('F j, Y H:i', strtotime($report['reported_at'])) ?></p>
                            <form method="POST" action="admin_handle_report.php" class="mt-4 flex gap-2">
                                <input type="hidden" name="report_id" value="<?= $report['report_id'] ?>">
                                <input type="hidden" name="question_id" value="<?= $report['question_id'] ?>">
                                <button type="submit" name="action" value="ban" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">Ban Post</button>
                                <button type="submit" name="action" value="dismiss" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">Dismiss</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>

        <!-- Right Sidebar (Optional or Empty) -->
        <!-- <aside class="hidden lg:block w-full lg:w-72 px-4 py-6 bg-white rounded shadow mt-10 lg:mt-[75px] lg:mr-6 h-fit sticky top-24">
            <h3 class="text-xl font-bold text-gray-900 mb-2">Admin Tools</h3>
            <p class="text-sm text-gray-500">Future filters, logs or analytics can go here.</p>
        </aside> -->
    </div>

    <!-- Footer -->
    <footer class="mt-10">
        <?php include '../Partials/footer.php'; ?>
    </footer>
</body>

</html>