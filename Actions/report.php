<?php
session_start();
require_once '../DBConnection/DBConnector.php';

header('Content-Type: text/plain');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo "Please login to report questions.";
    exit;
}

$user_id = $_SESSION['user_id'];
$question_id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null;

if (!$question_id) {
    http_response_code(400);
    echo "Invalid question ID.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Method not allowed.";
    exit;
}

$reason = trim($_POST['reason'] ?? '');
if ($reason === '') {
    http_response_code(400);
    echo "Please provide a reason for reporting.";
    exit;
}

$checkStmt = $pdo->prepare("SELECT COUNT(*) FROM reportedQuestions WHERE user_id = :user_id AND question_id = :question_id AND status = 'pending'");
$checkStmt->execute([':user_id' => $user_id, ':question_id' => $question_id]);
if ($checkStmt->fetchColumn() > 0) {
    http_response_code(409);
    echo "You have already reported this question and it is pending review.";
    exit;
}

$stmt = $pdo->prepare("INSERT INTO reportedQuestions (user_id, question_id, reason, status, reported_at) VALUES (:user_id, :question_id, :reason, 'pending', NOW())");
$stmt->execute([':user_id' => $user_id, ':question_id' => $question_id, ':reason' => $reason]);

http_response_code(200);
echo "Report submitted successfully.";
?>

<!-- <!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Report Question</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 p-6">
    <div class="max-w-xl mx-auto bg-white p-6 rounded shadow">
        <h2 class="text-xl font-semibold mb-4">Report Question</h2>

        <?php if ($message): ?>
            <div class="mb-4 p-4 rounded 
                <?= $type === 'success' ? 'bg-green-100 text-green-700 border border-green-400' : '' ?>
                <?= $type === 'error' ? 'bg-red-100 text-red-700 border border-red-400' : '' ?>
                <?= $type === 'warning' ? 'bg-yellow-100 text-yellow-700 border border-yellow-400' : '' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php if (!$type || $type === 'error'): ?>
            <form method="POST" action="?id=<?= htmlspecialchars($question_id) ?>" class="space-y-4">
                <textarea name="reason" placeholder="Reason for reporting..." required rows="5"
                    class="w-full p-3 border border-gray-300 rounded resize-none focus:outline-none focus:ring focus:border-blue-300"></textarea>
                <button type="submit"
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded shadow transition">
                    Submit Report
                </button>
            </form>
        <?php endif; ?>
    </div>
</body>

</html> -->