<?php
namespace App\Db;
use PDO;
final class Db {
  private static $pdo = null;
  public static function pdo(): PDO {
    if (self::$pdo instanceof PDO) return self::$pdo;
    $cfg = require __DIR__ . '/../../config/database.php';
    self::$pdo = new PDO($cfg['dsn'], $cfg['user'], $cfg['pass'], $cfg['options']);
    return self::$pdo;
  }
}
