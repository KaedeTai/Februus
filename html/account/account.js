//Multilingua for this Page
setDict({
	'zh-tw': {
		'recent': '最新交易',
		'total':  '餘額',
		'debit':  '消費',
		'topup':  '儲值',
	},
	'en-us': {
		'recent': 'Recent',
		'total':  'Total',
		'debit':  'Debit',
		'topup':  'Top up',
	},
});

//Initialize
$(function() {
	// Initial Multilingua
	L();

	showInfo();
});

function showInfo() {
	var account_id = $.cookie('account_id');
	if (!account_id) {
		location.href = '/main/main.html';
		return;
	}
	// Show recent info
	act('info', {account_id: account_id}, function (json) {
		$.cookie('account_name', json.account.name, {path: '/'});
		
		if (json.account.balance <= 0 ) {
			$('#debit').attr('disabled', 'disabled');
		}
		else {
			$('#debit').click(function (e) {
				location.href = '/debit/debit.html';
			});
		}
		$('#topup').click(function (e) {
			location.href = '/topup/topup.html';
		});

		if (json.account.trans == 0 ) {
			$('.ui-block:nth-child(1), #panel, #recentTab').hide();
			$('.ui-block:nth-child(2)').addClass('ui-block-notTrans');
			return;
		}
		
		$('#notTransTab').hide();
		$('#name').text(json.account.name);
		$('#balance').text('$' + fmoney(json.account.balance));
		
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
		a.amount = '$' + fmoney(a.amount);
		results.push(a);
	}
	return results;
}

/**
 * 格式化數字
 *
 * 這個function, 可直接把數字增加千分位, 例如: 1000.01 轉換為 1,000.01 或 1,000 等格式
 * @global string balance		金額
 * @global string len_float		小數點後幾碼
 * @return string str_return	格式化字串
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

