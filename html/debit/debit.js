//Multilingua for this Page
setDict({
	'zh-tw': {
		'debit':'扣點',
		'clear':'清除',
	},
	'en-us': {
		'debit':'Debit',
		'clear':'Clear',
	},
});
var ADD = 1;
var SUB = 2;
var MUL = 3;
var DIV = 4;
var SUM = 5;

var operation	= ADD;
var current		= 0;
var subTotal	    = 0;
var numFlag		= false;
var floatFlag	= false;

//Initialize
$(function() {
	// Initial Multilingua
	L();
	
	showInfo();
	
	var amount = $('#amount');
	
	$('#clear').click(function () {
		operation = ADD;
		current = 0;
		subTotal = 0;
		numFlag = false;
		floatFlag = false;
		amount.text(subTotal);
		amount.data('total', subTotal);
	});

	$('.number').click(function(e) {
		var number = $(this).data('number');
		if (numFlag) {
			numFlag = false;
			current = number;
			amount.text(current);
			return;
		}
		
		if (parseFloat(current) == 0 && current.toString().indexOf('.') == -1 ) {
			current = number;
		}
		else {
			current += '' + number;
		}
		
		amount.text(current);
	});
	
	$('#point').click(function(e) {
		if (numFlag) {
			numFlag = false;
			current = '0.';
			amount.text(current);
			return;
		} 

		if (current.toString().indexOf('.') == -1) {
			current += '.' ;
		}
		amount.text(current);
	});
	
	$('#debit').click(function(e) {
		operate(SUM);
		debit(subTotal);
	});

	$('.operator').click(function(e) {
		var operator = $(this).data('operator');
		operate(operator);
	});
	
});

function showInfo() {
	var account_id = $.cookie('account_id');
	if (!account_id) {
		location.href = '../main/main.html'; 
		return;
	}
	// Show recent info
	act('info', {account_id: account_id}, function (json) {
		if (json.error != 0 ) {
			alert(json.message);
			location.href = '../main/main.html'; 
			return;
		}
		
		$('#name').text(json.account.name);
		$('#name').show();
	});
}

/**
 * 扣點
 *
 * 這個function, 發送扣款金額
 * 
 */
function debit(total) {
	
	if(!confirm('是否執行扣點')) {
		return;
	}
	
	var account_id = $.cookie('account_id');
	if (!account_id) {
		location.href = '../main/main.html'; 
		return;
	}
	
	// 增加一筆扣款記錄
	act('add', {account_id: account_id, amount: parseFloat(total)}, function (json) {
		if (json == null || typeof(json) == "undefined" ) {
			alert('交易失敗');
			return;
		}
		
		if (json.error != 0 ) {
			alert(json.message);
			return;
		}
		//交易成功導回使用者交易明細
		location.href = '../done/done.html';  
	});

}


/** 
 *
 * 這個function, 選擇運算方式
 * 
 */
function operate(op) {
	// 重複按符號鈕
	if (numFlag) {
		operation = op;
		return;
	}
	
	numFlag = true;

	// 算出前面的數值
	subTotal = calculate(subTotal,current,operation);
	
	// 儲存狀態
	operation = op;
	current = 0;
	
	// 顯示數值
	$('#amount').text(subTotal);
}

/** 
 *
 * 這個function, 金額的運算
 * 
 */
function calculate(num1, num2, operation) {
	var sum = 0;
	num1 = parseFloat(num1);
	num2 = parseFloat(num2);
	switch (operation) {
	case ADD:
		sum = num1 + num2;
		break;
	case SUB:
		sum = num1 - num2;
		break;
	case MUL:
		sum = num1 * num2;
		break;
	case DIV:
		if (num2 != 0) {
			sum = num1 / num2;
		}
		break;
	case SUM:
		sum = num1;
		break;	
	}
	return sum;
}