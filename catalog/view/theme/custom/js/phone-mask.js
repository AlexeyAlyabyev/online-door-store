$(document).ready(function(){
	$.mask.definitions['~']='[9]';
	$('input[type="tel"]').mask("+7 (~99) 999-99-99");
	$('input[type="tel"]').click(function(){
		$(this).setCursorPosition(4);
	});
});

$.fn.setCursorPosition = function (pos) {
	if ($(this).get(0).setSelectionRange) {
		$(this).get(0).setSelectionRange(pos, pos);
	} else if ($(this).get(0).createTextRange) {
		var range = $(this).get(0).createTextRange();
		range.collapse(true);
		range.moveEnd('character', pos);
		range.moveStart('character', pos);
		range.select();
	}
};