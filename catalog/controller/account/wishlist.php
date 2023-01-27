<?php
class ControllerAccountWishList extends Controller {
	public function index() {

		$this->load->language('account/wishlist');

		$this->load->model('catalog/product');

		$this->load->model('tool/image');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		// $data['breadcrumbs'][] = array(
		// 	'text' => $this->language->get('text_account'),
		// 	'href' => $this->url->link('account/account', '', true)
		// );

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('account/wishlist')
		);

		// Определение мобильного устройства
		$data['mobile'] = false;

		$mobile_agents = array('ipad', 'iphone', 'android', 'pocket', 'palm', 'windows ce', 'windowsce', 'cellphone', 'opera mobi', 'ipod', 'small', 'sharp', 'sonyericsson', 'symbian', 'opera mini', 'nokia', 'htc_', 'samsung', 'motorola', 'smartphone', 'blackberry', 'playstation portable', 'tablet browser');
		$agent = strtolower($_SERVER['HTTP_USER_AGENT']);    
		foreach ($mobile_agents as $value) {    
				if (strpos($agent, $value) !== false) {
					$data['mobile'] = "true";
					break;
				}
		}

		$data['products'] = array();

		if (isset($_COOKIE['wishlist'])) {
			foreach (json_decode($_COOKIE['wishlist']) as $product_id) {
				$result = $this->model_catalog_product->getProduct($product_id);
	
				if ($result) {
					if ($result['stock_status_id'] == 5) continue;
	
					// Получаем из аттрибутов цвет двери
					$result['product_color'] = "";
	
					if (!$data['mobile'] ) {
						$attribute_groups = $this->model_catalog_product->getProductAttributes($result['product_id']);
	
						if (!empty($attribute_groups)) {
							foreach ($attribute_groups[0]['attribute'] as $attribute_group){
								if ($attribute_group['attribute_id'] == 12) $result['product_color'] = $attribute_group['text'];
								if ($attribute_group['attribute_id'] == 51) $result['product_color'] = $attribute_group['text'];
							}
						}
					}
	
					// Подгружаем сжатое изображение картинки в общий список каталога
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
	
					// РАЗБИВАЕМ ЦЕНУ НА РАЗРЯДЫ
					$price = str_replace(preg_replace("/[^0-9]/", '', $price), number_format((float)preg_replace("/[^0-9]/", '', $price), 0, '', ' '), $price);
	
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
						'price'       => $price,
						'special'     => $special,
						'product_color'=> $result['product_color'],
						'href'        => $this->url->link('product/product', '&product_id=' . $result['product_id']),
						'special_label' => $special_label,
						'reserve_img'  => $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'))
					);
				} 
				// else {
				// 	$this->model_account_wishlist->deleteWishlist($product_id);
				// }
			}
		}		

		// $data['continue'] = $this->url->link('account/account', '', true);

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('account/wishlist', $data));
	}

	public function add() {
		$this->load->language('account/wishlist');

		$json = array();

		if (isset($this->request->post['product_id'])) {
			$product_id = $this->request->post['product_id'];
		} else {
			$product_id = 0;
		}

		$this->load->model('catalog/product');

		$product_info = $this->model_catalog_product->getProduct($product_id);

		if ($product_info) {
			if ($this->customer->isLogged()) {
				// Edit customers cart
				$this->load->model('account/wishlist');

				$this->model_account_wishlist->addWishlist($this->request->post['product_id']);

				$json['success'] = sprintf($this->language->get('text_success'), $this->url->link('product/product', 'product_id=' . (int)$this->request->post['product_id']), $product_info['name'], $this->url->link('account/wishlist'));

				$json['total'] = sprintf($this->language->get('text_wishlist'), $this->model_account_wishlist->getTotalWishlist());
			} else {
				if (!isset($this->session->data['wishlist'])) {
					$this->session->data['wishlist'] = array();
				}

				$this->session->data['wishlist'][] = $this->request->post['product_id'];

				$this->session->data['wishlist'] = array_unique($this->session->data['wishlist']);

				$json['success'] = sprintf($this->language->get('text_login'), $this->url->link('account/login', '', true), $this->url->link('account/register', '', true), $this->url->link('product/product', 'product_id=' . (int)$this->request->post['product_id']), $product_info['name'], $this->url->link('account/wishlist'));

				$json['total'] = sprintf($this->language->get('text_wishlist'), (isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : 0));
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
