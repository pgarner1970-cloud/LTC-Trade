<?php
require_once __DIR__ . '/functions/db.php';
require_once __DIR__ . '/functions/userpassfunctions.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if (isset($_POST['username'])) {
  $username = trim((string)$_POST['username']);

  try {
    $stmt = $pdo->prepare("SELECT email, hash FROM tblusers WHERE username = ? AND usertype = 'T' LIMIT 1");
    $stmt->execute([$username]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
      $error = "Invalid username entered.";
    } else {
      $hash = (string)$row['hash'];
      $email = (string)$row['email'];

      // Generate 6-digit passcode
      $token = (string)random_int(100000, 999999);

      // Store passcode in otp_code
      $upd = $pdo->prepare("UPDATE tblusers SET otp_code = ? WHERE username = ? AND usertype = 'T'");
      $upd->execute([$token, $username]);

      if ($upd->rowCount() > 0) {
        useremail($username, $token, $hash, $email);
        $error = "A password reset link has been sent to your registered email address";
      } else {
        $error = "An error occurred. Please try again.";
      }
    }
  } catch (Throwable $e) {
    $error = "An error occurred. Please try again.";
  }
}
?>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>LTC Tyres - Forgotten password</title>

    <!-- Bootstrap core CSS -->
	<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <!-- Custom styles for this template -->
    <link href="css/simple-sidebar.css" rel="stylesheet">

</head>

<body>
	<div class="container" style="margin-top: 5%;">
        <div class="col-md-6 col-md-offset-3"><img src="ltc_logo_600w.png" width="540px"></div>
		<div class="col-md-6 col-md-offset-3">
			<div class="panel panel-primary">
				<div class="panel-heading">Forgotten password?</div>
				<div class="panel-body">

				<!-- Login Form -->
				<form role="form" method="post" action="uforgotpass.php">

				<!-- Username Field -->
					<div class="row">
						<div class="form-group col-xs-12">
						<label for="username"><span class="text-danger" style="margin-right:5px;">*</span>Username:</label>
							<div class="input-group">
								<input class="form-control" id="username" type="text" name="username" placeholder="Username" required/>
								<span class="input-group-btn">
									<label class="btn btn-primary"><span class="glyphicon glyphicon-user" aria-hidden="true"></label>
								</span>
								</span>
							</div>
						</div>
					</div>

					<!-- Login Button -->
					<div class="row">
						<div class="form-group col-xs-4">
							<button class="btn btn-primary" type="submit">Submit</button>
						</div>
					</div>
					<?php
						global $error;
						if(!empty($error)) {
							echo "<div class='alert alert-danger'>".$error."</div>";
						}
					?>
				</form>
				<!-- End of Login Form -->

			</div>
		</div>
		Back to login - <a href="login.php">Click here</a>
	</div>

    <!-- Bootstrap core JavaScript -->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>


</body>

</html>
