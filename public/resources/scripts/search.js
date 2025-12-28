var pathname = window.location.pathname;
var resourceurl = $("#hdnResourceURL").val();
$(document).ready(function() {

	
	var color = getParameterByName('color');
	if (color!=null){
		arrColors = color.split(','); 
		for(var i=0; i< arrColors.length; i++){
		    $("input.color[value='"+arrColors[i]+"']").prop("checked", true);
		}
	}
	
	var size = getParameterByName('size');
	if(size!=null)
	{
		arrSizes = size.split(','); 
		for(var i=0; i< arrSizes.length; i++){
		    $("input.size[value='"+arrSizes[i]+"']").prop("checked", true);
		}
	}

  var price = getParameterByName('price');

  if(price!=null){
       $("input.price[value='"+price+"']").prop("checked", true);
  }

  $("#sortby").on("change",function(){
      var fdata = $(this).val();
      url = queryStringUrlReplacement(window.location.href, 'sortby', fdata);
      url = removeURLParameter(url,'page');
      history.pushState(null, null, url);
      var urlarr = location.pathname.split('/');      
      url = '/' + urlarr[1] + '/prodlisting/search' + location.search;
      productlist(url,1);
  });
  $(document).on('change', '[type=checkbox]', function() {
    GetFilters();
  });
  $(document).on('click', '.price', function() {
    GetFilters();
  });  
});