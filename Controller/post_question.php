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
    header("Location: ../ask.php");
    exit();
}

$validTagsString = implode(',', $submittedTags);

// Default to null
$imageData = null;

// Handle image upload (as binary BLOB)
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $imgTmp = $_FILES['image']['tmp_name'];
    $imageData = file_get_contents($imgTmp); // Read binary content
}

// Insert question with image BLOB
$stmt = $pdo->prepare("INSERT INTO questions (title, description, tags, user_id, created_at, image) VALUES (?, ?, ?, ?, NOW(), ?)");
$stmt->bindParam(1, $title);
$stmt->bindParam(2, $description);
$stmt->bindParam(3, $validTagsString);
$stmt->bindParam(4, $userId);
$stmt->bindParam(5, $imageData, PDO::PARAM_LOB); // Bind as binary

$stmt->execute();

// Redirect to home
header("Location: ../index.php");
exit();
?>
