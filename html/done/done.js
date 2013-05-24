//Multilingua for this Page
setDict({
	'zh-tw': {
		'debit': '扣點',
		'topup': '儲值',
		'recent': '最新交易',
		'amount':  '金額',
		'balance':  '餘額',
	},
	'en-us': {
		'debit': 'Debit',
		'topup': 'Topup',
		'recent': 'Recent',
		'amount':  'Amount',
		'balance':  'Total',
	},
});

//Initialize
$(function() {
	// Initial Multilingua
	L();

	showInfo();
	
	//TODO : 
	$('#debit').click(function () {
		location.href = '../debit/debit.html';
	});
	
});

function showInfo() {
	var account_id = $.cookie('account_id');
	if (!account_id) {
		//TODO
		return;
	}
	
	// Show recent info
	act('info', {account_id: account_id}, function (json) {
		
		if (json.error != 0 ) {
			alert(json.message);
		}
		if (json.account.balance <= 0 ) {
			$('#debit').attr('disabled', 'disabled');
		}
		
		//顯示交易類型
		var type = '';
		if (json.account.type < 0) {
			$('#debit').show();
			type = ' - ';
		}
		else {
			$('#topup').show();
			type = ' + ';
		}
		
		if (json.account.trans == 0 ) {
			$('.ui-block:nth-child(1), #panel, #recentTab').hide();
			$('.ui-block:nth-child(2)').addClass('ui-block-notTrans');
			return;
		}
		
		$('#notTransTab').hide();
		$('#name').text(json.account.name);
		$('#amount_dollar').text(type + '$' + fmoney(json.account.amount));
		$('#balance_dollar').text('$' + fmoney(json.account.balance));
		
		console.log(json);
		json.trans = find(json);
		render('#transList', '#transTmpl', json);
	});
}

//格式化交易金額
function find(json) {
	var results = [];
	for (i in json.trans) {
		var a = json.trans[i];
		var type = ' - ';
		if (a.type > 0) {
			type = ' + ';
		}
		a.amount = type + '$' + fmoney(a.amount);
		results.push(a);
	}
	return results;
}

/**
 * 格式化數字
 *
 * 這個function, 可直接把數字增加千分位, 例如: 1000.01 轉換為 1,000.01 或 1,000 等格式
 * @param	string balance		金額
 * @param	string len_float	小數點後幾碼
 * @return	string str_return	格式化字串
 */
function fmoney(balance, len_float)  {
	len_float	= len_float >= 0 && len_float <= 20 ? len_float : 0;
	balance	= parseFloat((balance + "").replace(/[^\d\.-]/g, "")).toFixed(len_float) + "";
	
	var str_balance = balance.split(".")[0].split("").reverse(),
		len_balance = str_balance.length,
		float = balance.split(".")[1],
		tmp = "",
		str_return ='';
	
	for(var i = 0; i < len_balance; i ++ ) {
		tmp += str_balance[i] + ((i + 1) % 3 == 0 && (i + 1) != str_balance.length ? "," : "");
	}
	
	str_return = tmp.split("").reverse().join("");
	if (typeof(float) != 'undefined') {
		str_return += "." + float;
	}
	
	return str_return;  
} 

