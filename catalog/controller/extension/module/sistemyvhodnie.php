<?php
class ControllerExtensionModulesistemyvhodnie extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/sistemyvhodnie');
		
		$this->load->model('extension/module/sistemyvhodnie');
				
		$data['heading_title'] = $this->language->get('heading_title');
		return $this->load->view('extension/module/sistemyvhodnie', $data);
	}}