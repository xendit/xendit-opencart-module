<?php

class ModelPaymentXenditCC extends Model {
    public function getMethod($address, $total) {
        $this->load->language('payment/xenditcc');

        $status = true;

        $method_data = array();
        $code = 'xenditcc';

        if ($status) {
            $method_data = array(
                'code'       => 'xenditcc',
                'title'      => $this->language->get('text_title'),
                'terms'      => '',
                'sort_order' => $this->config->get($code . '_sort_order')
            );
        }

        return $method_data;
    }
}