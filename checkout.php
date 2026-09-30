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

    <title>LTC Tyres - Checkout</title>

    <!-- Bootstrap core CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom styles for this template -->
    <link rel="stylesheet" href="css/toastr.min.css">
    <!-- Bootstrap core JavaScript -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/toastr.min.js"></script>
    <script src="js/checkout.js?v=20260930-responsive1"></script>
<style>
.checkout-summary,.checkout-details{border:1px solid #dee5ed;border-radius:.7rem;padding:1rem;background:#fff;height:100%}
.checkout-item{padding:.75rem 0;border-bottom:1px solid #e6eaf0}
.checkout-item:last-child{border-bottom:0}
.checkout-price{color:#1558b0;font-weight:700}
.checkout-totals{border-top:2px solid #e5eaf0;padding-top:.75rem;margin-top:.5rem}
.checkout-totals .total-pay{font-size:1.2rem;color:#1558b0}
.checkout-details label{font-weight:600;font-size:.9rem}
.checkout-details .form-control{min-height:42px}
.checkout-details .form-check-input{width:1.15rem;height:1.15rem}
@media(max-width:767.98px){.checkout-summary,.checkout-details{padding:.85rem}.checkout-submit{width:100%}}
</style></head>

<body>

    <div id="wrapper">

	<?php include("functions/menu.php"); ?>


        <!-- Page Content -->
        <div id="page-content-wrapper">
            <div class="container-fluid">
                <h3>Checkout</h3>

	  			<div id="resultsDiv"></div>
            </div>
		</div>
        <!-- /#page-content-wrapper -->

    </div>
    <!-- /#wrapper -->

</body>

</html>