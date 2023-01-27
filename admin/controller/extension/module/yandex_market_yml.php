<?php
class ControllerExtensionModuleYandexMarketYml extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/yandex_market_yml');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');




		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {


		 
			$this->request->post['module_yandex_market_yml_time'] = time() + 3600;


	

		if(isset($this->request->post['module_yandex_market_yml_product_category'])){
			$cats = $this->getCategoriesWithProducts($this->request->post['module_yandex_market_yml_product_category']);	

			if($cats){
			$this->createXML( $cats, strip_tags((trim($this->request->post['module_yandex_market_yml_shopname']))), strip_tags(trim($this->request->post['module_yandex_market_yml_companyname'])),strip_tags(trim($this->request->post['module_yandex_market_yml_currency'])) );
			}
			
		}
			$this->model_setting_setting->editSetting('module_yandex_market_yml', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');


			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['yandex_market_yml'] = $this->language->get('yandex_market_yml');
		$data['text_select_all'] = $this->language->get('text_select_all');
		$data['text_unselect_all'] = $this->language->get('text_unselect_all');
		$data['shop_name'] = $this->language->get('shop_name');
		$data['company_name'] = $this->language->get('company_name');
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
		$data['text_currency'] = $this->language->get('text_currency');


		$this->load->model('catalog/category');

		$filter_data = array(
			'sort'        => 'name',
			'order'       => 'ASC'
		);


		$data['categories'] = $this->model_catalog_category->getCategories($filter_data);

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);


		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/yandex_market_yml', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/module/yandex_market_yml', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->post['module_yandex_market_yml_status'])) {
			$data['module_yandex_market_yml_status'] = $this->request->post['module_yandex_market_yml_status'];
		} else {
			$data['module_yandex_market_yml_status'] = $this->config->get('module_yandex_market_yml_status');
		}

			if (isset($this->request->post['module_yandex_market_yml_shopname'])) {
			$data['module_yandex_market_yml_shopname'] = $this->request->post['module_yandex_market_yml_shopname'];
		} else {
			$data['module_yandex_market_yml_shopname'] = $this->config->get('module_yandex_market_yml_shopname');
		}

			if (isset($this->request->post['module_yandex_market_yml_companyname'])) {
			$data['module_yandex_market_yml_companyname'] = $this->request->post['yandex_market_yml_companyname'];
		} else {
			$data['module_yandex_market_yml_companyname'] = $this->config->get('module_yandex_market_yml_companyname');
		}

			if (isset($this->request->post['yandex_market_yml_product_category'])) {
			$data['module_yandex_market_yml_product_category'] = $this->request->post['module_yandex_market_yml_product_category'];
		} else {
			$data['module_yandex_market_yml_product_category'] = $this->config->get('module_yandex_market_yml_product_category');
		}


			if (isset($this->request->post['module_yandex_market_yml_currency'])) {
			$data['module_yandex_market_yml_currency'] = $this->request->post['module_yandex_market_yml_currency'];
		} else {
			$data['module_yandex_market_yml_currency'] = $this->config->get('module_yandex_market_yml_currency');
		}

		$data['HTTP_CATALOG'] = HTTP_CATALOG;

     $data['currency'] = array('RUB','USD');

			if(!$data['module_yandex_market_yml_product_category'] ){
				$data['module_yandex_market_yml_product_category'] = array();
			}


	

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');



		$this->response->setOutput($this->load->view('extension/module/yandex_market_yml', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/yandex_market_yml')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}


		return !$this->error;
	}

	protected function getCategoriesWithProducts($categiryID){
		if($categiryID && is_array($categiryID)){	
			$this->load->model('catalog/category');
			$this->load->model('catalog/product');
			$cats = array();
			foreach ($categiryID as $key => $value) {
					
				 	$cats[$value] = $this->model_catalog_category->getCategory($value);
				 	$cats[$value]['products'] = $this->model_catalog_product->getProductsByCategoryId($value);
			 	
			}
			return $cats;
		}else{
			return false;
		}

	}

	protected function createXML($cats, $yandex_market_yml_shopname, $yandex_market_yml_companyname,$currency_val){
	$this->load->model('tool/image');
	$this->load->model('catalog/product');
	$this->load->model('catalog/manufacturer');
	
	

	    $dom = new DomDocument('1.0','utf-8');
		$dom->formatOutput = true;
		$dom->preserveWhiteSpace = true;
		$yml_catalog = $dom->createElement('yml_catalog');
		$invalid_attr = $dom->createAttribute('date');
		$invalid_attr->value = $today = date("Y-j-n").' '.date("H:i");
		$yml_catalog->appendChild($invalid_attr);
		$dom->appendChild($yml_catalog);
		$shop = $dom->createElement('shop'); 
		$yml_catalog->appendChild($shop);  
		$name_shop = $dom->createElement('name', $yandex_market_yml_shopname );
		$company = $dom->createElement('company', $yandex_market_yml_companyname); 
		$url = $dom->createElement('url', HTTP_CATALOG ); 

		$currencies = $dom->createElement('currencies'); 
			$currency = $dom->createElement('currency'); 
				$id_attr = $dom->createAttribute('id');
				$id_attr->value = $currency_val;
				$rate_attr = $dom->createAttribute('rate');
				$rate_attr->value = 1;
				$currency->appendChild($id_attr);
				$currency->appendChild($rate_attr);
		$currencies->appendChild($currency); 

		$categories = $dom->createElement('categories');
		

		$offers = $dom->createElement('offers');

		foreach ($cats as $id => $value) {

			$category =  $dom->createElement('category', $value['name']);
			$id_cat = $dom->createAttribute('id');
			$id_cat->value = $value['category_id'];
			$category->appendChild($id_cat);
			$categories->appendChild($category);

			if(count($value['products']) > 0){
				foreach ($value['products'] as $id => $value) {
					if($value['status'] == 0)   continue;
					$offer = $dom->createElement('offer'); 

					$offer_id_attr = $dom->createAttribute('id');
					$offer_id_attr->value = $value['product_id']; 
					$offer->appendChild($offer_id_attr);

					$offer_available_attr = $dom->createAttribute('available');
					$offer_available_attr->value = 'true'; 
					$offer->appendChild($offer_available_attr);

					$url_href =	 $dom->createTextNode (htmlspecialchars( HTTP_CATALOG.'index.php?route=product/product&product_id='.$value["product_id"]));

					$product_url = $dom->createElement('url');

					$product_url->appendChild($url_href); 

					$image = $this->model_tool_image->resize($value['image'], 600, 600);
					$picture = $dom->createElement('picture',$image);
					
					$price = $dom->createElement('price',$value['price']);
					$currencyId = $dom->createElement('currencyId','RUR');
					$categoryId = $dom->createElement('categoryId', $value['category_id']);

					$shipping = ($value["shipping"]) ? 'true' : 'false';
					$delivery =  $dom->createElement('delivery', $shipping);

					$name =  $dom->createElement('name');
					$name_product = $dom->createTextNode($value['name']);
					$name->appendChild($name_product);

		
					$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($value['manufacturer_id']);

					if ($manufacturer_info) {
					$manufacturer_info = $manufacturer_info['name'];
					} else {
					$manufacturer_info = '';
					}	

					$desc = $dom->createTextNode($value['description']);

					$vendor =  $dom->createElement('vendor', $manufacturer_info);
					$vendorCode =  $dom->createElement('vendorCode', $value['model']);

					$description =  $dom->createElement('description');

					$description->appendChild($desc); 
					$offer->appendChild($product_url);
					$offer->appendChild($price);
					$offer->appendChild($currencyId);
					$offer->appendChild($categoryId);
					$offer->appendChild($picture);
					$offer->appendChild($delivery);
					$offer->appendChild($name);
					$offer->appendChild($vendor);
					$offer->appendChild($vendorCode);
					$offer->appendChild($description);
					$offers->appendChild($offer);

				}				
			}	  		
		}
		  
		$shop->appendChild($name_shop); 
		$shop->appendChild($company);
		$shop->appendChild($url); 
		$shop->appendChild($currencies); 
		$shop->appendChild($categories);
		$shop->appendChild($offers);
		
	   
	    $dom->save(DIR_SYSTEM.'yandex_market.xml');

	    unset($dom);
	
}




}

