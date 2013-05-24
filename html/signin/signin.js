//Multilingua for this Page
setDict({
	'zh-tw': {
		'email_title':     'Email',
		'password_title':  '密碼',
		'message':         '當您的消費者忘記密碼時, 您也可以透過這個密碼重新傳送密碼給消費者',
		'ok':              '登入',
		'no_email':        '請輸入正確Email!',
		'failed':          '登入失敗! 請重新輸入!',
	},
	'en-us': {
		'email_title':     'Email',
		'password_title':  'Password',
		'message':         'With your password, you can reset customer\'s password and send to their mobile phone if they forget their password.',
		'ok':              'Sign In',
		'no_email':        'Email is required!',
		'failed':          'Login failed! Please try again!',
	},
});

//Initialize
$(function() {
	// Initial Multilingua
	L();

	// Check input data
	$('#ok').click(function (e) {
		var email     = $('#email').val();
		var password  = $('#password').val();

		// Check email format
		if (!email.match(/^[0-9a-z_.-]+@[0-9a-z_.-]+\.[0-9a-z_.-]+$/i)) {
			$('#email').addClass('hightlight').focus();
			alert(L('no_email'));
			return;
		}

		// Call server
		act('ok', {email: email, password: password}, function (json) {
			// TODO
			console.log(json);
			if (json && json.error == 0) {
				if (device.Android()) {
					android.onLogin();
				} else if (device.iOS()) {
					window.location = "objc://signInSuccess";
				}
			}
			else {
				if (device.Android()) {
					
				} 
				else if (device.iOS()) {
					window.location = "objc://signInFail/" + json.error;
				} 
				
			}
			
		});
	});

	// Unset highlight
	$('input').on('change', function (e) {
		$(e.target).removeClass('hightlight');
	});
});
