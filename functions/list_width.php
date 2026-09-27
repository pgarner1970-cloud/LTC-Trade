<?php
require_once 'db.php';
header('Content-type: application/json');

$stmt = $pdo->query("SELECT widthDesc FROM tblwidth ORDER BY widthDesc ASC");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
