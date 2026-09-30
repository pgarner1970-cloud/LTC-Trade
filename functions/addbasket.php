<?php
session_start();
require_once 'db.php';

$user = session_id();

if (isset($_POST["id"])) {
  $a = explode("~", (string)$_POST["id"]);
  $ean = $a[0] ?? '';
  $supplier = $a[1] ?? '';
  $qty = filter_var($_POST['quantity'] ?? 1, FILTER_VALIDATE_INT);
  if ($qty === false || $qty < 1 || $qty > 99 || !$ean || !$supplier) {
    echo "Error: invalid tyre selection or quantity.";
    exit;
  }

  try {
    $stmt = $pdo->prepare('SELECT TyreDesc, Stock, UnitBuyPrice, (UnitBuyPrice + tblrim.rimMarkupTrade) AS UnitTrade FROM (tbltyredata A Inner Join tblrim ON Diameter = tblrim.rimDesc) WHERE EAN=? AND Supplier=?');
    $stmt->execute([$ean, $supplier]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
      echo "Error: tyre not found.";
      exit;
    }

    if ((int)($row["Stock"] ?? 0) < $qty) {
      echo "Error: requested quantity exceeds available stock.";
      exit;
    }
    $existing = $pdo->prepare('SELECT Quantity FROM tblbasket WHERE basket_id=? AND Supplier=? AND EAN=?');
    $existing->execute([$user, $supplier, $ean]);
    $existingQty = (int)($existing->fetchColumn() ?: 0);
    if ($existingQty + $qty > (int)$row["Stock"]) {
      echo "Error: basket quantity exceeds available stock.";
      exit;
    }
    $desc = $row["TyreDesc"] ?? '';
    $buyprice = $row["UnitBuyPrice"] ?? 0;
    $tradeprice = $row["UnitTrade"] ?? ($row["UnitBuyPrice"] ?? 0);

    $ins = $pdo->prepare('INSERT INTO tblbasket (basket_id, Supplier, EAN, TyreDesc, UnitBuyPrice, UnitSellPrice, Quantity) VALUES (?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE Quantity=Quantity+VALUES(Quantity)');
    $ins->execute([$user, $supplier, $ean, $desc, $buyprice, $tradeprice, $qty]);

    echo "Tyre added to basket.";
  } catch (Throwable $e) {
    echo "Error: could not add tyre.";
  }
}
