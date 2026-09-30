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

    <title>LTC Tyres - Basket</title>

    <!-- Bootstrap core CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom styles for this template -->
    <link rel="stylesheet" href="css/toastr.min.css">
    <!-- Bootstrap core JavaScript -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/toastr.min.js"></script>
    <script src="js/basket.js?v=20260930-mobile2"></script>
<style>
.basket-card{border:1px solid #dce2e8;border-radius:.7rem;padding:1rem;margin-bottom:.75rem}
.basket-price{color:#1558b0;font-weight:700;font-size:1.3rem}
.basket-qty{display:inline-flex;align-items:center;border:1px solid #cbd5e1;border-radius:.4rem;overflow:hidden}
.basket-qty button{border:0;background:white;padding:.35rem .75rem;min-height:38px}
.basket-qty span{min-width:2.5rem;text-align:center;font-weight:600}
.basket-summary{max-width:460px;margin-left:auto}
@media(max-width:767.98px){.basket-desktop{display:none!important}}
@media(min-width:768px){.basket-mobile{display:none!important}}
</style></head>

<body>

    <div id="wrapper">

	<?php include("functions/menu.php"); ?>


        <!-- Page Content -->
        <div id="page-content-wrapper">
            <div class="container-fluid">
                <h3>Your Basket</h3>

	  			<div id="resultsDiv"></div>
            </div>
		</div>
        <!-- /#page-content-wrapper -->

    </div>
    <!-- /#wrapper -->

</body>

</html>

<div id="basketModal" class="modal fade">
	<div class="modal-dialog">
		<form method="post" id="basket_form" enctype="multipart/form-data">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Edit Basket</h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
			</div>
			<div class="modal-body">
				<label>Quantity</label>
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