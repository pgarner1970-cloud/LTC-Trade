<?php
require_once 'db.php';
header('Content-type: application/json');

$stmt = $pdo->query("SELECT profileDesc FROM tblprofile ORDER BY profileDesc ASC");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
