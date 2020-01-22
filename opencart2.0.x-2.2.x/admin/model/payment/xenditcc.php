<?php

class Modelpaymentxenditcc extends Model {
    public function install() {
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "xenditcc_charge` (
            `xendit_charge_id` varchar(255) NOT NULL PRIMARY KEY,
            `order_id` int(11) NOT NULL DEFAULT '0',
            `date_added` DATETIME NOT NULL,
            `environment` varchar(5) NOT NULL DEFAULT 'test'
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "xenditcc_refund` (
            `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `xendit_refund_id` varchar(255),
            `order_id` int(11) NOT NULL DEFAULT '0',
            `xendit_charge_id` varchar(255) NOT NULL DEFAULT '0',
            `amount` int(11) NOT NULL DEFAULT '0',
            `date_added` DATETIME NOT NULL,
            `environment` varchar(5) NOT NULL DEFAULT 'test'
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
    }

    public function uninstall() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "xenditcc_charge`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "xenditcc_refund`");
    }

    public function getOrder($order_id) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "xenditcc_charge` `xenditcc`
            JOIN `" . DB_PREFIX . "order` `order` ON `order`.`order_id` =  `xenditcc`.`order_id`
            WHERE `xenditcc`.`order_id` = '" . $order_id . "' LIMIT 1");
        if ($query->num_rows) {
            return $query->row;
        } else {
            return false;
        }
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

    public function addRefund($xendit_refund_id='', $xendit_charge_id='', $order_id='', $amount='', $environment='') {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "xenditcc_refund` (`xendit_refund_id`, `xendit_charge_id`, `order_id`, `date_added`, `amount`, `environment`) VALUES ('" . $this->db->escape($xendit_refund_id) . "', '" . $this->db->escape($xendit_charge_id) . "', " . (int)$order_id . ", NOW(), " . (int)$amount . ", '" . $this->db->escape($environment) . "')");
    }

    public function getTotalRefunded($order_id='') {
        $query = $this->db->query("SELECT SUM(`amount`) AS `total` FROM `" . DB_PREFIX . "xenditcc_refund` WHERE `order_id` = " . (int)$order_id . "");

		return (double)$query->row['total'];
    }
    
    public function getRefundHistory($order_id='') {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "xenditcc_refund` WHERE `order_id` = " . (int)$order_id . "");

		return $query->rows;
    }
}