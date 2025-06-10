<?php
session_start();
require_once '../DBConnection/DBConnector.php';

if (!isset($_SESSION['user_id'])) {
    die("Please login to report questions.");
}

$user_id = $_SESSION['user_id'];
$question_id = $_GET['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reason = $_POST['reason'] ?? 'No reason given';

    $stmt = $pdo->prepare("INSERT INTO reportedQuestions (user_id, question_id, reason) VALUES (:user_id, :question_id, :reason)");
    $stmt->execute([
        ':user_id' => $user_id,
        ':question_id' => $question_id,
        ':reason' => $reason
    ]);

    echo "Report submitted. <a href='../index.php'>Go back</a>";
    exit;
}

if (!$question_id) {
    echo "Invalid question.";
    exit;
}
?>

<!-- Simple form -->
<form method="POST">
    <h2>Report Question</h2>
    <textarea name="reason" placeholder="Reason for reporting..." required></textarea><br>
    <button type="submit">Submit Report</button>
</form>
