//Multilingua for this Page
setDict({
	'zh-tw': {
		'title1': '哪一家?',
		'price': '售價:',
		'choose': '選擇語言:',
		'lang': {
			'zh-tw': '正體中文',
			'en-us': '英文',
		},
	},
	'en-us': {
		'title1': 'Vendor?',
		'price': 'Price:',
		'choose': 'Choose your language:',
		'lang': {
			'zh-tw': 'Traditional Chinese',
			'en-us': 'English',
		},
	},
});

//Define iScroll variables
var vendorScroll;
var productScroll;

//Initialize
$(function() {
	//Multilingua
	L();
	//L('title1');
	//L('title1', '#title1');

	//Show products when vendor selected
	$('#vendorList').click(function (e) {
		var vendor_id = $(e.target).attr('vendor_id');
		act('getProducts', {vendor_id: vendor_id}, showProducts);
	});

	//Show product into when selected
	$('#productList').click(function (e) {
		var product_id = $(e.target).attr('product_id');
		var amount = $(e.target).attr('amount');
		alert(L('price') + amount);
	});

	//Goto step1
	$('#back').click(function () {
		$('body').removeClass('step2');
	});

	//Change language
	$('#lang').change(function () {
		setLang($('#lang').val());
		L();

		//Read attribute dict and update content
		refreshItems();
	});

	//Create iScroll objects to enable scrolling
	vendorScroll = scroller('vendorPane');
	productScroll = scroller('productPane');
});

function showProducts(json) {
	//Render product list
	render('#productList', '#productTmpl', json);

	//Refresh iScroll object
	productScroll.refresh();

	//Goto step2
	$('body').addClass('step2');
}
