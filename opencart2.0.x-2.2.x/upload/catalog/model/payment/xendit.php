<?php

class Modelpaymentxendit extends Model
{
    public function getMethod($address, $total)
    {
        $method_data = array();
        return $method_data;
    }

    public function addOrder($order_info, $data, $environment = 'test', $type = 'invoice')
    {
        $query = "INSERT INTO `" . DB_PREFIX . "xendit_order`
                    SET `order_id` = '" . (int)$order_info['order_id'] . "',
                        `status` = 'PENDING',
                        `external_id` = '" . $data['external_id'] . "',
                        `amount` = '" . $data['amount'] . "',
                        `payment_method` = '" . $order_info['payment_method'] . "',
                        `environment` = '" . $environment . "'";

        if ($type == 'invoice') {
            $query .= ",
                        `xendit_invoice_id`     = '" . $data['id'] . "',
                        `xendit_expiry_date`    = '" . $data['expiry_date'] . "'";
        }

        $this->db->query($query);

        return $this->db->getLastId();
    }

    public function paidOrder($order_id, $extra = [])
    {
        $query = "UPDATE `" . DB_PREFIX . "xendit_order` SET `status` = 'PAID', `xendit_paid_date` = NOW()";

        if (count($extra) > 0) {
            foreach ($extra as $key => $value) {
                $query .= ", `".$key."` = '".$value."'";
            }
        }

        $query .= " WHERE `order_id` = '" . $order_id . "'";
        $this->db->query($query);
    }

    public function cancelOrder($order_id)
    {
        $this->db->query("UPDATE `" . DB_PREFIX . "xendit_order` SET `status` = 'CANCELLED', `xendit_cancelled_date` = NOW() WHERE `order_id` = '" . $order_id . "'");
    }
}
