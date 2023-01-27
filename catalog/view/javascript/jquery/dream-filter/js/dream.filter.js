!(function (e) {
	e.fn.dreamFilter = function (t) {
			t = e.extend(
					{
							search_mode: "auto",
							disable_null: "disable",
							show_count: !0,
							show_picked: !0,
							decodeURI: !0,
							loader: "",
							callbackBefore: !1,
							callbackAfter: !1,
							truncate: { mode: "none" },
							mobile: { mode: "none" },
							ajax: { enable: !1 },
							popper: { enable: !1 },
							filters: {},
					},
					t
			);
			var i,
					a,
					o,
					n,
					r,
					l,
					s = this,
					d = s.attr("action"),
					c = e("#" + t.widget_id),
					f = e("#" + t.popper.id),
					p = function (i, a) {
							a = void 0 === a || a;
							var o = e("#" + i),
									n = o.find("input, select"),
									r = s.find('.rdf-picked [data-clear="' + i + '"]'),
									l = o.find('[data-clear="' + i + '"]');
							if (n.is(":checkbox") || n.is(":radio")) n.prop("checked", !1), n.removeAttr("checked");
							else {
									if (n.hasClass("irs-hidden-input")) {
											var d = n.data("ionRangeSlider"),
													c = d.options.from_min ? d.options.from_min : d.options.min,
													f = d.options.to_max ? d.options.to_max : d.options.max;
											d.update({ from: c, to: f });
									}
									n.val("");
							}
							a && ("auto" === t.search_mode ? s.submit() : t.popper.enable && v(o.closest(".panel"))), r.remove(), l.remove();
					},
					u = function () {
							s.find("input, select").each(function (t) {
									var i = e(this);
									i.hasClass("irs-hidden-input") && i.closest(".slidewrapper").hasClass("irs-notinit") && i.val(""), i.val() || i.prop("disabled", !0);
							});
					},
					h = function () {
							s.find("input, select")
									.filter(":disabled")
									.each(function (t) {
											e(this).prop("disabled", !1);
									});
					};
			s.on("submit", async function (i) {
					i.preventDefault(), u(), m();
					var a = e(this).serialize();
					var o = d;
					var rdrf_query = a;
					let seo_page_exist = false;
					if (a.indexOf("rdrf") !== -1) {
						rdrf_query = a.substr(a.indexOf("rdrf")).split("&");
					}
					if (a == false) window.location.href = o;
						if (rdrf_query.length == 1) {
							let additional_params = a.substr(0, a.indexOf("&rdrf"));
							let url = await (await fetch('index.php?route=extension/module/dream_filter/getRdrfRoute&rdrf=' + rdrf_query[0]));
							let filter_chpu = await url.text();
							if (filter_chpu) {
								let link_with_chpu = o + filter_chpu + "/"
								let full_seo_url = link_with_chpu.substr(window.location.origin.length);
								if (full_seo_url !== "") {
									let seo_page = await (await fetch('index.php?route=extension/module/dream_filter/getRdrfSeoPage&full_seo_url=' + full_seo_url));
									let seo_page_exist = await seo_page.text();
									console.log(seo_page_exist);
									seo_page_exist = +seo_page_exist;
									console.log(seo_page_exist);
									if (seo_page_exist == false) {
										o += (a ? (d.indexOf("?") > 0 ? "&" : "?") + a : "");
									} else {
										if (additional_params) link_with_chpu += "?" + additional_params;
										window.location.href = link_with_chpu;
									}
								}
							}
							else
								o += (a ? (d.indexOf("?") > 0 ? "&" : "?") + a : "");
						} else {
							o += (a ? (d.indexOf("?") > 0 ? "&" : "?") + a : "");
						}
					t.ajax.enable ? (j(o, !0, !0), h(), "fixed" === t.mobile.mode && t.mobile.autoclose && c.hasClass("show") && e("#" + t.mobile.button_id).trigger("click")) : (window.location.href = o);
			}),
					s.on("reset", function (t) {
							t.preventDefault(),
									s.find(".rdf-filters input, .rdf-filters select").each(function (t) {
											p(e(this).data("id"), !1);
									}),
									s.submit();
					}),
					s.on("click", "[data-clear]", function () {
							p(e(this).data("clear"));
					}),
					s.on("change", "input:checkbox, input:radio", function () {
							var t = e(this).data("id");
							e(this).is(":radio") && e(this).closest(".rdf-group").find("[data-clear]").remove(),
									e(this).is(":checked")
											? e("#" + t)
														.find(".rdf-label")
														.before('<span class="rdf-clear" data-clear="' + t + '">&times;</span>')
											: e("#" + t)
														.find(".rdf-clear")
														.remove();
					}),
					s.on("change", "select, input:text", function () {
							var t = e(this).data("id");
							e(this).val()
									? e(this).closest(".input-group").find("[data-clear]").length ||
										e(this)
												.closest(".input-group")
												.append('<span class="rdf-clear input-group-addon" data-clear="' + t + '">&times;</span>')
									: e(this).closest(".input-group").find(".rdf-clear").remove();
					}),
					"auto" === t.search_mode &&
							(s.on("change", "input:not([type=hidden]), select", function () {
									s.submit();
							}),
							s.on("finish", "input.irs-hidden-input", function () {
									s.submit();
							}));
			var m = function () {
							i &&
									f.fadeOut(200, function () {
											i.destroy();
									});
					},
					v = function (a) {
							var o = a.offset().top + a.outerHeight() / 2 - f.outerHeight() / 2 - s.offset().top;
							u(),
									e.ajax({
											url: t.popper.action.replace(/&amp;/g, "&"),
											type: "get",
											data: s.serialize(),
											processData: !1,
											dataType: "html",
											beforeSend: function () {
													e("#" + t.popper.button_id).button("loading");
											},
											success: function (e) {
													var t;
													f.find("span").html(e),
															(t = (t = o) || 0),
															setTimeout(function () {
																	(i = new Popper(s, f, { placement: "right-start", modifiers: { offset: { offset: t }, computeStyle: { gpuAcceleration: !1 }, preventOverflow: { enabled: !0, boundariesElement: "viewport" } } })),
																			f.fadeIn(200);
															}, 200);
											},
											complete: function () {
													e("#" + t.popper.button_id).button("reset");
											},
									}),
									h();
					};
			t.popper.enable &&
					(s.on("change", "input:not([type=hidden]), select", function () {
							v(e(this).closest(".panel"));
					}),
					s.on("finish", "input.irs-hidden-input", function () {
							v(e(this).closest(".panel"));
					}),
					e(document).on("click", "#" + t.popper.button_id, function () {
							s.submit(), m();
					}),
					e(document).mouseup(function (e) {
							f.is(e.target) || 0 !== f.has(e.target).length || m();
					}));
			var g = function () {
							switch (t.truncate.mode) {
									case "element":
											s.find(".rdf-truncate-element .rdf-group").each(function (i) {
													var a = e(this);
													a.children().filter(":visible").length > t.truncate.elements
															? (a.css("padding-bottom", 0), a.parent().removeClass("rdf-full"), a.parent().hasClass("rdf-show") ? x(e(this), 0) : b(e(this), 0))
															: (a.parent().addClass("rdf-full"), a.css({ height: "", "padding-bottom": "" })),
															a.css("max-height", "none").show();
											});
											break;
									case "height":
											s.find(".rdf-truncate-height").each(function (i) {
													e(this).outerHeight() === parseInt(t.truncate.height) && (t.truncate.scrollbar ? e(this).hasClass("scroll-wrapper") || e(this).scrollbar() : e(this).find(".rdf-group").css("padding-right", 0));
											});
											break;
									case "width":
											t.truncate.scrollbar &&
													s.find(".rdf-truncate-width").each(function (t) {
															e(this).hasClass("scroll-wrapper") || e(this).scrollbar();
													});
							}
					},
					b = function (i, a) {
							a = void 0 === a ? 400 : a;
							var o = 0;
							i
									.children()
									.filter(":visible")
									.each(function (i) {
											i < t.truncate.elements && (o += e(this).outerHeight(!0));
									}),
									i.animate({ height: o + "px" }, a),
									i.parent().removeClass("rdf-show");
					},
					x = function (t, i) {
							i = void 0 === i ? 400 : i;
							var a = 0;
							t
									.children()
									.filter(":visible")
									.each(function (t) {
											a += e(this).outerHeight(!0);
									}),
									t.animate({ height: a + "px" }, i, function () {
											t.height("");
									}),
									t.parent().addClass("rdf-show");
					};
			s.on("click", '[data-toggle="truncate-show"]', function () {
					x(e(e(this).data("target")));
			}),
					s.on("click", '[data-toggle="truncate-hide"]', function () {
							b(e(e(this).data("target")));
					}),
					s.on("shown.bs.collapse", ".panel-collapse", function () {
							g();
					});
			var w = function () {
					if ("button" === t.mobile.mode) "mobile" !== !c.data("view") && (c.before('<div id="rdf-dummy"></div>'), c.detach().prependTo(t.ajax.selector), c.data("view", "mobile")), s.hasClass("collapse") || s.addClass("collapse");
					else {
							c.hasClass("rdf-mobile-view") ||
									(c.before('<div id="rdf-dummy"></div>'), c.detach().prependTo("body"), c.addClass("rdf-mobile-view"), c.css("top", t.mobile.indenting_top + "px"), c.css(t.mobile.side, -window.innerWidth - 10 + "px"));
							var i = e(window).height() - t.mobile.indenting_top - t.mobile.indenting_bottom,
									a = i - s.find(".rdf-header").outerHeight() - s.find(".rdf-footer").outerHeight() - 10;
							s.css("max-height", i + "px"), s.find(".rdf-body").css("max-height", a + "px"), s.find(".rdf-filters").scrollbar();
					}
			};
			"none" !== t.mobile.mode &&
					e(window).on("resize", function () {
							e(window).innerWidth() < t.mobile.width
									? w()
									: "button" === t.mobile.mode
									? ("mobile" === c.data("view") && (e("#rdf-dummy").after(c.detach()).remove(), c.data("view", "desktop")), s.hasClass("collapse") && s.removeClass("collapse"), s.css("height", "auto"))
									: (c.hasClass("rdf-mobile-view") && (c.removeClass("rdf-mobile-view"), e("#rdf-dummy").after(c.detach()).remove(), s.attr("style", ""), s.find(".rdf-body").attr("style", "")),
										c.removeClass("show"),
										e("div.rdr-backdrop").remove());
					}),
					"fixed" === t.mobile.mode &&
							e(document).on("click", "#" + t.mobile.button_id + ", .rdr-backdrop, .filter-toggle", function () {
									c.toggleClass("show"),
											c.hasClass("show")
													? (t.mobile.backdrop && e("<div>", { class: "rdr-backdrop" }).appendTo("body").fadeTo("swing", 0.7), "right" === t.mobile.side ? c.animate({ right: 0 }) : c.animate({ left: 0 }))
													: (t.mobile.backdrop &&
																e("div.rdr-backdrop").fadeOut(function () {
																		e(this).remove();
																}),
														"right" === t.mobile.side ? c.animate({ right: -window.innerWidth - 10 + "px" }) : c.animate({ left: -window.innerWidth - 10 + "px" }));
							}),
					"button" === t.mobile.mode &&
							e(document).on("click", "#" + t.mobile.button_id, function () {
									s.collapse("toggle");
							});
			var _ = function () {
							t.ajax.sorter && "select" === t.ajax.sorter_type && e(t.ajax.sorter).removeAttr("onchange"),
									t.ajax.limit && "select" === t.ajax.limit_type && e(t.ajax.limit).removeAttr("onchange"),
									e(t.ajax.selector).addClass("rdf-container");
							try {
									var i = !1;
									if ((e.cookie ? (i = e.cookie("display")) : e.totalStorage && (i = e.totalStorage("display")), i && "function" == typeof display)) display(i);
									else
											switch ((i = localStorage.getItem("display"))) {
													case "list":
															"function" == typeof list_view ? list_view() : e("#list-view").trigger("click");
															break;
													case "compact":
															"function" == typeof compact_view ? compact_view() : e("#compact-view").trigger("click");
															break;
													case "price":
															"function" == typeof price_view ? price_view() : e("#price-view").trigger("click");
															break;
													case "grid4":
															"function" == typeof grid_view4 ? grid_view4() : e("#grid-view4").trigger("click");
															break;
													default:
															"function" == typeof grid_view ? grid_view() : e("#grid-view").trigger("click");
											}
							} catch (e) {
									console.error("Display error " + e.name + ":" + e.message + "\n" + e.stack);
							}
					},
					j = function (i, d, f) {
							"https:" === window.location.protocol && (i = i.replace(/^http:\/\//g, "https://"));
							var p = i,
									u = "";
							(p += (p.indexOf("?") > 0 ? "&" : "?") + "rdf-ajax=1"),
									f && "leave" !== t.disable_null ? (p += "&rdf-reload=1&rdf-module=" + t.module) : (f = !1),
									t.decodeURI && (i = decodeURIComponent(i)),
									t.callbackBefore && "function" == typeof t.callbackBefore && t.callbackBefore(i),
									e.ajax({
											url: p,
											type: "get",
											processData: !1,
											dataType: f ? "json" : "html",
											beforeSend: function () {
													"function" == typeof e.fn.button && s.find(".rdf-footer button").button("loading"),
															e(t.ajax.selector).addClass("rdf-loading"),
															e("#grid-view").length && (a = e("#grid-view").clone(!0)),
															e("#grid-view4").length && (o = e("#grid-view4").clone(!0)),
															e("#list-view").length && (n = e("#list-view").clone(!0)),
															e("#price-view").length && (r = e("#price-view").clone(!0)),
															e("#compact-view").length && (l = e("#compact-view").clone(!0));
											},
											success: function (s) {
													var p = f && void 0 !== s.html ? s.html : s;
													e(t.ajax.selector).find("#" + t.widget_id).length
															? ((u = c.detach()),
																e(p)
																		.find("#" + t.widget_id)
																		.remove())
															: e(t.ajax.selector).find("#rdf-dummy").length && (u = e("#rdf-dummy").detach());
													var h = e(p).attr("id") === t.ajax.selector ? e(p) : e(p).find(t.ajax.selector);
													e(t.ajax.selector).children(":not(.rdf-loader)").remove(),
															e(t.ajax.selector).append(h.html()).prepend(u),
															t.ajax.pagination && !h.find(t.ajax.pagination).length && e(t.ajax.pagination).replaceWith(e(p).find(t.ajax.pagination)),
															t.ajax.sorter && !h.find(t.ajax.sorter).length && e(t.ajax.sorter).replaceWith(e(p).find(t.ajax.sorter)),
															t.ajax.limit && !h.find(t.ajax.limit).length && e(t.ajax.limit).replaceWith(e(p).find(t.ajax.limit)),
															a && e("#grid-view").replaceWith(a),
															o && e("#grid-view4").replaceWith(o),
															n && e("#list-view").replaceWith(n),
															r && e("#price-view").replaceWith(r),
															l && e("#compact-view").replaceWith(l),
															f && void 0 !== s.filters && (k(s.filters), g()),
															t.ajax.pushstate && d && history.pushState(null, null, i),
															y(),
															t.callbackAfter && "function" == typeof t.callbackAfter && t.callbackAfter(p),
															_();
											},
											error: function (e, t, i) {
													console.error("jqXHR", e), console.error("textStatus", t), console.error("errorThrown", i);
											},
											complete: function () {
													setTimeout(function () {
															e(t.ajax.selector).removeClass("rdf-loading"), "function" == typeof e.fn.button && s.find(".rdf-footer button").button("reset"), t.ajax.scroll && e("body,html").animate({ scrollTop: t.ajax.offset }, 500);
													}, 300);
											},
									});
					},
					k = function (i) {
							e.each(t.filters, function (a, o) {
									var n = e("#" + a);
									if ((void 0 === i[a] ? "hide" === t.disable_null && n.hide() : n.is(":hidden") && n.show(), o.values)) {
											var r = "";
											-1 !== ["checkbox", "multiimage"].indexOf(o.type) && n.find("input:checked").length && (r = "+"),
													e.each(o.values, function (o, n) {
															var l = e("#" + o);
															if (l)
																	if (l.is("option")) {
																			var s = l.text().replace(/\(.*\)/gm, "");
																			void 0 !== i[a] && void 0 !== i[a].values[o]
																					? (l.prop("disabled", !1), l.is(":hidden") && l.show(), t.show_count && l.html(s + "(" + r + i[a].values[o] + ")"))
																					: ("disable" !== t.disable_null || l.is(":selected") ? "hide" === t.disable_null && l.hide() : l.prop("disabled", !0), t.show_count && l.html(s));
																	} else
																			void 0 !== i[a] && void 0 !== i[a].values[o]
																					? ("disable" === t.disable_null ? l.fadeTo("fast", 1) : l.is(":hidden") && l.show(), l.find("input").prop("disabled", !1), t.show_count && l.find(".rdf-label").html(r + i[a].values[o]))
																					: (l.find("input").is(":checked") || ("disable" === t.disable_null ? l.fadeTo("slow", 0.5) : "hide" === t.disable_null && l.hide(), l.find("input").prop("disabled", !0)),
																						t.show_count && l.find(".rdf-label").html(""));
													});
									} else if (o.range || o.slider) {
											var l = e("#" + o.input_id),
													s = l.data("ionRangeSlider");
											if (s && !l.val()) {
													var d = null !== s.options.from_min ? s.options.from_min : s.options.min,
															c = null !== s.options.to_max ? s.options.to_max : s.options.max;
													if (((update = {}), void 0 !== i[a] && (i[a].range || i[a].slider))) {
															var f, p;
															if (
																	(o.range &&
																			i[a].range &&
																			(i[a].range.min !== d && ((update.from_min = i[a].range.min), (update.to_min = i[a].range.min), (update.from = i[a].range.min)),
																			i[a].range.max !== c && ((update.from_max = i[a].range.max), (update.to_max = i[a].range.max), (update.to = i[a].range.max))),
																	o.slider && i[a].slider)
															)
																	e.each(o.slider, function (e, t) {
																			-1 !== i[a].slider.indexOf(String(t)) && ((f = void 0 === f ? e : f), (p = e));
																	}),
																			void 0 !== f && f !== d && ((update.from_min = f), (update.to_min = f), (update.from = f)),
																			void 0 !== p && p !== c && ((update.from_max = p), (update.to_max = p), (update.to = p));
															s.options.disable && (update.disable = !1);
													} else update.disable = !0;
													e.isEmptyObject(update) || (s.update(update), l.val(""));
											}
									}
							});
					},
					y = function () {
							if ((s.find(".rdf-picked").html(""), t.show_picked)) {
									var i = [];
									e.each(t.filters, function (t, a) {
											var o = e("#" + t);
											if (a.values)
													e.each(o.find("input:checked"), function (t) {
															i.push({ id: e(this).attr("data-id"), name: "type_single" === a.type ? "" : a.title, value: e.trim(e(this).closest("label").text()) });
													}),
															e.each(o.find("option:selected"), function (o) {
																	e(this).val() &&
																			i.push({
																					id: t,
																					name: a.title,
																					value: e.trim(
																							e(this)
																									.text()
																									.replace(/\(.*\)/gm, "")
																					),
																			});
															});
											else if (a.input_id) {
													var n = e("#" + a.input_id);
													if (n && n.val())
															if (n.hasClass("irs-hidden-input")) {
																	var r = n.data("ionRangeSlider"),
																			l = "",
																			s = void 0 !== r.options.p_values[r.result.from] ? r.options.p_values[r.result.from] : r.result.from_value ? r.result.from_value : r.result.from,
																			d = void 0 !== r.options.p_values[r.result.to] ? r.options.p_values[r.result.to] : r.result.to_value ? r.result.to_value : r.result.to;
																	(l += r.options.prefix), (l += s === d ? s : s + " &mdash; " + d), (l += r.options.postfix), i.push({ id: t, name: a.title, value: l });
															} else i.push({ id: t, name: a.title, value: n.val() });
											}
									}),
											e.each(i, function (e, t) {
													s.find(".rdf-picked").append('<button type="button" data-clear="' + t.id + '" class="btn btn-default btn-xs">' + (t.name ? t.name + ": " : "") + t.value + "<i>&times;</i></button>");
											});
							}
					};
			return (
					t.ajax.enable &&
							(t.ajax.pushstate &&
									e(window).on("popstate", function (e) {
											j(location.href);
									}),
							t.ajax.pagination &&
									e(document).on("click", t.ajax.pagination + " a[href]", function (t) {
											return t.preventDefault(), j(e(this).attr("href"), !0), !1;
									}),
							t.ajax.sorter &&
									("button" === t.ajax.sorter_type
											? e(document).on("click", t.ajax.sorter + " a[href]", function (t) {
														t.preventDefault();
														var i = e(this).attr("href"),
																a = i.match("sort=([A-Za-z.]+)"),
																o = i.match("order=([A-Z]+)");
														return s.find('input[name="sort"]').val(a[1]), s.find('input[name="order"]').val(o[1]), j(i, !0), !1;
												})
											: e(document).on("change", t.ajax.sorter, function (t) {
														t.preventDefault();
														var i = e(this).val(),
																a = i.match("sort=([A-Za-z.]+)"),
																o = i.match("order=([A-Z]+)");
														s.find('input[name="sort"]').val(a[1]), s.find('input[name="order"]').val(o[1]), j(i, !0);
												})),
							t.ajax.limit &&
									("button" === t.ajax.limit_type
											? e(document).on("click", t.ajax.limit + " a[href]", function (t) {
														t.preventDefault();
														var i = e(this).attr("href"),
																a = i.match("limit=([0-9]+)");
														return s.find('input[name="limit"]').val(a[1]), j(i, !0), !1;
												})
											: e(document).on("change", t.ajax.limit, function (t) {
														t.preventDefault();
														var i = e(this).val(),
																a = i.match("limit=([0-9]+)");
														// s.find('input[name="limit"]').val(a[1]), j(i, !0);
												}))),
					s.addClass("initialized"),
					t.ajax.enable && (e(t.ajax.selector).prepend(t.loader), _()),
					e(window).innerWidth() < t.mobile.width && w(),
					g(),
					this
			);
	};
})(jQuery);
