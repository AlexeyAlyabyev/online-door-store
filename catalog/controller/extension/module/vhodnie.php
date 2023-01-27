<?php
class ControllerExtensionModulevhodnie extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/vhodnie');
		
		$this->load->model('extension/module/vhodnie');
				
		$data['heading_title'] = $this->language->get('heading_title');
		return $this->load->view('extension/module/vhodnie', $data);
	}}