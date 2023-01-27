<?php
class ControllerDesignSeoFilter extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('design/seo_filter');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('design/seo_filter');

		$this->getList();
	}

	public function add() {
		$this->load->language('design/seo_filter');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('design/seo_filter');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_design_seo_filter->addSeoFilter($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			$this->response->redirect($this->url->link('design/seo_filter', 'user_token=' . $this->session->data['user_token'], true));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('design/seo_filter');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('design/seo_filter');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_design_seo_filter->editSeoFilter($this->request->get['seo_url_id'], $this->request->post);

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

			$this->response->redirect($this->url->link('design/seo_filter', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function deleteOne() {
		$this->load->model('design/seo_filter');

		if (isset($this->request->post['seo_url_id']) && $this->validateDelete()) {
			$this->model_design_seo_filter->deleteSeoFilter($this->request->post['seo_url_id']);
		} else {
			$this->response->redirect($this->url->link('design/seo_filter', 'user_token=' . $this->session->data['user_token'], true));
		}
	}

	protected function getList() {

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('design/seo_filter', 'user_token=' . $this->session->data['user_token'], true)
		);

		// Получаем атрибуты и опции, присутствующие в моделях фильтра
		$this->load->model('setting/module');
		$module_info = $this->model_setting_module->getModulesByCode('dream_filter');
		$attributes_list = array();
		$options_list = array();
		$data['manufacturers_list'] = false;
		foreach ($module_info as $module){
			$module_settings = json_decode($module['setting']);
			foreach ($module_settings->filters as $filter){
				if ($filter->filter_by == "attributes") $attributes_list[] = $filter->item_id;
				if ($filter->filter_by == "options") $options_list[] = $filter->item_id;
				if ($filter->filter_by == "manufacturers") $data['manufacturers_list'] = true;
			}
		}
		$attributes_list = array_unique($attributes_list);
		$options_list = array_unique($options_list);

		$data['attribute_groups'] = $this->model_design_seo_filter->getSeoFilterAttributes();

		// Отсекаем все характеристики по которым не происходит фильтрации
		foreach ($data['attribute_groups'] as $group_key => $group){
			foreach ($group['attributes'] as $key => $attribute){
				if (!in_array($attribute['attribute_id'], $attributes_list)) unset($data['attribute_groups'][$group_key]['attributes'][$key]);
			}
			if (empty($data['attribute_groups'][$group_key]['attributes'])) unset($data['attribute_groups'][$group_key]);
		}

		$data['options'] = $this->model_design_seo_filter->getSeoFilterOptions();

		// Отсекаем все опции по которым не происходит фильтрации
		foreach ($data['options'] as $key => $option){
			if (!in_array($option['option_id'], $options_list)) unset($data['options'][$key]);
		}

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}
		
		$this->load->model('setting/store');

		$data['stores'] = $this->model_setting_store->getStores();
		
		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('design/seo_filter_list', $data));
	}

	protected function getForm() {
		$data['text_form'] = !isset($this->request->get['seo_url_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		if (isset($this->request->get['attribute_id'])) {
			$data["attribute_id"] = $this->request->get['attribute_id'];
			$data["attribute_name"] = $this->model_design_seo_filter->getAttributeName($data["attribute_id"]);
			$data["attribute_values"] = $this->model_design_seo_filter->getSeoFilterAttributeValues($data["attribute_id"]);

			foreach ($data["attribute_values"] as $key => $attribute_value){
				$data["attribute_values"][$key]["query"] = "rdrf[attr][" . $data["attribute_id"] . "][]=" . $attribute_value["text"];

				$seo_url = $this->model_design_seo_filter->getSeoFilterByQuery($data["attribute_values"][$key]["query"]);

				if (isset($seo_url['seo_url_id'])) {
					$data["attribute_values"][$key]["seo_url_id"] = $seo_url['seo_url_id'];
					$data["attribute_values"][$key]["keyword"] = $seo_url['keyword'];
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

			foreach ($data["option_values"] as $key => $option_value){
				$data["option_values"][$key]["query"] = "rdrf[opt][" . $data["option_id"] . "][]=" . $option_value["option_value_id"];

				$seo_url = $this->model_design_seo_filter->getSeoFilterByQuery($data["option_values"][$key]["query"]);

				if (isset($seo_url['seo_url_id'])) {
					$data["option_values"][$key]["seo_url_id"] = $seo_url['seo_url_id'];
					$data["option_values"][$key]["keyword"] = $seo_url['keyword'];
				} else {
					$data["option_values"][$key]["seo_url_id"] = "";
					$data["option_values"][$key]["keyword"] = "";
				}
			}

			$data["heading_title"] = "SEO для опции: " . $data["option_name"];
		} else if (isset($this->request->get['seo_filter'])) {
			$data['manufacturers'] = $this->model_design_seo_filter->getSeoFilterManufacturers();

			foreach ($data['manufacturers'] as $key => $manufacturer){
				$data["manufacturers"][$key]["query"] = "rdrf[man][]=" . $manufacturer["manufacturer_id"];

				$data["manufacturers"][$key]["language_id"] = (int)$this->config->get('config_language_id');

				$seo_url = $this->model_design_seo_filter->getSeoFilterByQuery($data["manufacturers"][$key]["query"]);

				if (isset($seo_url['seo_url_id'])) {
					$data["manufacturers"][$key]["seo_url_id"] = $seo_url['seo_url_id'];
					$data["manufacturers"][$key]["keyword"] = $seo_url['keyword'];
				} else {
					$data["manufacturers"][$key]["seo_url_id"] = "";
					$data["manufacturers"][$key]["keyword"] = "";
				}
			}
			$data["heading_title"] = "SEO для производителей";
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
			'href' => $this->url->link('design/seo_filter', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['cancel'] = $this->url->link('design/seo_filter', 'user_token=' . $this->session->data['user_token'], true);

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

		$this->response->setOutput($this->load->view('design/seo_filter_form', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'design/seo_filter')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['query']) {
			$this->error['query'] = $this->language->get('error_query');
		}
		
		$seo_urls = $this->model_design_seo_filter->getSeoFiltersByKeyword($this->request->post['keyword']);

		foreach ($seo_urls as $seo_url) {
			if ($seo_url['store_id'] == $this->request->post['store_id'] && $seo_url['query'] != $this->request->post['query']) {
				$this->error['keyword'] = $this->language->get('error_exists');
				
				break;
			}
		}
		
		if (!$this->request->post['keyword']) {
			$this->error['keyword'] = $this->language->get('error_keyword');
		}
		
		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'design/seo_filter')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}