var base_url=$("#base_url").val();
$(document).ready(function() {
	$('#form-login').validate({
		highlight: function(element) {
	        $(element).parent().addClass("has-error");
	    },
	    unhighlight: function(element) {
	        $(element).parent().removeClass("has-error");
	    }
	});
	$('#ra-register').on('click', function(e){
		e.preventDefault();
		var $overlayBody = $('#overlayBody'),
		$overlayLoader = $('#overlayLoader'),
		$this = $(this),
		loadurl = $this.attr('href') + "?t=" + Date.now();
		$overlayBody.html("");
		$overlayLoader.show();
		$.get(loadurl, function(data) {
			$overlayBody.html(data);
		}).done(function() {
			$overlayLoader.hide();
			$overlayBody.show();
		});
		return false;
	});
	$('#ra-forgot').on('click', function(e){
		e.preventDefault();
		var $overlayBody = $('#overlayBody'),
			$overlayLoader = $('#overlayLoader'),
			$this = $(this),
			loadurl = $this.attr('href') + "?t=" + Date.now();
		$overlayBody.html("");
		$overlayLoader.show();
		$.get(loadurl, function(data) {
			$overlayBody.html(data);
		}).done(function() {
			$overlayLoader.hide();
			$overlayBody.show();
		});
		return false;
	});

	$("#btnLogin").on('click',function(e){
		e.preventDefault();
		if ($('#form-login').valid()){
			var btn = $(this);
			var text = $(this).text();
			$(this).text(pleasewait); //change button text
		    $(this).attr('disabled',true); //set button disable 
		    var url = base_url+"login/validate";		   
		    var formData = new FormData($('#form-login')[0]);
		    $.ajax({
		        url : url,
		        type: "POST",
		        data: formData,
		        contentType: false,
		        processData: false,
		        dataType: "JSON",
		        success: function(data)
		        {

		            if(data.status){
	                	$(".print-error-msg").css('display','none');
	                	if(data.redirect=='shoppingcart')
	                	{
	                		location.href =  base_url + 'checkout';
	                	}
	                	else {
	                		$("#liWelcome").removeClass("hidden");
	                		var welcome = $("#liWelcome").html();
	                		welcome = welcome.replace("{{firstname}}", data.firstname);
	                		$("#liWelcome").html(welcome);
	                	}
	                	$("#closeOverlay").click();
	                	$("#ra-post-login").closest('li').show();
	                	$("#ra-login").closest('li').hide();
	                }else{
						$(".print-error-msg").css('display','block');
	                	$(".print-error-msg").html(data.msg);
	                }
		            $(btn).text(text); //change button text
		            $(btn).attr('disabled',false); //set button enable 
		        },
		        error: function (jqXHR, textStatus, errorThrown)
		        {
		            alert(textStatus);
		            $(btn).text(text); //change button text
		            $(btn).attr('disabled',false); //set button enable 

		        }
		    });

		}

	   
	});

	$("#btnLoginGuest").on('click',function(e){
		e.preventDefault();
		location.href =  base_url + 'checkout';
	});

	$("#btnLoginGuest1").on('click',function(e){
		e.preventDefault();
		if ($('#form-login-guest').valid()){
			var btn = $(this);
			var text = $(this).text();
			$(this).text(pleasewait); //change button text
		    $(this).attr('disabled',true); //set button disable 
		    var url = base_url+"login/validate_guest";		   
		    var formData = new FormData($('#form-login-guest')[0]);
		    $.ajax({
		        url : url,
		        type: "POST",
		        data: formData,
		        contentType: false,
		        processData: false,
		        dataType: "JSON",
		        success: function(data)
		        {

		            if(data.status){
	                	$(".print-error-msg").css('display','none');
	                	if(data.redirect=='shoppingcart')
	                	{
	                		location.href =  base_url + 'checkout';
	                	}
	                	else {
	                		$("#liWelcome").removeClass("hidden");
	                		var welcome = $("#liWelcome").html();
	                		welcome = welcome.replace("{{firstname}}", data.firstname);
	                		$("#liWelcome").html(welcome);
	                	}
	                	$("#closeOverlay").click();
	                	$("#ra-post-login").closest('li').show();
	                	$("#ra-login").closest('li').hide();
	                }else{
						$(".print-error-msg").css('display','block');
	                	$(".print-error-msg").html(data.msg);
	                }
		            $(btn).text(text); //change button text
		            $(btn).attr('disabled',false); //set button enable 
		        },
		        error: function (jqXHR, textStatus, errorThrown)
		        {
		            alert(textStatus);
		            $(btn).text(text); //change button text
		            $(btn).attr('disabled',false); //set button enable 

		        }
		    });
		}	   
	});
});