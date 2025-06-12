<?php
session_start();
require_once '../DBConnection/DBConnector.php';

$userId = $_SESSION['user_id'] ?? null;
echo $userId; // Debugging line to check if userId is set

if (!$userId) {
    header('Location: ../User/Login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Use empty string fallback to avoid null in trim()
    $name  = trim($_POST['name'] ?? '');
    // $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if (empty($name)) {
        header("Location: ../User/profile.php?error=empty_name");
        exit;
    }

    // if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    //     header("Location: ../User/profile.php?error=invalid_email");
    //     exit;
    // }

    if (!preg_match('/^[0-9+\-\s]*$/', $phone)) {
        header("Location: ../User/profile.php?error=invalid_phone");
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE users SET full_name = :fullname, phone = :phone WHERE id = :id");
        $stmt->execute([
            ':fullname'  => $name,
            // ':email' => $email,
            ':phone' => $phone,
            ':id'    => $userId,
        ]);

        header("Location: ../User/profile.php?success=1");
        exit;
    } catch (PDOException $e) {
        error_log("Update Error: " . $e->getMessage());
        header("Location: ../User/profile.php?error=update_failed");
        exit;
    }
} else {
    // If not POST request, redirect to profile page
    header("Location: ../User/profile.php");
    exit;
}
?>
