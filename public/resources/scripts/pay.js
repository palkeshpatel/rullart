$(document).ready(function() {
	$('#formPayment').validate({
		highlight: function(element) {
	        $(element).parent().addClass("has-error");
	    },
	    unhighlight: function(element) {
	        $(element).parent().removeClass("has-error");
	    }
	});

	$("#btnUpdateProfile").on('click',function(e){
		e.preventDefault();
		if ($('#formPayment').valid()){
			var btn = $(this);
			var text = $(this).text();
			$(this).text(pleasewait); //change button text
		    $(this).attr('disabled',true); //set button disable 
		    var url = base_url+"pay/process";		   
		    var formData = new FormData($('#formPayment')[0]);
		    $.ajax({
		        url : url,
		        type: "POST",
		        data: formData,
		        contentType: false,
		        processData: false,
		        dataType: "JSON",
		        success: function(data)
		        {

		            if ($.isEmptyObject(data.error)) {

	                	location.href = data.redirect;

	                }else{
						$("#formPayment").find(".print-error-msg").css('display','block');
	                	$("#formPayment").find(".print-error-msg").html(data.error);
	                	 $(btn).text(text); //change button text
		            	$(btn).attr('disabled',false); //set button enable 
	                }
		           
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