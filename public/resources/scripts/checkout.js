var address_label = "Address";
var additional_label = "Additional details";
if (window.location.href.indexOf("/ar/") > -1) {
	address_label = 'العنوان';
	additional_label = 'تفاصيل اخرى';
}

$(document).ready(function() {
	$('#formAddressCheckout').validate({
		highlight: function(element) {
			$(element).parent().addClass("has-error");
		},
		unhighlight: function(element) {
			$(element).parent().removeClass("has-error");
		},
		onfocusout: false,
		invalidHandler: function(form, validator) {
			var errors = validator.numberOfInvalids();
			if (errors) {
				validator.errorList[0].element.focus();
			}
		}
	});
	$("#btnContinueShopping").on('click', function(e) {
		location.href = base_url + 'home';
	});
	$("#btnContinue").on('click', function(e) {
		location.href = base_url + 'checkout';
	});
	$(document).on("click", ".clsremovecoupon", function() {
		$.ajax({
			type: "post",
			url: base_url + "checkout/couponremove",
			dataType: "json",
			success: function(data) {
				$(".success-code").addClass("hidden");
				$(".couponapply").removeClass("hidden");
				$("#cartview").html(data.cartview);
			},
			error: function(jqXHR, textStatus, errorThrown) {
				alert(textStatus);
			}
		});
	});
	$(document).on("click", ".delivery", function() {
		if ($(this).val() == "Avenues Mall Delivery") {
			$("#avenueDelivery").show();
			$("#addressDelivery").hide();
			// $("#divshippingrow").hide();
			$("#divShippingMethod").hide();
			$("#divshippingavenue").show();
			$("#country").val("Kuwait").trigger("change");
		} else {
			$("#addressDelivery").show();
			$("#avenueDelivery").hide();
			//$("#divshippingrow").show();
			$("#divshippingavenue").hide();
			$("#country").val("Kuwait").trigger("change");
		}
	});
	$(document).on("change", "#messageid", function() {
		//var messageid = $("#messages option:selected"); 
		//$("#giftMessage").text(value.val());
		var messageid = $(this).val();
		$.ajax({
			type: "post",
			url: base_url + "checkout/message_get_by_id",
			dataType: "json",
			data: {
				messageid: messageid
			},
			success: function(data) {
				//$("#giftMessage").text(data.msg);
			},
			error: function(jqXHR, textStatus, errorThrown) {
				alert(textStatus);
			}
		});
	});


	$(document).on("change", "#shipping_method", function() {
		var shipping_method = $("#shipping_method").val();
		$.ajax({
			type: "post",
			url: base_url + "checkout/shippingmethod",
			dataType: "json",
			data: {
				shipping_method: shipping_method
			},
			success: function(data) {
				$("#cartview").html(data.cartview);
			},
			error: function(jqXHR, textStatus, errorThrown) {
				alert(textStatus);
			}
		});
	});
	$(document).on("click", ".btn-edit", function(e) {
		e.preventDefault();
		$(this).parent().parent().find('.gift-change').toggleClass('show');
	});

	$("#btnCouponcode").on('click', function(e) {
		if ($("#couponcode").val() == '') {
			$(".print-error-msg").css('display', 'block');
			$(".print-error-msg").html('Please enter couponcode');
		} else {
			$(".print-error-msg").css('display', 'none');
			var couponcode = $("#couponcode").val();
			$.ajax({
				type: "post",
				url: base_url + "checkout/apply",
				data: {
					couponcode: couponcode
				},
				dataType: "json",
				success: function(data) {
					if (data.status == false) {
						$(".print-error-msg").css('display', 'block');
						$(".print-error-msg").html(data.msg);
					} else {
						$(".success-code").removeClass("hidden");
						$(".success-code").find('.code').html(data.msg);
						$("#cartview").html(data.cartview);
						$(".couponapply").addClass("hidden");
					}
				},
				error: function(jqXHR, textStatus, errorThrown) {
					alert(textStatus);
				}
			});
		}

	});
	$("#chk_kuwait_delivery").on('click', function(e) {
		$("#divCheckboxMessage").hide();
	});
	$("#btnProceedPayment").on('click', function(e) {
		//e.preventDefault();

		if ($("#chk_kuwait_delivery").length > 0) {
			if ($('#chk_kuwait_delivery').prop("checked") == false) {
				$('#chk_kuwait_delivery').focus();
				$("#divCheckboxMessage").show();
				$('html, body').animate({
					scrollTop: $("#divCheckboxMessage").offset().top
				}, 1000);
				return false;
			}
		}
		if ($("#itemtotal").val() == '0') {
			location.href = base_url;
			return false;
		}
		if ($('#formPayment').valid()) {
			var btn = $(this);
			var text = $(this).text();
			$(this).text(pleasewait);
			$(this).attr('disabled', true);
			var url = base_url + "thankyou/process";
            var paymentmethod = $("input[name='method']:checked").val();
            //alert(paymentmethod);

			var formData = new FormData($('#formPayment')[0]);
			$.ajax({
				url: url,
				type: "POST",
				data: formData,
				contentType: false,
				processData: false,
				dataType: "JSON",
				success: function(data) {

					if ($.isEmptyObject(data.error)) {
					    
					    
                    //  fbq('track', 'Purchase', {
                    //     value: <?php echo $order_total; ?>, // The total order value
                    //     currency: 'USD',
                    //     content_ids: <?php echo json_encode($product_ids); ?>, // Array of purchased product IDs
                    //     content_type: 'product'
                    //   });
					     
						$(".print-error-msg").css('display', 'none');
						    location.href = data.redirect;
						
					} else {
						//$(".print-error-msg").css('display', 'block');
						// $(".print-error-msg").html(data.error);
						alert(data.error);
						location.href = '/';
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
	
	


	$("#btnProceed").on('click', function(e) {
		e.preventDefault();

		if ($('#formAddressCheckout').valid()) {
			var btn = $(this);
			var text = $(this).text();
			$(this).text(pleasewait);
			$(this).attr('disabled', true);
			var url = base_url + "checkout/delivery";

			// ajax adding data to database

			var formData = new FormData($('#formAddressCheckout')[0]);
			$.ajax({
				url: url,
				type: "POST",
				data: formData,
				contentType: false,
				processData: false,
				dataType: "JSON",
				success: function(data) {

					if ($.isEmptyObject(data.error)) {
						$(".print-error-msg").css('display', 'none');
						//alert(data.status);
						location.href = base_url + 'payment';
					} else {
						$(".print-error-msg").css('display', 'block');
						$(".print-error-msg").html(data.error);
						//$("html, body").animate({ scrollTop: 100 }, 600);
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


	});

	$("#emailGuest").on('change', function(e) {
		var email = $(this).val();
		$.ajax({
			url: base_url + "login/validate_guest",
			data: {
				email: email
			},
			type: "POST",
			dataType: "JSON",
			success: function(data) {
				if (data.status) {
					location.reload(true);
				} else {
					//alert(data.msg);
					$(".print-error-msg-email").css('display', 'block');
					$(".print-error-msg-email").html(data.msg);
				}
			}
		});
	});
	$("#country").on('change', function(e) {

		//window.location.href = 'checkout?country='+$("#country").val()+'&;

		var hdncurrencyrate = $("#hdncurrencyrate").val();

		//$("#divSecurityID").hide();
		if ($("#country").val() == 'Qatar') {
			$("#divSecurityID").show();
		} else {
			$("#divSecurityID").hide();
			$("#securityid").val('');
		}
		var hdnDiscountvalue = $("#hdnDiscountvalue").val();
		var hdncurrencycode = $("#hdncurrencycode").val();

		$("#shipping_method").val('standard');
		if ($("#country").val() == 'Kuwait') {
			fillArea($("#country").val(), '');
			//$("#address_label").html(additional_label);			
			$("#divCity").hide();
			$("#divArea").show();
		} else {
			$("#divCity").show();
			$("#divArea").hide();
		}
		if ($("#country").val() == $('#hdndefaultcountry').val()) {
			if ($("#hdnexpresstimeclose").val() == 0) {
				if ($("#avenue").prop("checked") == true) {
					$("#divShippingMethod").hide();
				} else {
					$("#divShippingMethod").show();
				}

			}
			// $("#divArea,.showStreet").show();
		} else {
			$("#divShippingMethod").hide();
			//$("#address_label").html(address_label);		
		}



		showLoading();
		$.ajax({
			url: base_url + "areas/get_country_shipping",
			data: {
				country: $("#country").val(),
				delivery_method: $('input[name="delivery_method"]:checked').val()
			},
			type: "POST",
			dataType: "JSON",
			success: function(data) {

				// $("#tdShippingCharge").html(numberFormat(data.shipping_charge * hdncurrencyrate, 3) + ' ' + $("#hdncurrencycode").val());
				// var GrandTotal = parseFloat($("#itemtotal").val()) + parseFloat(data.shipping_charge * hdncurrencyrate) - parseFloat(hdnDiscountvalue * hdncurrencyrate);

				// $("#tdGrandTotal").html(numberFormat(GrandTotal, 3) + ' ' + hdncurrencycode);
				$("#cartview").html(data.cartview);
				hideLoading();
			}
		});

	});

	$("#countryBill").on('change', function(e) {

		/*if ($("#countryBill").val() == 'Kuwait') {
			fillAreaBill($("#countryBill").val(), '');
			$("#addressBill_label").html(additional_label);
			$("#divCityBill").hide();
			$("#divAreaBill,.showStreetBill").show();
		} else {
			$("#divCityBill").show();
			$("#addressBill_label").html(address_label);
			$("#divAreaBill,.showStreetBill").hide();
		}*/

		if ($("#countryBill").val() == 'Kuwait') {
			fillAreaBill($("#countryBill").val(), '');
			$("#divCityBill").hide();
			$("#divAreaBill").show();
		} else {
			$("#divCityBill").show();
			$("#divAreaBill").hide();
		}

	});

	$("#addNewAddress").on('change', function(e) {
		var mySelect = $('#area');
		var addressid = $("#addNewAddress").val();
		mySelect.empty();
		mySelect.append($('<option></option>').val('').html('Select'));
		showLoading();
		$.ajax({
			url: base_url + "addressbook/getdata",
			data: {
				addressid: addressid
			},
			type: "POST",
			dataType: "JSON",
			success: function(data) {
				if (data.country == undefined) {
					data.country = "Kuwait";
				}
				if (addressid == "0") {
					$("#trAddressTitle").show();
				} else {
					$("#trAddressTitle").hide();
				}
				$("#addressTitle").val(data.title);

				$("#firstname").val(data.firstname);
				$("#lastname").val(data.lastname);
				$("#mobile").val(data.mobile);
				$("#country").val(data.country).trigger('render.customSelect');
				$("#country").trigger('change');
				if (data.country == "Kuwait") {
					fillArea(data.country, data.fkareaid);
					$("#address_label").html(additional_label);
				} else {
					$("#city").val(data.city);
					$("#address_label").html(address_label);
				}
				$("#address").val(data.address);
				$("#regular").prop("checked", "checked");
				$("#street_number").val(data.street_number);
				$("#block_number").val(data.block_number);
				$("#house_number").val(data.house_number);
				$("#avenue_number").val(data.avenue_number);
				// if (data.country == "Qatar") {
				$("#securityid").val(data.securityid);
				// }

				hideLoading();
			}
		});

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

function fillAreaBill(country, fkareaid) {
	var mySelect = $('#areaBill');
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

				$("#areaBill").val(fkareaid).trigger('render.customSelect');
			}
		}
	});
}