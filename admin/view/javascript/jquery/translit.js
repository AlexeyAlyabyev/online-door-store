// version 3x for opencart-russia.ru

var ru2en = {
	fromChars : 'абвгдезиклмнопрстуфыэйхёц',
	toChars : 'abvgdeziklmnoprstufyejhec',
	biChars : {'ж':'zh','ч':'ch','ш':'sh','щ':'sch','ю':'yu','я':'ya','&':'-and-'},
	vowelChars : 'аеёиоуыэюя',
	translit : function(str) {
		str = str.replace(/[_\s\.,?!\[\](){}\\\/"':;]+/g, '-')
						.toLowerCase()
						.replace(new RegExp('(ь|ъ)(['+this.vowelChars+'])', 'g'), 'j$2')
						.replace(/(ь|ъ)/g, '');

		var _str = '';
		for (var x=0; x<str.length; x++)
			if ((index = this.fromChars.indexOf(str.charAt(x))) > -1)
				_str += this.toChars.charAt(index);
			else
				_str += str.charAt(x);
		str = _str;

		var _str = '';
		for (var x=0; x<str.length; x++)
			if (this.biChars[str.charAt(x)])
				_str += this.biChars[str.charAt(x)];
			else
				_str += str.charAt(x);
		str = _str;

		str = str.replace(/j{2,}/g, 'j')
						.replace(/[^-0-9a-z]+/g, '')
						.replace(/-{2,}/g, '-')
						.replace(/^-+|-+$/g, '');

		return str;
	}
}

function createURL(title, seo_url){
	let item_name = title.val();

	if ($("input[type=hidden][value=12]").parent().next().find("textarea").val() != undefined){
		item_name += " " + $("input[type=hidden][value=12]").parent().next().find("textarea").val();
		if ($("input[type=hidden][value=17]").parent().next().find("textarea").val() != undefined){
			item_name += " " + $("input[type=hidden][value=17]").parent().next().find("textarea").val();
		}
	}
	else if ($("input[type=hidden][value=51]").parent().next().find("textarea").val() != undefined)
		item_name += " " + $("input[type=hidden][value=51]").parent().next().find("textarea").val();

	seo_url.val(ru2en.translit(item_name));
}

$(document).ready(function(){
	if (!$("[id*='title-slug']").val() || !$('input[name *="seo_url"]').val()) {
		$("[id*='title-slug']").change(function(){
			createURL($("[id*='title-slug']"), $('input[name *="seo_url"]'));
		})
	}

	if ($("[id*='title-slug']").val() && !$('input[name *="seo_url"]').val()){
		createURL($("[id*='title-slug']"), $('input[name *="seo_url"]'));
	}

	$(".seo_refresh button").click(function(){
		createURL($("[id*='title-slug']"), $('input[name *="seo_url"]'));
	});
});
