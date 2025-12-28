var login = function(e){
     //e.preventDefault();
        $('html').addClass('overlay');
        $("#overlayBg").show();
        $('#overlayContent').show();
        var $overlayBody = $('#overlayBody'),
            $overlayLoader = $('#overlayLoader'),
        loadurl = base_url+"login?redirect=checkout&t=" + Date.now();
        $overlayBody.html("");
        $overlayLoader.show();
        $.get(loadurl, function(data) {
            $overlayBody.html(data);
        }).done(function() {
            $overlayLoader.hide();
            $overlayBody.show();
        });
        return false;
    };
$(document).ready(function() {
    $("#btnCheckoutLogin").on('click',function(e){
       /**/
        $.ajax({
            type:"get",
            url:base_url+"shoppingcart/checkout",
            success:function(result){
                if(result=='login'){
                    login(this);
                }
            }
        });

    });

    $("#btnContinueShoppingcart").on('click',function(){
        $("#closeOverlay").click();
    })
});