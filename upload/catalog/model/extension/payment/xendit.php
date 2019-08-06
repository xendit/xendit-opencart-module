<?php

class ModelExtensionPaymentXendit extends Model {
    public function getMethod($address, $total) {
        $method_data = array();
        return $method_data;
    }

    public function addOrder($order_info, $invoice_id, $environment = 'test') {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "xendit_order` SET `order_id` = '" . (int)$order_info['order_id'] . "', `xendit_invoice_id` = '" . $invoice_id . "', `environment` = '" . $environment . "'");
        return $this->db->getLastId();
    }
}