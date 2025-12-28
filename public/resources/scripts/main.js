;
(function() {
	'use strict';
	var isMobile = {
		Android: function() {
			return navigator.userAgent.match(/Android/i);
		},
		BlackBerry: function() {
			return navigator.userAgent.match(/BlackBerry/i);
		},
		iOS: function() {
			return navigator.userAgent.match(/iPhone|iPad|iPod/i);
		},
		Opera: function() {
			return navigator.userAgent.match(/Opera Mini/i);
		},
		Windows: function() {
			return navigator.userAgent.match(/IEMobile/i);
		},
		any: function() {
			return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Opera() || isMobile.Windows());
		}
	};

	var swipeCarousel = function() {
		$(".carousel").swipe({
			swipe: function(event, direction, distance, duration, fingerCount, fingerData) {
				if (direction == 'left') $(this).carousel('next');
				if (direction == 'right') $(this).carousel('prev');
			},
			allowPageScroll: "vertical"
		});
	};

	// var moveCurrency = function() {
	// 	var $selCurrency = $('#currency');
	// 	if ($(window).width() < 600) {
	// 		$selCurrency.detach();
	// 		$selCurrency.appendTo("#mCurrency");
	// 	} else {
	// 		$selCurrency.detach();
	// 		$selCurrency.appendTo("#dCurrency");
	// 	}

	// 	$(window).resize(function() {
	// 		if ($(window).width() < 600) {
	// 			$('html').removeClass('offcanvas');
	// 			$('#menuToggle').removeClass('active');
	// 		}
	// 	});
	// };

	var burgerMenu = function() {
		$('#menuToggle').on('click', function(event) {
			event.preventDefault();
			var $this = $(this);
			var $html = $('html');
			$html.toggleClass('offcanvas');
			$this.toggleClass('active');
		});

		$(window).resize(function() {
			if ($('html').hasClass('offcanvas')) {
				$('html').removeClass('offcanvas');
				$('#menuToggle').removeClass('active');
			}
		});

		$(document).on("click","#closeMenu",function(event) {
			event.preventDefault();
			$('#menuToggle').click();
		});
		$(document).on("click",".has-sub > a",function(event) {			
			event.preventDefault();
			if ($(window).width() < 992) {
				$(this).parent().toggleClass('open').siblings().removeClass('open');
			}
		});

		$(document).on("click","#filterToggle",function(event) {			
			$('#colFilters').slideDown();
		});

		$(document).on("click","#closeFilters",function(event) {
			$('#colFilters').removeAttr('style');
		});

	};

	var searchBox = function() {
		var searchToggle = $('#ra-search');
		var searchBar = $('.ra-search-bar');
		var closeSearch = $('#closeSearch');

		searchToggle.on("click", function(e) {
			e.preventDefault();
			searchBar.fadeIn();
		});

		closeSearch.on("click", function(e) {
			e.preventDefault();
			searchBar.fadeOut();
		});
	};

	var overlayShow = function() {
		$('html').addClass('overlay');
		$("#overlayBg").show();
		$('#overlayContent').show();
	};

	var overlayHide = function() {
		$('#overlayContent').hide();
		$("#overlayBg").removeAttr("style");
		$('html').removeClass('overlay');
		$('html').removeClass('offcanvas');
	};

	var closeOverlay = function() {
		$('#overlayBg, #closeOverlay').on('click', function(e) {
			e.preventDefault();
			overlayHide();
		});
	};

	var login = function() {
		$('#ra-login').on('click', function(e) {
			e.preventDefault();
			overlayShow();
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
	};

	var cart = function() {
		$('#ra-cart').on('click', function(e) {
			if ($('.col-cart').is(":visible") == false) {
				e.preventDefault();
				overlayShow();
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
			}
			return false;
		});
	};

	var cancelItem = function() {
		$('.cancel-item').on('click', function(e) {
			e.preventDefault();

			var selectedItem = new Array();
			$('input[name="chkItem"]:checked').each(function() {
				selectedItem.push(this.value);
			});
			if (selectedItem.length > 0) {
				overlayShow();
				var $overlayBody = $('#overlayBody'),
					$overlayLoader = $('#overlayLoader'),
					$this = $(this),
					loadurl = $this.attr('href') + "?t=" + Date.now() + "&items=" + selectedItem;
				$overlayBody.html("");
				$overlayLoader.show();
				$.get(loadurl, function(data) {
					$overlayBody.html(data);
				}).done(function() {
					$overlayLoader.hide();
					$overlayBody.show();
				});
			} else {
				alert("Please select items to cancel");
			}

			return false;
		});
	};

	var cancelOrder = function() {
		$('.cancel-link').on('click', function(e) {
			e.preventDefault();
			overlayShow();
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
	};

	var wishlist = function() {
		$('#ra-wishlist').on('click', function(e) {
			e.preventDefault();
			overlayShow();
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
	};

	var accordion = function() {
		$('.accordion-collapse').on('show.bs.collapse hidden.bs.collapse', function() {
			$(this).prev().toggleClass("show");
		});
	};

	if ($("#base_url").val().indexOf('/ar') == -1) {
		var productGallery = function() {
			$('#prodSlider').slick({
				infinite: false,
				slidesToShow: 1,
				slidesToScroll: 1,
				arrows: false,
				dots: false,
				asNavFor: '#prodThumbs',
				responsive: [{
					breakpoint: 768,
					settings: {
						dots: true
					}
				}]
			});
			$('#prodThumbs').slick({
				infinite: false,
				slidesToShow: 5,
				slidesToScroll: 1,
				vertical: true,
				asNavFor: '#prodSlider',
				dots: false,
				prevArrow: "<a href='javascript:;' class='icon-up'><svg class='icon icon-arrow-down'><use xlink:href='/resources/images/symbol-defs.svg#icon-arrow-down'></use></svg></a>",
				nextArrow: "<a href='javascript:;' class='icon-down'><svg class='icon icon-arrow-down'><use xlink:href='/resources/images/symbol-defs.svg#icon-arrow-down'></use></svg></a>",
				focusOnSelect: true
			});
			$('.thumb-item').mouseover(function() {
				$(this).click();
			});
			if (Modernizr.touch && $(window).width() < 768) {
				$('a.img-zoom').magnifik();
			} else {
				$('[data-imagezoom]').imageZoom({
					zoomviewposition: 'right'
				});
				/*$('#prodSlider').magnificPopup({
				   delegate: 'a.img-zoom',
				   type: 'image',
				   tLoading: 'Loading image...',
				   mainClass: 'mfp-img-mobile'
				 });*/
			}
		};

	} else {
		var productGallery = function() {
			$('#prodSlider').slick({
				rtl: false,
				infinite: false,
				slidesToShow: 1,
				slidesToScroll: 1,
				arrows: false,
				dots: false,
				asNavFor: '#prodThumbs',
				responsive: [{
					breakpoint: 768,
					settings: {
						dots: true
					}
				}]
			});
			$('#prodThumbs').slick({
				infinite: false,
				slidesToShow: 5,
				slidesToScroll: 1,
				vertical: true,
				asNavFor: '#prodSlider',
				dots: false,
				prevArrow: "<a href='javascript:;' class='icon-up'><svg class='icon icon-arrow-down'><use xlink:href='/resources/images/symbol-defs.svg#icon-arrow-down'></use></svg></a>",
				nextArrow: "<a href='javascript:;' class='icon-down'><svg class='icon icon-arrow-down'><use xlink:href='/resources/images/symbol-defs.svg#icon-arrow-down'></use></svg></a>",
				focusOnSelect: true
			});

			$('.thumb-item').mouseover(function() {
				$(this).click();
			});
			if (Modernizr.touch && $(window).width() < 768) {
				$('a.img-zoom').magnifik();
			} else {
				$('[data-imagezoom]').imageZoom({
					zoomviewposition: 'left'
				});

				/*$('#prodSlider').magnificPopup({
				   delegate: 'a.img-zoom',
				   type: 'image',
				   tLoading: 'Loading image...',
				   mainClass: 'mfp-img-mobile'
				 });*/
			}
		};
	}

	var addressStep = function() {
		//$('#billing').hide();
		//$('#formAddress').validate();
		$('#sameAddress').change(function() {
			if (!this.checked) {
				$('#billing').slideDown();
			} else {
				$('#billing').slideUp();
			}
			$("#countryBill,#areaBill").trigger('render.customSelect');
		});
	};

	var giftStep = function() {
		var $gift = $('#gift');
		//$gift.hide();
		$('#asGift').change(function() {
			if (this.checked) {
				$gift.slideDown();
			} else {
				$gift.slideUp();
			}
		});
	};

	var rateThis = function() {
		$('a.rate-item').on('click', function(e) {
			e.preventDefault();
			overlayShow();
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
	};

	var resetpassword = function() {
		overlayShow();
		//debugger
		var email = getParameterByName('email');
		var token = getParameterByName('token');
		var $overlayBody = $('#overlayBody'),
			$overlayLoader = $('#overlayLoader'),
			$this = $(this),
			loadurl = "resetpassword?email=" + email + '&token=' + token;
		$overlayBody.html("");
		$overlayLoader.show();
		$.get(loadurl, function(data) {
			$overlayBody.html(data);
		}).done(function() {
			$overlayLoader.hide();
			$overlayBody.show();
		});

	};
	if ($("#base_url").val().indexOf('/ar') !== -1) {
		// Arabic Messages
		$.extend($.validator.messages, {
			required: "هذه الخانة مطلوبة",
			remote: "يرجى تصحيح هذا الحقل للمتابعة",
			email: "رجاء إدخال عنوان بريد إلكتروني صحيح",
			url: "رجاء إدخال عنوان موقع إلكتروني صحيح",
			date: "رجاء إدخال تاريخ صحيح",
			dateISO: "رجاء إدخال تاريخ صحيح (ISO)",
			number: "رجاء إدخال عدد بطريقة صحيحة",
			digits: "رجاء إدخال أرقام فقط",
			creditcard: "رجاء إدخال رقم بطاقة ائتمان صحيح",
			equalTo: "رجاء إدخال نفس القيمة",
			accept: "رجاء إدخال ملف بامتداد موافق عليه",
			maxlength: jQuery.validator.format("الحد الأقصى لعدد الحروف هو {0}"),
			minlength: jQuery.validator.format("الحد الأدنى لعدد الحروف هو {0}"),
			rangelength: jQuery.validator.format("عدد الحروف يجب أن يكون بين {0} و {1}"),
			range: jQuery.validator.format("رجاء إدخال عدد قيمته بين {0} و {1}"),
			max: jQuery.validator.format("رجاء إدخال عدد أقل من أو يساوي (0}"),
			min: jQuery.validator.format("رجاء إدخال عدد أكبر من أو يساوي (0}")
		});
	}

	$(function() {
		$('select.cs').customSelect();
		if (!Modernizr.svg) {
			$('img[src$=".svg"]').each(function() {
				$(this).attr('src', $(this).attr('src').replace('.svg', '.png'));
			});
		}
		searchBox();
		swipeCarousel();
		burgerMenu();
		login();
		cart();
		wishlist();
		closeOverlay();
		// moveCurrency();
		if ($('#resetpassword').length) {
			resetpassword();
		}

		if ($('#accordion').length) {
			accordion();
		}
		if ($('#prodSlider').length) {
			productGallery();
		}
		if ($('#formAddressCheckout').length) {
			addressStep();
		}
		if ($('#gift').length) {
			giftStep();
		}
		if ($('#formProfile').length) {
			$('#formProfile').validate();
		}
		if ($('#formChangePassword').length) {
			$('#formChangePassword').validate();
		}

		if ($('a.rate-item').length) {
			rateThis();
		}
		if ($('a.cancel-link').length) {
			cancelOrder();
		}

		if ($('a.cancel-item').length) {
			cancelItem();
		}
	});

}());