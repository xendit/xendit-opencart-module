<?php

class Modelpaymentxenditcc extends Model {
    public function getMethod($address, $total) {
        $this->load->language('payment/xenditcc');

        $status = true;

        $method_data = array();

        if ($status) {
            $method_data = array(
                'code'       => 'xenditcc',
                'title'      => $this->language->get('text_title'),
                'terms'      => '',
                'sort_order' => $this->config->get('xenditcc_sort_order')
            );
        }

        return $method_data;
    }
    
    public function addCharge($xendit_charge_id='', $order_id='', $environment='') {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "xenditcc_charge` (`xendit_charge_id`, `order_id`, `date_added`, `environment`) VALUES ('" . $this->db->escape($xendit_charge_id) . "', " . (int)$order_id . ", NOW(), '" . $this->db->escape($environment) . "')");
    }
}