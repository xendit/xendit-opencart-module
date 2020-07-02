<?php

class ModelExtensionPaymentXendit extends Model
{
    public function getMethod($address, $total)
    {
        $method_data = array();
        return $method_data;
    }

    public function addOrder($order_info, $data, $environment = 'test', $type = 'invoice')
    {
        $query = "  INSERT INTO `" . DB_PREFIX . "xendit_order` 
                    SET `order_id`              = '" . $order_info['order_id'] . "',
                        `external_id`           = '" . $data['external_id'] . "',
                        `amount`                = '" . $data['amount'] . "',
                        `payment_method`        = '" . $order_info['payment_method'] . "',
                        `status`                = 'PENDING',
                        `environment`           = '" . $environment . "'";
        
        if ($type == 'invoice') {
            $query .= ",
                        `xendit_invoice_id`     = '" . $data['id'] . "',
                        `xendit_expiry_date`    = '" . $data['expiry_date'] . "'";
        }
        $this->db->query($query);
        return $this->db->getLastId();
    }

    public function paidOrder($order_id, $date, $extra = [])
    {
        $date = date("Y-m-d H:i:s", strtotime($date)); 
        $query = "UPDATE `" . DB_PREFIX . "xendit_order` SET `status` = 'PAID', `xendit_paid_date` = '$date'";

        foreach ($extra as $key => $value) {
            $query .= ", `".$key."` = '".$value."'";
        }

        $query .= " WHERE `order_id` = '$order_id'";
        $this->db->query($query);
    }

    public function cancelOrder($order_id)
    {
        $date = gmdate("Y-m-d H:i:s");
        $this->db->query("UPDATE `" . DB_PREFIX . "xendit_order` 
                          SET `status` = 'CANCELLED', `xendit_cancelled_date` = '$date' 
                          WHERE `order_id` = '" . $order_id . "'");
    }
}