<?php

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

                $refunds = $this->model_extension_payment_xenditcc->getRefundByChargeId($xendit_order['xendit_charge_id']);

				$xendit_order['amount_formatted'] = $this->currency->format($xendit_order['amount'], $order['currency_code'], false);
                $xendit_order['refunded_amount_formatted'] = $this->currency->format($xendit_order['refunded_amount'], $order['currency_code'], false);

                $data['text_payment_info'] = $this->language->get('text_payment_info');
                $data['text_transaction_amount'] = $this->language->get('text_transaction_amount');
                $data['text_refunded_amount'] = $this->language->get('text_refunded_amount');
                $data['text_refund_info'] = $this->language->get('text_refund_info');
                $data['text_confirm_refund'] = $this->language->get('text_confirm_refund');

                $data['button_refund'] = $this->language->get('button_refund');

				$data['order_id'] = $this->request->get['order_id'];
                $data['token'] = $this->request->get['token'];
                $data['xendit_order'] = $xendit_order;

				return $this->load->view('extension/payment/xenditcc_order', $data);
			}
		}
    }

    /**
     * Provide refund functionality
     */
    public function refund()
    {
        
    }
}