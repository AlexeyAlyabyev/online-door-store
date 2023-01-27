<?php
class ControllerProductAddedToCart extends Controller {
	public function index() {

		if (isset($this->request->get['cart_id'])) {
			$cart_id = (int)$this->request->get['cart_id'];
		} else {
			$cart_id = 0;
		}
		
		$data['cart_id'] = $cart_id;

		$products = $this->cart->getProducts();
		// print_r($products);

		$product_key = array_search($cart_id, array_column($products, "cart_id"));

		$product_in_cart = $products[$product_key];
		// print_r($product_in_cart);

		if ($product_in_cart){

			$data['cart_total'] = 0;
			$data['cart_quantity'] = 0;

			foreach ($products as $product){
				$data['cart_total'] += $product['total'];
				$data['cart_quantity'] += $product['quantity'];
			}
			$data['cart_total'] = number_format($data['cart_total'] , 0, '', ' ');

			$plural = $data['cart_quantity'] % 10;

			if ($data['cart_quantity'] > 10 && $data['cart_quantity'] < 21 || !$plural || $plural > 4) 
				$data['cart_quantity'] = $data['cart_quantity'] . " товаров";
			elseif ($plural > 1 && $plural < 5) 
				$data['cart_quantity'] = $data['cart_quantity'] . " товара";
			else
				$data['cart_quantity'] = $data['cart_quantity'] . " товар";

			$data['quantity'] = $product_in_cart['quantity'];
			$data['total'] = number_format($product_in_cart['total'] , 0, '', ' ');
			
			$this->load->model('catalog/product');

			$data['heading_title'] = trim($product_in_cart['name']);			

			$data['product_color'] = $this->model_catalog_product->getProductAttributeValue($product_in_cart['product_id'], 12);
			if ($data['product_color'] == "") $data['product_color'] = $this->model_catalog_product->getProductAttributeValue($product_in_cart['product_id'], 51);

			$this->load->model('tool/image');

			if (strpos($product_in_cart['image'], ".webp") != false){
				$data['thumb'] = 'image/' . $product_in_cart['image'];
			} elseif ($product_in_cart['image']) {
				$data['thumb'] = $this->model_tool_image->resize($product_in_cart['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_height'));
			} else {
				$data['thumb'] = '';
			}

			$related_products = $this->model_catalog_product->getProductRelated($product_in_cart['product_id']);

			// $related_products = array_slice($related_products, 0, 4);

			foreach ($related_products as $product){
				$related_product['category_id'] = $this->model_catalog_product->getProductMainCategory($product['product_id']);
				if ($related_product['category_id'] == 61){
					$related_product['product_id'] = $product['product_id'];
					$related_product['name'] = $product['name'];

					if (strpos($product['image'], ".webp") != false){
						$related_product['thumb'] = 'image/' . $product['image'];
					} elseif ($product['image']) {
						$related_product['thumb'] = $this->model_tool_image->resize($product['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_height'));
					} else {
						$related_product['thumb'] = '';
					}

					if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
						$related_product['price'] = (int)$this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'));
						$related_product['price'] = number_format($related_product['price'] , 0, '', ' ');
					} else {
						$related_product['price'] = false;
					}
		
					if ((float)$product['special']) {
						$related_product['special'] = (int)$this->tax->calculate($product['special'], $product['tax_class_id'], $this->config->get('config_tax'));
						$related_product['special'] = number_format($related_product['special'] , 0, '', ' ');
					} else {
						$related_product['special'] = false;
					}

					$related_product['href'] = $this->url->link('product/product', 'product_id=' . $product['product_id']);

					$data['related'][] = $related_product;
				}
			}

			print_r($data['related']);

			$this->response->setOutput($this->load->view('product/added_to_cart', $data));		
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

		return $this->load->view('product/added_to_cart', $data);		
	}

	public function update() {
		if (isset($this->request->get['cart_id'])) {
			$cart_id = (int)$this->request->get['cart_id'];
		} else {
			$cart_id = 0;
		}

		$products = $this->cart->getProducts();

		$data['cart_total'] = 0;
		$data['cart_quantity'] = 0;

		foreach ($products as $product){
			$data['cart_total'] += $product['total'];
			$data['cart_quantity'] += $product['quantity'];
		}
		$data['cart_total'] = number_format($data['cart_total'] , 0, '', ' ');

		$plural = $data['cart_quantity'] % 10;

		if ($data['cart_quantity'] > 10 && $data['cart_quantity'] < 21 || !$plural || $plural > 4) 
			$data['cart_quantity_text'] = $data['cart_quantity'] . " товаров";
		elseif ($plural > 1 && $plural < 5) 
			$data['cart_quantity_text'] = $data['cart_quantity'] . " товара";
		else
			$data['cart_quantity_text'] = $data['cart_quantity'] . " товар";

		$product_key = array_search($cart_id, array_column($products, "cart_id"));

		$product_in_cart = $products[$product_key];

		$data['quantity'] = $product_in_cart['quantity'];
		$data['total'] = number_format($product_in_cart['total'] , 0, '', ' ');

		$this->response->setOutput(json_encode($data));
	}
}