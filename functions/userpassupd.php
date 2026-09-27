<?php
require_once 'db.php';
header('Content-Type: application/json; charset=UTF-8');

if (isset($_POST["operation"]) && $_POST["operation"] === "upd") {
  $otp = (string)($_POST["otp"] ?? "");
  $newpass = (string)($_POST["newpass"] ?? "");
  $confpass = (string)($_POST["confpass"] ?? "");

  if ($newpass !== $confpass) {
    echo json_encode(["success" => false, "message" => "Passwords do not match"]);
    exit;
  }

  try {
    $newhash = password_hash($newpass, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE tblusers SET hash = ?, otp_code = NULL WHERE otp_code = ?");
    $stmt->execute([$newhash, $otp]);

    if ($stmt->rowCount() > 0) {
      echo json_encode(["success" => true]);
    } else {
      echo json_encode(["success" => false, "message" => "Invalid passcode"]);
    }
  } catch (Throwable $e) {
    echo json_encode(["success" => false, "message" => "Password update failed"]);
  }
}
