<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../User/Login.php");
    exit();
}

require_once '../DBConnection/DBConnector.php';

// Sanitize and get form inputs
$title = htmlspecialchars(trim($_POST['title'] ?? ''));
$description = htmlspecialchars(trim($_POST['description'] ?? ''));
$tags = trim($_POST['selected_tags'] ?? '');
$userId = $_SESSION['user_id'];

// Validate title and description
if (empty($title) || empty($description)) {
    $_SESSION['error'] = "Title and description are required.";
    header("Location: ../ask.php");
    exit();
}

// Convert tags to array
$submittedTags = array_filter(array_map('trim', explode(',', $tags)));

// Validate tag count
if (count($submittedTags) === 0) {
    $_SESSION['error'] = "Please select at least one valid tag.";
    header("Location: ../ask.php");
    exit();
}

// Fetch valid tags from DB
$stmt = $pdo->query("SELECT tag_name FROM tags");
$allowedTags = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Check for invalid tags (case-sensitive match)
$invalidTags = array_diff($submittedTags, $allowedTags);
if (!empty($invalidTags)) {
    $_SESSION['error'] = "Invalid tag(s): " . implode(', ', $invalidTags);
    header("Location: ../ask.php");
    exit();
}

// Re-join validated tags
$validTagsString = implode(',', $submittedTags);

// Insert the question
$stmt = $pdo->prepare("
    INSERT INTO questions (title, description, tags, user_id, created_at)
    VALUES (?, ?, ?, ?, NOW())
");

if (!$stmt->execute([$title, $description, $validTagsString, $userId])) {
    $_SESSION['error'] = "Failed to post your question. Please try again.";
    header("Location: ../ask.php");
    exit();
}

// Insert the question count
$stmt = $pdo->prepare("
    UPDATE tags SET question_count = question_count + 1
    WHERE tag_name = ?;
");
$stmt->execute([$validTagsString]);

$questionId = $pdo->lastInsertId();

// Optional: Handle image upload
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $imgTmp = $_FILES['image']['tmp_name'];
    $imgName = basename($_FILES['image']['name']);
    $targetDir = '../uploads/';
    $targetPath = $targetDir . time() . '_' . $imgName;

    if (move_uploaded_file($imgTmp, $targetPath)) {
        // Optional: save image path to the DB if you have an image column
        // $pdo->prepare("UPDATE questions SET image_path = ? WHERE id = ?")->execute([$targetPath, $questionId]);
    }
}

// ✅ Redirect to homepage or success page
header("Location: ../index.php");
exit();
