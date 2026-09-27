<?php
require __DIR__ . '/../app/bootstrap.php';

use App\Db\Db;

header('Content-type: application/json; charset=utf-8');

if (!isset($_SESSION['username'])) {
  echo json_encode([]);
  exit;
}

$pdo = Db::pdo();
$stmt = $pdo->prepare("SELECT * FROM tblOrderHdr WHERE order_user = :u ORDER BY order_id DESC LIMIT 20");
$stmt->execute([':u' => $_SESSION['username']]);
echo json_encode($stmt->fetchAll());
