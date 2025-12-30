$(document).ready(function() {
    // Use global base_url if available, otherwise get from DOM
    var base_url = window.base_url || $("#base_url").val();
    if (!base_url) {
        // Fallback: construct from current URL
        var path = window.location.pathname;
        var pathParts = path.split('/');
        if (pathParts.length >= 2) {
            base_url = '/' + pathParts[1] + '/';
        } else {
            base_url = '/';
        }
    }
    window.base_url = base_url; // Make it global for other scripts

    var pid = $("#hdnProductId").val();
    var price = $("#price").html();
    console.log('Details.js loaded');
    console.log('Product ID:', pid);
    console.log('Price:', price);
    console.log('Base URL:', base_url);

    // Check if buttons exist
    console.log('Add to Cart button exists:', $("#addToCart").length > 0);
    console.log('Add to Wish button exists:', $("#addToWish").length > 0);

    // Facebook pixel tracking (only if fbq is defined)
    if (typeof fbq !== 'undefined') {
        fbq('track', 'ViewContent', {
            content_ids: [pid], // Your product ID
            content_type: 'product',
            value: 100, // Product price
            currency: 'KWD'
        });
    } else {
        console.warn('Facebook Pixel (fbq) is not loaded');
    }


	$(".go-back").on("click", function(e) {
		e.preventDefault();
		if (document.referrer == "")
			location.href = base_url;
		else if (window.history.length == 1)
			location.href = document.referrer;
		else
			window.history.back();
	});

	// Check if buttons exist before attaching handlers
	console.log('Checking for buttons...');
	console.log('#addToCart exists:', $("#addToCart").length);
	console.log('#addToWish exists:', $("#addToWish").length);
	console.log('#addToWish1 exists:', $("#addToWish1").length);

	$(document).on('click', '#addToCart', function(e) {
	    console.log('Add to Cart button clicked');
	    e.preventDefault();
	    var maxGlobalQuantity =4;
		var pid = $("#hdnProductId").val();
		console.log('Product ID:', pid);
		var qty = $("#selectQty").val();
		console.log('Quantity:', qty);
		var giftmessageid = $("#giftmessageid").val();
		var giftqty = $("#selectQty2").val();

		var giftmessage = $("#giftmessage").val();
		var gift_type = $("#gift_type").val();
		var size = '0'; /* default to nosize */
		var giftproductid = 0;
		var giftproductid2 = 0;
		var giftproductid3 = 0;
		var giftproductid4 = 0;
		console.log('Size selector exists:', $('#selectSize').length > 0);
		if ($('#selectSize').length) {
			size = $("#selectSize").val();
			console.log('Selected size:', size);
			if (size == '') {
				console.log('Size not selected, showing error');
				$("#hdnSize").show();
				return false;
			} else {
				$("#hdnSize").hide();
			}
		}
		if ($('.giftoccasion').length) {
			if ($('.giftoccasion').prop("checked")) {
				if (giftmessageid == '0') {
					$("#hdnGiftoccassion").show();
					return false;
				}
			}
		}
		if ($('#giftmessageid').length) {
			if (giftmessageid == '0' && giftmessage != '') {
				$("#hdnGiftoccassion").show();
				return false;
			} else {
				$("#hdnGiftoccassion").hide();
			}
		}

		if ($('.productcategory').length > 0) {
			if ($('.selectedProduct').length == 0) {
				$("#hdnSelectProduct").show();
            if ($("#hdnSelectProduct").length) {
                $('html, body').animate({
                    scrollTop: $("#hdnSelectProduct").offset().top
                }, 2000);
            } else {
                console.error("Element #hdnSelectProduct not found!");
            }
				return false;
			} else {
				$("#hdnSelectProduct").hide();
				giftproductid = $('.selectedProduct').attr("data-value");

			}
		}
		if ($('.productcategory2').length > 0) {
			if ($('.selectedProduct2').length == 0) {
				$("#hdnSelectProduct2").show();
				$('html, body').animate({
					scrollTop: $("#hdnSelectProduct2").offset().top
				}, 2000);
				return false;
			} else {
				$("#hdnSelectProduct2").hide();
				giftproductid2 = $('.selectedProduct2').attr("data-value");

			}
		}
		if ($('.productcategory3').length > 0) {
			if ($('.selectedProduct3').length == 0) {
				$("#hdnSelectProduct3").show();
				$('html, body').animate({
					scrollTop: $("#hdnSelectProduct3").offset().top
				}, 2000);
				return false;
			} else {
				$("#hdnSelectProduct3").hide();
				giftproductid3 = $('.selectedProduct3').attr("data-value");

			}
		}



	   if ($('.productcategory4').length > 0) {
			if ($('.selectedProduct4').length == 0) {
				$("#hdnSelectProduct4").show();
				$('html, body').animate({
					scrollTop: $("#hdnSelectProduct4").offset().top
				}, 2000);
				return false;
			} else {
				$("#hdnSelectProduct4").hide();
				giftproductid4 = $('.selectedProduct4').attr("data-value");

			}
		}


		if(gift_type ==2)
		{

		    if ($('.selectedProduct4').length == 0) {

				$("#hdnSelectProduct").show();
				$("#hdnSelectProduct2").show();
				$("#hdnSelectProduct3").show();
				$("#hdnSelectProduct4").show();
				$('html, body').animate({
					scrollTop: $("#hdnSelectProduct").offset().top
				}, 2000);


				return false;
			} else {
				$("#hdnSelectProduct4").hide();
				$("#hdnSelectProduct3").hide();
				$("#hdnSelectProduct2").hide();
				$("#hdnSelectProduct").hide();
				giftproductid = $('.selectedProduct').attr("data-value");
				giftproductid2 = $('.selectedProduct2').attr("data-value");
				giftproductid3 = $('.selectedProduct3').attr("data-value");
	            giftproductid4 = $('.selectedProduct4').attr("data-value");


			}

		}



		console.log('Base URL:', base_url);
		console.log('Full URL:', base_url + "shoppingcart/ajax_cart");
		console.log('CSRF Token:', $('meta[name="csrf-token"]').attr('content'));

		$.ajax({
			type: "post",
			url: base_url + "shoppingcart/ajax_cart",
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			data: {
				action: 'add',
				p_id: pid,
				quantity: qty,
				size: size,
				giftproductid: giftproductid,
				giftproductid2: giftproductid2,
				giftproductid3: giftproductid3,
			    giftproductid4: giftproductid4,
				giftmessageid: giftmessageid,
				giftmessage: giftmessage,
				gift_type: gift_type ?gift_type : 0,
				giftqty: giftmessageid ?giftqty : 0,

			},
			success: function(result) {
				console.log('Add to cart success, result:', result);
				if ($("#ra-cart .badge").length == 0) {


					$('li.licart a').html(function(_, html) {
						return html + '<span class="badge">' + result + '</span>';
					});
					 var price = $("#price").html();

                  fbq('track', 'AddToCart', {
                    content_ids: [pid], // Your product ID
                    content_type: 'product',
                    value: price, // Product price
                    currency: 'KWD'
                  });
				} else {
					$("#ra-cart .badge").text(result);
				}
				$("#ra-cart").click();
			},
			error: function(xhr, status, error) {
				console.error('Add to cart error:', error);
				console.error('Status:', status);
				console.error('Response:', xhr.responseText);
				console.error('Status code:', xhr.status);
				console.error('Full XHR:', xhr);
				alert('Error adding to cart. Please check console for details.');
			}
		});

	});
	// $("#selectSize").on('change',function(){
	// 	if ($("#selectSize").val()=='')
	// 		$("#hdnSize").show();
	// 	else
	// 		$("#hdnSize").hide();
	// });
	$('#selectSize').on('change', function() {
		var productid = $("#hdnProductId").val();
		var sizeid = $(this).val();
		if ($("#selectSize").val() == '')
			$("#hdnSize").removeClass('hidden');
		else
			$("#hdnSize").addClass('hidden');
		if (sizeid > 0) {
			$.ajax({
				type: "GET",
				data: {
					productid: productid,
					sizeid: sizeid
				},
				url: base_url + 'product/getqty',
				dataType: "JSON",
				success: function(data) {

					if (parseInt(data.qty) > 0) {
						$("#selectQty").show();
						$("#addToCartSoldout").addClass('hidden');
						$("#addToCart").removeClass('hidden');
						var output = [];
						var length = data.length;
						qty = data.qty;
						if (data.qty > 10)
							qty = 10;
						for (var i = 1; i <= qty; i++) {
							output[i] = '<option value="' + i + '">' + i + '</option>';
						}
						$('#selectQty').get(0).innerHTML = output.join('');
						$('#selectQty2').get(0).innerHTML = output.join('');

						$("#selectQty").trigger("change");
						$("#selectQty2").trigger("change");
					} else {
						alert(parseInt(data.qty));
						$("#selectQty").hide();
						$("#selectQty2").hide();

						$("#addToCartSoldout").removeClass('hidden');
						$("#addToCart").addClass('hidden');

					}
				}
			});
		}
	});




    if( $("#gift_type").val() == '1')
    {


    	$(".productcategory").on("click", function() {

    		$("#hdnSelectProduct").hide();
    		$(".productcategory").removeClass('selectedProduct');
    		$(this).addClass('selectedProduct');

    		$(".giftItemImage").removeClass("hidden");
    		$("html, body").animate({
    			scrollTop: 0
    		}, "slow");

    		var imageurl = $("#imageurl").val();
    		$("#giftItemImage").attr("src", imageurl + 'storage/gallary-' + $('.selectedProduct').attr("data-image"));

    		$("#giftItemImageZoom").removeClass("hidden");
    		$("#giftItemImageZoom").attr("src", imageurl + 'storage/detail-' + $('.selectedProduct').attr("data-image"));
    		$("#giftItemImageZoom").attr("data-imagezoom", imageurl + 'storage/' + $('.selectedProduct').attr("data-image"));

    		$("#productprice1").val($(this).attr("data-price"));

    		calcprice();
    	});



    	$(".productcategory2").on("click", function() {
    		$("#hdnSelectProduct2").hide();
    		$(".productcategory2").removeClass('selectedProduct2');
    		$(this).addClass('selectedProduct2');

    		$(".giftItemImage2").removeClass("hidden");
    		$("html, body").animate({
    			scrollTop: 0
    		}, "slow");

    		var imageurl = $("#imageurl").val();
    		$("#giftItemImage2").attr("src", imageurl + 'storage/gallary-' + $('.selectedProduct2').attr("data-image"));

    		$("#giftItemImageZoom2").removeClass("hidden");
    		$("#giftItemImageZoom2").attr("src", imageurl + 'storage/detail-' + $('.selectedProduct2').attr("data-image"));
    		$("#giftItemImageZoom2").attr("data-imagezoom", imageurl + 'storage/' + $('.selectedProduct2').attr("data-image"));
    		$("#productprice2").val($(this).attr("data-price"));

    		calcprice();
    	});

    	$(".productcategory3").on("click", function() {
    		$("#hdnSelectProduct3").hide();
    		$(".productcategory3").removeClass('selectedProduct3');
    		$(this).addClass('selectedProduct3');

    		$(".giftItemImage3").removeClass("hidden");
    		$("html, body").animate({
    			scrollTop: 0
    		}, "slow");

    		var imageurl = $("#imageurl").val();
    		$("#giftItemImage3").attr("src", imageurl + 'storage/gallary-' + $('.selectedProduct3').attr("data-image"));

    		$("#giftItemImageZoom3").removeClass("hidden");
    		$("#giftItemImageZoom3").attr("src", imageurl + 'storage/detail-' + $('.selectedProduct3').attr("data-image"));
    		$("#giftItemImageZoom3").attr("data-imagezoom", imageurl + 'storage/' + $('.selectedProduct3').attr("data-image"));
    		$("#productprice3").val($(this).attr("data-price"));

    		calcprice();
    	});


    	$(".productcategory4").on("click", function() {
    		$("#hdnSelectProduct4").hide();
    		$(".productcategory4").removeClass('selectedProduct4');
    		$(this).addClass('selectedProduct4');

    		$(".giftItemImage4").removeClass("hidden");
    		$("html, body").animate({
    			scrollTop: 0
    		}, "slow");

    		var imageurl = $("#imageurl").val();
    		$("#giftItemImage4").attr("src", imageurl + 'storage/gallary-' + $('.selectedProduct4').attr("data-image"));

    		$("#giftItemImageZoom4").removeClass("hidden");
    		$("#giftItemImageZoom4").attr("src", imageurl + 'storage/detail-' + $('.selectedProduct4').attr("data-image"));
    		$("#giftItemImageZoom4").attr("data-imagezoom", imageurl + 'storage/' + $('.selectedProduct4').attr("data-image"));
    		$("#productprice4").val($(this).attr("data-price"));

    		calcprice();
    	});
    }

	$(document).on('click', '#addToWish', function(e) {
		console.log('Add to Wish List button clicked');
		e.preventDefault();
		var pid = $("#hdnProductId").val();
		console.log('Product ID:', pid);
		var btn = $(this);
		if (!pid) {
			console.error('Product ID not found');
			alert('Product ID not found');
			return;
		}
		console.log('Base URL:', base_url);
		console.log('Full URL:', base_url + "shoppingcart/ajax_wishlist");
		$.ajax({
			type: "post",
			url: base_url + "shoppingcart/ajax_wishlist",
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			data: {
				action: 'add',
				pid: pid
			},
			success: function(result) {
				console.log('Add to wishlist success, result:', result);
				if (result == 'login') {
					console.log('User not logged in, opening login');
					$("#ra-login").click();
				} else {
					$(btn).text(itemaddedwishlist);
					if ($("#ra-wishlist .badge").length == 0) {
						$('li.liwishlist a').html(function(_, html) {
							return html + '<span class="badge">' + result + '</span>';
						});
					} else
						$("#ra-wishlist .badge").text(result);
				}
			},
			error: function(xhr, status, error) {
				console.error('Add to wishlist error:', error);
				console.error('Status:', status);
				console.error('Response:', xhr.responseText);
				console.error('Status code:', xhr.status);
				console.error('Full XHR:', xhr);
				alert('Error adding to wishlist. Please check console for details.');
			}
		});

	});
});

function calcprice() {
	var currencycode = $("#currencycode").val();
	var sellingprice = parseFloat($("#sellingprice").val().replace(',', ''));
	var productprice1 = parseFloat($("#productprice1").val().replace(',', ''));


	var productprice2 = parseFloat($("#productprice2").val().replace(',', ''));
	var productprice3 = parseFloat($("#productprice3").val().replace(',', ''));
	var productprice4 = parseFloat($("#productprice4").val().replace(',', ''));
	//$("#price").html(currencycode + ' ' + numberFormat(parseFloat(sellingprice) + parseFloat(productprice1) + parseFloat(productprice2)+ parseFloat(productprice3)),3);
	$("#price").html(currencycode + ' ' + (sellingprice + productprice1 + productprice2 + productprice3 + productprice4).toFixed(3));
}
