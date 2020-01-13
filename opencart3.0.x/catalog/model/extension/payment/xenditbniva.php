<?php

class ModelExtensionPaymentXenditBNIVA extends Model {
    public function getMethod($address, $total) {
        $this->load->language('extension/payment/xendit');

        $status = true;

        $method_data = array();

        if ($status) {
            $method_data = array(
                'code'       => 'xenditbniva',
                'title'      => 'Bank Transfer BNI',
                'terms'      => '',
                'sort_order' => $this->config->get('payment_xenditbniva_sort_order')
            );
        }

        return $method_data;
    }
}