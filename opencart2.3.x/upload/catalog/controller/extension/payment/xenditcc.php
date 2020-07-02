<?php

require_once(DIR_SYSTEM . 'library/xendit.php');

class ControllerExtensionPaymentXenditCC extends Controller {
    const EXT_ID_PREFIX = 'opencart-xendit-';

    public function index() {
        $this->load->language('extension/payment/xenditcc');

        $api_key = $this->get_api_key();

        $data['environment'] = $this->config->get('xendit_environment');
        $data['text_instructions'] = $this->language->get('text_instructions');
        $data['text_test_instructions'] = $this->language->get('text_test_instructions');
        $data['xendit_public_key'] = $api_key['public_key'];
		$data['button_confirm'] = $this->language->get('button_confirm');
		$data['text_loading'] = $this->language->get('text_loading');

        return $this->load->view('extension/payment/xenditcc', $data);
    }

    public function process_payment() {
        $this->load->model('extension/payment/xenditcc');
        $this->load->model('checkout/order');
        $this->load->model('extension/total/shipping');
        $this->load->language('extension/payment/xendit');

        $order_id = $this->session->data['order_id'];
        $order = $this->model_checkout_order->getOrder(
            $order_id
        );

        $store_name = $this->config->get('config_name');
        $request_payload = array(
            'external_id' => self::EXT_ID_PREFIX . $order_id,
            'token_id' => $this->request->post['token_id'],
            'amount' => (int)$order['total'],
            'return_url' => $this->url->link('extension/payment/xenditcc/process_3ds')
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
                $json['error'] = 'Failed to authenticate, please try again.';
            }
            else {
                $message = 'Authentication ID: ' . $response['id'] . '. Authenticating..';
                $this->model_checkout_order->addOrderHistory(
                    $order_id,
                    1,
                    $message,
                    false
                );
                $this->model_extension_payment_xenditcc->addOrder($order, $this->config->get('xendit_environment'));

                $json['redirect'] = $response['redirect']['url'];
            }

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function process_3ds() {
        $this->load->model('extension/payment/xendit');
        $this->load->model('extension/payment/xenditcc');
        $this->load->model('checkout/order');
        $this->load->model('extension/total/shipping');
        $this->load->language('extension/payment/xendit');

        try {
            $order_id = $this->session->data['order_id'];
            $store_name = $this->config->get('config_name');

            $api_key = $this->get_api_key();
            Xendit::set_secret_key($api_key['secret_key']);
            Xendit::set_public_key($api_key['public_key']);

            if (!isset($this->request->get['hosted_3ds_id'])) {
                $message = 'Empty authentication. Cancelling order.';
                $this->cancel_order($order_id, $message);

                $redir_url = $this->url->link('extension/payment/xenditcc/failure');
                $this->response->redirect($redir_url);
                return;
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
                $redir_url = $this->url->link('extension/payment/xenditcc/failure');
                $this->response->redirect($redir_url);
                return;
            }

            if ('VERIFIED' !== $hosted_3ds['status']) {
                $message = 'Authentication failed. Cancelling order.';
                $this->cancel_order($order_id, $message);

                $redir_url = $this->url->link('extension/payment/xenditcc/failure');
                $this->response->redirect($redir_url);
                return;
            }

            $token_id = $hosted_3ds['token_id'];
            $authentication_id = $hosted_3ds['authentication_id'];
            $amount = $hosted_3ds['amount'];

            $charge_url = '/payment/xendit/credit-card/charges';
            $charge_data = array(
                'token_id' => $token_id,
                'authentication_id' => $authentication_id,
                'amount' => $amount,
                'external_id' => self::EXT_ID_PREFIX . $order_id,
            );

            $charge = Xendit::request(
                $charge_url,
                Xendit::METHOD_POST,
                $charge_data,
                array(
                    'store_name' => $store_name
                )
            );

            $this->process_order($charge, $order_id);
        } catch (Exception $e) {
            $redir_url = $this->url->link('extension/payment/xenditcc/failure');
            $this->response->redirect($redir_url);
            return;
        }
    }

    public function failure() {
        $this->load->language('extension/payment/xendit');

        $this->document->setTitle($this->language->get('heading_title'));
        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_failure'] = $this->language->get('text_failure');

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');
        $data['checkout_url'] = $this->url->link('checkout/cart');

        $this->response->setOutput($this->load->view('extension/payment/xendit_failed', $data));
    }

    private function process_order($charge, $order_id) {
        $this->model_extension_payment_xenditcc->storeChargeId($order_id, $charge);
        if ($charge['status'] !== 'CAPTURED') {
            $message = 'Charge failed. Cancelling order. Charge id: ' . $charge['id'];
            $this->cancel_order($order_id, $message);

            $redir_url = $this->url->link('extension/payment/xenditcc/failure');
            $this->response->redirect($redir_url);
            return;
        }
        $this->cart->clear();
        $message = 'Payment successful. Charge id: ' . $charge['id'];
        $this->model_checkout_order->addOrderHistory(
            $order_id,
            2,
            $message,
            false
        );
        $this->model_extension_payment_xenditcc->completeOrder($order_id);

        $redir_url = $this->url->link('checkout/success&');
        $this->response->redirect($redir_url);
    }

    private function cancel_order($order_id, $message) {
        $this->model_extension_payment_xendit->cancelOrder($order_id, $this->config->get('xendit_environment'));
        $this->model_checkout_order->addOrderHistory(
            $order_id,
            7,
            $message,
            false
        );

        return;
    }

    private function get_api_key() {
        if ($this->config->get('xendit_environment') === 'live') {
            return array(
                'secret_key' => $this->config->get('xendit_live_secret_key'),
                'public_key' => $this->config->get('xendit_live_public_key')
            );
        } else {
            return array(
                'secret_key' => $this->config->get('xendit_test_secret_key'),
                'public_key' => $this->config->get('xendit_test_public_key')
            );
        }
    }
}