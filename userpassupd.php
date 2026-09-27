<?php
include("functions/db.php");
?>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>LTC Tyres - Reset your password</title>

    <!-- Bootstrap core CSS -->
	<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <!-- Custom styles for this template -->
    <link href="css/simple-sidebar.css" rel="stylesheet">

</head>

<body>
    <!-- Bootstrap core JavaScript -->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    <script src="js/userpassupd.js"></script>

	<div class="container" style="margin-top: 5%;">
        <div class="col-md-6 col-md-offset-3"><img src="ltc_logo_600w.png" width="540px"></div>
		<div class="col-md-6 col-md-offset-3">
			<div class="panel panel-primary">
				<div class="panel-heading">Reset your password</div>
				<div class="panel-body">

				<!-- Login Form -->
				<form role="form" method="post" action="userpassupd.php" name="user_form" id="user_form">
                <input type="hidden" id="operation" name="operation" value="upd">
				<!-- OPT Field -->
					<div class="row">
						<div class="form-group col-xs-12">
						<label for="otp"><span class="text-danger" style="margin-right:5px;">*</span>Enter your passcode:</label>
							<div class="input-group">
								<input class="form-control" id="otp" type="text" name="otp" placeholder="passcode from email" required/>
								<span class="input-group-btn">
									<label class="btn btn-primary"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></label>
								</span>
							</div>
						</div>
					</div>

				<!-- New password Field -->
					<div class="row">
						<div class="form-group col-xs-12">
						<label for="otp"><span class="text-danger" style="margin-right:5px;">*</span>New password:</label>
							<div class="input-group">
								<input class="form-control" id="newpass" type="password" name="newpass" placeholder="New password" required/>
								<span class="input-group-btn">
									<label class="btn btn-primary"><span class="glyphicon glyphicon-envelope" aria-hidden="true"></span></label>
								</span>
							</div>
						</div>
					</div>

				<!-- Confirm password Field -->
					<div class="row">
						<div class="form-group col-xs-12">
						<label for="otp"><span class="text-danger" style="margin-right:5px;">*</span>Confirm password:</label>
							<div class="input-group">
								<input class="form-control" id="confpass" type="password" name="confpass" placeholder="Confirm password" required/>
								<span class="input-group-btn">
									<label class="btn btn-primary"><span class="glyphicon glyphicon-envelope" aria-hidden="true"></span></label>
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

</body>

</html>
