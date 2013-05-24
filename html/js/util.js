function htmlEncode(value) {
	return $('<div/>').text(value).html();
}

function htmlDecode(value) {
	return $('<div/>').html(value).text();
}

function setSelect(selector, data) {
	var o = $(selector);
	var i = o[0].selectedIndex;
	var cnt = 0;

	$(selector).html('');
	for (var key in data) {
		$('<option>', {value: key, text: data[key]}).appendTo(selector);
		cnt ++;
	}
	if (i > -1 && i < cnt) {
		o[0].selectedIndex = i;
	}
	
	// jquery mobile refresh select menu
	if ($(selector).selectmenu) {
		$(selector).selectmenu('refresh');
	}
}

function mergeObj(o1, o2) {
	var o = o1;
	for (var k in o2) {
		if (k in o1 && typeof o1[k] == 'object' && typeof o2[k] == 'object') {
			o[k] = mergeObj(o1[k], o2[k]);
		}
		else {
			o[k] = o2[k];
		}
	}
	return o;
}

function scroller(id) {
	if (id.indexOf('#') == 0) {
		id = id.substring(1);
	}
	var s = new iScroll(id);
	setTimeout(function() {
		s.refresh();
	}, 500);
	return s;
}

function render(id, tmpl, view) {
	$(id).html(Mustache.render($(tmpl).html(), view));

	//for multilingua support
	refreshItems && refreshItems();
}

function loadjs(filename) {
	var fileref=document.createElement('script');
	fileref.setAttribute("type","text/javascript");
	fileref.setAttribute("src", filename);
	if (typeof fileref!="undefined") {
		document.getElementsByTagName("head")[0].appendChild(fileref);
	}
}

function loadcss(filename) {
	var fileref=document.createElement("link");
	fileref.setAttribute("rel", "stylesheet");
	fileref.setAttribute("type", "text/css");
	fileref.setAttribute("href", filename);
	if (typeof fileref!="undefined") {
		document.getElementsByTagName("head")[0].appendChild(fileref);
	}
}

/*
 * Error code
 * -99 : system busy
 * -1  : no session
 * 0   : success
 * 1   : loging failed
 */
function act(action, data, success, async, session_timeout) {
	async = async && true;
	//data.XDEBUG_SESSION_START = 'ECLIPSE_DBGP';
	
	$.ajax({
		url: action,
		type: 'post',
		data: data,
		dataType: 'json',
		async: async,
		success: function(json) {
			console.log(json);
			if (json && json.error == -99) {
				console && console.log(json);
				alert('System busy! Please try again later!');
				return;
			}
			if (json && json.error == -1) {
				if (device && device.Android()) {
					android.onLogout();
				}
				else if (device && device.iOS()) {
					window.location = 'objc://onLogout';
				}
				return;
			}

			//成功的狀態
			success && success(json);
		},
		error: function(xhr, msg) {
			console && console.log(xhr.responseText);
			// 連線發生錯誤狀態
			// 1. 手機沒有連線狀態
			// 2. 無法連接伺服器
			if (device.Android()) {
				android.onError();
			} else if (device.iOS()) {
				window.location = 'objc://onError';
			}
		}
	});
}
