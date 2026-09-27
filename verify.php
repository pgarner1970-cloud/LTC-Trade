<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require __DIR__ . '/app/bootstrap.php';
use App\Db\Db;

$pdo = Db::pdo();

$username = $_GET['u'] ?? '';
$plain = $_GET['p'] ?? '';

$stmt = $pdo->prepare("SELECT username, usertype, hash FROM tblusers WHERE username = :u LIMIT 1");
$stmt->execute([':u' => $username]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
  echo "No such user\n";
  exit;
}

echo "DB username: {$row['username']}\n";
echo "usertype: {$row['usertype']}\n";
echo "hash len: " . strlen((string)$row['hash']) . "\n";
$info = password_get_info((string)$row['hash']);
echo "algo: " . ($info['algoName'] ?? 'unknown') . "\n";
echo "verify: " . (password_verify($plain, (string)$row['hash']) ? 'YES' : 'NO') . "\n";
