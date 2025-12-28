$(document).ready(function() {
	$('#formEditAddress').validate({
		highlight: function(element) {
			$(element).parent().addClass("has-error");
		},
		unhighlight: function(element) {
			$(element).parent().removeClass("has-error");
		}
	});
	$('#formAddAddress').validate({
		highlight: function(element) {
			$(element).parent().addClass("has-error");
		},
		unhighlight: function(element) {
			$(element).parent().removeClass("has-error");
		}
	});
	$("#btnUpdateAddress").on('click', function(e) {
		if ($('#formEditAddress').valid()) {
			var btn = $(this);
			var text = $(this).text();
			$(this).text(pleasewait);
			$(this).attr('disabled', true);
			var url = base_url + "editaddress/update";
			$(".alert-success").hide();
			$(".print-error-msg").hide();
			// ajax adding data to database

			var formData = new FormData($('#formEditAddress')[0]);
			$.ajax({
				url: url,
				type: "POST",
				data: formData,
				contentType: false,
				processData: false,
				dataType: "JSON",
				success: function(data) {

					if (data.status) {
						$(".print-error-msg").css('display', 'none');
						$(".alert-success").show();
						location.href = base_url + 'myaddresses';
					} else {
						$(".print-error-msg").css('display', 'block');
						$(".print-error-msg").html(data.msg);
					}
					$(btn).text(text); //change button text
					$(btn).attr('disabled', false); //set button enable 
				},
				error: function(jqXHR, textStatus, errorThrown) {

					$(btn).text(text); //change button text
					$(btn).attr('disabled', false); //set button enable 

				}
			});

		}

		e.preventDefault();
	});


	$("#country").on('change', function(e) {
		if ($("#country").val() == 'Kuwait') {
			fillArea($("#country").val(), '');
			//$("#address_label").html(additional_label);			
			$("#divCity").hide();
			$("#divArea").show();
			// $("#divArea,.showStreet").show();
		} else {
			$("#divCity").show();
			$("#divArea").hide();
			//$("#address_label").html(address_label);		
		}
		if ($("#country").val() == 'Qatar') {
			$("#divSecurityID").show();
		} else {
			$("#divSecurityID").hide();
			$("#securityid").val('');
		}
	});

	$("#btnSaveAddress").on('click', function(e) {
		if ($('#formAddAddress').valid()) {
			var btn = $(this);
			var text = $(this).text();
			$(this).text(pleasewait); //change button text
			$(this).attr('disabled', true); //set button disable 
			var url = base_url + "editaddress/add";
			$(".alert-success").hide();
			$(".print-error-msg").hide();
			// ajax adding data to database
			var formData = new FormData($('#formAddAddress')[0]);
			$.ajax({
				url: url,
				type: "POST",
				data: formData,
				contentType: false,
				processData: false,
				dataType: "JSON",
				success: function(data) {

					if (data.status) {
						$(".alert-success").show();
						location.href = base_url + 'myaddresses';
					} else {

						$(".print-error-msg").show();
						$(".print-error-msg").html(data.msg);
						$(btn).text(text); //change button text
						$(btn).attr('disabled', false); //set button enable 

					}

				},
				error: function(jqXHR, textStatus, errorThrown) {

					$(btn).text(text); //change button text
					$(btn).attr('disabled', false); //set button enable 

				}
			});

		}

		e.preventDefault();
	});
});

function fillArea(country, fkareaid) {
	var mySelect = $('#area');
	mySelect.empty();
	mySelect.append($('<option></option>').val('').html('Select'));
	$.ajax({
		url: base_url + "areas/getdata",
		data: {
			country: country
		},
		type: "POST",
		dataType: "JSON",
		success: function(data) {

			if (data != 'FALSE') {
				$.each(data, function(key, value) {
					mySelect.append($('<option></option>').val(value.areaid).html(value.areaname));
				});

				$("#area").val(fkareaid).trigger('render.customSelect');
			}
		}
	});
}