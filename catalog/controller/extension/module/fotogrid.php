<?php
class ControllerExtensionModulefotogrid extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/fotogrid');
		
		$this->load->model('extension/module/fotogrid');
				
		$data['heading_title'] = $this->language->get('heading_title');
		return $this->load->view('extension/module/fotogrid', $data);
	}}