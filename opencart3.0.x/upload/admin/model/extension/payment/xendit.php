<?php

class ModelExtensionPaymentXendit extends Model {
    public function install() {
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "xendit_order` (
            `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `order_id` int(11) NOT NULL,
            `external_id` varchar(255) NOT NULL,
            `amount` decimal(10,2) NOT NULL,
            `payment_method` varchar(255) NOT NULL,
            `xendit_invoice_id` varchar(255),
            `xendit_invoice_fee` decimal(10,2),
            `xendit_charge_id` varchar(255),
            `xendit_paid_date` datetime,
            `xendit_expiry_date` datetime,
            `xendit_cancelled_date` datetime,
            `status` varchar(150) NOT NULL,
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

    public function addOrderHistory($order_info, $order_id, $order_status_id, $comment = '') {
		if ($order_info) {
			// Update the DB with the new statuses
			$this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . (int)$order_status_id . "', date_modified = NOW() WHERE order_id = '" . (int)$order_id . "'");
			$this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = '" . (int)$order_id . "', order_status_id = '" . (int)$order_status_id . "', notify = '" . (int)false . "', comment = '" . $this->db->escape($comment) . "', date_added = NOW()");
			$order_history_id = $this->db->getLastId();

			return $order_history_id;
		}
	}

    public function removePermission($payment_method = ''){
        // Remove permission when uninstall
        $this->load->model('user/user_group');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/payment/xendit'. $payment_method);
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'extension/payment/xendit'. $payment_method);
    }
}
