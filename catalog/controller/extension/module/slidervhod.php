<?php
class ControllerExtensionModuleslidervhod extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/slidervhod');
		
		$this->load->model('extension/module/slidervhod');
				
		$data['heading_title'] = $this->language->get('heading_title');
		return $this->load->view('extension/module/slidervhod', $data);
	}}