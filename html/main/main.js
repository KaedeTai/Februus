//Multilingua for this Page
setDict({
	'zh-tw': {
		'recent':   '最新交易',
		'more':     '顯示更多',
		'notfound': '找不到您輸入的關鍵字',
	},
	'en-us': {
		'recent':   'Recent',
		'more':     'More',
		'notfound': 'Not found!',
	},
});

//Static variables
var accounts;
var total;
var count = 5;
var more = 10;

//Initialize
$(function() {
	// Initial Multilingua
	L();

	showRecent();

	$('#accountList').click(selected);
	$('#resultList').click(selected);
	$('#search').on('keyup', search);
	$('#more').click(function () {
		count += more;
		showRecent();
	});
});

function showRecent() {
	// Show recent info
	act('accounts', {start: 0, count: count}, function (json) {
		accounts = json.accounts;
		total = json.total;
		if (count < total) {
			$('#more').show();
		}
		else {
			$('#more').hide();
		}
		render('#accountList', '#accountTmpl', json);
	});
}

function selected(e) {
	var account_id = $(e.target).attr('account_id');
	if (!account_id) {
		account_id = $(e.target).parents('li').attr('account_id');
	}
	$.cookie('account_id', account_id, {path: '/'});
	
    location.href = '../account/account.html';  
}

function search(e) {
	var key = $('#search').val();
	if (key == '') {
		$('#recentTab').show();
		$('#resultTab').hide();
		return;
	}

	act('accounts', {key: key}, function (json) {
		$('#recentTab').hide();
		$('#resultTab').show();

		if (json.total == 0) {
			$('#resultList').hide();
			$('#notfound').show();
			return;
		}

		json.results = find(json, key);
		render('#resultList', '#resultTmpl', json);
		$('#resultList').show();
		$('#notfound').hide();
	});
}

function find(json, key) {
	var results = [];
	for (i in json.accounts) {
		var a = json.accounts[i];
		a.name = mark(a.name, key);
		a.email = mark(a.email, key);
		a.phone = mark(a.phone, key);
		results.push(a);
	}
	return results;
}

function mark(str, key) {
	var html = '';
	while ((i = str.toLowerCase().indexOf(key.toLowerCase())) >= 0) {
		var pre = str.substr(0, i);
		html += htmlEncode(pre);
		html += '<span class="mark">';
		html += htmlEncode(str.substr(i, key.length));
		html += '</span>';
		str = str.substring(i + key.length);
	}
	html += htmlEncode(str);
	return html;
}