var base_url = window.base_url || $("#base_url").val() || '/';
$(document).ready(function() {
	$('#formProfile').validate({
		highlight: function(element) {
	        $(element).parent().addClass("has-error");
	    },
	    unhighlight: function(element) {
	        $(element).parent().removeClass("has-error");
	    }
	});

	$("#btnUpdateProfile").on('click',function(e){
		e.preventDefault();
		if ($('#formProfile').valid()){
			var btn = $(this);
			var text = $(this).text();
			$(this).text(pleasewait); //change button text
		    $(this).attr('disabled',true); //set button disable 
		    var url = base_url+"myprofile/profile_update";		   
		    var formData = new FormData($('#formProfile')[0]);
		    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
		    $.ajax({
		        url : url,
		        type: "POST",
		        data: formData,
		        contentType: false,
		        processData: false,
		        dataType: "JSON",
		        headers: {
		            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		        },
		        success: function(data)
		        {

		            if(data.status){
	                	$("#formProfile").find(".print-error-msg").css('display','none');
	                	$("#formProfile").find(".alert-success").show();
	                	
	                }else{
						$("#formProfile").find(".print-error-msg").css('display','block');
	                	$("#formProfile").find(".print-error-msg").html(data.msg);
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

	$("#btnUpdatePassword").on('click',function(e){
		e.preventDefault();
		if ($('#formChangePassword').valid()){
			var btn = $(this);
			var text = $(this).text();
			$("#formChangePassword").find(".alert-success").hide();
		    $("#formChangePassword").find(".print-error-msg").hide();
			$(this).text(pleasewait); //change button text
		    $(this).attr('disabled',true); //set button disable 
		    var url = base_url+"myprofile/change_password";		   
		    var formData = new FormData($('#formChangePassword')[0]);
		    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
		    $.ajax({
		        url : url,
		        type: "POST",
		        data: formData,
		        contentType: false,
		        processData: false,
		        dataType: "JSON",
		        headers: {
		            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		        },
		        success: function(data)
		        {

		            if(data.status){
	                	$("#formChangePassword").find(".print-error-msg").css('display','none');
	                	$("#formChangePassword").find(".alert-success").show();
	                	
	                }else{
						$("#formChangePassword").find(".print-error-msg").css('display','block');
	                	$("#formChangePassword").find(".print-error-msg").html(data.msg);
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