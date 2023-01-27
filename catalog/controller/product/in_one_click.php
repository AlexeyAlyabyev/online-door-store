<?php
class ControllerProductInOneClick extends Controller {
	public function index() {

		$this->load->language('product/in_one_click');

		if (isset($this->request->get['product_id'])) {
			$product_id = (int)$this->request->get['product_id'];
		} else {
			$product_id = 0;
		}

		$data['product_id'] = $product_id;

		// Определение мобильного устройства
		$mobile_agent_array = array('ipad', 'iphone', 'android', 'pocket', 'palm', 'windows ce', 'windowsce', 'cellphone', 'opera mobi', 'ipod', 'small', 'sharp', 'sonyericsson', 'symbian', 'opera mini', 'nokia', 'htc_', 'samsung', 'motorola', 'smartphone', 'blackberry', 'playstation portable', 'tablet browser');
		$agent = strtolower($_SERVER['HTTP_USER_AGENT']);    
		foreach ($mobile_agent_array as $value) {    
				if (strpos($agent, $value) !== false) $data['mobile'] = "true";   
		}

		$this->load->model('catalog/product');

		$product_info = $this->model_catalog_product->getProduct($product_id);

		if ($product_info){

			if (isset($product_info['h1']) && $product_info['h1'] != "")
				$data['heading_title'] = trim($product_info['h1']);				
			else
				$data['heading_title'] = trim($product_info['name']);			

			$data['product_color'] = $this->model_catalog_product->getProductAttributeValue($product_id, 12);
			if ($data['product_color'] == "") $data['product_color'] = $this->model_catalog_product->getProductAttributeValue($product_id, 51);

			$data['stock_status_id'] = $product_info['stock_status_id'];

			// Передаем id корневой категории
			$category_data = $this->model_catalog_product->getCategories($product_id);
			$data["main_category"] = $category_data[0]['category_id'];

			$this->load->model('tool/image');

			if (strpos($product_info['image'], ".webp") != false){
				$data['thumb'] = 'image/' . $product_info['image'];
			} elseif ($product_info['image']) {
				$data['thumb'] = $this->model_tool_image->resize($product_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_height'));
			} else {
				$data['thumb'] = '';
			}

			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				$data['price'] = (int)$this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax'));
				$data['price'] = number_format($data['price'] , 0, '', ' ');
			} else {
				$data['price'] = false;
			}

			if ((float)$product_info['special']) {
				$data['special'] = (int)$this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax'));
				$data['special'] = number_format($data['special'] , 0, '', ' ');
			} else {
				$data['special'] = false;
			}

			// Проверка на наличие доборов и других выборочных (не radio) опций
			$data['is_options_with_checkboxes'] = 0;

			$data['price_with_options'] = (int)str_replace(' ', '', $data['price']);

			$data['tooltip_to_option_price'] = "Полотно, ";

			foreach ($this->model_catalog_product->getProductOptions($product_id) as $option) {
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
					$data['price_with_options'] = number_format($data['price_with_options'] , 0, '', ' ');
					$data['tooltip_to_option_price'] .= $product_option_value_data[0]['name'] . ' - ' . $product_option_value_data[0]['price'] . ' ₽, ';
					$data['tooltip_to_option_price'] .= $product_option_value_data[1]['name'] . ' - ' . $product_option_value_data[1]['price'] . ' ₽';
				}
			}

			$this->response->setOutput($this->load->view('product/in_one_click', $data));		
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link('product/in_one_click')
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

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}
}