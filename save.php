<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Save</title>
</head>

<body>
    <?php include 'Partials/nav.php'; ?>

    <aside class="hidden lg:block fixed top-16 left-0 h-[calc(100%-4rem)] w-[200px] bg-white z-10 shadow overflow-auto">
        <?php include 'Partials/left_nav.php'; ?>
    </aside>
</body>

</html>