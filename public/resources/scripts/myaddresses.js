$(document).ready(function() {
	$(".removeAddress").on("click",function(e){
		e.preventDefault();
		var msg = "Are you sure to remove address?";
		if ($("#base_url").val().indexOf('/ar') > -1){
			msg = "هل أنت متأكد من إزالة العنوان؟";
		}
		if (confirm(msg)){
			var addressid = $(this).attr("data-id");
			var div = $(this).closest(".col-sm-6");
			 $.ajax({
		        type:"post",
		        url:base_url+"myaddresses/remove",
		        data:{addressid:addressid},
		        dataType: "json",
		        success: function(data)
		        {
		            if(data.status){
	                	div.remove();
	                }
		        },
		        error: function (jqXHR, textStatus, errorThrown)
		        {
		            alert(textStatus);		            
		        }
		    });
		}
	});
});