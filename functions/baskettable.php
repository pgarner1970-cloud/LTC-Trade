<?php
session_start();
require_once 'db.php';
header('Content-type: application/json');

$sql = "SELECT b.*, t.Manufacturer
        FROM tblbasket b
        INNER JOIN tbltyredata t
          ON b.EAN = t.EAN AND b.Supplier = t.Supplier
        WHERE b.basket_id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([session_id()]);

$data = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
  $results_arr = [];
  $results_arr[] = $row['EAN'];
  $results_arr[] = $row['Supplier'];
  $results_arr[] = $row['TyreDesc'];
  $results_arr[] = $row['UnitSellPrice'];
  $results_arr[] = $row['Quantity'];
  $results_arr[] = $row['UnitSellPrice'];
  $results_arr[] = $row['Manufacturer'];
  $data[] = $results_arr;
}

echo json_encode($data);
