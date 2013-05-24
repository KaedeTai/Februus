//Define iScroll variables
var vendorScroll;
var productScroll;

var jQT = $.jQTouch({
	icon: 'icon.png',
	startupScreen: 'startup.png'
});

// Some sample Javascript functions:
$(function(){
	
	var userAgent = navigator.userAgent.toLowerCase();
	var isiPhone = userAgent.match(/iphone os/i) != null;
	var isiPad = userAgent.match(/iPad/i) != null;
	var isAndroid = userAgent.match(/android/i) != null;  
	
	// For use within iPad developer UIWebView
	// Thanks to Andrew Hedges!
	//var ua = navigator.userAgent;
	//var isiPad = /iPad/i.test(ua) || /iPhone OS 3_1_2/i.test(ua) || /iPhone OS 3_2_2/i.test(ua);

	console.log('ipad = ' + isiPad);
	console.log('iphone = ' + isiPhone);
	console.log('android = ' + isAndroid);
	
	if (isiPhone || isAndroid) {
		// TODO do something for mobile?
	} else {
		$('#jqt').addClass('splitscreen');
		$('#menu').addClass('current');
		$('#content').addClass('current');
	}
	
	
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
	
	//Create iScroll objects to enable scrolling
	vendorScroll = scroller('menu');
	productScroll = scroller('content');
});

function showProducts(products) {
	//Render product list
	render('#productList', '#productTmpl', {products: products});
	jQT.goTo('#content', 'slideleft');
	
	//Refresh iScroll object
	productScroll.refresh();
}
