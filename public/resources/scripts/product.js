function productlist(url,firstload){
  var page = getParameterByName('page');

  if (page==null || firstload==1){
    page=1;
  }
	var urlarr = location.pathname.split('/');
  var produrl = base_url + 'product/';

  var KD = $("#hdncurrencycode").val();
  var currencyrate = $("#hdncurrencyrate").val();
  var ResultsFound = 'Results Found';
  var NoProduct = 'No products found !!!';     
  var OtherFilter ='Please check other filters.';  
  var SOLDOUT = 'SOLD OUT';
   if ($("#base_url").val().indexOf('/ar') > -1){
      ResultsFound = 'نتائج البحث';
      OtherFilter = 'الرجاء اختيار فلتر آخر';
      NoProduct = 'لم نجد أي منتجات!!!';
      SOLDOUT = 'نفدت الكمية';
  } 
	$.ajax({
	    type: "GET",
      data:'firstload=' + firstload,
	    url: url,
	    success: function (response) {
          if (page==1){
            var target = $(".breadcrumb");
            $("html, body").animate({ scrollTop: target.offset().top }, 600);
          }
	        if(response) {
              if (response=='FALSE'){
                $('.catalog-items').html('<div class="no-results"><h3>'+NoProduct+'</h3><p class="small">'+OtherFilter+'</p></div>');
              } else {
  	            var obj = $.parseJSON(response);
                var prodlist = ''; 
                var photo = '';
               

  	            $.each(obj["products"], function(k, v) {
                  photo = v.photo1;
                  if (photo=='')
                    photo = 'noimage.jpg';
  	              prodlist += '<div class="col-xs-6 col-sm-4"><div class="product-item"><a href="' + produrl + v.categorycode + '/' + v.productcode+'"><span class="product-image"><img src="'+resourceurl+'storage/thumb-'+photo+'" alt="'+v.title+'"></span>';
  	              prodlist += '<span class="product-content"><span class="product-title">'+v.title+'</span><span class="product-price">';
  	              if (v.discount>0)
  	                prodlist += '<span class="standard-price">' + KD + ' '+ parseFloat(v.price * currencyrate).toFixed(2)  +'</span>';
  	              prodlist += '<span class="actual-price">' + KD + ' '+  parseFloat(v.sellingprice * currencyrate).toFixed(2) +'</span>';
  	              prodlist += '</span>';
  	              prodlist += '</span>';
                  if (v.discount>0){
                    prodlist += '<span class="product-discount">-';                   
                    prodlist += removeTrailingZeros(v.discount);
                    prodlist += '%</span>';
                  }
                  if (v.qty<=0){
                    prodlist += '<p class="sold-out">'+SOLDOUT+'</p>';
                  }
                  prodlist += '</a></div></div>';
  	            });
                 if(page==1)
  	               $('.catalog-items').html(prodlist);
                else
                  $('.catalog-items').append(prodlist);

                
                $('.results-num').html(obj["productcnt"] + ' ' + ResultsFound);

                $('#colFilters').html(obj["sidefilter"]);

                if (obj["totalpage"] > page)
                  $('.catalog-footer').removeClass('hidden');
                else
                  $('.catalog-footer').addClass('hidden');

              }
	        }else{
            $('.catalog-items').html('nodata');
          }
	      
	    }
	});
}

function removeTrailingZeros(value) {
    value = value.toString();

    if (value.indexOf('.') === -1) {
        return value;
    }

    while((value.slice(-1) === '0' || value.slice(-1) === '.') && value.indexOf('.') !== -1) {
        value = value.substr(0, value.length - 1);
    }
    return value;
}

function queryStringUrlReplacement(url, param, value) 
{
    var re = new RegExp("[\\?&]" + param + "=([^&#]*)"), match = re.exec(url), delimiter, newString;

    if (match === null) {
        // append new param
        var hasQuestionMark = /\?/.test(url); 
        delimiter = hasQuestionMark ? "&" : "?";
        newString = url + delimiter + param + "=" + value;
    } else {
        delimiter = match[0].charAt(0);
        newString = url.replace(re, delimiter + param + "=" + value);
    }

    return newString;
}

function removeURLParameter(url, parameter) {
    //prefer to use l.search if you have a location/link object
    var urlparts= url.split('?');   
    if (urlparts.length>=2) {

        var prefix= encodeURIComponent(parameter)+'=';
        var pars= urlparts[1].split(/[&;]/g);

        //reverse iteration as may be destructive
        for (var i= pars.length; i-- > 0;) {    
            //idiom for string.startsWith
            if (pars[i].lastIndexOf(prefix, 0) !== -1) {  
                pars.splice(i, 1);
            }
        }

        url= urlparts[0]+'?'+pars.join('&');
        return url;
    } else {
        return url;
    }
}


function GetFilters() {
  
      var data = {},
          fdata = [],
          loc = window.location.href.split('?')[0];  
      //loc = $('<a>', { href: window.location })[0];
      $('input[type="checkbox"]').each(function (i) {
          if (this.checked) {
              if (!data.hasOwnProperty(this.name)) {
                  data[this.name] = [];
              }
              data[this.name].push(this.value);
          }
      });
      if($(".price:checked").val()!=undefined){
        data['price']=[];
        data['price'].push($(".price:checked").val());
      }

      // get all keys.
      var keys = Object.keys(data);
      var fdata = "";
      // iterate over them and create the fdata
      keys.forEach(function(key,i){
          if (i>0) fdata += '&'; // if its not the first key add &
          fdata += key+"="+data[key].join(',');
      });

      var keyword = getParameterByName('keyword');
      if (keyword!=null){
        if(fdata!='')
          fdata += "&keyword="+ keyword
        else
          fdata = "keyword="+ keyword
      }

    var category = getParameterByName('category');
    if (category!=null){
      if(fdata!='')
        fdata += "&category="+ category
      else
        fdata = "category="+ category
    }

    var main = getParameterByName('main');
    if (main!=null){
      if(fdata!='')
        fdata += "&main="+ main
      else
        fdata = "main="+ main
    }

    var sortby =  $("#sortby").val();
    if (sortby=='relevance')
      sortby = '';
    if (sortby!='')
    {
      if(fdata!='')
        fdata += "&sortby="+ sortby
      else
        fdata = "sortby="+ sortby
    }
    
    
    
    var url = '';
	  var urlarr = loc.split('/');
    
    if (urlarr[4]=="search")
      url = base_url + 'prodlisting/search?' + fdata;
    else if (urlarr[4]=="whatsnew")
      url = base_url + 'prodlisting/whatsnew?' + fdata;
    else if (urlarr[4]=="sale")
      url = base_url + 'prodlisting/sale?' + fdata;
    else if (urlarr[4]=="occassion")
	    url = base_url + 'prodlisting/occassion/' + urlarr[5] + '?' + fdata;
	  else
		  url = base_url + 'prodlisting/category/' + urlarr[5] + '?' + fdata;

    //$("#hdnPageNo").val(1);
    productlist(url,1);
     
    if (history.pushState) {
      if(fdata!='')
      	url = loc + '?' + fdata;
  	  else
		    url = loc;
     // url = base_url.slice(0,-3) + url;
      
    	history.pushState(null, null, url);
    }
  
}

$(document).ready(function() {

  $("#showall").on("click",function(e){
     // var fdata = $(this).val();
  //  var page = $("#hdnPageNo").val();
    var page = getParameterByName('page');
    if (page==null){
      page=1;
    }
    page = parseInt(page)+1;
    e.preventDefault();
    url = queryStringUrlReplacement(window.location.href, 'page', page);
    history.pushState(null, null, url);
    var urlarr = location.pathname.split('/');
    if (urlarr[2]=="search")
      url = base_url + 'prodlisting/search' + location.search;
    else if (urlarr[2]=="occassion")
      url = base_url + 'prodlisting/occassion/' + urlarr[3] + location.search;
    else if (urlarr[2]=="whatsnew")
      url = base_url + 'prodlisting/whatsnew' + location.search;
    else if (urlarr[2]=="sale")
      url = base_url + 'prodlisting/sale' + location.search;    
    else
      url = base_url + 'prodlisting/category/' + urlarr[3] + location.search;
    
    productlist(url,0);
  });
});