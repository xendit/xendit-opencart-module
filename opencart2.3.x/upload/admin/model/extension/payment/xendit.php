<?php

class ModelExtensionPaymentXendit extends Model {
    public function install() {
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "xendit_order` (
            `xendit_invoice_id` varchar(255) NOT NULL PRIMARY KEY,
            `xendit_expiry_date` datetime NOT NULL,
            `status` varchar(255) NOT NULL,
            `order_id` int(11) NOT NULL DEFAULT '0',
            `environment` varchar(5) NOT NULL DEFAULT 'test'
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
    }

    public function uninstall() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "xendit_order`");
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

    public function expireOrder($order_id) {
        $this->db->query("UPDATE `" . DB_PREFIX . "xendit_order` SET `status` = 'EXPIRED' WHERE `order_id` = '" . $order_id . "'");
    }

    public function getExpiredOrders() {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "xendit_order` WHERE `xendit_expiry_date` < NOW() AND `status` = 'PENDING'");

        if ($query->num_rows) {
            return $query->rows;
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
}