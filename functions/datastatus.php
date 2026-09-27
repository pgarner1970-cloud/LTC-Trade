<?php
require_once 'db.php';
header('Content-type: application/json');

$stmt = $pdo->query("SELECT Supplier, LastUpdate FROM tbltyredataupdates ORDER BY LastUpdate ASC");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
