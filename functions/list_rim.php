<?php
require_once 'db.php';
header('Content-type: application/json');

$stmt = $pdo->query("SELECT rimDesc FROM tblrim ORDER BY rimDesc ASC");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
