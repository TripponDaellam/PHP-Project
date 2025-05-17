<?php
try {
    $localPdo = new PDO("mysql:host=localhost;dbname=php_school_project", "root", "");
    $localPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Local DB connection failed: " . $e->getMessage();
    $localPdo = null;
}
