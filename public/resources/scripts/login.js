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

	// Registration form handling (using event delegation for dynamically loaded content)
	// Use off() first to prevent multiple handlers
	$(document).off('click', '#btnRegister').on('click', '#btnRegister', function(e){
		e.preventDefault();
		e.stopPropagation();
		
		// Prevent multiple submissions
		if ($(this).attr('disabled') === 'disabled') {
			return false;
		}
		
		if ($('#form-register').valid()){
			var btn = $(this);
			var text = $(this).text();
			$(this).text(pleasewait); //change button text
		    $(this).attr('disabled',true); //set button disable 
		    var url = base_url+"login/registration";
		   
		    // ajax adding data to database
		    var formData = new FormData($('#form-register')[0]);
		    formData.append('_token', $('meta[name="csrf-token"]').attr('content')); // Add CSRF token
		    
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
	                	$(".print-error-msg").css('display','none');
	                	$("#closeOverlay").click();
	                	$("#ra-post-login").closest('li').show();
	                	$("#ra-login").closest('li').hide();
	                	// Reload page to update cart/wishlist counts
	                	location.reload();
	                }else{
						$(".print-error-msg").css('display','block');
	                	$(".print-error-msg").html(data.msg);
	                }
		            $(btn).text(text); //change button text
		            $(btn).attr('disabled',false); //set button enable 
		        },
		        error: function (jqXHR, textStatus, errorThrown)
		        {
		            console.error("Registration error:", textStatus, errorThrown, jqXHR.responseText);
		            $(".print-error-msg").css('display','block');
		            $(".print-error-msg").html("An error occurred during registration. Please try again.");
		            $(btn).text(text); //change button text
		            $(btn).attr('disabled',false); //set button enable 
		        }
		    });
		}
		return false;
	});

	// Registration form validation - initialize when form is loaded
	// Check if form exists and validation not already initialized
	function initRegisterFormValidation() {
		if ($('#form-register').length && !$('#form-register').data('validator')) {
			$('#form-register').validate({
				highlight: function(element) {
			        $(element).parent().addClass("has-error");
			    },
			    unhighlight: function(element) {
			        $(element).parent().removeClass("has-error");
			    }
			});
		}
	}
	
	// Initialize when document is ready (if form already exists)
	$(document).ready(function() {
		initRegisterFormValidation();
	});
	
	// Also initialize when overlay content is loaded (for dynamically loaded forms)
	// Use MutationObserver for better browser support
	if (typeof MutationObserver !== 'undefined') {
		var observer = new MutationObserver(function(mutations) {
			initRegisterFormValidation();
		});
		
		// Observe the overlay body for changes
		var overlayBody = document.getElementById('overlayBody');
		if (overlayBody) {
			observer.observe(overlayBody, {
				childList: true,
				subtree: true
			});
		}
	}
	
	// Also prevent form submission if someone presses Enter
	$(document).off('submit', '#form-register').on('submit', '#form-register', function(e){
		e.preventDefault();
		e.stopPropagation();
		$('#btnRegister').click();
		return false;
	});

	// Handle login link from registration page
	$(document).on('click', '#ra-login1', function(e){
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
});