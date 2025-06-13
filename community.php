<?php 
session_start();
require_once 'DBConnection/DBConnector.php';
$isLoggedIn = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body>

    <?php include 'Partials/nav.php'; ?>
    <aside class="hidden lg:block fixed top-[90px] left-0 h-[calc(100%-4rem)] w-[200px] bg-white z-10 shadow">
    <?php include 'Partials/left_nav.php'; ?>
  </aside>
    
</body>
</html>