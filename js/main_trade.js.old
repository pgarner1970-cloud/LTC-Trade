$(document).ready(function() {
	$("#wrapper").toggleClass("toggled");
	$("#orderStatus").empty();

	$.ajax({
		url: 'functions/orderstatustrade.php',
		type: 'get',
		dataType: 'json'
	})

	.done(function(data) {

		// log data to the console so we can see
		console.log(data); 
		var len = data.length;
		console.log(len);

		$("#orderStatus").empty();
		var resultsHtml = "<h4>Your Orders</h4><table cellspacing=2 cellpadding=2 class='table table-striped w-75'>";
		resultsHtml += "<thead><tr bgcolor=#cecece><td>Order</td><td>Date</td><td>Placed by</td><td>Reference</td><td>Quantity</td><td>Total Price (excl.VAT)</td><td>Status</td></tr></thead>";
		for (var i = 0; i<len; i++){
		   resultsHtml += '<tr><td>' + data[i]['order_id'] + '</td><td>' + data[i]['order_date'] + '</td><td>' + data[i]['order_name'] + '</td>';
		   resultsHtml += '<td>' + data[i]['order_ref'] + '</td><td>' + data[i]['order_quantity'] + '</td><td>' + data[i]['order_value'] + '</td>';
		   resultsHtml += '<td>' + data[i]['order_processed'] + '</td>';
		   //resultsHtml += '<td><button type="button" name="view" id="' + data[i]['order_id'] + '" class="btn btn-success btn-sm view">View</button></td>';
		   resultsHtml += '</tr>';
		} 
		resultsHtml += "</table>";
		$("#orderStatus").html(resultsHtml);
	});
});	  