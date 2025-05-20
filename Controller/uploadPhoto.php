<?php
session_start();
require_once '../DBConnection/DBConnector.php';

$userId = $_SESSION['user_id'];

if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $fileTmp = $_FILES['image']['tmp_name'];
    $fileName = basename($_FILES['image']['name']);
    $targetDir = 'uploads/';
    $targetPath = $targetDir . time() . '_' . $fileName;

    if (move_uploaded_file($fileTmp, $targetPath)) {
        $stmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
        $stmt->execute([$targetPath, $userId]);
        header("Location: ../User/profile.php?upload=success");
        exit;
    } else {
        echo "Error moving file.";
    }
} else {
    echo "Image upload error.";
}
