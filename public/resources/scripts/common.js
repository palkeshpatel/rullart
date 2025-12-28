var base_url = $("#base_url").val();
var pleasewait = 'Please wait...';
var itemaddedwishlist = 'Item is added in wishlist';
if ($("#base_url").val().indexOf('/ar/') > -1) {
    pleasewait = 'يرجى الإنتظار...';
    itemaddedwishlist = 'تم إضافة المنتج لقائمة الأمنيات';
}

$(document).ready(function() {
    $('#frmSearch').validate({
        highlight: function(element) {
            $(element).parent().addClass("has-error");
        },
        unhighlight: function(element) {
            $(element).parent().removeClass("has-error");
        }
    });

    // $("#selCurrency").on("change", function() {
    //     location.href = base_url + '?currency=' + $(this).val();
    // });

    $(".country-currency-selection select").change(function() {
        var val = $(this).val();
        console.log("currency :: ", val);
        $("#country-currency .selected-currency, #country-currency-mobile .selected-currency").html(val);
        location.href = base_url + 'changecurrency?currency=' + $(this).val();

    });

    $(".country-currency-selection input.optCountry").click(function() {
        var val = $(this).val();
        console.log("country :: ", val);
        $("#country-currency .selected-country, #country-currency-mobile .selected-country").html(val);

        location.href = $(this).attr("data-url");

    });


    /* var notice = getCookie("notice");
     if (notice!=1){
         showNotice();
     }*/
});

function showLoading() {
    $(".inside-content").addClass('disabled');
    $('#loading').removeClass('hidden');
}

function hideLoading() {
    $(".inside-content").addClass('disabled');
    $('#loading').addClass('hidden');
}

function getParameterByName(name, url) {
    if (!url) url = window.location.href;
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, " "));
}

function remove_cart(rowid) {
    $.ajax({
        type: "post",
        url: base_url + "shoppingcart/ajax_cart",
        data: {
            action: 'delete',
            rowid: rowid
        },
        dataType: "JSON",
        success: function(result) {
            $("#ra-cart").click();
            $("#ra-cart .badge").text(result.cnt);
            var urlarr = location.pathname.split('/');
            if (urlarr[2] == "checkout") {
                $("#cartview").html(result.cartview);
                // location.reload();
            }
        }
    });
}

function remove_wishlist(pid) {
    $.ajax({
        type: "post",
        url: base_url + "shoppingcart/ajax_wishlist",
        data: {
            action: 'delete',
            pid: pid
        },
        success: function(result) {
            $("#ra-wishlist .badge").text(result);
            $("#ra-wishlist").click();
        }
    });
}

function numberFormat(number, dec, dsep, tsep) {
    if (isNaN(number) || number == null) return '';

    number = number.toFixed(~~dec);
    tsep = typeof tsep == 'string' ? tsep : ',';

    var parts = number.split('.'),
        fnums = parts[0],
        decimals = parts[1] ? (dsep || '.') + parts[1] : '';

    return fnums.replace(/(\d)(?=(?:\d{3})+$)/g, '$1' + tsep) + decimals;
}

var overlayShow = function() {
    $('html').addClass('overlay');
    $("#overlayBg").show();
    $('#overlayContent').show();
};

var overlayHide = function() {
    $('#overlayContent').hide();
    $("#overlayBg").removeAttr("style");
    $('html').removeClass('overlay');
    $('html').removeClass('offcanvas');
};

var closeOverlay = function() {
    $('#overlayBg, #closeOverlay').on('click', function(e) {
        e.preventDefault();
        overlayHide();
    });
};

function showNotice() {
    overlayShow();
    setCookie("notice", "1", 1);
    var $overlayBody = $('#overlayBody'),
        $overlayLoader = $('#overlayLoader'),
        loadurl = base_url + "notice?t=" + Date.now();
    $overlayBody.html("");
    $overlayLoader.show();
    $.get(loadurl, function(data) {
        $overlayBody.html(data);
    }).done(function() {
        $overlayLoader.hide();
        $overlayBody.show();
    });
};

function setCookie(key, value, expiry) {
    var expires = new Date();
    expires.setTime(expires.getTime() + (expiry * 24 * 60 * 60 * 1000));
    document.cookie = key + '=' + value + ';expires=' + expires.toUTCString();
}

function getCookie(key) {
    var keyValue = document.cookie.match('(^|;) ?' + key + '=([^;]*)(;|$)');
    return keyValue ? keyValue[2] : null;
}

function eraseCookie(key) {
    var keyValue = getCookie(key);
    setCookie(key, keyValue, '-1');
}
$(document).on("change", ".frmmessage .giftmessagetext", function() {
    if ($(this).closest(".frmmessage").find(".btngiftmessage").length > 0) {
        $(this).closest(".frmmessage").find(".btngiftmessage").trigger("click");
    } else if ($(this).closest(".frmmessage").find(".btngiftmessage2").length > 0) {
        $(this).closest(".frmmessage").find(".btngiftmessage2").trigger("click");
    }
});
$(document).on("change", ".frmmessage .giftmessageid", function() {
    if ($(this).closest(".frmmessage").find(".btngiftmessage").length > 0) {
        $(this).closest(".frmmessage").find(".btngiftmessage").trigger("click");
    } else if ($(this).closest(".frmmessage").find(".btngiftmessage2").length > 0) {
        $(this).closest(".frmmessage").find(".btngiftmessage2").trigger("click");
    }
});
$(document).on("click", ".remove-gift-options", function() {

    $(this).closest(".frmmessage").find(".giftmessageid").val(0);
    $(this).closest(".frmmessage").find(".giftmessagetext").text('');
    if ($(this).closest(".frmmessage").find(".btngiftmessage").length > 0) {
        $(this).closest(".frmmessage").find(".btngiftmessage").trigger("click");
    } else if ($(this).closest(".frmmessage").find(".btngiftmessage2").length > 0) {
        $(this).closest(".frmmessage").find(".btngiftmessage2").trigger("click");
    }
});

$(document).on("click", ".btngiftmessage", function() {
    var cartitem = $(this).closest(".cart-item");
    var data = getFormData($(this).closest(".cart-item").find('.frmmessage'));
    var giftmessageid = $(this).closest(".cart-item").find(".giftmessageid").val();
    var myVar = $(this).closest(".cart-item").find(".giftmessageid");
    var gifttitle = $("option:selected", myVar).text();
    var gift_type = $(this).attr('data-type') ? $(this).attr('data-type') : 0;
   
    if (giftmessageid == 0) {
        gifttitle = "";
    }
    data['gifttitle'] = gifttitle;
    
    data['gift_type'] = gift_type;


    if (giftmessageid == 0 && data['giftmessage'] != '') {
        $(this).closest(".cart-item").find(".giftmsgselect").show();
    } else {
        $(this).closest(".cart-item").find(".giftmsgselect").hide();
        // var giftmessage = $(this).closest(cartitem).find(".giftmessagetext").text();
        var rowid = $(this).closest(".cart-item").find(".hdnrowid").val();
        $.ajax({
            type: "post",
            url: base_url + "shoppingcart/ajax_cart",
            data: {
                action: 'giftmessage',
                rowid: rowid,
                data: data
            },
            success: function(result) {
                $("#ra-cart").click();
                // $(cartitem).find('.pgifttitle').html(data['gifttitle']);
                // $(cartitem).find('.pgiftmessage').html(data['giftmessage']);
                // $(cartitem).find('.gift-change').hide();
            }
        });
    }
});

$(document).on("click", ".btngiftmessage2", function() {
    var cartitem = $(this).closest(".cart-item");
    var data = getFormData($(this).closest(".cart-item").find('.frmmessage'));
    var giftmessageid = $(this).closest(".cart-item").find(".giftmessageid").val();
    var myVar = $(this).closest(".cart-item").find(".giftmessageid");
    var gifttitle = $("option:selected", myVar).text();
    var qty = $("option:selected", $(this).closest(".cart-item").find(".giftmessageid")).text();
    if (giftmessageid == 0) {
        gifttitle = '';
    }
    data['gifttitle'] = gifttitle;
    data['qty'] = qty;

    if (giftmessageid == 0 && data['giftmessage'] != '') {
        $(this).closest(".cart-item").find(".giftmsgselect").show();
    } else {
        $(this).closest(".cart-item").find(".giftmsgselect").hide();
        // var giftmessage = $(this).closest(cartitem).find(".giftmessagetext").text();
        var rowid = $(this).closest(".cart-item").find(".hdnrowid").val();
        $.ajax({
            type: "post",
            dataType: "json",
            url: base_url + "shoppingcart/ajax_cart",
            data: {
                action: 'giftmessage',
                rowid: rowid,
                data: data
            },
            success: function(result) {
                $("#cartview").html(result.cartview);
                // location.reload();
                // $(cartitem).find(".gift").html(result.giftmessagecart);
                // $(cartitem).find('.btn-edit').trigger('click');
                // $("#ra-cart").click();
                // $(cartitem).find('.pgifttitle').html(data['gifttitle']);
                // $(cartitem).find('.pgiftmessage').html(data['giftmessage']);
                // $(cartitem).find('.gift-change').hide();
            }
        });
    }
});

function getFormData($form) {
    var unindexed_array = $form.serializeArray();
    var indexed_array = {};

    $.map(unindexed_array, function(n, i) {
        indexed_array[n['name']] = n['value'];
    });

    return indexed_array;
}