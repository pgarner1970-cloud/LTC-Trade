<?php
require __DIR__ . '/../app/bootstrap.php';

use App\Db\Db;

header('Content-type: application/json; charset=utf-8');

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
  echo json_encode([]);
  exit;
}

$pdo = Db::pdo();
$stmt = $pdo->prepare("
  SELECT Supplier, EAN, TyreDesc, UnitSellPrice, Quantity,
         (COALESCE(Quantity,0) * COALESCE(UnitSellPrice,0)) AS line_total
  FROM tblOrderLines
  WHERE order_id = :id
  ORDER BY Supplier, EAN
");
$stmt->execute([':id' => $id]);
$data=[];
foreach ($stmt->fetchAll() as $row) {
  $data[] = [
    $row['Supplier'],
    $row['EAN'],
    $row['TyreDesc'],
    $row['UnitSellPrice'],
    $row['Quantity'],
    $row['line_total'],
  ];
}
echo json_encode($data);
