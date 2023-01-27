<?php 
class ControllerExtensionQuickCheckoutCartPrices extends Controller {
	public function index() {
		$data = $this->load->language('checkout/checkout');
		$data = array_merge($data, $this->load->language('extension/quickcheckout/checkout'));
		$data['error_warning'] = '';

		if ($this->cart->hasProducts() || !empty($this->session->data['vouchers'])) {
			if (!$this->cart->hasStock() && (!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning'))) {
				$data['error_warning_stock'] = $this->language->get('error_stock');
			}
		}
		
		// Totals
		$this->load->model('setting/extension');
		
		$total_data = array();					
		$total = 0;
		$taxes = $this->cart->getTaxes();
		
		$total_data = array(
			'totals' => &$totals,
			'taxes'  => &$taxes,
			'total'  => &$total
		);

		
		// Display prices
		$data['totals'] = array();
		
		if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
			$sort_order = array(); 
			
			$results = $this->model_setting_extension->getExtensions('total');
			
			foreach ($results as $key => $value) {
				$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
			}
			
			array_multisort($sort_order, SORT_ASC, $results);
			foreach ($results as $result) {
				if ($this->config->get('total_' . $result['code'] . '_status')) {
					$this->load->model('extension/total/' . $result['code']);
					// print_r($result['code']);
		
					$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
				}
			}
			
			$total_data = $totals;
	
			$sort_order = array(); 
		  
			foreach ($total_data as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $total_data);
			
			foreach ($total_data as $total) {
				$text = $this->currency->format($total['value'], $this->session->data['currency']);
				
				$data['totals'][] = array(
					'title' => $total['title'],
					'text'  => $text
				);
			}
		}
	
		$this->response->setOutput($this->load->view('extension/quickcheckout/cart_prices', $data));
	}
	
	public function update() {
		$this->load->language('extension/quickcheckout/checkout');
		$json = array();
		
		if (!empty($this->request->post['quantity'])) {
			foreach ($this->request->post['quantity'] as $key => $value) {
				$this->cart->update($key, $value);
			}
		}
		
		if (isset($this->request->get['remove'])) {
			$this->cart->remove($this->request->get['remove']);
			
			unset($this->session->data['vouchers'][$this->request->get['remove']]);
		}

		if (!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) {
			$json['redirect'] = $this->url->link('checkout/cart');
		}

		if (!$this->cart->hasStock() && (!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning'))) {
            $json['error']['stock'] = $this->language->get('error_stock');
        } else {
            $json['error']['stock'] = '';
        }
		
		// Validate minimum quantity requirements.			
		$products = $this->cart->getProducts();
				
		foreach ($products as $product) {
			$product_total = 0;
				
			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}		
			
			if ($product['minimum'] > $product_total) {
				$data['error_warning_minimum'] = sprintf($this->language->get('error_minimum'), $product['name'], $product['minimum']);
			} else {
				$data['error_warning_minimum'] = '';
			}				
		}

		if ($this->cart->getTotal() < $this->config->get('quickcheckout_minimum_order')) {
			$json['error']['warning'] = sprintf($this->language->get('error_minimum_order'), $this->currency->format($this->config->get('quickcheckout_minimum_order'), $this->session->data['currency']));
		} elseif (isset($data['error_warning_minimum'])){
			$json['error']['warning'] = $data['error_warning_minimum'];
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));	
	}
}