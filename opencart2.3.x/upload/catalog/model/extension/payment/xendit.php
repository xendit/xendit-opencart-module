<?php

class ModelExtensionPaymentXendit extends Model
{
    public function getMethod($address, $total)
    {
        $method_data = array();
        return $method_data;
    }

    public function addOrder($order_info, $invoice, $environment = 'test')
    {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "xendit_order` SET `order_id` = '" . (int)$order_info['order_id'] . "',
            `status` = 'PENDING',
            `xendit_invoice_id` = '" . $invoice['id'] . "',
            `xendit_expiry_date` = '" . $invoice['expiry_date'] . "',
            `environment` = '" . $environment . "'");
        return $this->db->getLastId();
    }

    public function completeOrder($order_id)
    {
        $this->db->query("UPDATE `" . DB_PREFIX . "xendit_order` SET `status` = 'COMPLETED' WHERE `order_id` = '" . $order_id . "'");
    }
}
