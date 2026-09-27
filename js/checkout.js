$(document).ready(function(){
	$("#wrapper").toggleClass("toggled");
	$("#resultsDiv").empty();
	$.ajax({
			url:"functions/userfetchsingle.php",
			async: false,
			method:"POST",
			dataType:"json",
		    success:function(data) {
		        console.log(data);
		        username = data["username"];
		        rightNow = new Date();
                res = rightNow.toISOString().slice(0,10).replace(/-/g,"");
		        order_ref = data["username"] + "-" + res;
		        email = data["email"];
		}
	})
	$.ajax({
		url: 'functions/baskettable.php',
		type: 'post',
		dataType: 'json',
		success:function(data) {

				// log data to the console so we can see
				//console.log(data); 
				var len = data.length;
				//console.log(len);
				var subtot=0;

				$("#resultsDiv").empty();
				var resultsHtml = "<div class='row'><div class='w-50 d-inline-block col-xs-6'><table cellspacing=2 cellpadding=2 class='table table-striped'>";
				resultsHtml += '<thead><tr bgcolor=#cecece><td>Description</td><td>Qty</td><td align=right>Price</td></tr></thead>';
				for(var i = 0; i<len; i++) {
					resultsHtml += '<tr><td width=40%>' + data[i][2] + '</td>';
    				resultsHtml += '<td width=5%>' + data[i][4] + '</td>';
					resultsHtml += '<td width=5% align=right>' + (data[i][3] * data[i][4]).toFixed(2) + '</td></tr>';
					subtot = subtot + (data[i][3] * data[i][4]);
				} 
				resultsHtml += '<tr><td colspan=2 align=right><strong>Sub-total (excl.VAT)</strong></td><td align=right><strong>' + subtot.toFixed(2) + '</strong></th></tr>';
				resultsHtml += '<tr><td colspan=2 align=right><strong>VAT</strong></td><td align=right><strong>' + (subtot * 0.2).toFixed(2) + '</strong></th></tr>';
				resultsHtml += '<tr><td colspan=2 align=right bgcolor=#cecece><strong>Total (incl.VAT)</strong></td><td align=right><strong>' + (subtot * 1.2).toFixed(2) + '</strong></th></tr>';
				resultsHtml += "</table></div>";
				resultsHtml += "<div class='w-50 d-inline-block col-xs-6'>";
				resultsHtml += "<h4>Order Details</h4>";
				resultsHtml += "<form method=POST name='checkout_form' id='checkout_form' class='row gy-2 gx-3 align-items-center'>";
				resultsHtml += "<input type='hidden' class='form-control' id='username' name='username' value='" + username + "'>";
				resultsHtml += "<div class='input-group mb-3'><span class='input-group-text'>Name</span>";
				resultsHtml += "<input type='text' class='form-control' placeholder='Enter your name' id='order_name' name='order_name' required></div>";
				resultsHtml += "<div class='input-group mb-3'><span class='input-group-text'>Reference</span>";
				resultsHtml += "<input type='text' class='form-control' placeholder='Reference' id='order_ref' name='order_ref' value='" + order_ref + "' required></div>";
 				resultsHtml += "<div class='input-group mb-3'><span class='input-group-text'>Email</span>";
				resultsHtml += "<input type='text' class='form-control' placeholder='Email address' id='email' name='email' value='" + email + "'></div>";
                //resultsHtml += "<div class='input-group'><span class='input-group-text'>Notes</span><textarea class='form-control' aria-label='Notes' id='notes' name='notes' placeholder='Optional notes..'></textarea></div>";
				resultsHtml += "<div class='form-check mb-3'>";
				resultsHtml += "<input class='form-check-input' type='checkbox' id='agree' name='agree' required>";
				resultsHtml += "<label class='form-check-label' for='agree'>Click here to agree to the Terms & Conditions of Sale</label></div>";
				if (subtot > 0) {
				    resultsHtml += "<div class='col-auto'><button type='submit' class='btn btn-primary submitbtn' id='submitbtn'>Submit Order</button></div>";
				} else {
				    resultsHtml += "<div class='col-auto'><button type='submit' class='btn btn-primary submitbtn' id='submitbtn' disabled>Submit Order</button></div>";
				}
				resultsHtml += "</form></div>";
				$("#resultsDiv").html(resultsHtml);
			}
		});
		
		$(document).on('submit', '#checkout_form', function(event){
     		event.preventDefault();
     		$("#submitbtn").attr("disabled", true);
    		var regex = /^[0-9]*$/i;
    		var order_name = $('#order_name').val();
    		var order_ref = $('#order_ref').val();
     		var order_notes = $('#notes').val();
       		var err = false;
    		var errMsg = 'You need to enter values for:\n';
    		if (order_name == '') {
    			err = true;
    			errMsg += " - Name \n";
    		}
    		if (order_ref == '') {
    		    err = true;
    			errMsg += " - Reference\n";
    		}
    		if (err == true) {
    		    alert(errMsg);
    		    $("#submitbtn").attr("disabled", false);
    		    return false;
    		}
			var formData = jQuery('#checkout_form').serialize();
			//console.log(formData);
   			$.ajax({
				url: "functions/basketcheckout.php",
				method: "POST",
				data: formData,
				success:function(data) {
					//alert(data);
					window.location.href = 'index.php';
				}
			});

    	});
    	
	});