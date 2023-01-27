<?php
class ControllerCheckoutOneClickBuy extends Controller {
	public function index() {
		// ГОСТЬ (checkout/guest/save)
		$customer_group_id = $this->config->get('config_customer_group_id');

		$this->session->data['guest']['customer_group_id'] = $customer_group_id;
		$this->session->data['guest']['telephone'] = $this->request->post['telephone'];

		// ЗАПИСЬ ТОВАРА В БД (checkout/confirm)
		$order_data = array();

		$totals = array();
		$taxes = $this->cart->getTaxes();
		$total = 0;

		// Because __call can not keep var references so we put them into an array.
		$total_data = array(
			'totals' => &$totals,
			'taxes'  => &$taxes,
			'total'  => &$total
		);

		$this->load->model('setting/extension');

		$sort_order = array();

		$results = $this->model_setting_extension->getExtensions('total');

		foreach ($results as $key => $value) {
			$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
		}

		array_multisort($sort_order, SORT_ASC, $results);

		foreach ($results as $result) {
			if ($this->config->get('total_' . $result['code'] . '_status')) {
				$this->load->model('extension/total/' . $result['code']);

				// We have to put the totals in an array so that they pass by reference.
				$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
			}
		}

		$sort_order = array();

		foreach ($totals as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}

		array_multisort($sort_order, SORT_ASC, $totals);

		$order_data['totals'] = $totals;

		$this->load->language('checkout/checkout');

		$order_data['invoice_prefix'] = $this->config->get('config_invoice_prefix');
		$order_data['store_id'] = $this->config->get('config_store_id');
		$order_data['store_name'] = $this->config->get('config_name');

		if ($order_data['store_id']) {
			$order_data['store_url'] = $this->config->get('config_url');
		} else {
			if ($this->request->server['HTTPS']) {
				$order_data['store_url'] = HTTPS_SERVER;
			} else {
				$order_data['store_url'] = HTTP_SERVER;
			}
		}
		
		$this->load->model('account/customer');

		$order_data['customer_id'] = 0;
		$order_data['customer_group_id'] = $this->session->data['guest']['customer_group_id'];

		$order_data['telephone'] = $this->session->data['guest']['telephone'];
			
		$order_data['shipping_firstname'] = '';
		$order_data['shipping_lastname'] = '';
		$order_data['shipping_company'] = '';
		$order_data['shipping_address_1'] = '';
		$order_data['shipping_address_2'] = '';
		$order_data['shipping_city'] = '';
		$order_data['shipping_postcode'] = '';
		$order_data['shipping_zone'] = '';
		$order_data['shipping_zone_id'] = '';
		$order_data['shipping_country'] = '';
		$order_data['shipping_country_id'] = '';
		$order_data['shipping_address_format'] = '';
		$order_data['shipping_custom_field'] = array();
		$order_data['shipping_method'] = '';
		$order_data['shipping_code'] = '';

		$order_data['firstname'] = "Заказ в 1 клик";
		$order_data['lastname'] = "";
		$order_data['email'] = $this->config->get('config_email');
		$order_data['fax'] = "";

		$order_data['payment_firstname'] = "";
		$order_data['payment_lastname'] = "";
		$order_data['payment_company'] = "";
		$order_data['payment_address_1'] = "";
		$order_data['payment_address_2'] = "";
		$order_data['payment_city'] = "";
		$order_data['payment_postcode'] = "";
		$order_data['payment_country'] = "";
		$order_data['payment_country_id'] = "";
		$order_data['payment_zone'] = "";
		$order_data['payment_zone_id'] = "";
		$order_data['payment_address_format'] = "";
		$order_data['payment_custom_field'] = array();
		$order_data['payment_method'] = "";
		$order_data['payment_code'] = "";

		$order_data['comment'] = "";

		$order_data['products'] = array();

		foreach ($this->cart->getProducts() as $product) {
			$option_data = array();

			foreach ($product['option'] as $option) {
				$option_data[] = array(
					'product_option_id'       => $option['product_option_id'],
					'product_option_value_id' => $option['product_option_value_id'],
					'option_id'               => $option['option_id'],
					'option_value_id'         => $option['option_value_id'],
					'name'                    => $option['name'],
					'value'                   => $option['value'],
					'type'                    => $option['type']
				);
			}

			$order_data['products'][] = array(
				'product_id' => $product['product_id'],
				'name'       => $product['name'],
				'model'      => $product['model'],
				'option'     => $option_data,
				'download'   => $product['download'],
				'quantity'   => $product['quantity'],
				'subtract'   => $product['subtract'],
				'price'      => $product['price'],
				'total'      => $product['total'],
				'tax'        => $this->tax->getTax($product['price'], $product['tax_class_id']),
				'reward'     => $product['reward']
			);
		}

		$order_data['total'] = $total_data['total'];

		$order_data['affiliate_id'] = 0;
		$order_data['commission'] = 0;
		$order_data['marketing_id'] = 0;
		$order_data['tracking'] = '';

		$order_data['language_id'] = $this->config->get('config_language_id');
		$order_data['currency_id'] = $this->currency->getId($this->session->data['currency']);
		$order_data['currency_code'] = $this->session->data['currency'];
		$order_data['currency_value'] = $this->currency->getValue($this->session->data['currency']);
		$order_data['ip'] = $this->request->server['REMOTE_ADDR'];

		if (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
			$order_data['forwarded_ip'] = $this->request->server['HTTP_X_FORWARDED_FOR'];
		} elseif (!empty($this->request->server['HTTP_CLIENT_IP'])) {
			$order_data['forwarded_ip'] = $this->request->server['HTTP_CLIENT_IP'];
		} else {
			$order_data['forwarded_ip'] = '';
		}

		if (isset($this->request->server['HTTP_USER_AGENT'])) {
			$order_data['user_agent'] = $this->request->server['HTTP_USER_AGENT'];
		} else {
			$order_data['user_agent'] = '';
		}

		if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
			$order_data['accept_language'] = $this->request->server['HTTP_ACCEPT_LANGUAGE'];
		} else {
			$order_data['accept_language'] = '';
		}

		$this->load->model('checkout/order');

		$this->session->data['order_id'] = $this->model_checkout_order->addOrder($order_data);

		// ЗАКАЗ ИЗ КОРЗИНЫ В БИТРИКС 
		$this->load->model('catalog/product');

		$order_text = "Способ доставки: не выбран;";
		$total_price = 0;

		foreach ($order_data['products'] as $product) {
			$attributes = $this->model_catalog_product->getProductAttributes($product['product_id']);
			if (!empty($attributes)) {
				foreach ($attributes[0]['attribute'] as $attribute){
					if ($attribute['attribute_id'] == 12) $color = " ".$attribute['text'];
					if ($attribute['attribute_id'] == 51) $color = " ".$attribute['text'];
				}
			}
			if (!isset($color)) $color = "";

			$order_text .= "<br>" . $product['name'].$color." - ".$product['quantity'].";";
			$total_price += $product['quantity'] * $product['price'];
			foreach ($product['option'] as $option){
				$order_text .= "<br>- <i>" . $option['value'] . "</i>";
			}
		}

		if ($order_data['shipping_method'] == "Доставка") $total_price += 1000;

		// формируем URL в переменной $queryUrl
		$queryUrl = 'https://b24-rceipv.bitrix24.ru/rest/7109/700tstol1i2x2ki9/crm.lead.add.json';

		// формируем параметры для создания лида в переменной $queryData
		$queryData = http_build_query(array(
			'fields' => array(
				'NAME' => 'Заказ в 1 клик',   // сохраняем имя
				"PHONE" => array(array('VALUE' => $order_data['telephone'], 'VALUE_TYPE' => 'WORK')),
				'COMMENTS' => $order_text,
				'OPPORTUNITY' => $total_price,
			),
			'params' => array("REGISTER_SONET_EVENT" => "Y")
		));

		// обращаемся к Битрикс24 при помощи функции curl_exec
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_POST => 1,
			CURLOPT_HEADER => 0,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => $queryUrl,
			CURLOPT_POSTFIELDS => $queryData,
		));
		$result = curl_exec($curl);
		curl_close($curl);
		$result = json_decode($result, 1);
		if (array_key_exists('error', $result)) echo "Ошибка при сохранении лида: ".$result['error_description']."<br/>";

		// МЕНЯЕМ СТАТУС ЗАКАЗА 																															(В ЗАСИСИМОСТИ ОТ ОПЛАТЫ (payment/free_checkout/confirm)
		$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], 1);

		$response['order_id'] = $this->session->data['order_id'];

		// УСПЕШНОЕ ОФОРМЛЕНИЕ ЗАКАЗА (checkout/success)
		if (isset($this->session->data['order_id'])) {
			$this->cart->clear();

			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
			unset($this->session->data['guest']);
			unset($this->session->data['comment']);
			unset($this->session->data['order_id']);
			unset($this->session->data['coupon']);
			unset($this->session->data['reward']);
			unset($this->session->data['voucher']);
			unset($this->session->data['vouchers']);
			unset($this->session->data['totals']);
		}

		$this->response->setOutput(json_encode($response));
	}
}