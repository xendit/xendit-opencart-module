<?php

class ModelExtensionPaymentXenditPermataVA extends Model {
    public function getMethod($address, $total) {
        $this->load->language('extension/payment/xendit');

        $status = true;

        $method_data = array();
        $code = 'xenditpermatava';

        if ($status) {
            $method_data = array(
                'code'       => $code,
                'title'      => 'Bank Transfer Permata',
                'terms'      => '',
                'sort_order' => $this->config->get('payment_' . $code . '_sort_order')
            );
        }

        return $method_data;
    }
}