$(document).ready(function() {
	$("#wrapper").toggleClass("toggled");
	$("#orderStatus").empty();
	readOrders("Open");
});

$(document).on('click', '.openorders', function(){
	$("#orderStatus").empty();
	readOrders("Open");
});

$(document).on('click', '.complete', function(){
	$("#orderStatus").empty();
	readOrders("Complete");
});

$(document).on('click', '.inprogress', function(){
	$("#orderStatus").empty();
	readOrders("In-Progress");
});

$(document).on('click', '.update', function(){
	var id = $(this).attr("id");
	$("#orderStatus").empty();
	$.ajax({
    url:"functions/orderupdate.php",
    method:"POST",
    data:{id:id},
    success:function(data) {
    	location.reload();
    	}
    });		
});

function readOrders($type){
	$.ajax({
		url: 'functions/orderstatus.php',
		data: {type:$type},
		type: 'GET',
		contentType: 'text/html',
		dataType: 'html'
	})
	.done(function(data) {
    	$("#orderStatus").html(data);
	})
};	  