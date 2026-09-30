<?php
session_start();
require_once 'db.php';
header('Content-Type: text/plain; charset=utf-8');
$ean=(string)($_POST['id']??'');$supplier=(string)($_POST['supplier']??'');
if($ean===''||$supplier===''){http_response_code(400);exit('Error: missing selection.');}
try{
 $s=$pdo->prepare('DELETE FROM tblbasket WHERE basket_id=? AND EAN=? AND Supplier=?');
 $s->execute([session_id(),$ean,$supplier]);
 echo $s->rowCount()?'Tyre removed':'Error: line not found.';
}catch(Throwable $e){http_response_code(500);echo 'Error: remove failed.';}
