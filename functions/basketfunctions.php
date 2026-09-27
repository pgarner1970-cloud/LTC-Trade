<?php
session_start();
require_once 'db.php';

if (isset($_POST["operation"]) && $_POST["operation"] === "Edit") {
  $quantity = (int)($_POST["quantity"] ?? 0);
  $basketEan = (string)($_POST["basket_id"] ?? "");

  try {
    $stmt = $pdo->prepare("UPDATE tblbasket SET Quantity = ? WHERE basket_id = ? AND EAN = ?");
    $stmt->execute([$quantity, session_id(), $basketEan]);
    echo "Basket quantity updated";
  } catch (Throwable $e) {
    echo "Error: update failed";
  }
}
