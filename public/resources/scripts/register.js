var base_url=$("#base_url").val();

$(document).ready(function() {
	$('#form-register').validate({
		highlight: function(element) {
	        $(element).parent().addClass("has-error");
	    },
	    unhighlight: function(element) {
	        $(element).parent().removeClass("has-error");
	    }
	});
	$('#ra-login1').on('click', function(e){
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

	$("#btnRegister").on('click',function(e){
		if ($('#form-register').valid()){
			var btn = $(this);
			var text = $(this).text();
			$(this).text(pleasewait); //change button text
		    $(this).attr('disabled',true); //set button disable 
		    var url = base_url+"login/registration";
		   
		    // ajax adding data to database

		    var formData = new FormData($('#form-register')[0]);
		    $.ajax({
		        url : url,
		        type: "POST",
		        data: formData,
		        contentType: false,
		        processData: false,
		        dataType: "JSON",
		        success: function(data)
		        {

		            //if($.isEmptyObject(data.error)){
		            if(data.status){
	                	$(".print-error-msg").css('display','none');
	                	//alert(data.status);
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

	    e.preventDefault();
	});
});