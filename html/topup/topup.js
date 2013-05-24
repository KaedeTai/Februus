//Multilingua for this Page
setDict({
	'zh-tw': {
		'add': '新增',
	},
	'en-us': {
		'add': 'Add',
	},
});

//Initialize
$(function() {
	// Initial Multilingua
	L();

	var account_id = $.cookie('account_id');
	var account_name = $.cookie('account_name');
	if (!account_id) {
		location.href = '/main/main.html';
		return;
	}
	$('#name').text(account_name);

	$('#add').click(function (e) {
		var amount = $.cookie('amount');
		act('add', {account_id: account_id, amount: amount}, function (json) {
			location.href = '/account/account.html';
		});
	});

	$('#amount').focus();
});
