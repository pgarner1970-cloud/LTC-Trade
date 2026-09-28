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

    <title>LTC Tyres - Tyre search</title>

    <!-- Bootstrap core CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/v/bs5/jq-3.7.0/dt-2.2.2/datatables.min.css" rel="stylesheet" integrity="sha384-WMi+Ec+QE8hxW/3qKvuefShIddYjwMalSgy0MR4FZnl285C4HGYfISceaagw0Am3" crossorigin="anonymous">
    <!-- Custom styles for this template -->
    <link rel="stylesheet" href="css/toastr.min.css">
    <!-- Bootstrap core JavaScript -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/v/bs5/jq-3.7.0/dt-2.2.2/datatables.min.js" integrity="sha384-WcZXtPeSp12Ybwm08R/IL8F3bMhrj0WW6jKsqKXTqJSwCSkISe4unYVY8Vzc1RZc" crossorigin="anonymous"></script>
    <script src="js/toastr.min.js"></script>
    <script src="js/stock_multi.js"></script>
    <style>
      .multi-stock-table thead th { white-space: nowrap; background: #f1f3f5; }
      .multi-stock-table td, .multi-stock-table th { padding: .55rem .5rem; }
      .multi-stock-table .stock-group-even > td { background-color: #f2f2f2; }
      .multi-stock-table .stock-group-odd > td { background-color: #fff; }
      .multi-stock-table .stock-offer-extra > td { border-top: 1px dashed #d5d9dd; }
      .multi-stock-table .trade-price { font-weight: 600; white-space: nowrap; }
      .multi-stock-table .delivery-cell { white-space: nowrap; }
      .multi-stock-table .tyre-description { min-width: 310px; }
      .multi-stock-table .badge { font-size: .68rem; vertical-align: middle; }
    </style>
</head>

<body>

    <div id="wrapper">

        <?php include("functions/menu.php"); ?>


        <!-- Page Content -->
        <div id="page-content-wrapper">

            <div class="container-fluid">
                <h3>Tyre Search <small class="text-muted fs-6">Multi-stock preview</small></h3>
				<form name="search" id="search" class="row gy-2 gx-3 align-items-center">

                    <!-- NEW: Quick tyre size input -->
                    <div class="col-auto">
                        <input
                            type="text"
                            name="tyresize"
                            id="tyresize"
                            class="form-control"
                            placeholder="e.g. 2255519"
                            maxlength="7"
                            autocomplete="off"
                            inputmode="numeric"
                            style="width: 140px;"
                        />
                    </div>

					<div class="col-auto"><select name="width" id="width" class="form-control"></select></div>
					<div class="col-auto"><select name="profile" id="profile" class="form-control"></select></div>
					<div class="col-auto"><select name="rim" id="rim" class="form-control"></select></div>
					<div class="col-auto"><div class="input-group"><div class="input-group-text">Speed</div><select name="speed" id="speed" class="form-control"></select></div></div>
					<div class="col-auto">
  <div class="input-group">
    <div class="input-group-text">Brand</div>
    <select name="manufacturer" id="manufacturer" class="form-control"></select>
    <button type="button" class="btn btn-outline-secondary" id="clear-brand" title="Clear brand filter">✕</button>
  </div>
</div>
					<div class="col-auto"><div class="input-group"><div class="input-group-text">Fuel</div><select name="fuel" id="fuel" class="form-control">
						<option value="ABCDEFG">ALL</option>
						<option value="A">A only</option>
						<option value="AB">A > B</option>
						<option value="ABC">A > C</option>
						<option value="ABCD">A > D</option>
						<option value="ABCDE">A > E</option>
						<option value="ABCDEF">A > F</option>
					</select></div></div>
					<div class="col-auto"><div class="input-group"><div class="input-group-text">WetGrip</div><select name="wetgrip" id="wetgrip" class="form-control">
						<option value="ABCDEFG">ALL</option>
						<option value="A">A only</option>
						<option value="AB">A > B</option>
						<option value="ABC">A > C</option>
						<option value="ABCD">A > D</option>
						<option value="ABCDE">A > E</option>
						<option value="ABCDEF">A > F</option>
						</select></div></div>
					<div class="col-auto"><button type="submit" class="btn btn-primary">Submit</button></div>
				</form>
	  			<div id="resultsDiv"></div>
            </div>
		</div>
        <!-- /#page-content-wrapper -->

    </div>
    <!-- /#wrapper -->

    <!-- Menu Toggle Script -->
    <script>
    $("#menu-toggle").click(function(e) {
        e.preventDefault();
        $("#wrapper").toggleClass("toggled");
    });
    </script>

</body>

</html>
