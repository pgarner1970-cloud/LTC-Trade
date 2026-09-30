<?php
session_start();
require_once 'db.php';
header('Content-Type: text/plain; charset=utf-8');
$ean=(string)($_POST['basket_id']??'');
$supplier=(string)($_POST['supplier']??'');
$qty=filter_var($_POST['quantity']??null,FILTER_VALIDATE_INT);
if(($_POST['operation']??'')!=='Edit'||$ean===''||$supplier===''||$qty===false||$qty<1||$qty>99){
 http_response_code(400);exit('Error: invalid quantity or selection.');
}
try{
 $s=$pdo->prepare('SELECT Stock FROM tbltyredata WHERE EAN=? AND Supplier=? LIMIT 1');
 $s->execute([$ean,$supplier]);$stock=$s->fetchColumn();
 if($stock===false||(int)$stock<$qty){http_response_code(409);exit('Error: insufficient stock.');}
 $s=$pdo->prepare('SELECT 1 FROM tblbasket WHERE basket_id=? AND EAN=? AND Supplier=?');
 $s->execute([session_id(),$ean,$supplier]);
 if(!$s->fetchColumn()){http_response_code(404);exit('Error: basket line missing.');}
 $s=$pdo->prepare('UPDATE tblbasket SET Quantity=? WHERE basket_id=? AND EAN=? AND Supplier=?');
 $s->execute([$qty,session_id(),$ean,$supplier]);echo 'Quantity updated';
}catch(Throwable $e){http_response_code(500);echo 'Error: update failed.';}
