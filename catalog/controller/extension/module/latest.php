<?php
class ControllerExtensionModulelatest extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/latest');
		
		$this->load->model('extension/module/latest');
				
		$data['heading_title'] = $this->language->get('heading_title');
		return $this->load->view('extension/module/latest', $data);
	}}