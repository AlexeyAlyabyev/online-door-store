<?php
class ControllerCommonHome extends Controller {
	public function index() {

		// Last-Modified
		$template_name = "common/home";
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

		$this->load->model('catalog/product');

		$this->document->setTitle($this->config->get('config_meta_title'));
		$this->document->setDescription($this->config->get('config_meta_description'));

		if (isset($this->request->get['route']) && $this->config->get('config_language') == "ru-ru") {
			$this->document->addLink($this->config->get('config_url'), 'canonical');
		}

		$this->document->addScript('catalog/view/theme/custom/js/slick.js', 'footer');
		$this->document->addScript('catalog/view/javascript/jquery/swiper/js/swiper.min.js', 'footer');
		$this->document->addStyle('catalog/view/javascript/jquery/swiper/css/swiper.min.css');

		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		// Проверка на спам
		$data['spam_check'] = md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT']);
		// Проверка на спам

		$data['mobile'] = false;
		// Определение мобильного устройства
		$mobile_agent_array = array('ipad', 'iphone', 'android', 'pocket', 'palm', 'windows ce', 'windowsce', 'cellphone', 'opera mobi', 'ipod', 'small', 'sharp', 'sonyericsson', 'symbian', 'opera mini', 'nokia', 'htc_', 'samsung', 'motorola', 'smartphone', 'blackberry', 'playstation portable', 'tablet browser');
		$agent = strtolower($_SERVER['HTTP_USER_AGENT']);
		foreach ($mobile_agent_array as $value) {
			if (strpos($agent, $value) !== false) $data['mobile'] = "true";
		}
		// Определение мобильного устройства

		// Список акционных дверей
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

		$filter_data = array(
			'limit'  => 20,
			'start'  => 0
		);

		$results = $this->model_catalog_product->getProductSpecials($filter_data);
		// print_r($results);
		foreach ($results as $result) {
			if ($result['price'] == 0) continue;
			
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

			// Получаем и выводим цвета и цвета связанных товаров
			$product_info = $this->model_catalog_product->getProduct($result['product_id']);
			$related_products = $this->model_catalog_product->getProductRelated($result['product_id']);
			$color_manufacture_img = "";
			$product_atr = $this->model_catalog_product->getProductAttributes($result['product_id']);

			foreach ($product_atr[0]['attribute'] as $attribute){
				if ($attribute['attribute_id'] == 54)
					$color_manufacture_img = $product_info['manufacturer_id'].'/'.$attribute['text'].'.png';
			}

			$related_products_colors = [];

			foreach ($related_products as $key => $product){
				if ($result['name'] != $product['name']) continue;

				$related_product_atr = $this->model_catalog_product->getProductAttributes($product['product_id']);

				foreach ($related_product_atr[0]['attribute'] as $attribute){
					if ($attribute['attribute_id'] == 54)
						$related_products_colors[$key][] = $product_info['manufacturer_id'].'/'.$attribute['text'].'.png';
				}
					if (isset($related_products_colors[$key][0])) $related_products_colors[$key][] = $this->url->link('product/product', 'product_id=' . $product['product_id']);
			}

			$result['name'] = str_replace("Входная дверь ","",$result['name']);
			$result['name'] = str_replace("Межкомнатная дверь ","",$result['name']);

			$sql = "SELECT * FROM `oc_product_to_category` WHERE `product_id`='".$result['product_id']."' AND `main_category`='1'";
			$query_result = $this->db->query($sql);
			$main_parent_category = $query_result->rows[0]['category_id'];

			$data['products'][] = array(
				'product_id'  => $result['product_id'],
				'thumb'       => $image,
				'name'        => $result['name'],
				'price'       => $price,
				'special'     => $special,
				'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
				'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'] . $url),
				'sale_percent'=> ceil(100 - (((int)preg_replace("/[A-zА-я]/","",$special)) / ((int)preg_replace("/[A-zА-я]/","",$price)) * 100)),
				'color_manufacture_img' => $color_manufacture_img,
				'related_products_colors' => $related_products_colors,
				'viewed' => $result['viewed'],
				'main_category' => $main_parent_category,
				'reserve_img'  => $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'))
			);
		}

		// Сортируем акционные товары по убыванию популярности
		foreach ($data['products'] as $key => $row) {
			$viewed[$key]  = $row['viewed'];
			$main_category[$key]  = $row['main_category'];
		}
		array_multisort($main_category, SORT_ASC, $viewed, SORT_DESC, $data['products']);

		// Минимальная цена на межкомнатные двери
		$data['min_mezh_price'] = $this->getCategoryMinPrice(59);

		// Минимальная цена на входные двери
		$data['min_vhod_price'] = $this->getCategoryMinPrice(60);

		// if (!$data['mobile']) {

			// Минимальная цена на двери гост
			$filter_data = array(
				'filter_name'         => "межкомнатная дверь гост",
				'sort'                => "p.price",
				'order'               => "ASC",
				'limit'               => 1
			);
			$results = $this->model_catalog_product->getProducts($filter_data);
			$results = array_shift($results);
			$data["gost_price"] = number_format($results['special'], 0, " ", " ");
			// Минимальная цена на двери мастер
			// $filter_data = array(
			// 	'filter_name'         => "Мастер",
			// 	'sort'                => "p.price",
			// 	'order'               => "ASC",
			// 	'limit'               => 1
			// );
			// $results = $this->model_catalog_product->getProducts($filter_data);
			// $results = array_shift($results);
			// $data["master_price"] = number_format($results['price'], 0, " ", " ");
			// $data["master_special"] = number_format($results['special'], 0, " ", " ");

			// Минимальная цена на фурнитуру
			$data['min_furn_price'] = $this->getCategoryMinPrice(61);

			// Минимальная цена на дверные ручки
			$data['min_ruchk_price'] = $this->getCategoryMinPrice(72);

			// Минимальная цена на межки по подкатегориям
			$data['ekoshpon'] = $this->getCategoryMinPrice(67);
			$data['ekoshpon3d'] = $this->getCategoryMinPrice(78);
			$data['shponirovannie'] = $this->getCategoryMinPrice(62);
			$data['shpon_ekonom'] = $this->getCategoryMinPrice(114);
			$data['shpon_duba'] = $this->getCategoryMinPrice(70);
			$data['krashennie'] = $this->getCategoryMinPrice(64);
			$data['glyancevie'] = $this->getCategoryMinPrice(63);
			$data['euroshpon'] = $this->getCategoryMinPrice(69);
			$data['pvh'] = $this->getCategoryMinPrice(79);
			$data['steklyanie'] = $this->getCategoryMinPrice(119);
			$data['massiv100'] = $this->getCategoryMinPrice(120);
			$data['sale50_mezh'] = $this->getCategoryMinPrice(240);

			// Минимальная цена на входные по подкатегориям
			$data['dlya_kvartiri'] = $this->getCategoryMinPrice(65);
			$data['dlya_doma'] = $this->getCategoryMinPrice(66);
			$data['ulichnie'] = $this->getCategoryMinPrice(148);
			$data['tamburnie'] = $this->getCategoryMinPrice(149);
			$data['s_termorazrivom'] = $this->getCategoryMinPrice(127);
			$data['s_zerkalom'] = $this->getCategoryMinPrice(128);
			$data['shumoizolyacionnie'] = $this->getCategoryMinPrice(144);
			$data['sale50_vhod'] = $this->getCategoryMinPrice(150);

			$dir_grid_mezh_vhod = $_SERVER['DOCUMENT_ROOT'].'/catalog/view/theme/custom/image/grid_mezh_vhod';
			$grid_mezh_vhod = array_diff(scandir($dir_grid_mezh_vhod, $sorting_order= SCANDIR_SORT_DESCENDING ), array('..','.'));
			$data['grid_mezh_vhod'] = array_slice($grid_mezh_vhod, 0, 10);
		// }

		$data['telephone'] = $this->config->get('config_telephone');
		$data["language"] = $this->config->get('config_language');

		$this->response->setOutput($this->load->view('common/home', $data));
	}

	public function getCategoryMinPrice($category_id){
		$min_category_price = $this->model_catalog_product->getProducts(array('filter_category_id' => $category_id,'sort' => 'p.price','order' => 'ASC','limit'=> 1));
		$min_category_price = array_shift($min_category_price);
		if (isset($min_category_price["special"]))
			return number_format(round($min_category_price["special"]), 0, '', ' ');
		else
			return number_format(round($min_category_price["price"]), 0, '', ' ');
	}

}
