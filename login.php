<?php
require_once __DIR__ . '/functions/db.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if (isset($_POST['username'], $_POST['password'])) {
  $username = trim((string)$_POST['username']);
  $password = (string)$_POST['password'];

  try {
    $stmt = $pdo->prepare("SELECT usertype, hash, otp_code FROM tblusers WHERE username = ? AND usertype = 'T' LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || empty($user['hash']) || !password_verify($password, $user['hash'])) {
      $error = "Invalid username or password entered.";
    } elseif (!empty($user['otp_code'])) {
      $error = "Please verify your email address before logging in. Check your inbox for the verification link/code.";
    } else {
      // Login success
      $_SESSION['username'] = $username;
      $_SESSION['usertype'] = $user['usertype'];
      header("Location: index.php");
      exit;
    }
  } catch (Throwable $e) {
    $error = "Login failed. Please try again.";
  }
}
?>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>LTC Trade - Login</title>

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
				<div class="panel-heading">Trade Login</div>
				<div class="panel-body">

				<!-- Login Form -->
				<form role="form" method="post" action="login.php">

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

					<!-- Content Field -->
					<div class="row">
						<div class="form-group col-xs-12">
							<label for="password"><span class="text-danger" style="margin-right:5px;">*</span>Password:</label>
							<div class="input-group">
								<input class="form-control" id="password" type="password" name="password" placeholder="Password" required/>
								<span class="input-group-btn">
									<label class="btn btn-primary"><span class="glyphicon glyphicon-lock" aria-hidden="true"></label>
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
		Forgotten password? <a href="uforgotpass.php">Click here</a>
		<div>Need to register for Trade access? <a href="registeracc.php">Click here</a></div>
	</div>

    <!-- Bootstrap core JavaScript -->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>


</body>

</html>
