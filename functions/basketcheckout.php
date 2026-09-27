<?php
require __DIR__ . '/../app/bootstrap.php';

use App\Db\Db;

header('Content-Type: text/plain; charset=utf-8');

if (!isset($_SESSION['username'])) {
  http_response_code(401);
  echo "Not logged in.";
  exit;
}

$username = (string)($_POST['username'] ?? $_SESSION['username']);
$email = trim((string)($_POST['email'] ?? ''));
$orderName = trim((string)($_POST['order_name'] ?? ''));
$orderRef = trim((string)($_POST['order_ref'] ?? ''));
$notes = trim((string)($_POST['notes'] ?? ''));

if ($orderName === '' || $orderRef === '') {
  http_response_code(400);
  echo "Missing order details.";
  exit;
}

$pdo = Db::pdo();

try {
  $pdo->beginTransaction();

  $basketId = session_id();

  // Pull basket lines
  $stmt = $pdo->prepare("SELECT Supplier, EAN, TyreDesc, UnitBuyPrice, UnitSellPrice, Quantity
                         FROM tblbasket WHERE basket_id = :bid");
  $stmt->execute([':bid' => $basketId]);
  $lines = $stmt->fetchAll();

  if (!$lines || count($lines) === 0) {
    $pdo->rollBack();
    http_response_code(400);
    echo "Basket is empty.";
    exit;
  }

  // Compute totals (and enforce no zero value orders)
  $totalQty = 0;
  $totalVal = 0.0;
  foreach ($lines as $ln) {
    $q = (int)($ln['Quantity'] ?? 0);
    $p = (float)($ln['UnitSellPrice'] ?? 0);
    $totalQty += $q;
    $totalVal += $q * $p;
  }
  if ($totalQty <= 0 || $totalVal <= 0) {
    $pdo->rollBack();
    http_response_code(400);
    echo "Invalid basket total.";
    exit;
  }

  // Create order header
  $insHdr = $pdo->prepare("
    INSERT INTO tblOrderHdr (order_user, order_processed, order_name, order_ref, order_notes, order_quantity, order_value)
    VALUES (:user, 'Open', :name, :ref, :notes, :qty, :val)
  ");
  $insHdr->execute([
    ':user' => $username,
    ':name' => $orderName,
    ':ref' => $orderRef,
    ':notes' => $notes,
    ':qty' => $totalQty,
    ':val' => $totalVal,
  ]);

  $orderId = (int)$pdo->lastInsertId();

  // Insert order lines
  $insLine = $pdo->prepare("
    INSERT INTO tblOrderLines (order_id, Supplier, EAN, TyreDesc, UnitBuyPrice, UnitSellPrice, Quantity)
    VALUES (:oid, :supplier, :ean, :desc, :buy, :sell, :qty)
  ");

  foreach ($lines as $ln) {
    $insLine->execute([
      ':oid' => $orderId,
      ':supplier' => (string)$ln['Supplier'],
      ':ean' => (string)$ln['EAN'],
      ':desc' => $ln['TyreDesc'],
      ':buy' => $ln['UnitBuyPrice'],
      ':sell' => $ln['UnitSellPrice'],
      ':qty' => (int)($ln['Quantity'] ?? 0),
    ]);
  }

  // Clear basket
  $del = $pdo->prepare("DELETE FROM tblbasket WHERE basket_id = :bid");
  $del->execute([':bid' => $basketId]);

  $pdo->commit();

  // Store last order id in session so index.php can show a confirmation message
  // (displayOrderThanks() reads and clears $_SESSION['lastid']).
  $_SESSION['lastid'] = $orderId;

  // Send email immediately (as per your answer)
  require_once __DIR__ . '/orderemail.php';
  if (function_exists('orderemail')) {
    // orderemail() sends internally; we call it to preserve behaviour
    @orderemail($orderId, $email);
  }

  echo "Order placed.";
} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  http_response_code(500);
  echo "Error: " . $e->getMessage();
}
