<?php
require __DIR__ . '/../app/bootstrap.php';

use App\Db\Db;

header('Content-type: application/json; charset=utf-8');

if (!isset($_SESSION['username'])) {
  echo json_encode(['error' => 'Not logged in']);
  exit;
}

$pdo = Db::pdo();
$stmt = $pdo->prepare("SELECT username, email FROM tblusers WHERE username = :u LIMIT 1");
$stmt->execute([':u' => $_SESSION['username']]);
$row = $stmt->fetch();

if (!$row) {
  echo json_encode(['error' => 'User not found']);
  exit;
}

echo json_encode([
  'username' => $row['username'],
  'email' => $row['email'] ?? ''
]);
