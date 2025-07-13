<?php
session_start();
require_once '../DBConnection/DBConnector.php';

// if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
//     die("Access denied.");
// }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $report_id = $_POST['report_id'] ?? null;
    $question_id = $_POST['question_id'] ?? null;
    $action = $_POST['action'] ?? null;

    if (!$report_id || !$question_id || !in_array($action, ['ban', 'dismiss'])) {
        die("Invalid request.");
    }

    if ($action === 'ban') {
        // Ban the post (make sure your questions table has a 'banned' column, 0 or 1)
        $stmt = $pdo->prepare("UPDATE questions SET banned = 1 WHERE id = :question_id");
        $stmt->execute([':question_id' => $question_id]);

        // Mark report as reviewed
        $stmt = $pdo->prepare("UPDATE reportedQuestions SET status = 'reviewed' WHERE id = :report_id");
        $stmt->execute([':report_id' => $report_id]);

        echo "Post banned and report marked as reviewed. <a href='admin_reported.php'>Back to reports</a>";
        exit;
    }

    if ($action === 'dismiss') {
        // Mark report as dismissed
        $stmt = $pdo->prepare("UPDATE reportedQuestions SET status = 'dismissed' WHERE id = :report_id");
        $stmt->execute([':report_id' => $report_id]);

        echo "Report dismissed. <a href='admin_reported.php'>Back to reports</a>";
        exit;
    }
}

echo "Invalid request.";
