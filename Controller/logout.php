<?php
session_start();
session_unset();
session_unset();
session_destroy();
header("Location: ../User/Login.php");
exit();
