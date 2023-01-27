<?php
class ControllerProductQuickView extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('product/product');

		if (isset($this->request->get['product_id'])) {
			$product_id = (int)$this->request->get['product_id'];
		} else {
			$product_id = 0;
		}

		$this->load->model('catalog/product');

		$product_info = $this->model_catalog_product->getProduct($product_id);

		if ($product_info) {
			$data['attribute_groups'] = $this->model_catalog_product->getProductAttributes($this->request->get['product_id']);

			$data['attributes_to_show'] = [24, 26, 17, 25, 12, 13, 14, 23, 18, 37, 35, 36, 39, 46];

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

			if (isset($product_info['h1']) && $product_info['h1'] != "")
				$data['heading_title'] = trim($product_info['h1']);				
			else
				$data['heading_title'] = trim($product_info['name']);

			$data['product_id'] = (int)$this->request->get['product_id'];			

			$data['stock_status_id'] = $product_info['stock_status_id'];

			$this->load->model('tool/image');

			if ($product_info['image']) {
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
					'product_id'	=> $product_id,
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
				$data['images'][] = array(
					'popup' => $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height')),
					'thumb' => $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_additional_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_additional_height'))
				);
				
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

			$data['options'] = array();

			// Проверка на наличие доборов и других выборочных (не radio) опций
			$data['is_options_with_checkboxes'] = 0;

			$data['price_with_options'] = (int)str_replace(' ', '', $data['price']);

			if ($product_info['minimum']) {
				$data['minimum'] = $product_info['minimum'];
			} else {
				$data['minimum'] = 1;
			}

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

			$data['products'] = array();
			
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
						'product_id' 	=> $related_color['product_id'],
						'color_image' => $related_color['color_image'],
						'href' 				=> $this->url->link('product/product', 'product_id=' . $related_color['product_id']),
					);
			}

			$data['href'] = $this->url->link('product/product', 'product_id=' . $product_id);

			$data['recurrings'] = $this->model_catalog_product->getProfiles($this->request->get['product_id']);

			$this->model_catalog_product->updateViewed($this->request->get['product_id']);

			$this->response->setOutput($this->load->view('product/quick_view', $data));
		} else {
			$url = '';

			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
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
}