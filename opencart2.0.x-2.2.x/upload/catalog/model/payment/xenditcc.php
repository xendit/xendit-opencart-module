<?php

class Modelpaymentxenditcc extends Model {
    public function getMethod($address, $total) {
        $this->load->language('payment/xenditcc');

        $status = true;

        $method_data = array();

        if ($status) {
            $method_data = array(
                'code'       => 'xenditcc',
                'title'      => $this->language->get('text_title'),
                'terms'      => '',
                'sort_order' => $this->config->get('xenditcc_sort_order')
            );
        }

        return $method_data;
    }
}