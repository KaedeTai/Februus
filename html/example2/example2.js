//Define iScroll variables
var vendorScroll;
var productScroll;

//Initialize
$(function() {
	//Show products when vendor selected
	$('#vendorList').click(function (e) {
		var vendor_id = $(e.target).attr('vendor_id');
		act('getProducts', {vendor_id: vendor_id}, showProducts);
	});

	//Show product into when selected
	$('#productList').click(function (e) {
		var product_id = $(e.target).attr('product_id');
		act('/getProduct', {product_id: product_id}, showProduct);
	});

	//Goto step1
	$('#back').click(function () {
		$('body').removeClass('step2');
	});

	//Create iScroll objects to enable scrolling
	vendorScroll = scroller('vendorPane');
	productScroll = scroller('productPane');
});

function showProducts(products) {
	//Render product list
	render('#productList', '#productTmpl', {products: products});

	//Refresh iScroll object
	productScroll.refresh();

	//Goto step2
	$('body').addClass('step2');
}

function showProduct(product) {
	alert('售價:' + product.amount + '\n' + product.descr);
}
