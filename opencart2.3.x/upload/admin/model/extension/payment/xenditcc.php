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
            `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY, 
            `xendit_charge_id` varchar(255) NOT NULL,
            `xendit_refund_id` varchar(255) NOT NULL,
            `order_id` int(11) NOT NULL DEFAULT '0',
            `amount` int NOT NULL,
            `environment` varchar(5) NOT NULL DEFAULT 'test',
            `date_added` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
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

    public function addRefundManual($order_id, $charge_id, $amount, $environment = 'test') {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "xenditcc_refund` SET `order_id` = '" . (int)$order_id . "',
            `xendit_charge_id` = '" . $charge_id . "',
            `amount` = '" . (int)$amount . "',
            `environment` = '" . $environment . "'");
        return $this->db->getLastId();
    }
    
    public function addOrderHistory($order_info, $order_id, $order_status_id, $comment = '') {
		if ($order_info) {
			// Update the DB with the new statuses
			$this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . (int)$order_status_id . "', date_modified = NOW() WHERE order_id = '" . (int)$order_id . "'");
			$this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = '" . (int)$order_id . "', order_status_id = '" . (int)$order_status_id . "', notify = '" . (int)false . "', comment = '" . $this->db->escape($comment) . "', date_added = NOW()");
			$order_history_id = $this->db->getLastId();
			
			return $order_history_id;
		}
    }

    public function updateOrderRefundedAmount($order_id, $refunded_amount) {
        $this->db->query("UPDATE `" . DB_PREFIX . "xenditcc_charge` SET `refunded_amount` = '" . (int)$refunded_amount . "' WHERE `order_id` = '" . (int)$order_id . "'");
    }

    public function getCharge($order_id) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "xenditcc_charge` WHERE `order_id` = '" . $order_id . "' LIMIT 1");
        if ($query->num_rows) {
            return $query->row;
        } else {
            return false;
        }
    }

    public function getRefundsByChargeId($charge_id) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "xenditcc_refund` WHERE `xendit_charge_id` = '" . $charge_id . "' LIMIT 1");

        $transactions = array();
        if ($query->num_rows) {
            foreach ($query->rows as $row) {
				$transactions[] = $row;
			}
			return $transactions;
        } else {
            return false;
        }
    }
}
