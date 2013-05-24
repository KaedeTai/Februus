//Multilingua for the Site
var _lang_, _dict_, _dicts_ = {
	'zh-tw': {
		'title1': '請選擇廠商',
		'title2': '請選擇商品',
	},
	'en-us': {
		'title1': 'Title1',
		'title2': 'Title2',
	},
};

function L(key, target) {
	//Decide language
	_lang_ || setLang();

	//Get localized string
	if (typeof key == 'string') {
		var value = _dict_[key];
		if (typeof value == 'undefined') {
			value = key;
		}
		//Set target value
		if (typeof target == 'string') {
			doL('#' + target, _dict_[key]);
		}
		return value;
	}

	//Set values of all targets with the same key
	if (typeof key == 'undefined') {
		for (var k in _dict_) {
			doL('#' + k, _dict_[k]);
		}
	}
}

function setDict(dict) {
	//Override localized strings
	_dicts_ = mergeObj(_dicts_, dict);
	setLang();
}

function setLang(lang) {
	if (lang) {
		_lang_ = lang;
	}
	else if (!_lang_) {
		_lang_ = navigator.language.toLowerCase();
	}
	_dict_ = _dicts_[_lang_];
	if (!_dict_) {
		_dict_ = _dicts_['zh-tw'];
	}
}

function doL(selector, value) {
	var tag = $(selector);

	if (tag[0] && tag[0].nodeName.toLowerCase() == 'input') {
		var type = tag.attr('type');

		//set placeholder for input
		if (typeof type == 'undefined' || type.toLowerCase().match(/(text|email|password|number|search|tel)/)) {
			tag.attr('placeholder', value);
			return;
		}
	}

	if (tag[0] && tag[0].nodeName.toLowerCase() == 'select') {
		setSelect(selector, value);
		return;
	}

	//find the deepest span to set value
	var c;
	while ((c = tag.find('span')) && c.length > 0) {
		tag = c;
	}
	tag.first().html(value);
}

function refreshItems() {
	$('[dict]').each(function () {
		var json = $(this).attr('dict');
		var dict = jQuery.parseJSON(json);
		if (dict[_lang_]) {
			$(this).html(dict[_lang_]);
		}
	});
}