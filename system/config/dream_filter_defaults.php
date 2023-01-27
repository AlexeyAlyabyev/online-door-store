<?php
return array(
	'name' => 'Dream Filter',
	'title' => array(),
	'status' => true,
	'filters' => array(
	),
	'view' => array(
		'template' => 'vertical',
		'truncate_mode' => 'height',
		'truncate_view' => 'scrollbar',
		'truncate_height' => 150,
		'truncate_elements' => 4,
		'truncate_text_show' => array(),
		'truncate_text_hide' => array(),
		'truncate_hrz_mode' => 'none',
		'truncate_hrz_view' => 'scrollbar',
		'bootstrap' => 0,
		'skin' => 'default',
		'color' => 'default',
		'button' => 'btn-default',
		'loader' => 'ball-pulse',
		'grid' => 0,
		'shadow' => 1,
		'show_picked' => true,
		'show_count' => true,
		'disable_null' => 'disable',
		'image_width' => 30,
		'image_height' => 30,
		'mobile' => array(
			'mode' => 'fixed',
			'width' => 768,
			'autoclose' => false,
			'button_text' => array(),
			'backdrop' => 0,
			'side' => 'left',
			'indenting_top' => 50,
			'indenting_bottom' => 10,
		)
	),
	'settings' => array(
		'mode' => 'ajax',
		'search_mode' => 'auto',
		'use_popper' => false,
		'min_values' => 1,
		'search_btn_text' => array(),
		'reset_btn_text' => array(),
		'reset_btn' => true,
		'categories' => array(),
		'categories_child' => false,
		'excategories' => array(),
		'excategories_child' => false,
		'selector' => '#content',
		'ajax_pagination' => true,
		'pagination_selector' => '#content .pagination',
		'ajax_sorter' => true,
		'sorter_selector' => '#input-sort',
		'sorter_type' => 'select',
		'ajax_limit' => true,
		'limit_selector' => '#input-limit',
		'limit_type' => 'select',
		'pushstate' => true,
		'ajax_scroll' => false,
		'scroll_offset' => 0,
		'callback_before_enable' => false,
		'callback_before' => "function(action) {\n}",
		'callback_after_enable' => false,
		'callback_after' => "function(content) {\n}"
	),
	'config' => array()
);