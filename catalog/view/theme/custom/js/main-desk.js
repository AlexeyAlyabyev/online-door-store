$(document).ready(function(){
	$(".popular-questions .question").click(function(){
		if ($(this).hasClass("active")) {
			$(this).next().slideToggle();
			$(this).removeClass("active");
		} else {
			$(".popular-questions .question.active").next().slideToggle();
			$(".popular-questions .question.active").removeClass("active");
			$(this).addClass("active");
			$(this).next().slideToggle();
		}
	});
});