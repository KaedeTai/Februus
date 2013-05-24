if (window.applicationCache != undefined) {
		// api can use
	if (applicationCache.status == applicationCache.UPDATEREADY) {
		// 以更新
		console.log('已更新，等待重新載入');
	} else {
		console.log('applicationCache.status = ' + applicationCache.status);
	}
} else {
		console.log('無法使用 api');
}

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

//Initialize
$(function() {
	
	L();
	
	//Show products when vendor selected
	$('#vendorList').click(function (e) {
		var vendor_id = $(e.target).attr('vendor_id');
		$('#productList').html('');
		act('getProducts', {vendor_id: vendor_id}, showProducts);
	});

	//Show product into when selected
	$('#productList').click(function (e) {
		var product_id = $(e.target).attr('product_id');
		var amount = $(e.target).attr('amount');
		if (amount) {
			alert('售價:' + amount);
		}
	});
	
	//Change language
	$('#lang').change(function () {
		setLang($('#lang').val());
		L();

		//Read attribute dict and update content
		refreshItems();
		$('#vendorList').listview('refresh');
	});
});

function showProducts(products) {
	//Render product list
	render('#productList', '#productTmpl', {products: products});
	$('ul').listview('refresh');
}
