<?php
session_start();
require_once '../DBConnection/DBConnector.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: ../Login.php");
  exit;
}

$user_id = $_SESSION['user_id'];
$question_id = $_GET['id'] ?? null;

$message = '';
$type = ''; // success, warning, error

if (!$question_id) {
  $message = 'Invalid request.';
  $type = 'error';
} else {
  $check = $pdo->prepare("SELECT 1 FROM savedQuestions WHERE user_id = :user_id AND question_id = :question_id");
  $check->execute([
    ':user_id' => $user_id,
    ':question_id' => $question_id
  ]);

  if ($check->rowCount() === 0) {
    $stmt = $pdo->prepare("INSERT INTO savedQuestions (user_id, question_id) VALUES (:user_id, :question_id)");
    $stmt->execute([
      ':user_id' => $user_id,
      ':question_id' => $question_id
    ]);
    $message = 'The question has been saved successfully.';
    $type = 'success';
  } else {
    $message = 'You have already saved this question.';
    $type = 'warning';
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Save Question</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script>
    setTimeout(() => {
      window.location.href = '../index.php';
    }, 3000);
  </script>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 p-6">
  <div class="max-w-xl mx-auto bg-white p-6 rounded shadow text-center">
    <?php if ($type === 'success'): ?>
      <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
        <strong class="font-bold">Saved!</strong>
        <span class="block sm:inline"><?= htmlspecialchars($message) ?></span>
      </div>
    <?php elseif ($type === 'warning'): ?>
      <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
        <strong class="font-bold">Already Saved</strong>
        <span class="block sm:inline"><?= htmlspecialchars($message) ?></span>
      </div>
    <?php elseif ($type === 'error'): ?>
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
        <strong class="font-bold">Error</strong>
        <span class="block sm:inline"><?= htmlspecialchars($message) ?></span>
      </div>
    <?php endif; ?>
    <p class="mt-4 text-gray-500">Redirecting in 3 seconds...</p>
  </div>
</body>

</html>