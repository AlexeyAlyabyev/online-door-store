<?php
class ModelExtensionPaymentRBSCredit extends Model {
    public function getMethod($address, $total) {
        $this->load->language('extension/payment/rbs_credit');

        $method_data = array(
            'code'     => 'rbs_credit',
            'title'    => $this->language->get('rbs_credit_text_title'),
            'terms'      => $this->language->get('rbs_credit_text_terms'),
            'sort_order' => $this->config->get('payment_rbs_credit_sort_order')
        );

        return $method_data;
    }
}