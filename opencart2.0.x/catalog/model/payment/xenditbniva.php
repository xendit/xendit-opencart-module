<?php

class Modelpaymentxenditbniva extends Model {
    public function getMethod($address, $total) {
        $this->load->language('payment/xendit');

        $status = true;

        $method_data = array();

        if ($status) {
            $method_data = array(
                'code'       => 'xenditbniva',
                'title'      => 'Bank Transfer BNI',
                'terms'      => '',
                'sort_order' => $this->config->get('xenditbniva_sort_order')
            );
        }

        return $method_data;
    }
}