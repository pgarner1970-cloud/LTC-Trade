<?php
require __DIR__ . '/../app/bootstrap.php';

use App\Db\Db;

header('Content-type: application/json; charset=utf-8');

$pdo = Db::pdo();

$sql = "
SELECT ol.Supplier,
       ol.EAN,
       ol.TyreDesc,
       ol.UnitBuyPrice,
       SUM(COALESCE(ol.Quantity,0)) AS Qty
FROM tblOrderLines ol
JOIN tblOrderHdr oh ON oh.order_id = ol.order_id
WHERE oh.order_processed = 'Open'
GROUP BY ol.Supplier, ol.EAN, ol.TyreDesc, ol.UnitBuyPrice
ORDER BY ol.Supplier, ol.EAN
";

$stmt = $pdo->query($sql);
echo json_encode($stmt->fetchAll());
