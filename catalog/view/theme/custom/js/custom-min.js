document.addEventListener("DOMContentLoaded", function () {
			(window.onload = function () {
				$(".tabs-content").css({ opacity: "1" });
			}),
			$("ul.tabs-caption").on("click", "li.active:not(.whatsapp)", function () {
					$(this).removeClass("active").removeClass("op").siblings().removeClass("op").closest("div.tabs").find("div.tabs-content").eq($(this).index()).removeClass("active"), (document.body.style.overflow = "auto");
			}),
			$("ul.tabs-caption").on("click", "li:not(.active):not(.whatsapp)", function () {
					$(this).addClass("active").removeClass("op").siblings().removeClass("active").addClass("op").closest("div.tabs").find("div.tabs-content").removeClass("active").eq($(this).index()).addClass("active"),
							(document.body.style.overflow = "hidden");
			}),
			$(".submenu .slideToggle").click(function () {
					$(this).hasClass("active") ? ($(this).parent().siblings("ul").slideToggle(), $(this).removeClass("active")) : ($(this).parent().siblings("ul").slideToggle(), $(this).addClass("active"));
			}),
			document.addEventListener("click", function e(t) {
				"search-container active" == event.target.className && $("#search-container").hasClass("active") &&
					($("#search-container").removeClass("active"),
					$("body").css("overflow", "auto"));
			}),
			$(".search_btn").click(function() {
				$("#search-container").addClass("active");
				$("body").css("overflow", "hidden");
				$("#search input").focus();
			});

			if ($(".option").length) {
				$(".minus").click(function () {
						var e = $(this).parent().find("input"),
								t = parseInt(e.val()) - 1;
						return (t = t < 1 ? 1 : t), e.val(t), e.change(), !1;
				}),
				$(".plus").click(function () {
						var e = $(this).parent().find("input");
						return e.val(parseInt(e.val()) + 1), e.change(), !1;
				});
				$('.components .components-options .option input').on("change", function(){
					calculate_set();
				});
				$("input[name='component_quantity']").on("change", function(){
					$(this).next().children("span").text( ( +$(this).val() * +$(this).next().attr("data-price").replace(/\s+/g, '') ).toLocaleString('ru') );
				});
			}

			if ($(".door-data").length){
				$(".door-data ul").on("click", "li:not(.active)", function(){
					$(".door-data ul li.active, .door-data .tabs .tab.active").removeClass("active");
					$(this).addClass("active");
					$(".door-data .tabs .tab").eq($(this).index()).addClass("active");
				});
			}
}),
	$(function () {
			}),
	$('[data-src="#modal"]').each(function () {
			$(this).on("click", function (e) {
					$(this).hasClass("product__btn-modal")
							? ($("#modal").find("p").eq(0).html("Заполните форму для связи с Вами!"), ($('[name="form_subject"]')[0].value = "Узнать стоимость"))
							: $(this).hasClass("installment")
							? ($("#modal").find("p").eq(0).html('<img class="product__installments-img" src="/catalog/view/theme/custom/image/installments.png" alt="В рассрочку" width="178" height="38">'),
								$("#modal").find("p").eq(1).html("Закажите этот товар прямо сейчас с&nbsp;Рассрочкой - без первого взноса<br> и переплат"),
								($('[name="form_subject"]')[0].value = "Рассрочка"))
							: ($("#modal").find("p").eq(0).html("Обратный звонок"),
								$("#modal").find("p").eq(1).html("Укажите свои контактные данные, мы перезвоним в удобное для Вас время и ответим на все вопросы"),
								($('[name="form_subject"]')[0].value = "Вызов замерщика/обр звонок"));
					if ($(this).text() == "Заказать монтаж") {
						$("#modal>p:first-of-type").text("Заказать монтаж");
						$("input[name='form_subject']").val("Заказать монтаж");
					}
					else if (!$(this).hasClass("installment") && !$(this).hasClass("product__btn-modal")) {
						$("#modal>p:first-of-type").text("Обратный звонок");
						$("input[name='form_subject']").val("Вызов замерщика/обр звонок");
					};
			});
	}),
	$(".product__installments").click(function () {
			$("#modal").find("p").eq(0).html('<img class="product__installments-img" src="/catalog/view/theme/custom/image/installments.png" alt="В рассрочку" width="178" height="38">'),
					$("#modal").find("p").eq(1).html("Закажите этот товар прямо сейчас с&nbsp;Рассрочкой - без первого взноса<br> и переплат"),
					($('[name="form_subject"]')[0].value = "Рассрочка");
	});
let gets = (function () {
	let e = window.location.search,
			t = new Object();
	e = e.substring(1).split("&");
	for (var n = 0; n < e.length; n++) (c = e[n].split("=")), (t[c[0]] = c[1]);
	return t;
})();
"/sber" == window.location.pathname && ($("footer").remove(), $("#error-not-found").remove(), $(".mob-menu").remove(), $(".fixed_menu").remove()),
	$("#sber_credit_pay").submit(function (e) {
			e.preventDefault();
			var t = $(this).serialize();
			(t +=
					"&orderBundle=" +
					JSON.stringify({
							cartItems: {
									items: [
											{
													positionId: 1,
													name: "Дверные полотна, фурнитура и комплектующие",
													quantity: { value: 1, measure: "шт" },
													itemPrice: 100 * +$(this).find("input[name=amount]").val(),
													itemAmount: 100 * +$(this).find("input[name=amount]").val(),
													itemCode: "1",
											},
									],
							},
							installments: { productID: "1", productType: "INSTALLMENT", rightTerms: [6] },
					})),
					console.log(t),
					$.ajax({
							type: "POST",
							url: "sber_credit_pay.php",
							data: t,
							success: function (e) {
									console.log(e);
									let t = JSON.parse(e);
									console.log(t), $(".container span strong").text(t.formUrl);
							},
					});
	}),
	$("#sber_credit_pay_cr").submit(function (e) {
			e.preventDefault();
			var t = $(this).serialize();
			(t +=
					"&orderBundle=" +
					JSON.stringify({
							cartItems: {
									items: [
											{
													positionId: 1,
													name: "Дверные полотна, фурнитура и комплектующие",
													quantity: { value: 1, measure: "шт" },
													itemPrice: 100 * +$(this).find("input[name=amount]").val(),
													itemAmount: 100 * +$(this).find("input[name=amount]").val(),
													itemCode: "1",
											},
									],
							},
							installments: { productID: "1", productType: "CREDIT" },
					})),
					console.log(t),
					$.ajax({
							type: "POST",
							url: "sber_credit_pay.php",
							data: t,
							success: function (e) {
									console.log(e);
									let t = JSON.parse(e);
									console.log(t), $(".container span strong").text(t.formUrl);
							},
					});
	}),
	$("#sber_pay").submit(function (e) {
			e.preventDefault();
			var t = $(this).serialize();
			$.ajax({
					type: "POST",
					url: "sber_pay.php",
					data: t,
					success: function (e) {
							console.log(e);
							let t = JSON.parse(e);
							(t.amount = $("#sber_pay input[name=amount]").val()),
									(t.zoneName = $("#sber_pay input[name=zoneName]").val()),
									console.log(t),
									console.log("result_end"),
									$.ajax({
											type: "POST",
											url: "sber_pay_mail.php",
											data: t,
											success: function (e) {
													console.log("Письмо отправлено!"), console.log(e);
											},
									}),
									setTimeout(function () {
											window.location.href = t.formUrl;
									}, 100);
					},
			});
	}),
	gets.orderId &&
			$.ajax({
					type: "POST",
					url: "sber_pay_mail_success.php",
					data: gets,
					success: function (e) {
							console.log(e);
					},
			}),
	$("input[name=utm_campaing]").val(gets.utm_campaign);

function addToCart(e) {
	let t = $(e);
	$.ajax({
			url: "index.php?route=checkout/cart/add",
			type: "post",
			data: t.prev(),
			dataType: "json",
			success: function (e) {
				// showNotice("Товар добавлен в корзину", "/cart");
				$(".added_to_cart").load("index.php?route=product/added_to_cart&cart_id=" + e.cart_id + " .body", function(){
					$(".added_to_cart img").eq(0).on("load", showAddedToCart);
				});
				$(".mobile_menu .navigation .item .quantity, #cart .cart__amount").html(e.total);
				e.redirect && (location = e.redirect);
				$("#cart > ul").load("index.php?route=common/cart/info ul li");
			},
			error: function (e, t, n) {
					alert(n + "\r\n" + e.statusText + "\r\n" + e.responseText);
			},
	});
}

$(".auctions-doors-nav__btn ").click(function (e) {
		e.preventDefault();
		let t = $(this);
		$.ajax({
				url: "index.php?route=checkout/cart/add",
				type: "post",
				data: t.prev(),
				dataType: "json",
				beforeSend: function () {
						t.button("loading");
				},
				success: function (e) {
					// showNotice("Товар добавлен в корзину", "/cart");
					$(".added_to_cart").load("index.php?route=product/added_to_cart&cart_id=" + e.cart_id + " .body", function(){
						$(".added_to_cart img").eq(0).on("load", showAddedToCart);
					});
					$(".mobile_menu .navigation .item .quantity, #cart .cart__amount").html(e.total);
					e.redirect && (location = e.redirect);
					$("#cart > ul").load("index.php?route=common/cart/info ul li");
				},
				error: function (e, t, n) {
						alert(n + "\r\n" + e.statusText + "\r\n" + e.responseText);
				},
		});
});

setTimeout(function () {
	$(".free-measure_pop-up").addClass("free-measure_pop-up-visible");
}, 6e4);
$(".free-measure__close").click(function () {
	$(".free-measure_pop-up").removeClass("free-measure_pop-up-visible"), $(".free-measure_pop-up").addClass("free-measure_pop-up_active");
}),

$(".price-option-checkbox").click(function () {
	if (!$(this).hasClass("border-yellow")){
		$(".price-option-checkbox").removeClass("border-yellow");

		let price = $(this).find(".price_value").text();
		price = price.trim().replace(" ","").replace(" ","");
		price= +price;

		let installment = Math.round(price/6).toLocaleString('ru');
		$("#product-product .installment span").html(installment);

		$(this).addClass("border-yellow");
		$("#components").click();
		$(document).animate({
			scrollTop: $("#components").offset().top - parseInt($(".fixed_menu").css("height"))
		}, 500);
	}
});

$(".sort button").on('click touch', function(){
	$(this).next().toggleClass("active");
});
$(".sort li").on('click touch', function(){
	$(this).next().toggleClass("active");
});

$(".sort li").each(function(index, item){
	if ($(item).attr("data-href") == window.location.href) {
		$(item).addClass("active");
	}
});
if (!$(".sort li.active").length) $(".sort li").eq(0).addClass("active");

if ($('.hit_slider .body').length) {
	$('.hit_slider .body').slick({
		prevArrow: '<button type="button" class="slick-prev"></button>',
		nextArrow: '<button type="button" class="slick-next"></button>',
		dots: true,
		slidesToShow: 5,
		slidesToScroll: 5,
		responsive: [
			{
				breakpoint: 1870,
				settings: {
					slidesToShow: 4,
					slidesToScroll: 4,
				}
			},
			{
				breakpoint: 1500,
				settings: {
					slidesToShow: 3,
					slidesToScroll: 3,
				}
			},
			{
				breakpoint: 1110,
				settings: {
					slidesToShow: 2,
					slidesToScroll: 2,
					dots: true,
				}
			},
			{
				breakpoint: 992,
				settings: {
					slidesToShow: 3,
					slidesToScroll: 3,
					dots: false,
				}
			},
			{
				breakpoint: 780,
				settings: {
					slidesToShow: 2,
					slidesToScroll: 2,
					dots: false,
				}
			},
			{
				breakpoint: 520,
				settings: {
					slidesToShow: 1,
					slidesToScroll: 1,
					dots: false,
				}
			}
		]
	});
}

// Отзывы после выполнения заказа
$(document).ready(function () {
	$("#feedback").submit(function () {
		let self = $(this);
		let bonus_text = $('#text-5-stars');
		let all_text = $('#text-all');
		var star;
		if ($('#star-5').prop("checked")){
			star = 1;
		} else
			star = 0;
		$.ajax({
			type: "POST",
			url: '/feedback-order.php',
			data: self.serialize(),
			beforeSend: function () {
				// Вывод текста в процессе отправки
				self.html('<p style="text-align:center">Отправка...</p>');
			},
			success: function (data) {
				// Вывод текста результата отправки
				self.html('');
				if (star == 1){
					self.append(bonus_text);
				} else
					self.append(all_text);
			},
			error: function (jqXHR, text, error) {
				// Вывод текста ошибки отправки
				self.html(error);
			}
		});
		return false;
	});
});
// Отзывы после выполнения заказа

function calculate_set() {
	let count = 0;
	$('.components .components-options .option input[type="checkbox"]:checked+label').each(function(){
		count += parseInt($(this).siblings(".price").attr("data-price").replace(/\s+/g, '')) * parseInt($(this).siblings("input[name='component_quantity']").val());
	});
	count += parseInt($("#calc-price").text().replace(/\s+/g, ''));

	let installment = Math.round(count/6).toLocaleString('ru');
	$("#product-product .installment span").html(installment);

	$("#calc-final").html(count.toLocaleString('ru'));
	$(".sum span").html(count.toLocaleString('ru'));
}
// ----------------------------------------------			НОВАЯ ВЕРСИЯ САЙТА			----------------------------------------------
$(function(){

	// ---------- Взаимодействия с нижней панелькой мобильного меню ----------
	$(".mobile_menu .navigation .catalog_opener, .mobile_menu .navigation .search").click(function(){

		if (window.pageYOffset > 0) $("header").toggleClass("sticky");

		if ($(".mobile_menu .catalog").hasClass("active") && $(this).siblings().hasClass("active")) {

			if ($(this).hasClass("catalog_opener")) {
				$(".mobile_menu .navigation .item").removeClass("active");
				$("body").toggleClass("replace_scroll");
				$("html").toggleClass("no_scroll");
				$(".mobile_menu .catalog").toggleClass("active");
			}
			if ($(this).hasClass("search")) {
				$(".mobile_menu .navigation .item").removeClass("active");
				$(this).addClass("active");
				$(".mobile_menu .catalog .search input").focus();
			}
		} else {
			$(this).toggleClass('active');
			$("body").toggleClass("replace_scroll");
			$("html").toggleClass("no_scroll");
			$(".mobile_menu .catalog").toggleClass("active");
		}

		if ($(this).hasClass("search") && $(".mobile_menu .catalog").hasClass("active"))
				$(".mobile_menu .catalog .search input").focus();
	});

	// Раскрывашки в каталоге мобильного меню
	$(".mobile_menu .catalog .links_list .item button").click(function(e){
		e.preventDefault();
		if ($(this).parent().next().hasClass("subcategories"))
			$(this).parent().next().slideToggle();
	});

	// Модалка с поиском
	$(".top_menu .body .cart_and_search >img").click(searchModalShow);
	$(document).click(function(e){
		if ($(e.target).hasClass("search_modal")) searchModalHide();
	});

	// Подсветка фиксированного меню
	highlightFixedMenu();
	$(document).scroll(highlightFixedMenu);

	// Счетчик количества бесплатных замеров на главной странице
	if ($(".counter").length) {
		var next_val;
		var date = new Date();
		// console.log(+date/1000/60);
		var curr_val = Math.floor(+date/1000/60%100000);
		var processed_val = curr_val;

		$(".counter .body .numbers .number").each(function( index ){
			$(this).children().html(
				Math.floor(processed_val / Math.pow(10, 4-index))
			);
			processed_val = processed_val % Math.pow(10, 4-index);
		});

		setInterval(function(){
			date = new Date();
			next_val = Math.floor(+date/1000/60%100000);
			if (next_val > curr_val) {
				flipNumber($(".counter .body .numbers .number:last-child .top"));
				curr_val = next_val;
			}
		}, 1000);
	}

	// Видео отзывы на главной
	if ($(".happy_client").length) {

			// Ютуб API подключение в документ
			var tag = document.createElement('script');
			tag.src = "https://www.youtube.com/player_api";
			var firstScriptTag = document.getElementsByTagName('script')[0];
			firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

		$(".happy_client .item .image").click(function(){
			var player;
			player = new YT.Player($(this)[0], {
				height: 'auto',
				width: 'fit-content',
				videoId: $(this).attr("data-video"),
				events: {
					'onReady': play,
				}
			});

			function play(){
				player.playVideo();
			}
		});
	}

	// Интерктивная карта с точками филиалов на странице контактов
	if ($(".branch_offices").length) {
		ymaps.ready(function ()
		{
			var map = new ymaps.Map("map_frame", {
				center: [55.708582, 37.605294],
				zoom: 9,
				controls:['zoomControl']
			});

			map.behaviors.disable('MultiTouch');

			var offices = [];

			$(".branch_offices .map .office").each(function(index){
				offices[index] = new ymaps.Placemark([$(this).attr("data-latitude"), $(this).attr("data-longitude")], {}, {
					preset:'islands#Icon',
					iconColor:'#2C2B3F'
				});
			});

			offices.forEach(function (element, index){
				map.geoObjects.add(element);
				element.events.add('click', function (){
					offices.forEach(function (element2){
						element2.options.set('preset','islands#Icon');
						element2.options.set('iconColor','#2C2B3F');
					});
					element.options.set('preset','islands#dotIcon');
					element.options.set('iconColor','#FBD239');
					$(".branch_offices .map .office").stop().hide();
					$(".branch_offices .map .office").eq(index).stop().show();
				});
			});

			$(".branch_offices .map .office .cross").click(function(){
				$(this).parents(".office").stop().hide();
				offices[$(this).index(".cross")].options.set('preset','islands#Icon');
				offices[$(this).index(".cross")].options.set('iconColor','#2C2B3F');
			});

			if (window.innerWidth < 992) $(".branch_offices .map .office").hide();
		});
	}

	let last_activity = Date.now();
	let choise_help_interval = setInterval(function(){
		if (localStorage.getItem("dont_show_choise_help_date") == null){
			if (Date.now() - last_activity > 30000) {
				showChoiseHelp();
				clearInterval(choise_help_interval);
			}
		} else {
			if (Date.now() - last_activity > 30000 && localStorage.getItem("dont_show_choise_help_date") < Date.now() - 1000*60*60){
				showChoiseHelp();
				clearInterval(choise_help_interval);
			}
		}
	}, 1000);
	
	$(document).on("click scroll mousemove touch touchmove", function(){
		last_activity = Date.now();
	});
	$(".choise_help").click(function(e){
		if ($(e.target)[0] == $(this)[0]) closeChoiseHelp();
	});
	$(".choise_help .body form").submit(function(){
		localStorage.setItem("dont_show_choise_help_date", Date.now());		
	});

	$(".choise_help .body .cross").click(closeChoiseHelp);

	// ПОКУПКА В 1 КЛИК
	if ($("button.one_click").length){
		$("button.one_click").click(function(){
			$(".one_click_buy").load("index.php?route=product/in_one_click&product_id=" + $(this).parents("[data-id]").attr("data-id") + " .body", function(){
				$(".one_click_buy img").on("load", showOneClickBuy);
				$(".one_click_buy input[type='tel']").mask("+7 (~99) 999-99-99");
				$(".one_click_buy input[type='tel']").click(function(){
					$(this).setCursorPosition(4);
				});
			});
		});

		$(".one_click_buy").click(function(e){
			$(this)[0] == e.target ? closeOneClickBuy() : ""; 
		});
	}

	// Быстрый просмотр
	if ($("button.quick_view").length){
		$("button.quick_view").click(function(e){
			e.preventDefault();
			$(".quick_view_block").load("index.php?route=product/quick_view&product_id=" + $(this).parents("[data-id]").attr("data-id") + " .body", function(){
				$(".quick_view_block img").eq(0).on("load", showQuickView);
			});
		});

		$(".quick_view_block").click(function(e){
			$(this)[0] == e.target ? closeQuickView() : ""; 
		});
	}

	var ajax_data;
	var quick_view_data;

	if ($(".photo.swiper").length && $("#product-product .photo .swiper-slide").length > 1) {
		$('.swiper-pagination').after('<div class="swiper-button-next"></div><div class="swiper-button-prev"></div>');
		const product_photo_swiper = new Swiper('.photo', {
			slidesPerView: 1,
			pagination: {
				el: ".swiper-pagination",
				clickable: true,
			},
			navigation: {
				nextEl: ".swiper-button-next",
				prevEl: ".swiper-button-prev",
			},
		});
	}

	$(".added_to_cart").click(function(e){
		$(this)[0] == e.target ? closeAddedToCart() : ""; 
	});
});

function quickViewChooseColor(color){
	$(".quick_view_block").load("index.php?route=product/quick_view&product_id=" + $(color).attr("data-id") + " .body", function(){
		$(".quick_view_block img").eq(0).on("load", showQuickView);
	});
}

function setConfig(option){
	ajax_data = $(".one_click_buy .body form input[name='quantity'], .one_click_buy .body form input[name='product_id']");

	config_options = $(".one_click_buy .body form .info .price-option .checkbox");

	if (config_options.length > 1){
		config_options.removeClass("border-yellow");
		$(option).addClass("border-yellow");

		if ($(option).index(".one_click_buy .body form .info .price-option .checkbox")){
			$(".one_click_buy .body form input[type='checkbox']").each(function(key, item){
				ajax_data.push(item);
			});
		}
	}
}

function quickViewAddToCart(){
	if ($(".quick_view_block .body .info .price .price-option .price-option-checkbox:nth-child(2)").hasClass("border-yellow"))
		quick_view_data = $(".quick_view_block input[name='quantity'], .quick_view_block input[name='product_id'], .quick_view_block input[name^='option']:checked");
	else
		quick_view_data = $(".quick_view_block input[name='quantity'], .quick_view_block input[name='product_id'], .quick_view_block input[name^='option'][type='radio']:checked");
	$.ajax({
		url: 'index.php?route=checkout/cart/add',
		method: 'post',
		data: quick_view_data,
		success: function (e) {
			// showNotice("Товар добавлен в корзину", "/cart");
			$(".added_to_cart").load("index.php?route=product/added_to_cart&cart_id=" + e.cart_id + " .body", function(){
				$(".added_to_cart img").eq(0).on("load", showAddedToCart);
			});
			$(".mobile_menu .navigation .item .quantity, #cart .cart__amount").html(e.total);
			e.redirect && (location = e.redirect);
			$("#cart > ul").load("index.php?route=common/cart/info ul li");
		},
		error: function (e, t, n) {
				alert(n + "\r\n" + e.statusText + "\r\n" + e.responseText);
		},
	});
}

function quickViewConfig(item){
	$(".quick_view_block .body .info .price .price-option .price-option-checkbox").removeClass("border-yellow");
	$(item).addClass("border-yellow");
}
	
function oneClickBuy(){
	if ($(".one_click_buy .body form input[type='tel']").val() && $(".one_click_buy .body form input[type='tel']").val().length > 17)
		$.ajax({
			url: 'index.php?route=checkout/cart/clear',
			success: function(){
				$.ajax({
					url: 'index.php?route=checkout/cart/add',
					method: 'post',
					data: ajax_data,
					success: function(result){
						$.ajax({
							url: 'index.php?route=checkout/one_click_buy',
							method: 'post',
							data: $(".one_click_buy .body form input[type='tel']"),
							success: function(result){
								$(".one_click_buy .body >*:not(.cross)").remove();
								$(".one_click_buy .body").append("<p class='success'>Заказ <b>№" + JSON.parse(result)['order_id'] + "</b> успешно оформлен.<br>Ожидайте подтверждения от менеджера.</p>");
								$(".mobile_menu .navigation .item .quantity, #cart .cart__amount").html(0);
								$("#cart > ul").load("index.php?route=common/cart/info ul li");
							}
						});
					}
				});
			}
		});
	else 
		showNotice("Введите номер телефона!");
}

function quickViewMinus(button){
	(+$(button).siblings("input[name='quantity']").val() > 1) ? $(button).siblings("input[name='quantity']").val(+$(button).siblings("input[name='quantity']").val() - 1) : "";
}
function quickViewPlus(button){
	$(button).siblings("input[name='quantity']").val(+$(button).siblings("input[name='quantity']").val() + 1);
}

function showQuickView(){
	$(".quick_view_block").addClass("active").addClass("visible");
	$("body").addClass("replace_scroll");
	$("html").addClass("no_scroll");
	// setConfig($(".quick_view_block .body form .info .price-option .border-yellow"));

	if ($(".images.swiper").length && $(".images .swiper-slide").length > 1) {
		var quick_view_swiper = new Swiper('.images', {
			slidesPerView: 1,
			pagination: {
				el: ".swiper-pagination",
				clickable: true,
			},
		});
	}

	$(".quick_view_block .body .info .door-controls .one_click").click(function(){
		$(".one_click_buy").load("index.php?route=product/in_one_click&product_id=" + $(this).parents("[data-id]").attr("data-id") + " .body", function(){
			$(".one_click_buy img").on("load", showOneClickBuy);
			$(".one_click_buy input[type='tel']").mask("+7 (~99) 999-99-99");
			$(".one_click_buy input[type='tel']").click(function(){
				$(this).setCursorPosition(4);
			});
		});
	});
}

function closeQuickView(){
	$(".quick_view_block").removeClass("visible");
	setTimeout(function(){
		$(".quick_view_block").removeClass("active");
		$("body").removeClass("replace_scroll");
		$("html").removeClass("no_scroll");
	}, 500);
}

function showOneClickBuy(){
	$(".one_click_buy").addClass("active").addClass("visible");
	$("body").addClass("replace_scroll");
	$("html").addClass("no_scroll");
	setConfig($(".one_click_buy .body form .info .price-option .border-yellow"));
}

function closeOneClickBuy(){
	$(".one_click_buy").removeClass("visible");
		setTimeout(function(){
			$(".one_click_buy").removeClass("active");
			if (!$(".quick_view_block.visible").length){
				$("body").removeClass("replace_scroll");
				$("html").removeClass("no_scroll");
			}
		}, 500);
}

function showAddedToCart(){
	$(".added_to_cart").addClass("active").addClass("visible");
	$("body").addClass("replace_scroll");
	$("html").addClass("no_scroll");
	// setConfig($(".one_click_buy .body form .info .price-option .border-yellow"));
}

function closeAddedToCart(){
	$(".added_to_cart").removeClass("visible");
		setTimeout(function(){
			$(".added_to_cart").removeClass("active");
			// if (!$(".quick_view_block.visible").length){
				$("body").removeClass("replace_scroll");
				$("html").removeClass("no_scroll");
			// }
		}, 500);
}

function updateProductQuantityAddedToCart(cart_id, new_quantity){
	if (new_quantity > 0) {
		let quantity = "quantity[" + cart_id + "]";
		let ajax_data = {};
		ajax_data[quantity] = new_quantity;

		$.ajax({
			url: "index.php?route=checkout/cart/editproductsquantity",
			type: "post",
			data: ajax_data,
			success: function(result){
				$.ajax({
					url: "index.php?route=product/added_to_cart/update&cart_id=" + cart_id,
					success: function(product_data){
						let new_product_data = JSON.parse(product_data);
						$(".added_to_cart .cart_quantity").text(new_product_data['cart_quantity_text']);
						$(".added_to_cart .cart_total").text(new_product_data['cart_total']);
						$(".added_to_cart input[name='quantity']").val(new_product_data['quantity']);
						$(".added_to_cart .product .price span").text(new_product_data['total']);

						$("#cart > ul").load("index.php?route=common/cart/info ul li");
						$(".mobile_menu .navigation .item .quantity, #cart .cart__amount").text(new_product_data['cart_quantity']);
					}
				})
			}
		});
	}
	else if (new_quantity != ""){
		let ajax_data = {};
		ajax_data['key'] = cart_id;

		$.ajax({
			url: "index.php?route=checkout/cart/remove",
			type: "post",
			data: ajax_data,
			success: function(result){
				closeAddedToCart();
				showNotice("Товар удален из корзины");
			}
		});
	}
}

var added_to_cart_input_trigger;

function updateProductQuantityInputAddedToCart(cart_id, quantity){
	clearTimeout(added_to_cart_input_trigger);
	added_to_cart_input_trigger = setTimeout(function(){
		updateProductQuantityAddedToCart(cart_id, quantity);
	}, 500);
}

function showChoiseHelp(){
	$(".choise_help").toggleClass("active");
	$(".choise_help").toggleClass("visible");
}

function closeChoiseHelp(){
	$(".choise_help").toggleClass("visible");
	setTimeout(function(){
		$(".choise_help").toggleClass("active");
	}, 1000);
	localStorage.setItem("dont_show_choise_help_date", Date.now());
}

function highlightFixedMenu(){
	window.pageYOffset > parseInt($(".top_menu").css("height")) ? $(".fixed_menu").addClass("active") : $(".fixed_menu").removeClass("active");
}

function searchModalShow(){
	$("body").toggleClass("replace_scroll");
	$("html").toggleClass("no_scroll");
	$(".search_modal").fadeIn();
	$(".search_modal input").focus();
};
function searchModalHide(){
	$(".search_modal").fadeOut(300, 'linear', function(){
		$("html").toggleClass("no_scroll");
		$("body").toggleClass("replace_scroll");
	});
};

function flipNumber(element){
	let self = element;
	let next_number;
	if (+self.html() + 1 <= 9)
		next_number = +self.html() + 1;
	else {
		next_number = 0;
		flipNumber(self.parent().prev().find(".top"));
	}
	self.siblings(".top_rear").html(next_number);
	self.addClass("flip_start");
	setTimeout(function(){
		self.addClass("flip_middle");
		self.html(next_number);
		setTimeout(function(){
			self.addClass("flip_end1");
			setTimeout(function(){
				self.addClass("flip_end2");
				setTimeout(function(){
					self.siblings(".bottom").html(next_number);
					self.removeClass().addClass("top");
				},350);
			},150);
		},1);
	},500);
}

if ($(".offers .body.swiper").length) {
	const swiperActions = new Swiper('.offers .body.swiper', {
		loop: true,
		slidesPerView: 'auto',
		spaceBetween: 15,
		centeredSlides: true,	
		breakpoints: {
			768: {
				spaceBetween: 15
			},
			992: {
				spaceBetween: 20,
				loop: true,
				pagination: false,
			},
		},
	
		// pagination: {
		// 	el: '.brands_slider .swiper-pagination',
		// 	clickable: true,
		// },
	});
}

$("#report_receipt").click(function(){
	$("input[name='form_subject']").val("Сообщить о поступлении товара");
});

// Уведомление о добавлении/удалении товара из корзины/избранного 
let notice_toggler = true;
let notice_hide;

function showNotice(text, href = false){
	if (notice_toggler) {
		notice_toggler = false;
		if (href)
			$('<div class="notice">' + text + '<a href="' + href + '">Посмотреть</a></div>').appendTo('body');
		else
			$('<div class="notice">' + text + '</div>').appendTo('body');
		$(".notice").animate({right: "0px",}, 500);		
	} else {
		clearTimeout(notice_hide);
		$(".notice").html(text + '<br><a href="' + href + '">Посмотреть</a>');
	}

	notice_hide = setTimeout(() => {
		notice_toggler = true;
		$(".notice").animate({opacity: "0",}, 500, function() {$(this).remove();});
	}, 4000);
}

// Обработка нажатия на добавление в избранное
function wishlist(product, wishlist_page = false){
	let product_id = product.attr("data-id");
	let wishlist;

	if (product_id == null || product_id == undefined) return;
	
	// Если избранное пустое, создаем новый массив, иначе берем уже имеющийся
	getCookie('wishlist', true) == undefined ? wishlist = [] : wishlist = getCookie('wishlist', true);

	// Если товар не в избранном добавляем, иначе удаляем
	if (!product.find(".wishlist").hasClass('active')){
		wishlist.push(product_id);
		showNotice("Товар добавлен в избранное", "/wishlist");
	} else {
		wishlist.splice(wishlist.indexOf(product_id), 1);
		showNotice("Товар удален из избранного", "/wishlist");
		if (wishlist_page) {
			product.remove();
			if (!$(".products-list .products-list-body .product").length) location.reload();
		}
	};

	// Фильтруем массив на уникальные значения
	wishlist = [...new Set(wishlist)];

	// Записываем новое значение избранных товаров в куки на год 
	setCookie('wishlist', wishlist, {path: '/', 'max-age': 31536000});
	$(".header_wishlist span").html(wishlist.length);

	product.find(".wishlist").toggleClass("active");
}
// Помечает избранные товары при загрузке страницы
if (getCookie('wishlist', true) != undefined) {
	let products = getCookie('wishlist', true);
	products.forEach(function(val){
		$(".products-list .products-list-body .product[data-id='" + val + "'] .wishlist").addClass("active");
		$(".hit_slider .body .item[data-id='" + val + "'] .wishlist").addClass("active");
		$("#product-product .thumbnails[data-id='" + val + "'] .wishlist").addClass("active");
	});
	$(".header_wishlist span").html(products.length);
}

// Функции для более легкого управления куки
function getCookie(name, json=false) {
	if (!name) {
		return undefined;
	}

	let matches = document.cookie.match(new RegExp(
		"(?:^|; )" + name.replace(/([.$?*|{}()\[\]\\\/+^])/g, '\\$1') + "=([^;]*)"
	));
	if (matches) {
		let res = decodeURIComponent(matches[1]);
		if (json) {
			try {
				return JSON.parse(res);
			}
			catch(e) {}
		}
		return res;
	}

	return undefined;
}

function setCookie(name, value, options = {path: '/'}) {
	if (!name) {
		return;
	}

	options = options || {};

	if (options.expires instanceof Date) {
		options.expires = options.expires.toUTCString();
	}

	if (value instanceof Object) {
		value = JSON.stringify(value);
	}
	let updatedCookie = encodeURIComponent(name) + "=" + encodeURIComponent(value);
	for (let optionKey in options) {
		updatedCookie += "; " + optionKey;
		let optionValue = options[optionKey];
		if (optionValue !== true) {
			updatedCookie += "=" + optionValue;
		}
	}
	document.cookie = updatedCookie;
}

function deleteCookie(name) {
	setCookie(name, null, {
		expires: new Date(),
		path: '/'
	})
}