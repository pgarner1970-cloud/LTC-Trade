$(document).ready(function(){
	$("#wrapper").toggleClass("toggled");
	$("#resultsDiv").empty();
	$.ajax({
		url: 'functions/baskettable.php',
		type: 'post',
		dataType: 'json',
		success:function(data) {

				// log data to the console so we can see
				console.log(data); 
				var len = data.length;
				console.log(len);
				var subtot=0;
				
				$("#resultsDiv").empty();
				var resultsHtml = "<table cellspacing=2 cellpadding=2 class='table table-striped'>";
				resultsHtml += '<thead><tr bgcolor=#cecece><td>EAN</td><td>Manufacturer</td><td>Description</td><td>Price</td><td>Qty</td><td>Action</td><td>Extended</td></tr></thead>';
				for(var i = 0; i<len; i++) {
					resultsHtml += '<tr><td width=15%>' + data[i][0] + '</td>';
					resultsHtml += '<td width=15%>' + data[i][6] + '</td>';
					resultsHtml += '<td width=40%>' + data[i][2] + '</td>';
					resultsHtml += '<td width=5%>' + data[i][3] + '</td>';
    				resultsHtml += '<td width=5%>' + data[i][4] + '</td>';
					resultsHtml += '<td><button type="button" name="update" id="' + data[i][0] + '" class="btn btn-info btn-sm update">Update</button>';
					resultsHtml += ' <button type="button" name="delete" id="' + data[i][0] + '" class="btn btn-danger btn-sm delete">Delete</button></td>';
					resultsHtml += '<td width=5% align=right>' + (data[i][3] * data[i][4]).toFixed(2) + '</td></tr>';
					subtot = subtot + (data[i][3] * data[i][4]);
				} 
				resultsHtml += '<tr><td colspan=6 align=right><strong>Sub-total (excl.VAT)</strong></td><td align=right><strong>' + subtot.toFixed(2) + '</strong></th></tr>';
				resultsHtml += '<tr><td colspan=6 align=right><strong>VAT</strong></td><td align=right><strong>' + (subtot * 0.2).toFixed(2) + '</strong></th></tr>';
				resultsHtml += '<tr><td colspan=6 align=right bgcolor=#cecece><strong>Total (incl.VAT)</strong></td><td align=right><strong>' + (subtot * 1.2).toFixed(2) + '</strong></th></tr>';
				resultsHtml += "</table>";
				resultsHtml += "<table width='100%' cellspacing=2 cellpadding=2><tr><td><button type='button' name='deleteall' id='deleteall' class='btn btn-danger btn-s deleteall'>Delete Basket</button></td>"
				if (subtot > 0) {
				    resultsHtml += "<td align=right><button type='button' name='checkout' id='checkout' class='btn btn-info btn-s checkout'>Checkout</button></td></tr></table>"
				} else {
				    resultsHtml += "<td align=right><button type='button' name='checkout' id='checkout' class='btn btn-info btn-s checkout' disabled>Checkout</button></td></tr></table>"
				}
				$("#resultsDiv").html(resultsHtml);
			}
		});
		
		$(document).on('click', '.deleteall', function(){
		    if (confirm("Are you sure you want to delete the basket?")) {
		    	$.ajax({
		    	url:"functions/basketdeleteall.php",
		    	method:"POST",
		    	success:function(data) {
		    		location.reload();
		    		}
		    	});		
		    }
		    else {
			    return false;
		    }
	    });
	    
		$(document).on('click', '.delete', function(){
		    var id = $(this).attr("id");
		    if (confirm("Are you sure you want to delete this?")) {
		    	$.ajax({
		    	url:"functions/basketdeletesingle.php",
		    	method:"POST",
		    	data:{id:id},
		    	success:function(data) {
		    		location.reload();
		    		}
		    	});		
		    }
		    else {
			    return false;
		    }
	    });
	    
		$(document).on('click', '.checkout', function(){
	    	$("#checkout").attr("disabled", true);
    		window.location.href = 'checkout.php';
	    });
	    
		$(document).on('submit', '#basket_form', function(event){
     		event.preventDefault();
    		var regex = /^[0-9]*$/i;
    		var quantity = $('#quantity').val();
    		if (quantity.match(regex) == null) {
    			alert("Quantity can be integer numbers only ie. 10");
    			return false;
    		}
    		if(quantity != '') {
    			$.ajax({
    				url: "functions/basketfunctions.php",
    				method: "POST",
    				data: new FormData(this),
    				contentType:false,
    				processData:false,
    				success:function(data) {
    					//alert(data);
    					$('#basket_form')[0].reset();
    					$('#basketModal').modal('hide');
    					location.reload();
    				}
    			});
    		}
    		else {
    			alert("All fields are required");
    		}
    	});
    	
    	$(document).on('click', '.update', function(){
    		var id = $(this).attr("id");
    		$.ajax({
    			url:"functions/basketfetchsingle.php",
    			method:"POST",
    			data:{id:id},
    			dataType:"json",
    			success:function(data) {
    				$('#basketModal').modal('show');
    				$('#quantity').val(data.quantity);
    				$('#tyredesc').val(data.tyredesc);
    				$('.modal-title').text(data.tyredesc);
    				$('#basket_id').val(id);
    				$('#action').val("Edit");
    				$('#operation').val("Edit");
    			}
    		});
    	});    
	});