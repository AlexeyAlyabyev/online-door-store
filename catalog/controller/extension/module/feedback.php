<?php
class ControllerExtensionModulefeedback extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/feedback');
		
		$data['heading_title'] = $this->language->get('heading_title');
		//  custom code
		
		if ($_SERVER['REQUEST_METHOD'] == 'GET'){
			if (isset($_GET['deal_id']))
				$data['deal_id'] = $_GET['deal_id'];
			else
			$data['deal_id'] = "none";
		}
		return $this->load->view('extension/module/feedback', $data);

		
	}}