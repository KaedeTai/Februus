//Multilingua for this Page
setDict({
	'zh-tw': {
		'email_title':     'Email',
		'name_title':      '姓名',
		'phone_title':     '電話號碼',
		'ok':              'OK',
		'no_email':        '請輸入正確Email!',
		'no_name':         '請輸入顧客名稱!',
		'no_phone':        '請輸入!',
	},
	'en-us': {
		'email_title':     'Email',
		'name_title':      'Name',
		'phone_title':     'Phone Number',
		'ok':              'OK',
		'no_email':        'Email is required!',
		'no_name':         'Name is required!',
		'no_phone':        'Phone number is required!',
	},
});

//Initialize
$(function() {
	// Initial Multilingua
	L();

	// Check input data
	$('#ok').click(function (e) {
		var email     = $('#email').val();
		var name      = $('#name').val();
		var phone     = $('#phone').val();
		
		// Check shop name
		if (name == '') {
			$('#name').addClass('hightlight').focus();
			alert(L('no_name'));
			return;
		}

		// Check shop address
		if (phone == '') {
			$('#phone').addClass('hightlight').focus();
			alert(L('no_phone'));
			return;
		}
		
		// Check email format
		if (!email.match(/^[0-9a-z_.-]+@[0-9a-z_.-]+\.[0-9a-z_.-]+$/i)) {
			$('#email').addClass('hightlight').focus();
			alert(L('no_email'));
			return;
		}

		// Call server
		act('ok', {email: email, name: name, phone: phone}, function (json) {
			// TODO
			//console.log(json);
			if (json && json.error == 0) {
				if (device.Android()) {
					android.onComplete();
				} else if (device.iOS()) {
					window.location = "objc://registerSuccess";
				}
			}
			
		});
	});

	// Unset highlight
	$('input').on('change', function (e) {
		$(e.target).removeClass('hightlight');
	});
});
