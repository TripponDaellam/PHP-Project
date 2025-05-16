<?php
class Database {
  private static $host = "mysql-2bacbe81-heinkhantzin0-a0b1.f.aivencloud.com";
  private static $port = "16038";
  private static $dbname = "Php_School_Peoject";
  private static $username = "avnadmin";
  private static $password = "AVNS_KazazPwStUrRMEUKKgH";
  private static $pdo = null;

  public static function getConnection() {
    if (self::$pdo === null) {
      try {
        $dsn = "mysql:host=" . self::$host . ";port=" . self::$port . ";dbname=" . self::$dbname . ";sslmode=REQUIRED";
        self::$pdo = new PDO($dsn, self::$username, self::$password);
        self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
      }
    }
    return self::$pdo;
  }
}
?>
