<?php

require_once(DIR_SYSTEM . 'library/xendit.php');

class ControllerExtensionPaymentXenditCC extends Controller {
    private $error = array();

    public function index()
    {
        $this->load->model('setting/setting');
        $this->load->language('extension/payment/xenditcc');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('localisation/order_status');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('xenditcc', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true));
        }

        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true)
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/payment/xenditcc', 'token=' . $this->session->data['token'], true)
        );

        $data['heading_title'] = $this->language->get('heading_title');

        $data['entry_status'] = $this->language->get('entry_status');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_edit'] = $this->language->get('text_edit');

        $data['button_save'] = $this->language->get('button_save');

        $data['action'] = $this->url->link('extension/payment/xenditcc', 'token=' . $this->session->data['token'], true);
        $data['cancel'] = $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true);

        if (isset($this->request->post['xenditcc_status'])) {
            $data['xenditcc_status'] = $this->request->post['xenditcc_status'];
            $data['xendit_debug'] = 'request_post';
        } elseif ($this->config->has('xenditcc_status')) {
            $data['xenditcc_status'] = $this->config->get('xenditcc_status');
            $data['xendit_debug'] = 'have config';
        } else {
            $data['xenditcc_status'] = false;
            $data['xendit_debug'] = 'failover';
        }

        $data['token'] = $this->session->data['token'];
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/xenditcc', $data));
    }

    public function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/payment/xenditcc')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
    }

    public function install()
    {
        $this->load->model('extension/payment/xenditcc');
        $this->model_extension_payment_xenditcc->install();
    }

    public function uninstall()
    {
        $this->load->model('extension/payment/xenditcc');
        $this->model_extension_payment_xenditcc->uninstall();
    }

    /**
     * Display a new tab in order page with the provided data
     */
    public function order()
    {
        if ($this->config->get('xenditcc_status')) {
            $this->load->model('sale/order');
			$this->load->model('extension/payment/xenditcc');

            $order = $this->model_sale_order->getOrder($this->request->get['order_id']);
			$xendit_order = $this->model_extension_payment_xenditcc->getCharge($this->request->get['order_id']);

			if (!empty($xendit_order)) {
                $this->load->language('extension/payment/xenditcc');
                $data = array();

                $xendit_order['amount_formatted'] = $this->currency->format($xendit_order['amount'], $order['currency_code'], false);
                $xendit_order['refunded_amount_formatted'] = $this->currency->format($xendit_order['refunded_amount'], $order['currency_code'], false);

                $data['text_payment_info'] = $this->language->get('text_payment_info');
                $data['text_transaction_amount'] = $this->language->get('text_transaction_amount');
                $data['text_refunded_amount'] = $this->language->get('text_refunded_amount');
                $data['text_refund_info'] = $this->language->get('text_refund_info');
                $data['text_confirm_refund'] = $this->language->get('text_confirm_refund');

                $data['button_refund_xendit'] = $this->language->get('button_refund_xendit');
                $data['button_refund_manual'] = $this->language->get('button_refund_manual');

				$data['order_id'] = $this->request->get['order_id'];
                $data['token'] = $this->request->get['token'];
                $data['xendit_order'] = $xendit_order;

				return $this->load->view('extension/payment/xenditcc_order', $data);
			}
		}
    }

    /**
     * Provide refund functionality. Act as another endpoint.
     */
    public function refund()
    {
        $this->load->language('extension/payment/xenditcc');
		$json = array();

		if (
            isset($this->request->post['order_id']) &&
            !empty($this->request->post['order_id']) &&
            isset($this->request->post['amount']) &&
            !empty($this->request->post['amount']) &&
            isset($this->request->post['type']) &&
            !empty($this->request->post['type'])
        ) {
            $this->load->model('extension/payment/xenditcc');
            $this->load->model('sale/order');
            
            $order_id = $this->request->post['order_id'];
            $amount = $this->request->post['amount'];
            $type = $this->request->post['type'];

            $order = $this->model_sale_order->getOrder($order_id);
            $xenditcc_charge = $this->model_extension_payment_xenditcc->getCharge($order_id);

            $total_amount = $xenditcc_charge['amount'];
            $refunded_amount = empty($xenditcc_charge['refunded_amount']) ? 0 : $xenditcc_charge['refunded_amount'];
            $charge_id = $xenditcc_charge['xendit_charge_id'];

            if ($amount + $refunded_amount > $total_amount) {
                $json['error'] = true;
                $json['msg'] = 'Refund amount exceeded.';
                $this->response->setOutput(json_encode($json));
                return;
            }

            $api_key = $this->get_api_key();
            Xendit::set_secret_key($api_key['secret_key']);
            Xendit::set_public_key($api_key['public_key']);
            $refund_url = '/payment/xendit/credit-card/charges/' . $charge_id . '/refund';
            $refund = Xendit::request(
                $refund_url,
                Xendit::METHOD_POST,
                array(
                    'external_id' => 'opencart_xendit_' . $order_id . '_' . uniqid(),
                    'amount' => (int)$this->request->post['amount']
                )
            );

            if (isset($refund['error_code'])) {
				$json['error'] = true;
				$json['msg'] = isset($refund['message']) && !empty($refund['message']) ? (string)$refund['message'] : 'Unable to refund';
            } else {
                $this->model_extension_payment_xenditcc->addRefund($order_id, $charge_id, $refund, $this->config->get('xendit_environment'));
                $refunded_amount += $refund['amount'];
                $this->model_extension_payment_xenditcc->updateOrderRefundedAmount($order_id, $refunded_amount);
                $this->model_extension_payment_xenditcc->addOrderHistory(
                    $order,
                    $order_id,
                    $order['order_status_id'],
                    'Refund processed via Xendit at ' . date("Y/m/d h:i:sa") . " for " . $this->currency->format($refund['amount'], $order['currency_code'], false),
                    false
                );

                if ($refunded_amount == $total_amount) {
                    $this->model_extension_payment_xenditcc->addOrderHistory(
                        $order,
                        $order_id,
                        11,
                        'Order is fully refunded',
                        false
                    );
                }

                $json['refunded_amount_formatted'] = $this->currency->format($refunded_amount, $order['currency_code'], false);
                $json['error'] = false;
                $json['msg'] = $this->language->get('text_refund_ok_order');
            }
		} else {
			$json['error'] = true;
			$json['msg'] = 'Missing data';
		}

		$this->response->setOutput(json_encode($json));
    }

    /**
     * Provide refund functionality. Act as another endpoint.
     */
    public function refund_manual()
    {
        $this->load->language('extension/payment/xenditcc');
		$json = array();

		if (isset($this->request->post['order_id']) && !empty($this->request->post['order_id'])&& isset($this->request->post['amount']) && !empty($this->request->post['amount'])) {
            $this->load->model('extension/payment/xenditcc');
            $this->load->model('sale/order');
            
            $order_id = $this->request->post['order_id'];
            $amount = $this->request->post['amount'];

            $order = $this->model_sale_order->getOrder($order_id);
            $xenditcc_charge = $this->model_extension_payment_xenditcc->getCharge($order_id);

            $total_amount = $xenditcc_charge['amount'];
            $refunded_amount = empty($xenditcc_charge['refunded_amount']) ? 0 : $xenditcc_charge['refunded_amount'];
            $charge_id = $xenditcc_charge['xendit_charge_id'];

            if ($amount + $refunded_amount > $total_amount) {
                $json['error'] = true;
                $json['msg'] = 'Refund amount exceeded.';
                $this->response->setOutput(json_encode($json));
                return;
            }

            $this->model_extension_payment_xenditcc->addRefundManual($order_id, $charge_id, $amount, $this->config->get('xendit_environment'));
            $refunded_amount += $amount;
            $this->model_extension_payment_xenditcc->updateOrderRefundedAmount($order_id, $refunded_amount);
            $this->model_extension_payment_xenditcc->addOrderHistory(
                $order,
                $order_id,
                $order['order_status_id'],
                'Refund processed manually at ' . date("Y/m/d h:i:sa") . " for " . $this->currency->format($amount, $order['currency_code'], false),
                false
            );

            if ($refunded_amount == $total_amount) {
                $this->model_extension_payment_xenditcc->addOrderHistory(
                    $order,
                    $order_id,
                    11,
                    'Order is fully refunded',
                    false
                );
            }

            $json['refunded_amount_formatted'] = $this->currency->format($refunded_amount, $order['currency_code'], false);
            $json['error'] = false;
            $json['msg'] = $this->language->get('text_refund_ok_order');
		} else {
			$json['error'] = true;
			$json['msg'] = 'Missing data';
		}

		$this->response->setOutput(json_encode($json));
    }

    /**
     * Retrieve API key
     * 
     * @return array
     */
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