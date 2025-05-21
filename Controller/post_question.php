<?php
$pdo = new PDO(
        "mysql:host=mysql-2bacbe81-heinkhantzin0-a0b1.f.aivencloud.com;port=16038;dbname=Php_School_Peoject;sslmode=REQUIRED",
        "avnadmin",
        "AVNS_KazazPwStUrRMEUKKgH"
    );
$title = $_POST['title'];
$description = $_POST['description'];
$tags = $_POST['tags'];

// Insert into DB
$stmt = $pdo->prepare("INSERT INTO questions (title, description, tags, created_at) VALUES (?, ?, ?, NOW())");
$stmt->execute([$title, $description, $tags]);
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
  $imgTmp = $_FILES['image']['tmp_name'];
  $imgName = basename($_FILES['image']['name']);
  $targetPath = 'uploads/' . time() . '_' . $imgName;
  move_uploaded_file($imgTmp, $targetPath);
  // Save $targetPath in your database
}

// Redirect to home
header("Location: ../index.php");
exit();
?>
