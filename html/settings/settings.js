//Multilingua for this Page
setDict({
	'zh-tw': {
		'email_title':     'Email',
		'name_title':      '店家名稱',
		'address_title':   '店家地址',
		'password_title':  '密碼',
		'message':         '請輸入六位數以上的密碼, 當您的消費者忘記密碼時, 您可以透過這個密碼重新傳送密碼給消費者',
		'ok':              'OK',
		'no_email':        '請輸入正確Email!',
		'no_name':         '請輸入店家名稱!',
		'no_address':      '請輸入店家地址!',
		'not_match':       '您兩次輸入的密碼不相同! 請重新輸入!',
		'too_short':       '密碼太短! 請輸入至少六位數密碼!',
	},
	'en-us': {
		'email_title':     'Email',
		'name_title':      'Shop Name',
		'address_title':   'Shop Address',
		'password_title':  'Password',
		'message':         'Please input at least 6 digits of password. With your password, you can reset customer\'s password and send to their mobile phone if they forget their password.',
		'ok':              'OK',
		'no_email':        'Email is required!',
		'no_name':         'Shop Name is required!',
		'no_address':      'Shop Address is required!',
		'not_match':       'Passwords do not match! Please re-input the PIN again!',
		'too_short':       'Password is too short! At least 6 digits is reqired!',
	},
});

//Initialize
$(function() {
	// Initial Multilingua
	L();
	
	act('info', {}, function (json) {
		
		if (json && json.error == 0) {
			
			if (json.email) {
				$('#email').val(json.email);
			}
			
			if (json.name) {
				$('#name').val(json.name);
			}
			
			if (json.address) {
				$('#address').val(json.address);
			}
			
			$('#password').val('########');
		}
	});
	
	// Check input data
	$('#ok').click(function (e) {
		var email     = $('#email').val();
		var name      = $('#name').val();
		var address   = $('#address').val();
		var password  = $('#password').val();

		// Check email format
		if (!email.match(/^[0-9a-z_.-]+@[0-9a-z_.-]+\.[0-9a-z_.-]+$/i)) {
			$('#email').addClass('hightlight').focus();
			alert(L('no_email'));
			return;
		}

		// Check shop name
		if (name == '') {
			$('#name').addClass('hightlight').focus();
			alert(L('no_name'));
			return;
		}

		// Check shop address
		if (address == '') {
			$('#address').addClass('hightlight').focus();
			alert(L('no_address'));
			return;
		}

		// Check password
		if (password.length < 6) {
			$('#password').addClass('hightlight').focus();
			alert(L('too_short'));
			return;
		}

		// Call server
		act('update', {email: email, name: name, address: address, password: password}, function (json) {
			// TODO
			if (json && json.error == 0) {
				if (device.Android()) {
					
				} else if (device.iOS()) {
					window.location = "objc://settingsSuccess";
				}
			}
		});
	});

	// Unset highlight
	$('input').on('change', function (e) {
		$(e.target).removeClass('hightlight');
	});
	
	
});
