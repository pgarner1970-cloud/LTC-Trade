<?php
require_once __DIR__ . '/functions/db.php';
require_once __DIR__ . '/functions/email_template.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$success = false;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $company   = trim((string)($_POST['company'] ?? ''));
  $email     = trim((string)($_POST['email'] ?? ''));
  $confemail = trim((string)($_POST['confemail'] ?? ''));
  $telephone = trim((string)($_POST['telephone'] ?? ''));
  $username  = trim((string)($_POST['username'] ?? ''));
  $pass      = (string)($_POST['userpass'] ?? '');
  $confpass  = (string)($_POST['confpass'] ?? '');

  if ($company === '' || $email === '' || $username === '' || $pass === '') {
    $error = "Please complete all required fields.";
  } elseif (strcasecmp($email, $confemail) !== 0) {
    $error = "Email addresses do not match.";
  } elseif ($pass !== $confpass) {
    $error = "Passwords do not match.";
  } else {
    try {
      // Server-side uniqueness checks (don’t rely solely on JS)
      $stmt = $pdo->prepare("SELECT COUNT(id) FROM tblusers WHERE username = ?");
      $stmt->execute([$username]);
      if ((int)$stmt->fetchColumn() > 0) {
        $error = "That username is already taken.";
      } else {
        $stmt = $pdo->prepare("SELECT COUNT(id) FROM tblusers WHERE email = ?");
        $stmt->execute([$email]);
        if ((int)$stmt->fetchColumn() > 0) {
          $error = "That email address is already registered.";
        } else {
          // Create user
          $otp_code = (string)random_int(100000, 999999);
          $hash = password_hash($pass, PASSWORD_DEFAULT);

          $ins = $pdo->prepare("
            INSERT INTO tblusers (username, email, hash, usertype, description, telephone, otp_code)
            VALUES (?, ?, ?, 'T', ?, ?, ?)
          ");
          $ins->execute([$username, $email, $hash, $company, $telephone, $otp_code]);

          // Send verification email
          $subject = "Verify your email address";
          $verifyUrl = email_base_url() . "/verifyemail.php?u=" . rawurlencode($username) . "&code=" . rawurlencode($otp_code);

          $body = "";
          $body .= "<p>Hi " . htmlspecialchars($username, ENT_QUOTES, 'UTF-8') . ",</p>";
          $body .= "<p>Thanks for registering for Trade access.</p>";
          $body .= "<p><strong>Your verification code:</strong> " . htmlspecialchars($otp_code, ENT_QUOTES, 'UTF-8') . "</p>";
          $body .= "<p>You can verify your email by clicking this link:</p>";
          $body .= "<p><a href='" . htmlspecialchars($verifyUrl, ENT_QUOTES, 'UTF-8') . "'>Verify my email</a></p>";
          $body .= "<p>If you didn’t request this, you can ignore this email.</p>";

          // Wrap using standard template functions
          $headers = "MIME-Version: 1.0\r\n";
          $headers .= "Content-type:text/html;charset=UTF-8\r\n";
          $headers .= "From: LTC Tyres <no-reply@tyres4sale.com>\r\n";

          $html = email_header_html($subject) . $body . email_footer_html();

          @mail($email, $subject, $html, $headers);

          $success = true;
        }
      }
    } catch (Throwable $e) {
      $error = "Registration failed. Please try again.";
    }
  }
}
?>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>LTC Tyres - Register for Trade access</title>

    <!-- Bootstrap core CSS -->
	<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <!-- Custom styles for this template -->
    <link href="css/blurt.min.css" rel="stylesheet">

    <style>
        /* Better inline validation spacing (Bootstrap 3) */
        .form-group { margin-bottom: 18px; }
        .help-block.validation-msg { margin: 6px 0 0; }
    </style>

</head>

<body>
    <!-- Bootstrap core JavaScript -->
    <script src="js/blurt.min.js"></script>
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    <script src="js/registeracc.js"></script>

	<div class="container" style="margin-top: 5%;">
        <div class="col-md-6 col-md-offset-3"><img src="ltc_logo_600w.png" width="540px"></div>
		<div class="col-md-6 col-md-offset-3">
			<div class="panel panel-primary">
				<div class="panel-heading">Register for Trade</div>
				<div class="panel-body">

				<?php if (!empty($success)) { ?>
				  <div class="alert alert-success">
				    <?php echo htmlspecialchars($success); ?>
				    <div style="margin-top:6px;">Once verified, you can <a href="login.php">log in here</a>.</div>
				  </div>
				<?php } ?>
				<?php if (!empty($error)) { ?>
				  <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
				<?php } ?>

				<!-- Login Form -->
				<form role="form" method="post" action="registeracc.php" name="user_form" id="user_form">
                <input type="hidden" name="operation" id="operation" value="Add">
				<!-- Company Name Field -->
					<div class="row">
						<div class="form-group col-xs-12">
						<label for="company"><span class="text-danger" style="margin-right:5px;">*</span>Company name:</label>
							<div class="input-group">
								<input class="form-control" id="company" type="text" name="company" placeholder="Company name" required/>
								<span class="input-group-btn">
									<label class="btn btn-primary"><span class="glyphicon glyphicon-briefcase" aria-hidden="true"></label>
								</span>
								</span>
							</div>
						</div>
					</div>

				<!-- Telephone Field -->
					<div class="row">
						<div class="form-group col-xs-12">
						<label for="telephone"><span class="text-danger" style="margin-right:5px;">*</span>Telephone:</label>
							<div class="input-group">
								<input class="form-control" id="telephone" type="text" name="telephone" placeholder="Telephone number" required/>
								<span class="input-group-btn">
									<label class="btn btn-primary"><span class="glyphicon glyphicon-phone-alt" aria-hidden="true"></label>
								</span>
								</span>
							</div>
						</div>
					</div>

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

				<!-- Password Field -->
					<div class="row">
						<div class="form-group col-xs-12">
						<label for="userpass"><span class="text-danger" style="margin-right:5px;">*</span>Password:</label>
							<div class="input-group">
								<input class="form-control" id="userpass" type="password" name="userpass" placeholder="Password" required/>
								<span class="input-group-btn">
									<label class="btn btn-primary"><span class="glyphicon glyphicon-asterisk" aria-hidden="true"></label>
								</span>
								</span>
							</div>
						</div>
					</div>

				<!-- Confirm Password Field -->
					<div class="row">
						<div class="form-group col-xs-12">
						<label for="userpass"><span class="text-danger" style="margin-right:5px;">*</span>Confirm Password:</label>
							<div class="input-group">
								<input class="form-control" id="confpass" type="password" name="confpass" placeholder="Confirm Password" required/>
								<span class="input-group-btn">
									<label class="btn btn-primary"><span class="glyphicon glyphicon-asterisk" aria-hidden="true"></label>
								</span>
								</span>
							</div>
						</div>
					</div>

				<!-- Email address Field -->
					<div class="row">
						<div class="form-group col-xs-12">
						<label for="email"><span class="text-danger" style="margin-right:5px;">*</span>Email:</label>
							<div class="input-group">
								<input class="form-control" id="email" type="text" name="email" placeholder="Email address" required/>
								<span class="input-group-btn">
									<label class="btn btn-primary"><span class="glyphicon glyphicon-envelope" aria-hidden="true"></label>
								</span>
								</span>
							</div>
						</div>
					</div>

				<!-- Email confirm address Field -->
					<div class="row">
						<div class="form-group col-xs-12">
						<label for="confemail"><span class="text-danger" style="margin-right:5px;">*</span>Confirm Email:</label>
							<div class="input-group">
								<input class="form-control" id="confemail" type="text" name="confemail" placeholder="Confirm Email address" required/>
								<span class="input-group-btn">
									<label class="btn btn-primary"><span class="glyphicon glyphicon-envelope" aria-hidden="true"></label>
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
					<!-- server-side success/error messages are shown above the form -->
				</form>
				<!-- End of Login Form -->

			</div>
		</div>
		Back to login - <a href="login.php">Click here</a>
	</div>

    <!-- (Removed duplicate JS includes) -->


</body>

</html>
