<?php
$pdo = new PDO("mysql://avnadmin:AVNS_KazazPwStUrRMEUKKgH@mysql-2bacbe81-heinkhantzin0-a0b1.f.aivencloud.com:16038/defaultdb?ssl-mode=REQUIRED", "avnadmin", "AVNS_KazazPwStUrRMEUKKgH");

// Sanitize inputs
$title = $_POST['title'];
$description = $_POST['description'];
$tags = $_POST['tags'];

// Insert into DB
$stmt = $pdo->prepare("INSERT INTO questions (title, description, tags, created_at) VALUES (?, ?, ?, NOW())");
$stmt->execute([$title, $description, $tags]);

// Redirect to home
header("Location: index.php");
exit();
?>
