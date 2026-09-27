$(document).ready(function(){
	$("#wrapper").toggleClass("toggled");

	var dataTable;
	dataTable = $('#rimtable').DataTable( {
		"processing": true,
		"serverSide": true,
		"order":[],
		"ajax": {
			url: "functions/rimtable.php",
			method: "POST"
		},
		"columnDefs":[
			{
				"targets":[0, 1, 2, 3, 4, 5],
				"orderable":false
			},
		],
	});
	
	$(document).on('submit', '#rim_form', function(event){
		event.preventDefault();
		var regex = /^[0-9]+\.[0-9]{0,2}$/i;
		var rimDesc = $('#rim_desc').val();
		var rimMarkup = $('#rim_markup').val();
		if (rimMarkup.match(regex) == null) {
			alert("Markup can be decimal numbers only ie. 10.00");
			return false;
		}
		var rimType = $('#rim_type').val();
		var arr=["B", "C", "M"];
        if (arr.indexOf(rimType) == -1) {
			alert("Rim type can only be (C)ar, (M)otorcycle or (B)oth");
			return false;
		}
		if(rimDesc != '' && rimMarkup != '' && rimType != '') {
			$.ajax({
				url: "functions/rimfunctions.php",
				method: "POST",
				data: new FormData(this),
				contentType:false,
				processData:false,
				success:function(data) {
					alert(data);
					$('#rim_form')[0].reset();
					$('#rimModal').modal('hide');
					dataTable.ajax.reload();
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
			url:"functions/rimfetchsingle.php",
			method:"POST",
			data:{id:id},
			dataType:"json",
			success:function(data) {
				$('#rimModal').modal('show');
				$('#rim_desc').val(data.rimdesc);
				$('#rim_markup').val(data.rimmarkup);
				$('#rim_type').val(data.rimtype);
				$('.modal-title').text("Edit Rim");
				$('#rim_id').val(id);
				$('#action').val("Edit");
				$('#operation').val("Edit");
			}
		});
	});
	
	$(document).on('click', '.delete', function(){
		var id = $(this).attr("id");
		if (confirm("Are you sure you want to delete this?")) {
			$.ajax({
			url:"functions/rimdeletesingle.php",
			method:"POST",
			data:{id:id},
			success:function(data) {
				alert(data);
				dataTable.ajax.reload();
				}
			});		
		}
		else {
			return false;
		}

	});
});