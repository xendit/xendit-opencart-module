<?php

class ModelExtensionPaymentXenditCC extends Model {
    public function addCharge($order_id, $charge, $environment = 'test')
    {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "xenditcc_charge` SET `order_id` = '" . (int)$order_id . "',
            `xendit_charge_id` = '" . $charge['id'] . "',
            `amount` = '" . (int)$charge['capture_amount'] . "',
            `environment` = '" . $environment . "'");
        return $this->db->getLastId();
    }

    public function getMethod($address, $total) {
        $this->load->language('extension/payment/xenditcc');

        $status = true;

        $method_data = array();

        if ($status) {
            $method_data = array(
                'code'       => 'xenditcc',
                'title'      => $this->language->get('text_title'),
                'terms'      => '',
                'sort_order' => $this->config->get('payment_xenditcc_sort_order')
            );
        }

        return $method_data;
    }
}