//Multilingua for this Page
L({
	'zh-tw': {
		'btn_create_account': '建立帳戶',
		'btn_login': '登入',
		'login_email': '電子郵件',
		'login_password': '密碼',
		'login_done': '確認',
	},
	'en-us': {
		'title1': 'Vendor?',
		'price': 'Price:',
	},
});

var gPromotionData = null;

//Initialize
//ready
$(function() {
		
	//Multilingua
	L();
	//L('btn_create_account','btn_create_account');
	
	loginPageInit();
	createAccountPageInit();
	verificationPageInit();
	transactionPageInit();
	exchangePageInit();
	promotionsPageInit();
	newPromotionPageInit();
	
	$("#logout").click(function(){
        act('logout', null , null);
        return true;
    });
});

/*
 * Login Page Event 
 */
function loginPageInit() {
	/* Login Page Event */
	$('#login').live( 'pagebeforeshow',function(event){
	    $('#login_email').val('');
	    $('#login_password').val('');
	    $('#login_done').addClass('ui-disabled');
	});
	
	$('#login_email').bind( 'keyup', function(event, ui) {
		checkLoginPageInput();
	});
	
	$('#login_password').bind( 'keyup', function(event, ui) {
		checkLoginPageInput();
	});
	
	$("#login_done").click(function(){
    	var email = $('#login_email').val(); 
    	var password = $('#login_password').attr('value');
    	
        act('login', {email:email , password:password} , function(response) {
        	if (response && response.result) {
        		$.mobile.changePage( "#verification", { transition: "slide"} );
        	}
        });
        return false;
    });
}

function checkLoginPageInput() {
	var email = $('#login_email').val(); 
	var password = $('#login_password').attr('value');
	
	var velidate = validateEmail(email);
	if (!velidate || password == '') {
		$('#login_done').addClass('ui-disabled');
	} else {
		$('#login_done').removeClass('ui-disabled');
	}
}

function checkLogin() {
	act('checkLogin', null , function(response) {
    	if (!response || !response.result) {
    		$.mobile.changePage( "#home", { transition: "slide"} );
    	}
    });
}

/*
 * Create Account Page Event
 */
function createAccountPageInit() {
	/* Create Account Page Event */
	$('#create').live( 'pagebeforeshow',function(event){
		$('#create_shop').val('');
		$('#create_email').val('');
	    $('#create_password').val('');
	    $('#create_done').addClass('ui-disabled');
	});
	
	$('#create_shop').bind( 'keyup', function(event, ui) {
		checkCreatePageInput();
	});
	
	$('#create_email').bind( 'keyup', function(event, ui) {
		checkCreatePageInput();
	});
	
	$('#create_password').bind( 'keyup', function(event, ui) {
		checkCreatePageInput();
	});
	
	$("#create_done").click(function(){
        var formData = $("#create_form").serialize();
        
        var name = $('#create_shop').val(); 
    	var email = $('#create_email').val(); 
    	var password = $('#create_password').attr('value');
    	//{name: name,email:email , password:password} 
        act('createAccount', formData, function(response) {
        	if (response && response.result) {
        		$.mobile.changePage( "#verification", { transition: "slide"} );
        	}
        });
        return false;
    });
	
}

function checkCreatePageInput() {
	var name = $('#create_shop').val(); 
	var email = $('#create_email').val(); 
	var password = $('#create_password').attr('value');
	
	var velidate = validateEmail(email);
	if (name =='' || !velidate || password == '') {
		$('#create_done').addClass('ui-disabled');
	} else {
		$('#create_done').removeClass('ui-disabled');
	}
}

/*
 * Verification Page Event
 */
function verificationPageInit() {
	
	$('#verification').live( 'pagebeforeshow',function(event){
		$('#verification_code').val('');
		$('#verification_done').addClass('ui-disabled');
		
		checkLogin();
	});
	
	$('#verification_code').bind( 'keyup', function(event, ui) {
		
		var code = $('#verification_code').val(); 
		
		if (code == '') {
			$('#verification_done').addClass('ui-disabled');
		} else {
			$('#verification_done').removeClass('ui-disabled');
		}
	});
}

/*
 * Transaction Page Event
 */
function transactionPageInit() {
	
	$('#transaction').live( 'pagebeforeshow',function(event){
		$('#transaction_amount').val('');
		$('#transaction_phone').val('');
		$('#transaction_done').addClass('ui-disabled');
		
		checkLogin();
	});
	
	$("#transaction_done" ).bind( "click", function(event, ui) {
		var amount = $('#transaction_amount').val(); 
		
		$('#order_total').text('$' + amount);
		
		$('#order_plan0').attr('amount' , (amount - 6));
		$('#order_plan0 p').html('$'+ amount + '-$6=' + '$' + (amount - 6) +'(AND EARN 1 PT)' + '<span id="order_price0" class="ui-li-aside">$' + (amount - 6) +'</span>');
		$('#order_plan1').attr('amount' , amount);
		$('#order_price1').text('$' + amount);
	});
	
	$('#transaction_amount').bind( 'keyup', function(event, ui) {
		checkTransactionInput();
	});
	
	$('#transaction_phone').bind( 'keyup', function(event, ui) {
		checkTransactionInput();
	});
}

function checkTransactionInput() {
	var amount = $('#transaction_amount').val(); 
	var phone = $('#transaction_phone').attr('value');
	
	if (amount == '' || phone == '') {
		$('#transaction_done').addClass('ui-disabled');
	} else {
		$('#transaction_done').removeClass('ui-disabled');
	}
}

/*
 * Exchagne Page Event
 */
function exchangePageInit() {

	$('#exchange').live( 'pagebeforeshow',function(event){
		$('#exchange_amount').val('');
		$('#exchange_points').val('');
		$('#exchange_amount2').val('');
		$('#exchange_save').addClass('ui-disabled');
		
		checkLogin();
	});
	
	$('#exchange_checkbox').bind( 'change', function(event, ui) {

		if ($(this).is(':checked')) {
			$('#exchange_form').hide();
			$('#exchange_save').removeClass('ui-disabled');
		} else {
			$('#exchange_form').show();
			checkExchangeInput();
		}
		
	});
	
	$('#exchange_amount').bind( 'keyup', function(event, ui) {
		checkExchangeInput();
	});
	
	$('#exchange_points').bind( 'keyup', function(event, ui) {
		checkExchangeInput();
	});
	
	$('#exchange_amount2').bind( 'keyup', function(event, ui) {
		checkExchangeInput();
	});
}

function checkExchangeInput() {
	var amount = $('#exchange_amount').val(); 
	var amount2 = $('#exchange_amount2').val(); 
	var points = $('#exchange_points').attr('value');
	
	if (amount =='' || amount2 == '' || points == '') {
		$('#exchange_save').addClass('ui-disabled');
	} else {
		$('#exchange_save').removeClass('ui-disabled');
	}
}

/*
 * Promotion Page Event
 */
function promotionsPageInit() {
	
	$('#promotions').live( 'pagebeforeshow',function(event){		
		
		act('checkLogin', null , function(response) {
	    	if (!response || !response.result) {
	    		$.mobile.changePage( "#home", { transition: "slide"} );
	    	} else {
	    		act('getPromotions', null , function(response) {
	            	if (response) {
	            		var data = [];
	            		for (var i = 0; i < response.length; i++) {
	            			var title = response[i].name + ' ($' + response[i].amount + '=' + response[i].point + 'pt)';
	            			var subTitle = response[i].start_date + ' - ' + response[i].end_date;
	            			data.push({promotion_id: response[i].promotion_id , promotion_name: title, promotion_date: subTitle});
	            		}
	            		showPromotions(data);
	            	}
	            });
	    	}
	    });
		
	});
	
	$('#promotionList').click(function (e) {
		var promotion_id = $(e.target).attr('promotion_id');
		act('getPromotionInfo', {promotion_id : promotion_id} , function(response) {
        	if (response) {
        		var data = null;
        		for (var i = 0; i < response.length; i++) {
        			data = {
        				promotion_id : promotion_id,
        				name : response[i].name,
        				point : response[i].point,
        				amount : response[i].amount,
        				start_date : response[i].start_date,
        				end_date : response[i].end_date,
        			};
        		}
        		gPromotionData = data;
        		$.mobile.changePage( "#new_promotion", { transition: "slide"} );
        	}
        });
	});
}

function showPromotions(promotions) {
	//Render product list
	render('#promotionList', '#promotionTmpl', {promotions: promotions});
	$('#promotionList').listview('refresh');
}

/*
 * New Promotion Page Event
 */

function newPromotionPageInit() {
	
	$('#new_promotion').live( 'pagebeforeshow',function(event){
		
		if (gPromotionData) {
			$('#new_promotion_name').val(gPromotionData.name);
			$('#new_promotion_points').val(gPromotionData.point);
			$('#new_promotion_amount').val(gPromotionData.amount);
			$('#new_promotion_start_date').val(gPromotionData.start_date);
			$('#new_promotion_end_date').val(gPromotionData.end_date);
			// a 標籤做的 button , jquery 會多放一層 span
			$('#new_promotion_create span').text('SAVE CHANGE');
			
			window.sessionStorage.setItem("status", "edit");
			window.sessionStorage.setItem("promotion_id", gPromotionData.promotion_id);
		} else {
			$('#new_promotion_name').val('');
			$('#new_promotion_points').val('');
			$('#new_promotion_amount').val('');
			$('#new_promotion_start_date').val('');
			$('#new_promotion_end_date').val('');
			$('#new_promotion_create span').text('CREATE PROMOTION');
			$('#new_promotion_create').addClass('ui-disabled');
			
			window.sessionStorage.setItem("status", "add");
			window.sessionStorage.setItem("promotion_id", 0);
		}
		
		gPromotionData = null;
		checkLogin();
	});
	
	$("#new_promotion_create" ).bind( "click", function(event, ui) {
		var name = $('#new_promotion_name').val(); 
		var points = $('#new_promotion_points').val(); 
		var amount = $('#new_promotion_amount').attr('value');
		var start = $('#new_promotion_start_date').attr('value');
		var end = $('#new_promotion_end_date').attr('value');
		
		var status = window.sessionStorage.status;
		var promotion_id = window.sessionStorage['promotion_id'];
		
		var data = {
			promotion_id : promotion_id,
			name : name,
			point : points,
			amount : amount,
			start_date : start,
			end_date : end
		};
		
		if (status == 'edit') {
			act('editPromotion', data , function(response) {

	        });
		} else {
			act('addPromotion', data , function(response) {

	        });
		}
	});
	
	$('#new_promotion_name').bind( 'keyup', function(event, ui) {
		checkNewPromotionInput();
	});
	
	$('#new_promotion_points').bind( 'keyup', function(event, ui) {
		checkNewPromotionInput();
	});
	
	$('#new_promotion_amount').bind( 'keyup', function(event, ui) {
		checkNewPromotionInput();
	});
	
	$('#new_promotion_start_date').bind( 'change', function(event, ui) {
		checkNewPromotionInput();
	});
	
	$('#new_promotion_end_date').bind( 'change', function(event, ui) {
		checkNewPromotionInput();
	});
}

function checkNewPromotionInput() {
	var name = $('#new_promotion_name').val(); 
	var points = $('#new_promotion_points').val(); 
	var amount = $('#new_promotion_amount').attr('value');
	var start = $('#new_promotion_start_date').attr('value');
	var end = $('#new_promotion_end_date').attr('value');
	
	if (name =='' || points == '' || amount == '' || start == '' || end == '') {
		$('#new_promotion_create').addClass('ui-disabled');
	} else {
		$('#new_promotion_create').removeClass('ui-disabled');
	}
}
/*
 * 
 */
function validateEmail(email) { 
    var reg = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	return reg.test(email);
} 