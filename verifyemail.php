<?php
require_once __DIR__ . '/functions/db.php';

$success = "";
$error = "";

$username = isset($_GET['u']) ? trim((string)$_GET['u']) : "";
$code = isset($_GET['code']) ? trim((string)$_GET['code']) : "";

if ($username !== "" && $code !== "") {
  try {
    $stmt = $pdo->prepare("SELECT id, otp_code FROM tblusers WHERE username = ? AND usertype = 'T' LIMIT 1");
    $stmt->execute([$username]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
      $error = "We couldn't find that account.";
    } elseif ((string)$row['otp_code'] !== (string)$code) {
      $error = "That verification code is invalid or has expired.";
    } else {
      $upd = $pdo->prepare("UPDATE tblusers SET otp_code = NULL WHERE id = ?");
      $upd->execute([$row['id']]);
      $success = "Email address verified. You can now log in.";
    }
  } catch (Throwable $e) {
    $error = "Verification failed. Please try again.";
  }
} elseif ($username !== "" || $code !== "") {
  $error = "Missing verification details.";
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>LTC Trade - Verify Email</title>
  <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
  <link href="css/simple-sidebar.css" rel="stylesheet">
</head>
<body>
  <div class="container" style="margin-top: 5%; max-width: 720px;">
    <div class="text-center" style="margin-bottom: 20px;">
      <img src="ltc_logo_600w.png" width="540" alt="LTC Tyres">
    </div>

    <div class="panel panel-primary">
      <div class="panel-heading">Email Verification</div>
      <div class="panel-body">
        <?php if (!empty($success)) { ?>
          <div class="alert alert-success">
            <?php echo htmlspecialchars($success); ?>
            <div style="margin-top:8px;"><a class="btn btn-primary" href="login.php">Go to Login</a></div>
          </div>
        <?php } else { ?>
          <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
          <div style="margin-top:8px;">If you need a new verification email, please register again or contact LTC.</div>
        <?php } ?>
      </div>
    </div>

  </div>

  <script src="vendor/jquery/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
</body>
</html>
