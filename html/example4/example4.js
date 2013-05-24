var jQT = $.jQTouch({
	icon: 'icon.png',
	startupScreen: 'startup.png'
});

// Some sample Javascript functions:
$(function(){
	function submitForm(){
		$el = $('#add form');
		if ($('#todo', $el).val().length > 1) {
			var itemid = $('#vendorPane ul li').length + 1;
			$('#home .incomplete').append($('<li><input type="checkbox" /> <span>' + $('#todo', $el).val() + '</span></li>'));
		}
		jQT.goBack();
		$el.get(0).reset();
		return false;
	}
	$('#add form').submit(submitForm);
	$('#add .whiteButton').click(submitForm);
	$('.complete li, .incomplete li').bind('swipe', function(){
		$(this).toggleClass('editingmode');
	});
	
	$('input[type="checkbox"]').live('change', function(){
		var $el = $(this);
		if ($el.attr('checked')) {
			$el.parent().prependTo('.complete');
		} else {
			$el.parent().appendTo('.incomplete');
		}
	});
	
	//Show products when vendor selected
	$('#vendorList').click(function (e) {
		var vendor_id = $(e.target).attr('vendor_id');
		act('getProducts', {vendor_id: vendor_id}, showProducts);
	});

	//Show product into when selected
	$('#productList').click(function (e) {
		var product_id = $(e.target).attr('product_id');
		var amount = $(e.target).attr('amount');
		alert('售價:' + amount);
	});
});

function showProducts(products) {
	//Render product list
	render('#productList', '#productTmpl', {products: products});
	jQT.goTo('#step2', 'slideleft');
}
