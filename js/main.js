$(document).ready(function() {
	$("#wrapper").toggleClass("toggled");
	$("#dataStatus").empty();
	$("#orderStatus").empty();

	$.ajax({
		url: 'functions/datastatus.php',
		type: 'get',
		dataType: 'json'
	})
	.done(function(data) {
		var len = data.length;
		$("#dataStatus").empty();
		var resultsHtml = "<table cellspacing=2 cellpadding=2 class='table table-striped'>";
		resultsHtml += '<thead><tr bgcolor=#cecece><td>Supplier</td><td>Last Updated (Oldest first)</td></tr></thead>';
		for (var i = 0; i<len; i++){
							   resultsHtml += '<tr><td>' + data[i]['Supplier'] + '</td><td>' + data[i]['LastUpdate'] + '</td></tr>';
		} 
		resultsHtml += "</table>";
		$("#dataStatus").html(resultsHtml);
	});
	
	$("#orderStatus").empty();
	$.ajax({
		url: 'functions/ordersummary.php',
		type: 'GET',
		dataType: 'json'
	})
	.done(function(data) {
		var len = data.length;
		var resultsHtml = "<h4>Open Orders by Supplier</h4><table cellspacing=2 cellpadding=2 class='table table-striped'>";
		resultsHtml += "<thead><tr bgcolor=#cecece><td>Supplier</td><td>EAN</td><td>Tyre Description</td><td>Unit Buy Price</td><td>Quantity</td></tr></thead>";
		for (var i = 0; i<len; i++){
		   resultsHtml += '<tr><td>' + data[i]['Supplier'] + '</td><td>' + data[i]['EAN'] + '</td><td>' + data[i]['TyreDesc'] + '</td>';
		   resultsHtml += '<td>' + data[i]['UnitBuyPrice'] + '</td><td>' + data[i]['Qty'] + '</td></tr>';
		} 
		resultsHtml += "</table>";
    	$("#orderStatus").html(resultsHtml);
	});

});	  