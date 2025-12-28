$(document).ready( function() {
	$("#country-currency, #country-currency-mobile").click( function() {
		$("#currency, #currency-mobile").addClass("opened");
	});
    $(".country-currency-selection-mask, .country-currency-selection .btn").click( function() {
		$("#currency, #currency-mobile").removeClass("opened");
	});
    // $(".country-currency-selection select").change( function() {
    //     var val = $(this).val();
    //     console.log("currency :: ", val);
    //     $("#country-currency .selected-currency, #country-currency-mobile .selected-currency").html(val);
    // });
    // $(".country-currency-selection input[name=optCountry]").click(function() {
    //     var val = $(this).val();
    //     console.log("country :: ", val);
    //     $("#country-currency .selected-country, #country-currency-mobile .selected-country").html(val);
    // });
    $("input[name=giftcard]").click( function() {
        if ($(this).is(":checked")) {
            $(this).closest(".gift-card").find(".gift-card-message").removeClass("hide");
        } else {
            $(this).closest(".gift-card").find(".gift-card-message").addClass("hide");
        }
    });
    $("input[name=giftoccasion]").click( function() {
        if ($(this).is(":checked")) {
            $(this).closest(".gift-occasion").next(".gift-options").removeClass("hide");
        } else {
            $(this).closest(".gift-occasion").next(".gift-options").addClass("hide");
        }
    });
    // $(".remove-gift-options").click( function() {
    //     $(this).closest(".gift").remove();
    // });
}());