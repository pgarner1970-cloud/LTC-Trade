<?php
$ltcShowMultipleOffers = false;
$ltcMultipleOffersRaw = getenv('TRADE_SHOW_MULTIPLE_OFFERS');
if ($ltcMultipleOffersRaw === false) {
    $ltcEnvFile = __DIR__ . '/.env';
    if (is_readable($ltcEnvFile)) {
        $ltcEnv = parse_ini_file($ltcEnvFile, false, INI_SCANNER_RAW);
        if (is_array($ltcEnv) && array_key_exists('TRADE_SHOW_MULTIPLE_OFFERS', $ltcEnv)) {
            $ltcMultipleOffersRaw = $ltcEnv['TRADE_SHOW_MULTIPLE_OFFERS'];
        }
    }
}
if ($ltcMultipleOffersRaw !== false) {
    $ltcShowMultipleOffers = in_array(strtolower(trim((string)$ltcMultipleOffersRaw)), ['1','true','yes','on'], true);
}
?>
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
    <script>window.LTC_TRADE_SHOW_MULTIPLE_OFFERS = <?php echo $ltcShowMultipleOffers ? 'true' : 'false'; ?>;</script>
    <script src="js/stock_multi.js?v=20261004-1"></script>
    <style>
      .offer-actions { display:inline-flex; gap:.35rem; align-items:center; }
      .offer-qty { width:3.3rem; min-width:3.3rem; padding:.2rem; text-align:center; }
      .stock-mobile-card .stock-card-offer { flex-wrap:wrap; }
      .extra-filter-row { background:#f7f9fc; padding:.65rem; border-radius:.5rem; margin-top:.5rem; }
      .offer-actions { display:inline-flex; gap:.4rem; align-items:center; white-space:nowrap; }
      .qty-stepper { display:inline-flex; align-items:stretch; height:30px; border:1px solid #ced4da; border-radius:.4rem; overflow:hidden; background:#fff; }
      .qty-stepper button { border:0; background:#f8f9fa; color:#173a65; min-width:29px; padding:0 .35rem; font-weight:700; font-size:1rem; cursor:pointer; }
      .qty-stepper button:first-child { border-right:1px solid #ced4da; }
      .qty-stepper button:last-child { border-left:1px solid #ced4da; }
      .qty-stepper button:focus-visible { outline:2px solid #0d6efd; outline-offset:-2px; }
      .qty-stepper .offer-qty { width:36px; min-width:36px; border:0; border-radius:0; text-align:center; padding:0 2px; font-size:.84rem; appearance:textfield; -moz-appearance:textfield; }
      .qty-stepper .offer-qty::-webkit-inner-spin-button, .qty-stepper .offer-qty::-webkit-outer-spin-button { -webkit-appearance:none; margin:0; }
      .stock-mobile-card .qty-stepper { height:36px; }
      .stock-mobile-card .qty-stepper button { min-width:34px; }
      .multi-stock-table thead th { white-space: nowrap; background: #f1f3f5; }
      .multi-stock-table.table > :not(caption) > * > * { padding: .18rem .42rem; font-size: .88rem; line-height: 1.2; }
      .multi-stock-table .tyre-manufacturer { font-weight: 700; }
      .multi-stock-table .btn.buy { padding: .12rem .38rem; font-size: .76rem; line-height: 1.15; min-height: 24px; }
      .multi-stock-table { margin-bottom: 0; }
      .stock-mobile-list { display: none; }
      .stock-mobile-card { border: 1px solid #dce2e8; border-radius: .7rem; margin-bottom: .7rem; padding: .85rem; }
      .stock-mobile-card:nth-child(odd) { background: #f5f7f9; }
      .stock-mobile-card:nth-child(even) { background: #fff; }
      .stock-mobile-card .stock-card-manufacturer { font-size: .9rem; font-weight: 700; }
      .stock-mobile-card .stock-card-description { font-size: .92rem; margin: .25rem 0 .45rem; }
      .stock-mobile-card .stock-card-labels { font-size: .78rem; color: #56616b; display: flex; flex-wrap: wrap; gap: .3rem .8rem; }
      .stock-mobile-card .stock-card-offer { display: flex; justify-content: space-between; align-items: center; gap: .6rem; padding: .6rem 0; border-top: 1px solid #e0e5ea; }
      .stock-mobile-card .stock-card-offer:first-child { margin-top: .6rem; }
      .stock-mobile-card .stock-card-price { font-weight: 700; }
      .stock-mobile-card .stock-card-delivery { font-size: .84rem; }
      @media (max-width: 899.98px) {
        .multi-stock-desktop { display: none; }
        .stock-mobile-list { display: block; margin-top: .8rem; }
        #search > .col-auto { max-width: 100%; }
        #search .input-group { flex-wrap: nowrap; }
        #search .form-control { min-width: 0; }
      }
      @media (max-width: 575.98px) {
        #search { --bs-gutter-x: .5rem; }
        #search > .col-auto { flex: 1 1 auto; }
        #search > .col-auto:first-child { flex-basis: 100%; }
        #tyresize { width: 100% !important; }
      }
      .multi-stock-table .stock-group-even > td { background-color: #f2f2f2; }
      .multi-stock-table .stock-group-odd > td { background-color: #fff; }
      .multi-stock-table .stock-offer-extra > td { border-top: 1px dashed #d5d9dd; }
      .multi-stock-table .trade-price { font-weight: 600; white-space: nowrap; }
      .multi-stock-table .delivery-cell { white-space: nowrap; }
      .multi-stock-table .tyre-description { min-width: 310px; }
      .multi-stock-table .badge { font-size: .68rem; vertical-align: middle; }
    
      /* Clear price and delivery hierarchy, on desktop and mobile. */
      .multi-stock-table .trade-price,
      .stock-mobile-card .stock-card-price {
        color: #1558b0;
        font-weight: 750;
        font-variant-numeric: tabular-nums;
      }
      .stock-mobile-card .stock-card-price { font-size: 1.23rem; line-height: 1.2; }
      .stock-mobile-card .stock-card-price small { font-size: .7rem; color: #6b7280 !important; }
      .multi-stock-table .delivery-cell .delivery-label,
      .stock-mobile-card .stock-card-delivery {
        font-weight: 650;
        color: #237844;
      }
      .multi-stock-table .delivery-cell .delivery-label.delivery-later,
      .stock-mobile-card .stock-card-delivery.delivery-later {
        color: #586576;
      }
    </style>
</head>

<body>

    <div id="wrapper">

        <?php include("functions/menu.php"); ?>


        <!-- Page Content -->
        <div id="page-content-wrapper">

            <div class="container-fluid">
                <h3>Tyre Search</h3>
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
                    <div class="extra-filter-row row g-2 align-items-end">
                      <div class="col-6 col-md-2"><label for="season-filter" class="form-label small">Season</label><select id="season-filter" class="form-select form-select-sm"><option value="all">All</option><option value="summer">Summer</option><option value="winter">Winter</option><option value="allseason">All-season</option></select></div>
                      <div class="col-6 col-md-2"><label for="runflat-filter" class="form-label small">Runflat</label><select id="runflat-filter" class="form-select form-select-sm"><option value="any">Any</option><option value="yes">Yes</option><option value="no">No</option></select></div>
                      <div class="col-6 col-md-2"><label for="availability-filter" class="form-label small">Delivery</label><select id="availability-filter" class="form-select form-select-sm"><option value="all">All deliveries</option><option value="today">Today only</option></select></div>
                      <div class="col-6 col-md-3"><label for="sort-results" class="form-label small">Sort</label><select id="sort-results" class="form-select form-select-sm"><option value="price">Lowest price</option><option value="price-desc">Highest price</option><option value="delivery">Earliest delivery</option><option value="brand">Brand A–Z</option></select></div>
                      <div class="col-12 col-md-3"><button type="button" id="reset-extra-filters" class="btn btn-outline-secondary btn-sm w-100">Reset additional filters</button></div>
                    </div>
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
