<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json; charset=UTF-8');

if (isset($_POST["id"])) {
  try {
    $stmt = $pdo->prepare("SELECT Quantity, TyreDesc FROM tblbasket WHERE basket_id = ? AND EAN = ? LIMIT 1");
    $stmt->execute([session_id(), (string)$_POST["id"]]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $output = [];
    if ($row) {
      $output["quantity"] = $row["Quantity"];
      $output["tyredesc"] = $row["TyreDesc"];
    }
    echo json_encode($output);
  } catch (Throwable $e) {
    echo json_encode([]);
  }
}
