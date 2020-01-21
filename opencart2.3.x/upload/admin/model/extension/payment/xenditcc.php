<?php

class ModelExtensionPaymentXenditcc extends Model {
    public function install() {
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "xenditcc_charge` (
            `xendit_charge_id` varchar(255) NOT NULL PRIMARY KEY,
            `order_id` int(11) NOT NULL DEFAULT '0',
            `amount` int NOT NULL,
            `refunded_amount` int,
            `environment` varchar(5) NOT NULL DEFAULT 'test'
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "xenditcc_refund` (
            `xendit_charge_id` varchar(255) NOT NULL,
            `xendit_refund_id` varchar(255) NOT NULL PRIMARY KEY,
            `order_id` int(11) NOT NULL DEFAULT '0',
            `amount` int NOT NULL,
            `environment` varchar(5) NOT NULL DEFAULT 'test'
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
    }

    public function uninstall() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "xenditcc_charge`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "xenditcc_refund`");
    }

    public function addRefund($order_id, $charge_id, $refund, $environment = 'test') {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "xenditcc_refund` SET `order_id` = '" . (int)$order_id . "',
            `xendit_charge_id` = '" . $charge_id . "',
            `xendit_refund_id` = '" . $refund['id'] . "',
            `amount` = '" . (int)$refund['amount'] . "',
            `environment` = '" . $environment . "'");
        return $this->db->getLastId();
    }

    public function getCharge($order_id) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "xenditcc_charge` WHERE `order_id` = '" . $order_id . "' LIMIT 1");
        if ($query->num_rows) {
            return $query->row;
        } else {
            return false;
        }
    }

    public function getRefundByChargeId($charge_id) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "xenditcc_refund` WHERE `xendit_charge_id` = '" . $charge_id . "' LIMIT 1");
        if ($query->num_rows) {
            return $query->row;
        } else {
            return false;
        }
    }
}
