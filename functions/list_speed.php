<?php
require_once 'db.php';
header('Content-type: application/json');

$stmt = $pdo->query("SELECT speedDesc FROM tblspeed ORDER BY speedDesc ASC");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
