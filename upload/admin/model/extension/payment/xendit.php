<?php

class ModelExtensionPaymentXendit extends Model {
    public function install() {
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "xendit_order` (
            `xendit_invoice_id` varchar(255) NOT NULL PRIMARY KEY,
            `order_id` int(11) NOT NULL DEFAULT '0',
            `environment` varchar(5) NOT NULL DEFAULT 'test'
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "xendit_charge` (
            `xendit_charge_id` varchar(255) NOT NULL PRIMARY KEY,
            `order_id` int(11) NOT NULL DEFAULT '0',
            `environment` varchar(5) NOT NULL DEFAULT 'test'
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
    }

    public function uninstall() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "xendit_order`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "xendit_charge`");
    }

    public function getOrder($order_id) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "xendit_order` WHERE `order_id` = '" . $order_id . "' LIMIT 1");
        if ($query->num_rows) {
            return $query->row;
        } else {
            return false;
        }
    }

    public function getOrderByInvoiceId($invoice_id) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "xendit_order` WHERE `xendit_invoice_id` = '" . $invoice_id . "' LIMIT 1");
        if ($query->num_rows) {
            return $query->row;
        } else {
            return false;
        }
    }
}