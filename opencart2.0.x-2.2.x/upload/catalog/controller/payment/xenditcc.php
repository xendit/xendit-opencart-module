<?php

require_once(DIR_SYSTEM . 'library/xendit.php');

class Controllerpaymentxenditcc extends Controller
{
    const EXT_ID_PREFIX = 'xendit-opencart-';

    public function index() {
        $this->load->language('payment/xenditcc');

        $api_key = $this->get_api_key();

        $data['environment'] = $this->config->get('xendit_environment');
        $data['text_instructions'] = $this->language->get('text_instructions');
        $data['text_test_instructions'] = $this->language->get('text_test_instructions');
        $data['xendit_public_key'] = $api_key['public_key'];
        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['text_loading'] = $this->language->get('text_loading');

        if (version_compare(VERSION, '2.2', '>=') == true) {
            return $this->load->view('payment/xenditcc.tpl', $data);
        } else {
            return $this->load->view('default/template/payment/xenditcc.tpl', $data);
        }
    }

    public function process_payment() {
        $this->load->model('payment/xendit');
        $this->load->model('checkout/order');
        $this->load->model('total/shipping');
        $this->load->language('payment/xendit');

        $order_id = $this->session->data['order_id'];
        $order = $this->model_checkout_order->getOrder(
            $order_id
        );

        $store_name = $this->config->get('config_name');
        $request_payload = array(
            'external_id' => self::EXT_ID_PREFIX . $order_id,
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
                $json['error'] = 'Failed to authenticate, please try again.';
            }
            else {
                $response['external_id'] = $request_payload['external_id'];
                $this->model_payment_xendit->addOrder($order, $response, $this->config->get('xendit_environment'), 'cc');

                $message = 'Authentication ID: ' . $response['id'] . '. Authenticating..';
                $this->model_checkout_order->addOrderHistory(
                    $order_id,
                    1,
                    $message,
                    false
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
        $this->load->model('total/shipping');
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

                $redir_url = $this->url->link('payment/xenditcc/failure');
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
                $redir_url = $this->url->link('payment/xenditcc/failure');
                $this->response->redirect($redir_url);
                return;
            }

            if ('VERIFIED' !== $hosted_3ds['status']) {
                $message = 'Authentication failed. Cancelling order.';
                $this->cancel_order($order_id, $message);

                $redir_url = $this->url->link('payment/xenditcc/failure');
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
            
            if ($charge['status'] !== 'CAPTURED') {
                $message = 'Charge failed. Cancelling order. Charge id: ' . $charge['id'];
                $this->cancel_order($order_id, $message);
                $redir_url = $this->url->link('payment/xenditcc/failure');
                $this->response->redirect($redir_url);
                return;
            }

            $this->process_order($charge, $order_id);
        } catch (Exception $e) {
            $redir_url = $this->url->link('payment/xenditcc/failure');
            $this->response->redirect($redir_url);
            return;
        }
    }

    public function failure() {
        $this->load->language('payment/xendit');

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

        if (version_compare(VERSION, '2.2', '>=') == true) {
            $this->response->setOutput($this->load->view('payment/xendit_failed.tpl', $data));
        } else {
            $this->response->setOutput($this->load->view('default/template/payment/xendit_failed.tpl', $data));
        }
    }

    private function process_order($charge, $order_id) {
        if ($charge['status'] !== 'CAPTURED') {
            $message = 'Charge failed. Cancelling order. Charge id: ' . $charge['id'];
            return $this->cancel_order($order_id, $message);
        }
        $this->cart->clear();

        $this->model_payment_xendit->paidOrder($order_id, $charge['created'], array('xendit_charge_id' => $charge['id']));
        
        $message = 'Payment successful. Charge id: ' . $charge['id'];
        $this->model_checkout_order->addOrderHistory(
            $order_id,
            2,
            $message,
            false
        );

        $redir_url = $this->url->link('checkout/success&');
        $this->response->redirect($redir_url);
    }

    private function cancel_order($order_id, $message) {
        $this->model_payment_xendit->cancelOrder($order_id);

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