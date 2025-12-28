var base_url=$("#base_url").val();

$(document).ready(function() {
	$('#form-guest-create').validate({
		highlight: function(element) {
	        $(element).parent().addClass("has-error");
	    },
	    unhighlight: function(element) {
	        $(element).parent().removeClass("has-error");
	    }
	});

	$("#btnGuestCreate").on('click',function(e){
		if ($('#form-guest-create').valid()){
			var btn = $(this);
			var text = $(this).text();
			$(this).text(pleasewait); //change button text
		    $(this).attr('disabled',true); //set button disable 
		    var url = base_url+"login/registration_guest";
		   
		    // ajax adding data to database

		    var formData = new FormData($('#form-guest-create')[0]);
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
	                	$("#guest-register-details").hide();
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