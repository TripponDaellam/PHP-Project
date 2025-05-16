<?php
try {
    $pdo = new PDO(
        "mysql:host=mysql-2bacbe81-heinkhantzin0-a0b1.f.aivencloud.com;port=16038;dbname=Php_School_Peoject;sslmode=REQUIRED",
        "avnadmin",
        "AVNS_KazazPwStUrRMEUKKgH"
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   // echo "✅ Connected successfully to Aiven MySQL!";
} catch (PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage();
    $pdo = null;
}
