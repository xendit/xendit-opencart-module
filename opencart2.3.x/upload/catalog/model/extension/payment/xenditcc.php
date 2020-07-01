<?php

class ModelExtensionPaymentXenditCC extends Model {
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
    
    public function addOrder($order_info, $charge_id, $environment = 'test')
    {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "xendit_order` SET `order_id` = '" . (int)$order_info['order_id'] . "',
            `status` = 'PENDING',
            `xendit_charge_id` = '" . $charge_id . "',
            `environment` = '" . $environment . "'");
        return $this->db->getLastId();
    }

    public function completeOrder($order_id)
    {
        $this->db->query("UPDATE `" . DB_PREFIX . "xendit_order` SET `status` = 'PAID' WHERE `order_id` = '" . $order_id . "'");
    }
}