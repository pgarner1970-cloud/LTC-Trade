<?php
session_start();
require_once 'db.php';

try {
  $stmt = $pdo->prepare("DELETE FROM tblbasket WHERE basket_id = ?");
  $stmt->execute([session_id()]);
  echo "Basket cleared";
} catch (Throwable $e) {
  echo "Error";
}
