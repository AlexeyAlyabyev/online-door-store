<?php
class ControllerExtensionModuleCarousel extends Controller {
	public function index($setting) {
		static $module = 0;

		$this->load->model('design/banner');
		$this->load->model('tool/image');
		$this->load->model('catalog/product');
		
		$this->document->addStyle('catalog/view/javascript/jquery/swiper/css/swiper.min.css');
		$this->document->addStyle('catalog/view/javascript/jquery/swiper/css/opencart.css');
		$this->document->addScript('catalog/view/javascript/jquery/swiper/js/swiper.jquery.js');

		$data['banners'] = array();

		$results = $this->model_design_banner->getBanner($setting['banner_id']);

		foreach ($results as $result) {
			if (is_file(DIR_IMAGE . $result['image'])) {
				$data['banners'][] = array(
					'title' => $result['title'],
					'link'  => $result['link'],
					'image' => $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height'])
				);
			}
		}
		$data['total_products'] = $this->cart->countProducts();

		$data['module'] = $module++;

		$data['ekoshpon'] = $this->getCategoryMinPrice(67);

		$results = $this->model_catalog_product->getProducts(array(
			'filter_tag' => 'Хит'
		));
		foreach ($results as $result) {
			if ($result['price'] == 0) continue;
			if ($result['image']) {
				$image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
			} else {
				$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
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

			$data['hitproducts'][] = array(
				'product_id'  => $result['product_id'],
				'thumb'       => $image,
				'name'        => $result['name'],
				'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
				'price'       => $price,
				'special'     => $special,
				'tax'         => $tax,
				'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
				'rating'      => $result['rating'],
				'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id']),
				'sale_percent'=> ceil(100 - (((int)preg_replace("/[A-zА-я]/","",$special)) / ((int)preg_replace("/[A-zА-я]/","",$price)) * 100)),
				'tag'         => $result['tag'],
				'color_manufacture_img' => $color_manufacture_img,
				'related_products_colors' => $related_products_colors
			);
		}

		

		return $this->load->view('extension/module/carousel', $data);
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