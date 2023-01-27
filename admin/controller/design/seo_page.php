<?php
class ControllerDesignSeoPage extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('design/seo_page');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('design/seo_page');

		$this->getForm();
	}

	public function add() {
		$this->load->language('design/seo_page');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('design/seo_page');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_design_seo_page->addSeoPage($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			$this->response->redirect($this->url->link('design/seo_page', 'user_token=' . $this->session->data['user_token'], true));
		}

		$this->model_design_seo_page->deleteSeoPage((int)$this->request->get['seo_page_id']);
		$this->getForm();
	}

	public function edit() {
		$this->load->language('design/seo_page');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('design/seo_page');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_design_seo_page->editSeoPage($this->request->get['seo_page_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
			
			if (isset($this->request->get['filter_query'])) {
				$url .= '&filter_query=' . urlencode(html_entity_decode($this->request->get['filter_query'], ENT_QUOTES, 'UTF-8'));
			}
						
			if (isset($this->request->get['filter_keyword'])) {
				$url .= '&filter_keyword=' . urlencode(html_entity_decode($this->request->get['filter_keyword'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_store_id'])) {
				$url .= '&filter_store_id=' . $this->request->get['filter_store_id'];
			}

			if (isset($this->request->get['filter_language_id'])) {
				$url .= '&filter_language_id=' . $this->request->get['filter_language_id'];
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

			$this->response->redirect($this->url->link('design/seo_page', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->model_design_seo_page->deleteSeoPage((int)$this->request->get['seo_page_id']);
		$this->getForm();
	}

	protected function getForm() {
		$data['text_form'] = !isset($this->request->get['seo_url_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		$this->load->model('design/seo_filter');

		if (isset($this->request->get['category_id'])) {
			$this->load->model('catalog/category');

			$data['category_id'] = $this->request->get['category_id'];

			$data['cancel'] = $this->request->server['HTTP_REFERER'];

			$cache_seo = $this->cache->get('seo_pro.'.(int)$this->config->get('config_store_id').".".(int)$this->config->get('config_language_id'));
			if (!$cache_seo) {
				$query = $this->db->query("SELECT LOWER(`keyword`) as 'keyword', `query` FROM " . DB_PREFIX . "seo_url WHERE store_id='".(int)$this->config->get('config_store_id')."' AND language_id = '".(int)$this->config->get('config_language_id')."' ORDER BY seo_url_id");
				$cache_seo = array();
				foreach ($query->rows as $row) {
					if (isset($cache_seo['keywords'][$row['keyword']])){
						$cache_seo['keywords'][$row['query']] = $cache_seo['keywords'][$row['keyword']];
						continue;
					}
					$cache_seo['keywords'][$row['keyword']] = $row['query'];
					$cache_seo['queries'][$row['query']] = $row['keyword'];
				}
			}

			$category_path = $this->model_design_seo_page->getPathByCategory($data['category_id']);
			if ($category_path) {
				$data['seo_url'] = '';
				
				$categories = explode('_', $category_path);
				foreach($categories as $category) {
					$queries[] = 'category_id=' . $category;
				}

				if(empty($queries)) {
					$queries[] = $route;
				}
		
				$rows = array();
				foreach($queries as $query) {
					if(isset($cache_seo['queries'][$query])) {
						$rows[] = array('query' => $query, 'keyword' => $cache_seo['queries'][$query]);
					}
				}
		
				if(count($rows) == count($queries)) {
					$aliases = array();
					foreach($rows as $row) {
						$aliases[$row['query']] = $row['keyword'];
					}
					foreach($queries as $query) {
						$data['seo_url'] .= '/' . rawurlencode($aliases[$query]);
					}
				}
			}
		}

		if (isset($this->request->get['attribute_id'])) {
			$data["attribute_id"] = $this->request->get['attribute_id'];
			$data["attribute_name"] = $this->model_design_seo_filter->getAttributeName($data["attribute_id"]);
			$data["attribute_values"] = $this->model_design_seo_filter->getSeoFilterAttributeValues($data["attribute_id"]);
			$active_attributes = $this->model_design_seo_filter->getSeoFilterAttributeActiveValues($data["attribute_id"]);

			foreach($data["attribute_values"] as $key => $attribute){
				if (!in_array($attribute['text'], $active_attributes)) unset($data["attribute_values"][$key]);
			}

			foreach ($data["attribute_values"] as $key => $attribute_value){
				$data["attribute_values"][$key]["query"] = "rdrf[attr][" . $data["attribute_id"] . "][]=" . $attribute_value["text"];

				$seo_url = $this->model_design_seo_filter->getSeoFilterByQuery($data["attribute_values"][$key]["query"]);

				if (isset($seo_url['seo_url_id'])) {
					$data["attribute_values"][$key]["seo_url_id"] = $seo_url['seo_url_id'];
					$data["attribute_values"][$key]["keyword"] = $seo_url['keyword'];
					if ($data['seo_url']) $data["attribute_values"][$key]["full_seo_url"] = $data['seo_url'] . "/" . $data["attribute_values"][$key]["keyword"] . "/";
					
					$seo_page_data = $this->model_design_seo_page->getSeoPageByFullUrl($data["attribute_values"][$key]["full_seo_url"]);
					if ($seo_page_data) {
						$data["attribute_values"][$key] = array_merge($data["attribute_values"][$key], $seo_page_data);
					}
				} else {
					$data["attribute_values"][$key]["seo_url_id"] = "";
					$data["attribute_values"][$key]["keyword"] = "";
				}
			}

			$data["heading_title"] = "SEO для атрибута: " . $data["attribute_name"];
		} else if (isset($this->request->get['option_id'])) {			
			$data["option_id"] = $this->request->get['option_id'];
			$data["option_name"] = $this->model_design_seo_filter->getOptionName($data["option_id"]);
			$data["option_values"] = $this->model_design_seo_filter->getSeoFilterOptionValues($data["option_id"]);
			$active_options = $this->model_design_seo_filter->getSeoFilterOptionActiveValues($data["option_id"]);

			foreach($data["option_values"] as $key => $option){
				if (!in_array($option['option_value_id'], $active_options)) unset($data["option_values"][$key]);
			}

			foreach ($data["option_values"] as $key => $option_value){
				$data["option_values"][$key]["query"] = "rdrf[opt][" . $data["option_id"] . "][]=" . $option_value["option_value_id"];

				$seo_url = $this->model_design_seo_filter->getSeoFilterByQuery($data["option_values"][$key]["query"]);

				if (isset($seo_url['seo_url_id'])) {
					$data["option_values"][$key]["seo_url_id"] = $seo_url['seo_url_id'];
					$data["option_values"][$key]["keyword"] = $seo_url['keyword'];
					if ($data['seo_url']) $data["option_values"][$key]["full_seo_url"] = $data['seo_url'] . "/" . $data["option_values"][$key]["keyword"] . "/";

					$seo_page_data = $this->model_design_seo_page->getSeoPageByFullUrl($data["option_values"][$key]["full_seo_url"]);
					if ($seo_page_data) {
						$data["option_values"][$key] = array_merge($data["option_values"][$key], $seo_page_data);
					}
				} else {
					$data["option_values"][$key]["seo_url_id"] = "";
					$data["option_values"][$key]["keyword"] = "";
				}
			}

			$data["heading_title"] = "SEO для опции: " . $data["option_name"];
		} else if (isset($this->request->get['seo_filter'])) {
			$data['manufacturers'] = $this->model_design_seo_filter->getSeoFilterManufacturers();
			$active_manufacturers = $this->model_design_seo_filter->getSeoFilterManufacturersActiveValues();

			foreach($data["manufacturers"] as $key => $manufacturer){
				if (!in_array($manufacturer['manufacturer_id'], $active_manufacturers)) unset($data["manufacturers"][$key]);
			}

			foreach ($data['manufacturers'] as $key => $manufacturer){
				$data["manufacturers"][$key]["query"] = "rdrf[man][]=" . $manufacturer["manufacturer_id"];

				$data["manufacturers"][$key]["language_id"] = (int)$this->config->get('config_language_id');

				$seo_url = $this->model_design_seo_filter->getSeoFilterByQuery($data["manufacturers"][$key]["query"]);

				if (isset($seo_url['seo_url_id'])) {
					$data["manufacturers"][$key]["seo_url_id"] = $seo_url['seo_url_id'];
					$data["manufacturers"][$key]["keyword"] = $seo_url['keyword'];
					if ($data['seo_url']) $data["manufacturers"][$key]["full_seo_url"] = $data['seo_url'] . "/" . $data["manufacturers"][$key]["keyword"] . "/";

					$seo_page_data = $this->model_design_seo_page->getSeoPageByFullUrl($data["manufacturers"][$key]["full_seo_url"]);
					if ($seo_page_data) {
						$data["manufacturers"][$key] = array_merge($data["manufacturers"][$key], $seo_page_data);
					}
				} else {
					$data["manufacturers"][$key]["seo_url_id"] = "";
					$data["manufacturers"][$key]["keyword"] = "";
				}
			}
			$data["heading_title"] = "SEO для производителей";
		}

		if (isset($this->request->get['category_id'])) {
			$category_info = $this->model_catalog_category->getCategory($data["category_id"]);
			$data["heading_title"] .= " > " . $category_info['name'];
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['query'])) {
			$data['error_query'] = $this->error['query'];
		} else {
			$data['error_query'] = '';
		}

		if (isset($this->error['keyword'])) {
			$data['error_keyword'] = $this->error['keyword'];
		} else {
			$data['error_keyword'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('design/seo_page', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['cancel'] = $this->url->link('design/seo_page', 'user_token=' . $this->session->data['user_token'], true);

		$data['user_token'] = $this->session->data['user_token'];

		$this->load->model('setting/store');

		$data['stores'] = array();
		
		$data['stores'][] = array(
			'store_id' => 0,
			'name'     => $this->language->get('text_default')
		);
		
		$stores = $this->model_setting_store->getStores();

		foreach ($stores as $store) {
			$data['stores'][] = array(
				'store_id' => $store['store_id'],
				'name'     => $store['name']
			);
		}
				
		if (isset($this->request->post['store_id'])) {
			$data['store_id'] = $this->request->post['store_id'];
		} elseif (!empty($seo_url_info)) {
			$data['store_id'] = $seo_url_info['store_id'];
		} else {
			$data['store_id'] = '';
		}			
				
		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['language_id'])) {
			$data['language_id'] = $this->request->post['language_id'];
		} elseif (!empty($seo_url_info)) {
			$data['language_id'] = $seo_url_info['language_id'];
		} else {
			$data['language_id'] = '';
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('design/seo_page', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'design/seo_page')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['query']) {
			$this->error['query'] = $this->language->get('error_query');
		}

		if (!$this->request->post['meta_title'] && !$this->request->post['meta_description'] && !$this->request->post['h1'] && !$this->request->post['description']) {
			$this->error['meta_title'] = $this->language->get('error_meta');
		}
		
		if (!$this->request->post['keyword']) {
			$this->error['keyword'] = $this->language->get('error_keyword');
		}
		
		return !$this->error;
	}
}