<?php

require_once(DIR_SYSTEM . 'library/xendit.php');

class ControllerPaymentXendit extends Controller {
    const EXT_ID_PREFIX = 'xendit_opencart_';

    public function index() {
        $this->load->language('payment/xendit');

        $this->data['environment'] = $this->config->get('payment_xendit_environment');
        $this->data['text_instructions'] = $this->language->get('text_instructions');
        $this->data['text_test_instructions'] = $this->language->get('text_test_instructions');

        $this->template = 'default/template/payment/xendit.tpl';
        $this->render();
    }

    public function process_payment() {
        $this->load->model('payment/xendit');
        $this->load->model('checkout/order');
        $this->load->language('payment/xendit');

        $order_id = $this->session->data['order_id'];
        $order = $this->model_checkout_order->getOrder(
            $order_id
        );

        $api_key = $this->get_api_key();

        Xendit::set_secret_key($api_key['secret_key']);
        Xendit::set_public_key($api_key['public_key']);

        $store_name = $this->config->get('config_name');
        $request_payload = array(
            'external_id' => self::EXT_ID_PREFIX . $order_id,
            'amount' => (int)$order['total'],
            'payer_email' => $order['email'],
            'description' => 'Payment for order #' . $order_id . ' at ' . $store_name,
            'client_type' => 'INTEGRATION',
            'success_redirect_url' => $this->url->link('checkout/success'),
            'failure_redirect_url' => $this->url->link('checkout/cart'),
            'platform_callback_url' => $this->url->link('payment/xendit/process_notification')
        );
        $request_url = '/payment/xendit/invoice';
        $request_options = array(
            'store_name' => $store_name
        );

        try {
            $response = Xendit::request($request_url, Xendit::METHOD_POST, $request_payload, $request_options);
            
            if (isset($response['error_code'])) {
                $json['error'] = $response['message'];
            }
            else {
                $message = 'Invoice ID: ' . $response['id'] . '. Redirecting..';
                //$this->model_checkout_order->addOrder($order); //xendit add order
                //Update order status to pending
                $this->model_checkout_order->confirm(
                    $order_id,
                    1,
                    $message,
                    true
                );
    
                $json['redirect'] = $response['invoice_url'];
            }

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function process_notification() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            $this->load->model('payment/xendit');
            $this->load->model('checkout/order');

            $response = json_decode(file_get_contents('php://input'), true);
            $invoice_id = $response['id'];
            $external_id = $response['external_id'];
            $order_id = str_replace(self::EXT_ID_PREFIX, "", $external_id);

            $api_key = $this->get_api_key();
            Xendit::set_secret_key($api_key['secret_key']);
            $store_name = $this->config->get('config_name');
            $request_url = '/payment/xendit/invoice/' . $invoice_id;
            $request_options = array(
                'store_name' => $store_name
            );

            try {
                $response = Xendit::request($request_url, Xendit::METHOD_GET, array(), $request_options);

                if ( isset($response['error_code']) ) {
                    $message = 'Could not get xendit invoice. Invoice id: ' . $invoice_id . '. Cancelling order.';
                    $this->response->addHeader('HTTP/1.1 400 Bad Request');
                    $this->response->setOutput($message);
                    return;
                }

                return $this->process_order($response, $order_id);
            } catch (Exception $e) {
                echo 'something';
            }
        } else {
            echo 'Unexpected request method';
        }
    }

    private function get_api_key() {
        if ($this->config->get('payment_xendit_environment') === 'live') {
            return array(
                'secret_key' => $this->config->get('payment_xendit_live_secret_key'),
                'public_key' => $this->config->get('payment_xendit_live_public_key')
            );
        } else {
            return array(
                'secret_key' => $this->config->get('payment_xendit_test_secret_key'),
                'public_key' => $this->config->get('payment_xendit_test_public_key')
            );
        }
    }

    private function process_order($response, $order_id) {
        if ($response['status'] === 'PAID' || $response['status'] === 'SETTLED') {
            $this->cart->clear();
            $message = 'Payment successful. Invoice id: ' . $response['id'];
            //$this->model_payment_xendit->completeOrder($order_id);

            //Update order status to processing
            $this->model_checkout_order->update(
                $order_id,
                2,
                $message,
                false
            );
            $this->response->setOutput($message);
        } else {
            $message = 'Invoice not paid or settled. Cancelling order. Charge id: ' . $response['id'];
            return $this->cancel_order($order_id, $message);
        }
    }

    private function cancel_order($order_id, $message) {
        $this->cart->clear();
        $this->model_checkout_order->update(
            $order_id,
            7,
            $message,
            false
        );

        $message = 'Successfully completed order ' . $order_id;
        $this->response->setOutput($message);
    }
}