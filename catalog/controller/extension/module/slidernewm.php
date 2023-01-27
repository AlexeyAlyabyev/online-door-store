<?php
class ControllerExtensionModuleslidernewm extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/slidernewm');
		
		$this->load->model('extension/module/slidernewm');
				
		$data['heading_title'] = $this->language->get('heading_title');
		return $this->load->view('extension/module/slidernewm', $data);
	}}