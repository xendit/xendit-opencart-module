<?php

class ModelPaymentXenditBRIVA extends Model {
    public function getMethod($address, $total) {
        $this->load->language('payment/xendit');

        $status = true;

        $method_data = array();
        $code = 'xenditbriva';

        if ($status) {
            $method_data = array(
                'code'       => $code,
                'title'      => 'Bank Transfer BRI',
                'terms'      => '',
                'sort_order' => $this->config->get('payment_' . $code . '_sort_order')
            );
        }

        return $method_data;
    }
}