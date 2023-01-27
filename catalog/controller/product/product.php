<?php
class ControllerProductProduct extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('product/product');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$this->load->model('catalog/category');

		if (isset($this->request->get['path'])) {
			$path = '';

			$parts = explode('_', (string)$this->request->get['path']);

			$category_id = (int)array_pop($parts);

			foreach ($parts as $path_id) {
				if (!$path) {
					$path = $path_id;
				} else {
					$path .= '_' . $path_id;
				}

				$category_info = $this->model_catalog_category->getCategory($path_id);

				if ($category_info) {
					$data['breadcrumbs'][] = array(
						'text' => $category_info['name'],
						'href' => $this->url->link('product/category', 'path=' . $path)
					);
				}
			}

			// Set the last category breadcrumb
			$category_info = $this->model_catalog_category->getCategory($category_id);

			if ($category_info) {
				$url = '';

				if (isset($this->request->get['sort'])) {
					$url .= '&sort=' . $this->request->get['sort'];
				}

				if (isset($this->request->get['order'])) {
					$url .= '&order=' . $this->request->get['order'];
				}

				if (isset($this->request->get['page'])) {
					$url .= '&page=' . $this->request->get['page'];
				}

				if (isset($this->request->get['limit'])) {
					$url .= '&limit=' . $this->request->get['limit'];
				}

				$data['breadcrumbs'][] = array(
					'text' => $category_info['name'],
					'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'] . $url)
				);
			}
		}

		$this->load->model('catalog/manufacturer');

		if (isset($this->request->get['manufacturer_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_brand'),
				'href' => $this->url->link('product/manufacturer')
			);

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($this->request->get['manufacturer_id']);

			if ($manufacturer_info) {
				$data['breadcrumbs'][] = array(
					'text' => $manufacturer_info['name'],
					'href' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . $url)
				);
			}
		}

		if (isset($this->request->get['search']) || isset($this->request->get['tag'])) {
			$url = '';

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . $this->request->get['search'];
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . $this->request->get['tag'];
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_search'),
				'href' => $this->url->link('product/search', $url)
			);
		}

		if (isset($this->request->get['product_id'])) {
			$product_id = (int)$this->request->get['product_id'];
		} else {
			$product_id = 0;
		}
		
		$this->document->addLink($this->url->link('product/product', $url . '&product_id=' . $product_id), 'canonical');

		$this->load->model('catalog/product');

		// Если был вход в админку, показывает на странице кноку редактирования
		if (isset($this->session->data['user_token'])) {
			$data["edit"] = "admin/index.php?route=catalog/product/edit&" . "user_token=" .  $this->session->data['user_token'] . "&product_id=" . $product_id;
		}

		$product_info = $this->model_catalog_product->getProduct($product_id);

		if ($product_info) {

			// Last-Modified
			$template_name = "product/product";
			$template_path = DIR_TEMPLATE . $this->config->get('theme_default_directory') . "/template/"  . $template_name . ".twig";
			$LastModified_unix = max(strtotime($product_info['date_modified']) - 10800, filectime($template_path), filectime(__FILE__));
			$LastModified = gmdate("D, d M Y H:i:s", $LastModified_unix) . " GMT";

			$ifModified = false;
			$ifModified = strtotime(substr($_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? '', 5));

			if ($ifModified && $ifModified >= $LastModified_unix) {
				header($_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified');
				exit;
			}
			$this->response->addHeader('Last-Modified: '. $LastModified);
			// ---------------

			$url = '';

			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['manufacturer_id'])) {
				$url .= '&manufacturer_id=' . $this->request->get['manufacturer_id'];
			}

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . $this->request->get['search'];
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . $this->request->get['tag'];
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}
			
			

			$data['breadcrumbs'][] = array(
				'text' => $product_info['name'],
				'href' => $this->url->link('product/product', $url . '&product_id=' . $this->request->get['product_id'])
			);

			$data['attribute_groups'] = $this->model_catalog_product->getProductAttributes($this->request->get['product_id']);

			// Передаем id корневой категории
			$category_data = $this->model_catalog_product->getCategories($product_id);
			$data["main_category"] = $category_data[0]['category_id'];

			$data['product_color'] = "";
			$data['glazing'] = "";
			$data['glass_color'] = "";

			if ($data["main_category"] == 59){
				$data['product_color'] = $this->model_catalog_product->getProductAttributeValue($this->request->get['product_id'], 12);
				$color_manufacture_img = $this->model_catalog_product->getProductAttributeValue($this->request->get['product_id'], 54);
			}
			else 
				$data['product_color'] = $this->model_catalog_product->getProductAttributeValue($this->request->get['product_id'], 51);	

			$data['glazing'] = $this->model_catalog_product->getProductAttributeValue($this->request->get['product_id'], 17);
			$data['glass_color'] = $this->model_catalog_product->getProductAttributeValue($this->request->get['product_id'], 53);

			// Генерим ссылку на картинку цвета двери (Удалить при следующих изменениях в карточке товара)
			if (isset($color_manufacture_img) && $color_manufacture_img)
				$data['manufacturer_id_image'] = $product_info['manufacturer_id'] . '/' . $color_manufacture_img.'.png';
			

			$data['telephone'] = $this->config->get('config_telephone');

			
		$this->document->addScript('catalog/view/theme/custom/js/slick.js', 'footer');
		$this->document->addScript('catalog/view/javascript/jquery/swiper/js/swiper.min.js', 'footer');
		$this->document->addStyle('catalog/view/javascript/jquery/swiper/css/swiper.min.css');

			if ($product_info['meta_title'])
				$this->document->setTitle($product_info['meta_title']);
			else if ($data['glazing'] != "")
				$this->document->setTitle($product_info['name'].' '.$data['product_color'].' '.$data['glazing'].' цвета '.$data['glass_color'].' купить в '.$this->language->get('city').' с установкой');
			else if ($data["main_category"] !=61 && $data['product_color'])
				$this->document->setTitle($product_info['name'].' '.$data['product_color'].' купить в '.$this->language->get('city').' с установкой');
			else
				$this->document->setTitle($product_info['name'].' '.$data['product_color'].' купить в '.$this->language->get('city').'');
				
			if ($product_info['meta_description'])
				$this->document->setDescription($product_info['meta_description']);
			else if ($data['glazing'] != "")
				$this->document->setDescription($product_info['name'].' '.$data['product_color'].' '.$data['glazing'].' цвета '.$data['glass_color'].' купить в '.$this->language->get('in_city').' Недорого. ✅ Бесплатный замер ✅ Установка под ключ ✅ Сезонные скидки и акции  ☎ '.$data['telephone']);
			else if ($data["main_category"] !=61 && $data['product_color'])
				$this->document->setDescription($product_info['name'].' '.$data['product_color'].' купить в '.$this->language->get('in_city').' Недорого. ✅ Бесплатный замер ✅ Установка под ключ ✅ Сезонные скидки и акции  ☎ '.$data['telephone']);
			else
				$this->document->setDescription(''.$product_info['name'].' купить в '.$this->language->get('in_city').' Недорого. ✅ Бесплатный замер ✅ Установка под ключ ✅ Сезонные скидки и акции  ☎ '.$data['telephone']);


			$this->document->setKeywords($product_info['meta_keyword']);

			// Выводим цвет товара в хлебные крошки
			$data['breadcrumbs'][count($data['breadcrumbs'])-1]['text'] .= " ".$data['product_color'];

			if (isset($product_info['h1']) && $product_info['h1'] != "")
				$data['heading_title'] = trim($product_info['h1']);				
			else
				$data['heading_title'] = trim($product_info['name']);

			// Чтобы заголовок двери смотрелся нормально
			$data['heading_title'] = str_replace("дверь", "дверь<br>", $data['heading_title']);

			$data['text_minimum'] = sprintf($this->language->get('text_minimum'), $product_info['minimum']);
			$data['text_login'] = sprintf($this->language->get('text_login'), $this->url->link('account/login', '', true), $this->url->link('account/register', '', true));

			$this->load->model('catalog/review');

			$data['tab_review'] = sprintf($this->language->get('tab_review'), $product_info['reviews']);

			$data['product_id'] = (int)$this->request->get['product_id'];
			$data['manufacturer'] = $product_info['manufacturer'];
			$data['manufacturers'] = $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $product_info['manufacturer_id']);
			
			$data['model'] = $product_info['model'];
			$data['reward'] = $product_info['reward'];
			$data['points'] = $product_info['points'];
			$data['description'] = html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8');
			

			if ($product_info['stock_status']) {
				$data['stock'] = $product_info['stock_status'];
			}
			// elseif ($this->config->get('config_stock_display')) {
			// 	$data['stock'] = $product_info['quantity'];
			// } 
			else {
				$data['stock'] = $this->language->get('text_instock');
			}

			$data['stock_status_id'] = $product_info['stock_status_id'];

			$this->load->model('tool/image');

			if (strpos($product_info['image'], ".webp") != false){
				$data['popup'] = 'image/' . $product_info['image'];
			} elseif ($product_info['image']) {
				$data['popup'] = $this->model_tool_image->resize($product_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height'));
			} else {
				$data['popup'] = '';
			}

			if (strpos($product_info['image'], ".webp") != false){
				$data['thumb'] = 'image/' . $product_info['image'];
			} elseif ($product_info['image']) {
				$data['thumb'] = $this->model_tool_image->resize($product_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_height'));
			} else {
				$data['thumb'] = '';
			}

			if ($product_info['color_image']) {
				$data['products_related_by_color'][] = array(
					'color_image' => $product_info['color_image'],
					'href' 				=> false,
				);
			}

			if ($data['glazing']) {
				$data['products_related_by_glazing'][] = array(
					'glazing' => $data['glazing'],
					'href' 		=> false,
				);
			}

			$data['images'] = array();

			$results = $this->model_catalog_product->getProductImages($this->request->get['product_id']);
			

			foreach ($results as $result) {
				if (strpos($result['image'], ".webp") != false){
					$data['images'][] = array(
						'popup' => 'image/' . $result['image'],
						'thumb' => 'image/' . $result['image']
					);
				} else {
					$data['images'][] = array(
						'popup' => $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height')),
						'thumb' => $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_additional_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_additional_height'))
					);
				}
			}


			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				$data['price'] = (int)$this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax'));
				$data['price'] = number_format($data['price'] , 0, '', ' ');
				$data['installment'] = number_format(round($product_info['price'] / 6) , 0, '', ' ');
			} else {
				$data['price'] = false;
			}

			if ((float)$product_info['special']) {
				$data['special'] = (int)$this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax'));
				$data['special'] = number_format($data['special'] , 0, '', ' ');
				$data['installment'] = number_format(round($product_info['special'] / 6) , 0, '', ' ');
			} else {
				$data['special'] = false;
			}

			if ($this->config->get('config_tax')) {
				$data['tax'] = $this->currency->format((float)$product_info['special'] ? $product_info['special'] : $product_info['price'], $this->session->data['currency']);
			} else {
				$data['tax'] = false;
			}

			$discounts = $this->model_catalog_product->getProductDiscounts($this->request->get['product_id']);

			$data['discounts'] = array();

			foreach ($discounts as $discount) {
				$data['discounts'][] = array(
					'quantity' => $discount['quantity'],
					'price'    => $this->currency->format($this->tax->calculate($discount['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'])
				);
			}

			$data['options'] = array();

			// Проверка на наличие доборов и других выборочных (не radio) опций
			$data['is_options_with_checkboxes'] = 0;

			if ($data['special'])
				$data['price_with_options'] = (int)str_replace(' ', '', $data['special']);
			else
				$data['price_with_options'] = (int)str_replace(' ', '', $data['price']);

			$data['tooltip_to_option_price'] = "Полотно, ";

			foreach ($this->model_catalog_product->getProductOptions($this->request->get['product_id']) as $option) {
				$product_option_value_data = array();

				foreach ($option['product_option_value'] as $option_value) {
					if (!$option_value['subtract'] || ($option_value['quantity'] > 0)) {
						if ((($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) && (float)$option_value['price']) {	
							$price = (int)$this->tax->calculate($option_value['price'], $product_info['tax_class_id'], $this->config->get('config_tax') ? 'P' : false);							
						} else {
							$price = false;
						}

						$product_option_value_data[] = array(
							'product_option_value_id' => $option_value['product_option_value_id'],
							'option_value_id'         => $option_value['option_value_id'],
							'name'                    => $option_value['name'],
							'id_name'              		=> str_replace(' ', '', $option_value['name']),
							'image'                   => $this->model_tool_image->resize($option_value['image'], 50, 50),
							'price'                   => number_format($price, 0, "", " "),
							'price_prefix'            => $option_value['price_prefix']
						);
					}
				}

				$data['options'][] = array(
					'product_option_id'    => $option['product_option_id'],
					'product_option_value' => $product_option_value_data,
					'option_id'            => $option['option_id'],
					'name'                 => $option['name'],
					'type'                 => $option['type'],
					'value'                => $option['value'],
					'required'             => $option['required']
				);

				// Проверка на наличие доборов и других выборочных (не radio) опций
				if ($option['type'] == 'checkbox') {
					$data['is_options_with_checkboxes'] = 1;

					$data['price_with_options'] += (int)str_replace(' ', '', $product_option_value_data[0]['price']) + (int)str_replace(' ', '', $product_option_value_data[1]['price']);
					$data['tooltip_to_option_price'] .= $product_option_value_data[0]['name'] . ' - ' . $product_option_value_data[0]['price'] . ' ₽, ';
					$data['tooltip_to_option_price'] .= $product_option_value_data[1]['name'] . ' - ' . $product_option_value_data[1]['price'] . ' ₽';
				}
			}

			$data['price_with_options'] = number_format($data['price_with_options'], 0, "", " ");

			if ($product_info['minimum']) {
				$data['minimum'] = $product_info['minimum'];
			} else {
				$data['minimum'] = 1;
			}

			$data['review_status'] = $this->config->get('config_review_status');

			if ($this->config->get('config_review_guest') || $this->customer->isLogged()) {
				$data['review_guest'] = true;
			} else {
				$data['review_guest'] = false;
			}

			if ($this->customer->isLogged()) {
				$data['customer_name'] = $this->customer->getFirstName() . '&nbsp;' . $this->customer->getLastName();
			} else {
				$data['customer_name'] = '';
			}

			$data['reviews'] = sprintf($this->language->get('text_reviews'), (int)$product_info['reviews']);
			$data['rating'] = (int)$product_info['rating'];

			// Captcha
			if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('review', (array)$this->config->get('config_captcha_page'))) {
				$data['captcha'] = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha'));
			} else {
				$data['captcha'] = '';
			}

			$data['share'] = $this->url->link('product/product', 'product_id=' . (int)$this->request->get['product_id']);

			$data['products'] = array();

			$results = $this->model_catalog_product->getProductRelated($this->request->get['product_id']);
			
			$related_colors = $this->model_catalog_product->getProductRelatedByColor($this->request->get['product_id']); 
			
			$related_glazings = $this->model_catalog_product->getProductRelatedByGlazing($this->request->get['product_id'], 17);

			foreach ($related_glazings as $id => $glazing) {
				if ($glazing)
					$data['products_related_by_glazing'][] = array(
						'glazing' 		=> $glazing,
						'href' 				=> $this->url->link('product/product', 'product_id=' . $id),
					);
			}

			foreach ($related_colors as $related_color) {
				if (isset($related_color['color_image']) && $related_color['color_image'] !== "")
					$data['products_related_by_color'][] = array(
						'color_image' => $related_color['color_image'],
						'href' 				=> $this->url->link('product/product', 'product_id=' . $related_color['product_id']),
					);
			}
			
			foreach ($results as $result) {
				// Добавляем вывод id главной категории связанного товара
				$main_category = $this->model_catalog_product->getCategories($result['product_id'])[0]["category_id"];
				
				if ($result['compressed_image'] && is_file(DIR_IMAGE . $result['compressed_image'])){
					if (strpos($result['compressed_image'], ".webp") !== false || strpos($result['compressed_image'], ".avif") !== false)
						$image = 'image/' . $result['compressed_image'];		
					else
						$image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));			
				} elseif ($result['image'] && is_file(DIR_IMAGE . $result['image'])) {
					if (strpos($result['image'], ".webp") !== false || strpos($result['image'], ".avif") !== false)
						$image = "/image/".$result['image'];
					else
						$image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
				} else {
					$image = "/image/placeholder.svg";
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					//$price = $this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax'));
				} else {
					$price = false;
				}

				if ((float)$result['special']) {
					$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$special = false;
				}
				
				if (!(int)preg_replace("/[A-zА-я]/","",$price)) continue;

				$result['name'] = trim($result['name']);

				$data['products'][] = array(
					'product_id'  => $result['product_id'],
					'thumb'       => $image,
					'name'        => $result['name'],
					'price'       => $price,
					'special'     => $special,
					'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id']),
					'sale_percent'=> ceil(100 - (((int)preg_replace("/[A-zА-я]/","",$special)) / ((int)preg_replace("/[A-zА-я]/","",$price)) * 100)),
					// 'color_manufacture_img_related' => $color_manufacture_img_related
				);
			}

			$product_accessories = $this->model_catalog_product->getProductAccessories($this->request->get['product_id']);

			foreach ($product_accessories as $accessory) {
				if ($accessory['compressed_image'] && is_file(DIR_IMAGE . $accessory['compressed_image'])){
					if (strpos($accessory['compressed_image'], ".webp") !== false || strpos($accessory['compressed_image'], ".avif") !== false)
						$image = 'image/' . $accessory['compressed_image'];		
					else
						$image = $this->model_tool_image->resize($accessory['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));			
				} elseif ($accessory['image'] && is_file(DIR_IMAGE . $accessory['image'])) {
					if (strpos($accessory['image'], ".webp") !== false || strpos($accessory['image'], ".avif") !== false)
						$image = "/image/".$accessory['image'];
					else
						$image = $this->model_tool_image->resize($accessory['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
				} else {
					$image = "/image/placeholder.svg";
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($accessory['price'], $accessory['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if ((float)$accessory['special']) {
					$special = $this->currency->format($this->tax->calculate($accessory['special'], $accessory['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$special = false;
				}

				$data['product_accessories'][] = array(
					'product_id'  => $accessory['product_id'],
					'thumb'       => $image,
					'name'        => $accessory['name'],
					'price'       => $price,
					'special'     => $special,
					'href'        => $this->url->link('product/product', 'product_id=' . $accessory['product_id']),
					'sale_percent'=> ceil(100 - (((int)preg_replace("/[A-zА-я]/","",$special)) / ((int)preg_replace("/[A-zА-я]/","",$price)) * 100)),
				);
			}

			$data['tags'] = array();

			if ($product_info['tag']) {
				$tags = explode(',', $product_info['tag']);

				foreach ($tags as $tag) {
					$data['tags'][] = array(
						'tag'  => trim($tag),
						'href' => $this->url->link('product/search', 'tag=' . trim($tag))
					);
				}
			}

			$data['recurrings'] = $this->model_catalog_product->getProfiles($this->request->get['product_id']);

			$this->model_catalog_product->updateViewed($this->request->get['product_id']);
			
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');
			if (strpos($_SERVER['REQUEST_URI'],"dveri-vhodnye")) {
				$data["vhodnye"] = true;
			}

			// Определение мобильного устройства
			$mobile_agent_array = array('ipad', 'iphone', 'android', 'pocket', 'palm', 'windows ce', 'windowsce', 'cellphone', 'opera mobi', 'ipod', 'small', 'sharp', 'sonyericsson', 'symbian', 'opera mini', 'nokia', 'htc_', 'samsung', 'motorola', 'smartphone', 'blackberry', 'playstation portable', 'tablet browser');
			$agent = strtolower($_SERVER['HTTP_USER_AGENT']);    
			foreach ($mobile_agent_array as $value) {    
					if (strpos($agent, $value) !== false) $data['mobile'] = "true";   
			}

			$this->response->setOutput($this->load->view('product/product', $data));
		} else {
			$url = '';

			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['manufacturer_id'])) {
				$url .= '&manufacturer_id=' . $this->request->get['manufacturer_id'];
			}

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . $this->request->get['search'];
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . $this->request->get['tag'];
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link('product/product', $url . '&product_id=' . $product_id)
			);

			$this->document->setTitle($this->language->get('text_error'));

			$data['continue'] = $this->url->link('common/home');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');
			if (strpos($_SERVER['REQUEST_URI'],"dveri-vhodnye")) {
				$data["vhodnye"] = true;
			}
			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
		
	}

	public function review() {
		$this->load->language('product/product');

		$this->load->model('catalog/review');

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$data['reviews'] = array();

		$review_total = $this->model_catalog_review->getTotalReviewsByProductId($this->request->get['product_id']);

		$results = $this->model_catalog_review->getReviewsByProductId($this->request->get['product_id'], ($page - 1) * 5, 5);

		foreach ($results as $result) {
			$data['reviews'][] = array(
				'author'     => $result['author'],
				'text'       => nl2br($result['text']),
				'rating'     => (int)$result['rating'],
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added']))
			);
						
				
		}
		
		$pagination = new Pagination();
		$pagination->total = $review_total;
		$pagination->page = $page;
		$pagination->limit = 5;
		$pagination->url = $this->url->link('product/product/review', 'product_id=' . $this->request->get['product_id'] . '&page={page}');

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($review_total) ? (($page - 1) * 5) + 1 : 0, ((($page - 1) * 5) > ($review_total - 5)) ? $review_total : ((($page - 1) * 5) + 5), $review_total, ceil($review_total / 5));

		$this->response->setOutput($this->load->view('product/review', $data));
	}

	public function write() {
		$this->load->language('product/product');

		$json = array();

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 25)) {
				$json['error'] = $this->language->get('error_name');
			}

			if ((utf8_strlen($this->request->post['text']) < 25) || (utf8_strlen($this->request->post['text']) > 1000)) {
				$json['error'] = $this->language->get('error_text');
			}

			if (empty($this->request->post['rating']) || $this->request->post['rating'] < 0 || $this->request->post['rating'] > 5) {
				$json['error'] = $this->language->get('error_rating');
			}

			// Captcha
			if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('review', (array)$this->config->get('config_captcha_page'))) {
				$captcha = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha') . '/validate');

				if ($captcha) {
					$json['error'] = $captcha;
				}
			}

			if (!isset($json['error'])) {
				$this->load->model('catalog/review');

				$this->model_catalog_review->addReview($this->request->get['product_id'], $this->request->post);

				$json['success'] = $this->language->get('text_success');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function getRecurringDescription() {
		$this->load->language('product/product');
		$this->load->model('catalog/product');

		if (isset($this->request->post['product_id'])) {
			$product_id = $this->request->post['product_id'];
		} else {
			$product_id = 0;
		}

		if (isset($this->request->post['recurring_id'])) {
			$recurring_id = $this->request->post['recurring_id'];
		} else {
			$recurring_id = 0;
		}

		if (isset($this->request->post['quantity'])) {
			$quantity = $this->request->post['quantity'];
		} else {
			$quantity = 1;
		}

		$product_info = $this->model_catalog_product->getProduct($product_id);
		$recurring_info = $this->model_catalog_product->getProfile($product_id, $recurring_id);

		$json = array();

		if ($product_info && $recurring_info) {
			if (!$json) {
				$frequencies = array(
					'day'        => $this->language->get('text_day'),
					'week'       => $this->language->get('text_week'),
					'semi_month' => $this->language->get('text_semi_month'),
					'month'      => $this->language->get('text_month'),
					'year'       => $this->language->get('text_year'),
				);

				if ($recurring_info['trial_status'] == 1) {
					$price = $this->currency->format($this->tax->calculate($recurring_info['trial_price'] * $quantity, $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					$trial_text = sprintf($this->language->get('text_trial_description'), $price, $recurring_info['trial_cycle'], $frequencies[$recurring_info['trial_frequency']], $recurring_info['trial_duration']) . ' ';
				} else {
					$trial_text = '';
				}

				$price = $this->currency->format($this->tax->calculate($recurring_info['price'] * $quantity, $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);

				if ($recurring_info['duration']) {
					$text = $trial_text . sprintf($this->language->get('text_payment_description'), $price, $recurring_info['cycle'], $frequencies[$recurring_info['frequency']], $recurring_info['duration']);
				} else {
					$text = $trial_text . sprintf($this->language->get('text_payment_cancel'), $price, $recurring_info['cycle'], $frequencies[$recurring_info['frequency']], $recurring_info['duration']);
				}

				$json['success'] = $text;
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
