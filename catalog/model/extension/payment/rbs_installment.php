<?php
class ModelExtensionPaymentRBSInstallment extends Model {
    public function getMethod($address, $total) {
        $this->load->language('extension/payment/rbs_installment');

        $method_data = array(
            'code'     => 'rbs_installment',
            'title'    => $this->language->get('rbs_installment_text_title'),
            'terms'      => $this->language->get('rbs_installment_text_terms'),
            'sort_order' => $this->config->get('payment_rbs_installment_sort_order')
        );

        return $method_data;
    }
}