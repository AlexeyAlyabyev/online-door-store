<?php
class ControllerProductSpecial extends Controller {
	public function index() {

		// Last-Modified
		$template_name = "product/special";
		$template_path = DIR_TEMPLATE . $this->config->get('theme_default_directory') . "/template/"  . $template_name . ".twig";
		$LastModified_unix = max(filectime($template_path), filectime(__FILE__));
		$LastModified = gmdate("D, d M Y H:i:s GMT", $LastModified_unix);
		
		$ifModified = false;
		$ifModified = strtotime(substr($_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? '', 5));

		if ($ifModified && $ifModified >= $LastModified_unix) {
			header($_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified');
			exit;
		}
		$this->response->addHeader('Last-Modified: '. $LastModified);
		// ---------------
		
		$this->load->language('product/special');

		$this->load->model('catalog/product');

		$this->load->model('tool/image');

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'p.sort_order';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		if (isset($this->request->get['limit'])) {
			$limit = (int)$this->request->get['limit'];
		} else {
			$limit = $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit');
		}

		$data['zone_id'] = $this->config->get('config_zone_id');
		$this->load->model('localisation/zone');
		$city = $this->model_localisation_zone->getZone($data['zone_id'])['name'];

		$meta_title = 'Акции и спец. предложения на межкомнатные и входные двери - 100 Дверей '.$city;
		$meta_description = '✅ Выгодные акции и специальные предложения на межкомнатные и входные двери каждую неделю!';

		if (isset($_GET['page']))  {
			$meta_title .= " | Страница ".$_GET['page'];
			$meta_description .= " | Страница ".$_GET['page'];
			$data['page'] = " | Страница ".$_GET['page'];
		}

		//$this->document->setTitle($this->language->get('heading_title'));
		$this->document->setTitle($meta_title);
		$this->document->setDescription($meta_description);
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
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

		if (isset($this->request->get['filter'])) {
			$url .= '&filter=' . $this->request->get['filter'];
		}

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('product/special', $url)
		);

		$data['text_compare'] = sprintf($this->language->get('text_compare'), (isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0));

		$data['compare'] = $this->url->link('product/compare');

		$data['products'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $limit,
			'limit' => $limit
		);

		$product_total = $this->model_catalog_product->getTotalProductSpecials();

		// Передаем количество товаров в шаблон. Смена окончания в слове "товаров" в зависимости от числа
		function morph($n, $value1, $value2, $value5) {
			$n = abs($n) % 100;
			$n1= $n % 10;
			if ($n>10 && $n<20) return $value5;
			if ($n1>1 && $n1<5) return $value2;
			if ($n1==1) return $value1;
			return $value5;
		}
		$value_text_pagination = morph($product_total, $this->language->get('text_pagination_1'), $this->language->get('text_pagination_2'), $this->language->get('text_pagination_5'));
		$data['product_total'] = sprintf($value_text_pagination, ($product_total));

		$results = $this->model_catalog_product->getProductSpecials($filter_data);

		foreach ($results as $result) {
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
			} else {
				$price = false;
			}

			if ((float)$result['special']) {
				$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
			} else {
				$special = false;
			}

			if ($this->config->get('config_tax')) {
				$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
			} else {
				$tax = false;
			}

			if ($this->config->get('config_review_status')) {
				$rating = (int)$result['rating'];
			} else {
				$rating = false;
			}

			if ($special) {
				$special = str_replace(preg_replace("/[^0-9]/", '', $special), number_format(preg_replace("/[^0-9]/", '', $special), 0, '', ' '), $special);
				$special_label = round((1 - $result['special'] / $result['price']) * 100);
			}
			else 
				$special_label = "";

			$words_to_replace = array("Межкомнатная дверь ", "Входная дверь ", "Серая", "Белая", "Межкомнатная ", "дверь ");
			$result['name'] = str_replace($words_to_replace, "", $result['name']);
			$result['name'] = ucfirst($result['name']);

			$data['products'][] = array(
				'product_id'  => $result['product_id'],
				'thumb'       => $image,
				'name'        => $result['name'],
				'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
				'price'       => $price,
				'special'     => $special,
				'special_label'=> $special_label,
				'tax'         => $tax,
				'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
				'rating'      => $result['rating'],
				'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'] . $url)
			);
		}

		$url = '';

		if (isset($this->request->get['limit'])) {
			$url .= '&limit=' . $this->request->get['limit'];
		}

		if (isset($this->request->get['filter'])) {
			$url .= '&filter=' . $this->request->get['filter'];
		}

		$data['sorts'] = array();

		$data['sorts'][] = array(
			'text'  => 'Дешевле',
			'value' => 'ps.price-ASC',
			'href'  => $this->url->link('product/special', 'sort=ps.price&order=ASC' . $url),
			'img'		=> 'image/category/low-price.svg'
		);

		$data['sorts'][] = array(
			'text'  => 'Дороже',
			'value' => 'ps.price-DESC',
			'href'  => $this->url->link('product/special', 'sort=ps.price&order=DESC' . $url),
			'img'		=> 'image/category/hight-price.svg'
		);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['filter'])) {
			$url .= '&filter=' . $this->request->get['filter'];
		}

		$data['limits'] = array();

		$limits = array_unique(array($this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit'), 25, 50, 75, 100));

		sort($limits);

		foreach($limits as $value) {
			$data['limits'][] = array(
				'text'  => $value,
				'value' => $value,
				'href'  => $this->url->link('product/special', $url . '&limit=' . $value)
			);
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['limit'])) {
			$url .= '&limit=' . $this->request->get['limit'];
		}

		if (isset($this->request->get['filter'])) {
			$url .= '&filter=' . $this->request->get['filter'];
		}

		if (isset($this->request->get['rdrf'])) {
			foreach ($this->request->get['rdrf'] as $key => $filter_param){
				if (is_array($filter_param)) {
					foreach ($filter_param as	$lower_key => $param){
						if (is_array($param))
							foreach ($param as $index => $value){
								$url .= '&rdrf['.$key.']['.$lower_key.']['.$index.']=' . $value;
							}
						else
							$url .= '&rdrf['.$key.']['.$lower_key.']=' . $param;
					}
				}
				else {
					$url .= '&rdrf['.$key.']=' . $filter_param;
				}
			}
		}

		$pagination = new Pagination();
		$pagination->total = $product_total;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->url = $this->url->link('product/special', $url . '&page={page}');

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($product_total - $limit)) ? $product_total : ((($page - 1) * $limit) + $limit), $product_total, ceil($product_total / $limit));

		// http://googlewebmastercentral.blogspot.com/2011/09/pagination-with-relnext-and-relprev.html
		// if ($page == 1 && $this->config->get('config_language') == "ru-ru") {
				$this->document->addLink($this->url->link('product/special', '', true), 'canonical');
		// } else if ($this->config->get('config_language') == "ru-ru") {
		// 		$this->document->addLink($this->url->link('product/special', 'page='. $page , true), 'canonical');
		// }
		
		if ($page > 1) {
			$this->document->addLink($this->url->link('product/special', (($page - 2) ? '&page='. ($page - 1) : ''), true), 'prev');
		}

		if ($limit && ceil($product_total / $limit) > $page) {
				$this->document->addLink($this->url->link('product/special', 'page='. ($page + 1), true), 'next');
		}

		$filter_data = array(
			'filter_name'         => "межкомнатная дверь гост",
			'sort'                => "p.price",
			'order'               => "ASC",
			'limit'               => 1
		);
		$results = $this->model_catalog_product->getProducts($filter_data);
		$results = array_shift($results);
		$data["gost_price"] = number_format($results['special'], 0, " ", " ");

		$filter_data = array(
			'filter_name'         => "Мастер",
			'sort'                => "p.price",
			'order'               => "ASC",
			'limit'               => 1
		);
		$results = $this->model_catalog_product->getProducts($filter_data);
		$results = array_shift($results);
		$data["master_price"] = number_format($results['price'], 0, " ", " ");
		$data["master_special"] = number_format($results['special'], 0, " ", " ");

		$data['sort'] = $sort;
		$data['order'] = $order;
		$data['limit'] = $limit;

		$data['continue'] = $this->url->link('common/home');
		
		$this->document->addScript('catalog/view/javascript/jquery/swiper/js/swiper.min.js', 'footer');
		$this->document->addStyle('catalog/view/javascript/jquery/swiper/css/swiper.min.css');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		if (count($_GET) - 1)
			$data['cards'] = false;
		else
			$data['cards'] = !isset($_GET['no_cards']);

		// Определение мобильного устройства
		$mobile_agent_array = array('ipad', 'iphone', 'android', 'pocket', 'palm', 'windows ce', 'windowsce', 'cellphone', 'opera mobi', 'ipod', 'small', 'sharp', 'sonyericsson', 'symbian', 'opera mini', 'nokia', 'htc_', 'samsung', 'motorola', 'smartphone', 'blackberry', 'playstation portable', 'tablet browser');
		$agent = strtolower($_SERVER['HTTP_USER_AGENT']);    
		foreach ($mobile_agent_array as $value) {    
				if (strpos($agent, $value) !== false) $data['mobile'] = "true";   
		}

		$data["language"] = $this->config->get('config_language');

		$this->response->setOutput($this->load->view('product/special', $data));
	}
}
