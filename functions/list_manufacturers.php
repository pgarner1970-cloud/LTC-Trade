<?php
require_once 'db.php';
header('Content-type: application/json');

// Return distinct manufacturers from tyre data,
// plus banding from tblmanufacturers (default Budget if missing).
$sql = "
  SELECT
    td.Manufacturer AS Manufacturer,
    COALESCE(tm.banding, 'Budget') AS banding
  FROM (
    SELECT DISTINCT Manufacturer
    FROM tbltyredata
    WHERE Manufacturer IS NOT NULL AND Manufacturer <> ''
  ) td
  LEFT JOIN tblmanufacturers tm
    ON UPPER(tm.Manufacturer) = UPPER(td.Manufacturer)
  ORDER BY td.Manufacturer ASC
";

$stmt = $pdo->query($sql);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
