<?php

session_start();
if (isset($_SESSION['username'])) {

} else {
	header('Location: login.php') ;
}
	

?>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>LTC Trade Site</title>

    <!-- Bootstrap core CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/toastr.min.css">
    <link rel="stylesheet" href="css/messages.css">
    <link rel="stylesheet" href="css/chevron.css">
    <!-- Custom styles for this template -->
    
    <!-- <link href="css/simple-sidebar.css" rel="stylesheet"> -->

</head>

<body>

    <div id="wrapper">

	<?php include("functions/mainfunctions.php"); ?>
	<?php include("functions/menu.php"); ?>

        <!-- Page Content -->
        <div id="page-content-wrapper" class="row">
			<?php
			    displayCounter();
			    displayOrderThanks();
			?>
		    <div id="orderStatus"></div>
		</div>
        <!-- /#page-content-wrapper -->

    </div>
    <!-- /#wrapper -->

    <!-- Bootstrap core JavaScript -->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/main_trade.js"></script>

</body>

</html>

<div id="orderModal" class="modal fade">
	<div class="modal-dialog">
		<form method="post" id="basket_form" enctype="multipart/form-data">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Update Order</h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
			</div>
			<div class="modal-body">
				<label>Status</label>
				<input type="text" name="quantity" id="quantity" class="form-control" required />
			</div>
			<div class="modal-footer">
				<input type="hidden" name="basket_id" id="basket_id" />
				<input type="hidden" name="operation" id="operation" value="Edit"/>
				<input type="submit" name="action" id="action" class="btn btn-success" value="Update" />
			</div>
		</div>
		</form>
	</div>
</div>
