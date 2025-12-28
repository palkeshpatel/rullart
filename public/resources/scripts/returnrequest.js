$(document).ready(function() {
	$('#formData').validate({
		highlight: function(element) {
	        $(element).parent().addClass("has-error");
	    },
	    unhighlight: function(element) {
	        $(element).parent().removeClass("has-error");
	    }
	});

	$("#btnSave").on('click',function(e){
		if ($('#formData').valid()){

			if ($('#chk_confirm').prop("checked") == false) {
				$('#chk_confirm').focus();
				$("#divconfirmmessage").show();
				return false;
			} else {
				var btn = $(this);
				var text = $(this).text();
				$(this).text(pleasewait); //change button text
			    $(this).attr('disabled',true); //set button disable 
			    var url = base_url+"returnrequest/add";
			   	$(".alert-success").hide();
			   	$(".print-error-msg").hide();
			    // ajax adding data to database
			    var formData = new FormData($('#formData')[0]);
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
		                	$("#divthanks").show();
		                	$(".alert-success").show();
		                	$("#requestform").hide();
		                }else{

							$(".print-error-msg").show();
		                	$(".print-error-msg").html(data.msg);
		                	$(btn).text(text); //change button text
			            	$(btn).attr('disabled',false); //set button enable 
		                	
		                }
			            
			        },
			        error: function (jqXHR, textStatus, errorThrown)
			        {
			           
			            $(btn).text(text); //change button text
			            $(btn).attr('disabled',false); //set button enable 

			        }
			    });
			}
		}

	    e.preventDefault();
	});
});