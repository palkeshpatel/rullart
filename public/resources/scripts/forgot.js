$(document).ready(function() {
	$('#form-forgot,#form-change-password').validate({
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

	$("#btnReset").on('click',function(e){
		e.preventDefault();
		if ($('#form-forgot').valid()){
			var btn = $(this);
			var text = $(this).text();
			$(this).text(pleasewait); //change button text
		    $(this).attr('disabled',true); //set button disable 
		    var url = base_url+"login/resetpassword";		   
		    var formData = new FormData($('#form-forgot')[0]);
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
	                	$("#btnReset").hide();
	                	$("#forgotSuccess").show();
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


	$("#btnChangePassword").on('click',function(e){
		e.preventDefault();
		if ($('#form-change-password').valid()){
			var btn = $(this);
			var text = $(this).text();
			$(this).text(pleasewait); //change button text
		    $(this).attr('disabled',true); //set button disable 
		    var url = base_url+"login/changepass";		   
		    var formData = new FormData($('#form-change-password')[0]);
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
	                	$("#btnChangePassword").hide();
	                	$("#forgotSuccess").show();
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