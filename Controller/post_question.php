<?php
session_start(); // ✅ Start session to access user_id

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Not logged in – prevent unauthorized posting
    header("Location: ../User/Login.php");
    exit();
}

require_once '../DBConnection/DBConnector.php'; // adjust path if needed

$title = $_POST['title'];
$description = $_POST['description'];
$tags = $_POST['tags'];
$userId = $_SESSION['user_id']; // ✅ Use session, not POST!

// Insert into DB
$stmt = $pdo->prepare("INSERT INTO questions (title, description, tags, user_id, created_at) VALUES (?, ?, ?, ?, NOW())");
$stmt->execute([$title, $description, $tags, $userId]);

// Handle image upload if needed
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $imgTmp = $_FILES['image']['tmp_name'];
    $imgName = basename($_FILES['image']['name']);
    $targetPath = 'uploads/' . time() . '_' . $imgName;
    move_uploaded_file($imgTmp, $targetPath);
}

// Redirect to home
header("Location: ../index.php");
exit();
?>
