<?php
session_start();
require_once 'db.php';

if (isset($_POST["id"])) {
  try {
    $stmt = $pdo->prepare("DELETE FROM tblbasket WHERE basket_id = ? AND EAN = ?");
    $stmt->execute([session_id(), (string)$_POST["id"]]);
    echo "Tyre removed";
  } catch (Throwable $e) {
    echo "Error";
  }
}
