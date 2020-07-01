<?php

require_once(DIR_SYSTEM . 'library/xendit.php');

class ControllerPaymentXenditCC extends Controller {
    public function index() {
        $this->load->language('payment/xenditcc');

        $api_key = $this->get_api_key();

        $this->data['environment'] = $this->config->get('payment_xendit_environment');
        $this->data['text_instructions'] = $this->language->get('text_instructions');
        $this->data['text_test_instructions'] = $this->language->get('text_test_instructions');
        $this->data['xendit_public_key'] = $api_key['public_key'];
        $this->data['button_confirm'] = $this->language->get('button_confirm');
        $this->data['text_loading'] = $this->language->get('text_loading');

        $this->template = 'default/template/payment/xenditcc.tpl';
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

        $store_name = $this->config->get('config_name');
        $request_payload = array(
            'external_id' => 'opencart-xendit-' . $order_id,
            'token_id' => $this->request->post['token_id'],
            'amount' => (int)$order['total'],
            'return_url' => $this->url->link('payment/xenditcc/process_3ds')
        );
        $request_url = '/payment/xendit/credit-card/hosted-3ds';
        $request_options = array(
            'store_name' => $store_name,
            'should_use_public_key' => true
        );

        $api_key = $this->get_api_key();
        Xendit::set_public_key($api_key['public_key']);
        Xendit::set_secret_key($api_key['secret_key']);

        try {
            $response = Xendit::request($request_url, Xendit::METHOD_POST, $request_payload, $request_options);

            if (isset($response['error_code'])) {
                $json['error'] = $response['message'];
            }
            else {
                $response['external_id'] = $request_payload['external_id']; //original response doesn't return external_id
                $this->model_payment_xendit->addOrder($order, $response, $this->config->get('payment_xendit_environment'), 'cc');
                
                $message = 'Authentication ID: ' . $response['id'] . '. Authenticating..';
                $this->model_checkout_order->confirm(
                    $order_id,
                    1,
                    $message,
                    true
                );
    
                $json['redirect'] = $response['redirect']['url'];
            }

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function process_3ds() {
        $this->load->model('payment/xendit');
        $this->load->model('checkout/order');
        $this->load->language('payment/xendit');

        try {
            $order_id = $this->session->data['order_id'];
            $store_name = $this->config->get('config_name');

            $api_key = $this->get_api_key();
            Xendit::set_secret_key($api_key['secret_key']);
            Xendit::set_public_key($api_key['public_key']);

            if (!isset($this->request->get['hosted_3ds_id'])) {
                $message = 'Empty authentication. Cancelling order.';
                $this->cancel_order($order_id, $message);
            }

            $hosted_3ds_id = $this->request->get['hosted_3ds_id'];
            $hosted_3ds_url = '/payment/xendit/credit-card/hosted-3ds/' . $hosted_3ds_id;
            $hosted_3ds = Xendit::request(
                $hosted_3ds_url,
                Xendit::METHOD_GET,
                array(),
                array(
                    'store_name' => $store_name,
                    'should_use_public_key' => true
                )
            );
            
            if (isset($hosted_3ds['error_code'])) {
                $redir_url = $this->url->link('payment/xenditcc/failure');
                $this->response->redirect($redir_url);
                return;
            }
            
            if ('VERIFIED' !== $hosted_3ds['status']) {
                $message = 'Authentication failed. Cancelling order.';
                $this->cancel_order($order_id, $message);
            }

            $token_id = $hosted_3ds['token_id'];
            $authentication_id = $hosted_3ds['authentication_id'];
            $amount = $hosted_3ds['amount'];

            $charge_url = '/payment/xendit/credit-card/charges';
            $charge_data = array(
                'token_id' => $token_id,
                'authentication_id' => $authentication_id,
                'amount' => $amount,
                'external_id' => 'opencart-xendit-' . $order_id,
            );

            $charge = Xendit::request(
                $charge_url,
                Xendit::METHOD_POST,
                $charge_data,
                array(
                    'store_name' => $store_name
                )
            );

            $this->process_order($charge, $order_id, $charge_data);
        } catch (Exception $e) {
            $redir_url = $this->url->link('payment/xenditcc/failure');
            $this->response->redirect($redir_url);
            return;
        }
    }

    public function failure() {
        $this->load->language('payment/xendit');

        $this->document->setTitle($this->language->get('heading_title'));
        $this->data['heading_title'] = $this->language->get('heading_title');
        $this->data['text_failure'] = $this->language->get('text_failure');

        $this->data['column_left'] = $this->getChild('common/column_left');
        $this->data['column_right'] = $this->getChild('common/column_right');
        $this->data['content_top'] = $this->getChild('common/content_top');
        $this->data['content_bottom'] = $this->getChild('common/content_bottom');
        $this->data['footer'] = $this->getChild('common/footer');
        $this->data['header'] = $this->getChild('common/header');
        $this->data['checkout_url'] = $this->url->link('checkout/cart');

        $this->template = 'default/template/payment/xendit_failed.tpl';
        $this->response->setOutput($this->render());
    }

    private function process_order($charge, $order_id, $charge_data) {
        if (isset($charge['error_code'])) {
            $message = "Charge failed. Cancelling order.\nReason: " . $charge['message'];
            return $this->cancel_order($order_id, $message);
        }
        else if ($charge['status'] !== 'CAPTURED') {
            $message = 'Charge failed. Cancelling order. Charge ID: ' . $charge['id'];
            return $this->cancel_order($order_id, $message);
        }
        
        $this->cart->clear();

        $this->model_payment_xendit->completeOrder($order_id, 
            ", `xendit_charge_id` = '". $charge['id'] . "'"
        );

        $message = 'Payment successful. Charge ID: ' . $charge['id'];
        $this->model_checkout_order->update(
            $order_id,
            2,
            $message,
            false
        );

        $redir_url = $this->url->link('checkout/success');
        $this->response->redirect($redir_url);
    }

    private function cancel_order($order_id, $message) {
        $this->model_payment_xendit->cancelOrder($order_id);
        $this->model_checkout_order->update(
            $order_id,
            7,
            $message,
            false
        );

        $redir_url = $this->url->link('payment/xenditcc/failure');
        $this->response->redirect($redir_url);
        return;
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
}