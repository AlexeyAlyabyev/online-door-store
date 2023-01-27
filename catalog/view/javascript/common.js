function getURLVar(t) {
	var e,
			o = [];
	if ((e = String(document.location).split("?"))[1]) {
			var a = e[1].split("&");
			for (i = 0; i < a.length; i++) {
					var n = a[i].split("=");
					n[0] && n[1] && (o[n[0]] = n[1]);
			}
			return o[t] ? o[t] : "";
	}
	return "cart" == (e = String(document.location.pathname).split("/"))[e.length - 1] && (o.route = "checkout/cart"), "checkout" == e[e.length - 1] && (o.route = "checkout/checkout"), o[t] ? o[t] : "";
}
$(document).ready(function () {
	$(".text-danger").each(function () {
			var t = $(this).parent().parent();
			t.hasClass("form-group") && t.addClass("has-error");
	}),
			$("#form-currency .currency-select").on("click", function (t) {
					t.preventDefault(), $("#form-currency input[name='code']").val($(this).attr("name")), $("#form-currency").submit();
			}),
			$("#form-language .language-select").on("click", function (t) {
					t.preventDefault(), $("#form-language input[name='code']").val($(this).attr("name")), $("#form-language").submit();
			}),
			$('.search input[name=\'search\']').parent().find('button').on('click', function() {
				var url = $('base').attr('href') + 'index.php?route=product/search';
		
				var value = $(this).parent().find("input[name=\'search\']").val();
		
				if (value) {
					url += '&description=true' + '&search=' + encodeURIComponent(value);
				}
		
				location = url;
			}),
		
			$('input[name=\'search\']').on('keydown', function(e) {
				if (e.keyCode == 13) {
					$(this).parent().find('button').trigger('click');
				}
			}),
			$("#menu .dropdown-menu").each(function () {
					var t = $("#menu").offset(),
							e = $(this).parent().offset().left + $(this).outerWidth() - (t.left + $("#menu").outerWidth());
					0 < e && $(this).css("margin-left", "-" + (e + 10) + "px");
			}),
			$("#list-view").click(function () {
					$("#content .product-grid > .clearfix").remove(),
							$("#content .row > .product-grid").attr("class", "product-layout product-list col-xs-12"),
							$("#grid-view").removeClass("active"),
							$("#list-view").addClass("active"),
							localStorage.setItem("display", "list");
			}),
			$("#grid-view").click(function () {
					var t = $("#column-right, #column-left").length;
					2 == t
							? $("#content .product-list").attr("class", "product-layout product-grid col-lg-6 col-md-6 col-sm-12 col-xs-12")
							: 1 == t
							? $("#content .product-list").attr("class", "product-layout product-grid col-lg-3 col-md-3 col-sm-4 col-xs-6")
							: $("#content .product-list").attr("class", "product-layout product-grid col-lg-3 col-md-3 col-sm-6 col-xs-12"),
							$("#list-view").removeClass("active"),
							$("#grid-view").addClass("active"),
							localStorage.setItem("display", "grid");
			}),
			"list" == localStorage.getItem("display") ? ($("#list-view").trigger("click"), $("#list-view").addClass("active")) : ($("#grid-view").trigger("click"), $("#grid-view").addClass("active")),
			$(document).on("keydown", "#collapse-checkout-option input[name='email'], #collapse-checkout-option input[name='password']", function (t) {
					13 == t.keyCode && $("#collapse-checkout-option #button-login").trigger("click");
			}),
			$("[data-toggle='tooltip']").tooltip({ container: "body" }),
			$(document).ajaxStop(function () {
					$("[data-toggle='tooltip']").tooltip({ container: "body" });
			});
});
var cart = {
			add: function (t, e) {
					$.ajax({
							url: "index.php?route=checkout/cart/add",
							type: "post",
							data: "product_id=" + t + "&quantity=" + (void 0 !== e ? e : 1),
							dataType: "json",
							success: function (t) {
								if (notice_toggler) {
									notice_toggler = false;
									$('body').append(' <div class="cart_notice">Товар был добавлен в корзину. <br> <a class="link" href="/cart">Посмотреть</a></div> ');
									setTimeout(function () {
										$(".cart_notice").addClass("active");
									}, 30),
									setTimeout(function () {
										notice_toggler = true;
										$(".cart_notice").remove();
									}, 4030);
								}
								$(".mobile_menu .navigation .item .quantity, #cart .cart__amount").html(t.total);
								t.redirect && (location = t.redirect);
								$("#cart > ul").load("index.php?route=common/cart/info ul li");
							},
							error: function (t, e, o) {
									alert(o + "\r\n" + t.statusText + "\r\n" + t.responseText);
							},
					});
			},
			update: function (t, e) {
					$.ajax({
							url: "index.php?route=checkout/cart/edit",
							type: "post",
							data: "key=" + t + "&quantity=" + (void 0 !== e ? e : 1),
							dataType: "json",
							beforeSend: function () {
									$("#cart > button").button("loading");
							},
							complete: function () {
									$("#cart > button").button("reset");
							},
							success: function (t) {
									setTimeout(function () {
											$("#cart > button").html('<span id="cart-total"><i class="fa fa-shopping-cart"></i> ' + t.total + "</span>");
									}, 100),
											"checkout/cart" == getURLVar("route") || "checkout/checkout" == getURLVar("route") ? (location = "index.php?route=checkout/cart") : $("#cart > ul").load("index.php?route=common/cart/info ul li");
							},
							error: function (t, e, o) {
									alert(o + "\r\n" + t.statusText + "\r\n" + t.responseText);
							},
					});
			},
			remove: function (t) {
					$.ajax({
							url: "index.php?route=checkout/cart/remove",
							type: "post",
							data: "key=" + t,
							dataType: "json",
							beforeSend: function () {
									$("#cart > button").button("loading");
							},
							complete: function () {
									$("#cart > button").button("reset");
							},
							success: function (t) {
									setTimeout(function () {
											$("#cart > button span").html(t.total);
									}, 100),
											"checkout/cart" == getURLVar("route") || "checkout/checkout" == getURLVar("route") ? (location = "index.php?route=checkout/cart") : $("#cart > ul").load("index.php?route=common/cart/info ul li");
							},
							error: function (t, e, o) {
									alert(o + "\r\n" + t.statusText + "\r\n" + t.responseText);
							},
					});
			},
	},
	voucher = {
			add: function () {},
			remove: function (t) {
					$.ajax({
							url: "index.php?route=checkout/cart/remove",
							type: "post",
							data: "key=" + t,
							dataType: "json",
							beforeSend: function () {
									$("#cart > button").button("loading");
							},
							complete: function () {
									$("#cart > button").button("reset");
							},
							success: function (t) {
									setTimeout(function () {
											$("#cart > button").html('<span id="cart-total"><i class="fa fa-shopping-cart"></i> ' + t.total + "</span>");
									}, 100),
											"checkout/cart" == getURLVar("route") || "checkout/checkout" == getURLVar("route") ? (location = "index.php?route=checkout/cart") : $("#cart > ul").load("index.php?route=common/cart/info ul li");
							},
							error: function (t, e, o) {
									alert(o + "\r\n" + t.statusText + "\r\n" + t.responseText);
							},
					});
			},
	},
	wishlist = {
			add: function (t) {
					$.ajax({
							url: "index.php?route=account/wishlist/add",
							type: "post",
							data: "product_id=" + t,
							dataType: "json",
							success: function (t) {
									$(".alert-dismissible").remove(),
											t.redirect && (location = t.redirect),
											t.success &&
													$("#content")
															.parent()
															.before('<div class="alert alert-success alert-dismissible"><i class="fa fa-check-circle"></i> ' + t.success + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>'),
											$("#wishlist-total span").html(t.total),
											$("#wishlist-total").attr("title", t.total),
											$("html, body").animate({ scrollTop: 0 }, "slow");
							},
							error: function (t, e, o) {
									alert(o + "\r\n" + t.statusText + "\r\n" + t.responseText);
							},
					});
			},
			remove: function () {},
	},
	compare = {
			add: function (t) {
					$.ajax({
							url: "index.php?route=product/compare/add",
							type: "post",
							data: "product_id=" + t,
							dataType: "json",
							success: function (t) {
									$(".alert-dismissible").remove(),
											t.success &&
													($("#content")
															.parent()
															.before('<div class="alert alert-success alert-dismissible"><i class="fa fa-check-circle"></i> ' + t.success + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>'),
													$("#compare-total").html(t.total),
													$("html, body").animate({ scrollTop: 0 }, "slow"));
							},
							error: function (t, e, o) {
									alert(o + "\r\n" + t.statusText + "\r\n" + t.responseText);
							},
					});
			},
			remove: function () {},
	};
$(document).delegate(".agree", "click", function (t) {
	t.preventDefault(), $("#modal-agree").remove();
	var e = this;
	$.ajax({
			url: $(e).attr("href"),
			type: "get",
			dataType: "html",
			success: function (t) {
					(html = '<div id="modal-agree" class="modal">'),
							(html += '  <div class="modal-dialog">'),
							(html += '    <div class="modal-content">'),
							(html += '      <div class="modal-header">'),
							(html += '        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>'),
							(html += '        <h4 class="modal-title">' + $(e).text() + "</h4>"),
							(html += "      </div>"),
							(html += '      <div class="modal-body">' + t + "</div>"),
							(html += "    </div>"),
							(html += "  </div>"),
							(html += "</div>"),
							$("body").append(html),
							$("#modal-agree").modal("show");
			},
	});
}),
	(function (t) {
			t.fn.autocomplete = function (e) {
					return this.each(function () {
							(this.timer = null),
									(this.items = new Array()),
									t.extend(this, e),
									t(this).attr("autocomplete", "off"),
									t(this).on("focus", function () {
											this.request();
									}),
									t(this).on("blur", function () {
											setTimeout(
													function (t) {
															t.hide();
													},
													200,
													this
											);
									}),
									t(this).on("keydown", function (t) {
											switch (t.keyCode) {
													case 27:
															this.hide();
															break;
													default:
															this.request();
											}
									}),
									(this.click = function (e) {
											e.preventDefault(), (value = t(e.target).parent().attr("data-value")), value && this.items[value] && this.select(this.items[value]);
									}),
									(this.show = function () {
											var e = t(this).position();
											t(this)
													.siblings("ul.dropdown-menu")
													.css({ top: e.top + t(this).outerHeight(), left: e.left }),
													t(this).siblings("ul.dropdown-menu").show();
									}),
									(this.hide = function () {
											t(this).siblings("ul.dropdown-menu").hide();
									}),
									(this.request = function () {
											clearTimeout(this.timer),
													(this.timer = setTimeout(
															function (e) {
																	e.source(t(e).val(), t.proxy(e.response, e));
															},
															200,
															this
													));
									}),
									(this.response = function (e) {
											if (((html = ""), e.length)) {
													for (i = 0; i < e.length; i++) this.items[e[i].value] = e[i];
													for (i = 0; i < e.length; i++) e[i].category || (html += '<li data-value="' + e[i].value + '"><a href="#">' + e[i].label + "</a></li>");
													var o = new Array();
													for (i = 0; i < e.length; i++)
															e[i].category && (o[e[i].category] || ((o[e[i].category] = new Array()), (o[e[i].category].name = e[i].category), (o[e[i].category].item = new Array())), o[e[i].category].item.push(e[i]));
													for (i in o)
															for (html += '<li class="dropdown-header">' + o[i].name + "</li>", j = 0; j < o[i].item.length; j++)
																	html += '<li data-value="' + o[i].item[j].value + '"><a href="#">&nbsp;&nbsp;&nbsp;' + o[i].item[j].label + "</a></li>";
											}
											html ? this.show() : this.hide(), t(this).siblings("ul.dropdown-menu").html(html);
									}),
									t(this).after('<ul class="dropdown-menu"></ul>'),
									t(this).siblings("ul.dropdown-menu").delegate("a", "click", t.proxy(this.click, this));
					});
			};
	})(window.jQuery);
