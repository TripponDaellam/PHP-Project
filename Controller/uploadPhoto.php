<?php
session_start();
require_once '../DBConnection/DBConnector.php';

$userId = $_SESSION['user_id'];

if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
    $fileTmp = $_FILES['profile_image']['tmp_name'];
    $fileName = basename($_FILES['profile_image']['name']);
    $targetDir = '../User/uploads/';
    $targetPath = $targetDir . time() . '_' . $fileName;
    
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if(!in_array($_FILES['profile_image']['type'], $allowedTypes)) {
        
        exit;
    }
    if($_FILES['profile_image']['size'] > 5 * 1024 * 1024) { // 5MB limit
        echo "File size exceeds limit.";
        exit;
    }


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
