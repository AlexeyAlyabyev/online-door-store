<?php
class ControllerExtensionModuledop extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/dop');
		
		$this->load->model('extension/module/dop');
				
		$data['heading_title'] = $this->language->get('heading_title');
		return $this->load->view('extension/module/dop', $data);
	}}