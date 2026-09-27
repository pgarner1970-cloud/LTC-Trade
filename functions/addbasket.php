<?php
session_start();
require_once 'db.php';

$user = session_id();

if (isset($_POST["id"])) {
  $a = explode("~", (string)$_POST["id"]);
  $ean = $a[0] ?? '';
  $supplier = $a[1] ?? '';

  try {
    $stmt = $pdo->prepare('SELECT TyreDesc, UnitBuyPrice, (UnitBuyPrice + tblrim.rimMarkupTrade) AS UnitTrade FROM (tbltyredata A Inner Join tblrim ON Diameter = tblrim.rimDesc) WHERE EAN=? AND Supplier=?');
    $stmt->execute([$ean, $supplier]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
      echo "Error: tyre not found.";
      exit;
    }

    $desc = $row["TyreDesc"] ?? '';
    $buyprice = $row["UnitBuyPrice"] ?? 0;
    $tradeprice = $row["UnitTrade"] ?? ($row["UnitBuyPrice"] ?? 0);

    $ins = $pdo->prepare('INSERT INTO tblbasket (basket_id, Supplier, EAN, TyreDesc, UnitBuyPrice, UnitSellPrice, Quantity) VALUES (?,?,?,?,?,?,1) ON DUPLICATE KEY UPDATE Quantity=Quantity+1');
    $ins->execute([$user, $supplier, $ean, $desc, $buyprice, $tradeprice]);

    echo "Tyre added to basket.";
  } catch (Throwable $e) {
    echo "Error: could not add tyre.";
  }
}
