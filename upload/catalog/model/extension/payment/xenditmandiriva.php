<?php

class ModelExtensionPaymentXenditMandiriVA extends Model {
    public function getMethod($address, $total) {
        $this->load->language('extension/payment/xendit');

        $status = true;

        $method_data = array();
        $code = 'xenditmandiriva';

        if ($status) {
            $method_data = array(
                'code'       => $code,
                'title'      => 'Bank Transfer Mandiri',
                'terms'      => '',
                'sort_order' => $this->config->get('payment_' . $code . '_sort_order')
            );
        }

        return $method_data;
    }
}