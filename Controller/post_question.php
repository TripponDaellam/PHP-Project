<?php
session_start(); // Start session

if (!isset($_SESSION['user_id'])) {
    header("Location: ../User/Login.php");
    exit();
}

require_once '../DBConnection/DBConnector.php';

// Get form data
$title = $_POST['title'];
$description = $_POST['description'];
$tags = $_POST['selected_tags'] ?? '';
$userId = $_SESSION['user_id'];

// Convert tags to array
$submittedTags = array_filter(array_map('trim', explode(',', $tags)));

// Fetch valid tags from DB
$stmt = $pdo->query("SELECT tag_name FROM tags");
$allowedTags = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Check for invalid tags
$invalidTags = array_diff($submittedTags, $allowedTags);

if (!empty($invalidTags)) {
    $_SESSION['error'] = "Invalid tag(s): " . implode(', ', $invalidTags);
    header("Location: ../ask.php"); // Redirect back to form
    exit();
}

// Prepare tags string for DB
$validTagsString = implode(',', $submittedTags);

// Insert question
$stmt = $pdo->prepare("INSERT INTO questions (title, description, tags, user_id, created_at) VALUES (?, ?, ?, ?, NOW())");
$stmt->execute([$title, $description, $validTagsString, $userId]);

// Handle image upload
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $imgTmp = $_FILES['image']['tmp_name'];
    $imgName = basename($_FILES['image']['name']);
    $targetPath = 'uploads/' . time() . '_' . $imgName;
    move_uploaded_file($imgTmp, $targetPath);
}

// Redirect after successful post
header("Location: ../index.php");
exit();
?>
