<?php
require __DIR__ . '/../app/bootstrap.php';

use App\Db\Db;

header('Content-Type: text/plain; charset=utf-8');

if (!isset($_POST['id'])) {
  http_response_code(400);
  echo "Missing id.";
  exit;
}

$id = (int)$_POST['id'];
$pdo = Db::pdo();

$stmt = $pdo->prepare("SELECT order_processed FROM tblOrderHdr WHERE order_id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$statusNow = $stmt->fetchColumn();

if (!$statusNow) {
  http_response_code(404);
  echo "Order not found.";
  exit;
}

$statusNew = $statusNow;
if ($statusNow === 'Open') $statusNew = 'In-Progress';
else if ($statusNow === 'In-Progress') $statusNew = 'Complete';

$upd = $pdo->prepare("UPDATE tblOrderHdr SET order_processed = :s WHERE order_id = :id");
$ok = $upd->execute([':s' => $statusNew, ':id' => $id]);

if ($ok) echo "Order updated.";
else { http_response_code(500); echo "Error updating order."; }
