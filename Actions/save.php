<?php
session_start();
require_once '../DBConnection/DBConnector.php';

if (!isset($_SESSION['user_id'])) {
    die("Please login to save questions.");
}

$user_id = $_SESSION['user_id'];
$question_id = $_GET['id'] ?? null;

$saveSuccess = false;
$alreadySaved = false;

if ($question_id) {
    // Check if already saved
    $check = $pdo->prepare("SELECT 1 FROM savedQuestions WHERE user_id = :user_id AND question_id = :question_id");
    $check->execute([
        ':user_id' => $user_id,
        ':question_id' => $question_id
    ]);

    if ($check->rowCount() === 0) {
        // Not saved yet, insert
        $stmt = $pdo->prepare("INSERT INTO savedQuestions (user_id, question_id) VALUES (:user_id, :question_id)");
        $stmt->execute([
            ':user_id' => $user_id,
            ':question_id' => $question_id
        ]);
        $saveSuccess = true;
    } else {
        // Already saved
        $alreadySaved = true;
    }
} else {
    echo "Invalid request.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Save Question</title>
  <script>
    setTimeout(() => {
      window.location.href = '../index.php';
    }, 3000);
  </script>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-6">
  <div class="max-w-xl mx-auto bg-white p-6 rounded shadow">
    <?php if ($saveSuccess): ?>
      <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
        <strong class="font-bold">Saved!</strong>
        <span class="block sm:inline">The question has been saved successfully.</span>
      </div>
    <?php elseif ($alreadySaved): ?>
      <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
        <strong class="font-bold">Already Saved</strong>
        <span class="block sm:inline">You have already saved this question.</span>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
