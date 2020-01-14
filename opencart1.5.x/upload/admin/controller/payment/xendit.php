<?php

class ControllerPaymentXendit extends Controller
{
    private $error = array();

    public function index()
    {
        $this->load->model('setting/setting');
        $this->language->load('payment/xendit');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('localisation/order_status');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('xendit', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');

            $this->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
        }

        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL')
        );
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_payment'),
            'href'      => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL')
        );
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('payment/xendit', 'token=' . $this->session->data['token'], 'SSL')
        );

        $this->data['heading_title'] = $this->language->get('heading_title');

        $this->data['text_test_mode'] = $this->language->get('text_test_mode');
        $this->data['text_live_mode'] = $this->language->get('text_live_mode');
        $this->data['text_enabled'] = $this->language->get('text_enabled');
        $this->data['text_disabled'] = $this->language->get('text_disabled');
        $this->data['text_edit'] = $this->language->get('text_edit');

        $this->data['entry_status'] = $this->language->get('entry_status');
        $this->data['entry_mode'] = $this->language->get('entry_mode');
        $this->data['entry_public_key'] = $this->language->get('entry_public_key');
        $this->data['entry_secret_key'] = $this->language->get('entry_secret_key');

        $this->data['help_mode'] = $this->language->get('help_mode');
        $this->data['help_public_key'] = $this->language->get('help_public_key');
        $this->data['help_secret_key'] = $this->language->get('help_secret_key');

        $this->data['button_save'] = $this->language->get('button_save');
        $this->data['button_cancel'] = $this->language->get('button_cancel');

        $this->data['action'] = $this->url->link('payment/xendit', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

        if (isset($this->request->post['xendit_status'])) {
            $this->data['xendit_status'] = $this->request->post['xendit_status'];
        } elseif ($this->config->has('xendit_status')) {
            $this->data['xendit_status'] = $this->config->get('xendit_status');
        } else {
            $this->data['xendit_status'] = false;
        }

        if (isset($this->request->post['payment_xendit_environment'])) {
            $this->data['xendit_environment'] = $this->request->post['payment_xendit_environment'];
        } elseif ($this->config->has('payment_xendit_environment')) {
            $this->data['xendit_environment'] = $this->config->get('payment_xendit_environment');
        } else {
            $this->data['xendit_environment'] = 'test';
        }

        if (isset($this->request->post['payment_xendit_test_public_key'])) {
            $this->data['xendit_test_public_key'] = $this->request->post['payment_xendit_test_public_key'];
        } elseif ($this->config->has('payment_xendit_test_public_key')) {
            $this->data['xendit_test_public_key'] = $this->config->get('payment_xendit_test_public_key');
        } else {
            $this->data['xendit_test_public_key'] = '';
        }

        if (isset($this->request->post['payment_xendit_live_public_key'])) {
            $this->data['xendit_live_public_key'] = $this->request->post['payment_xendit_live_public_key'];
        } elseif ($this->config->has('payment_xendit_live_public_key')) {
            $this->data['xendit_live_public_key'] = $this->config->get('payment_xendit_live_public_key');
        } else {
            $this->data['xendit_live_public_key'] = '';
        }

        if (isset($this->request->post['payment_xendit_test_secret_key'])) {
            $this->data['xendit_test_secret_key'] = $this->request->post['payment_xendit_test_secret_key'];
        } elseif ($this->config->has('payment_xendit_test_secret_key')) {
            $this->data['xendit_test_secret_key'] = $this->config->get('payment_xendit_test_secret_key');
        } else {
            $this->data['xendit_test_secret_key'] = '';
        }

        if (isset($this->request->post['payment_xendit_live_secret_key'])) {
            $this->data['xendit_live_secret_key'] = $this->request->post['payment_xendit_live_secret_key'];
        } elseif ($this->config->has('payment_xendit_live_secret_key')) {
            $this->data['xendit_live_secret_key'] = $this->config->get('payment_xendit_live_secret_key');
        } else {
            $this->data['xendit_live_secret_key'] = '';
        }

        $this->data['token'] = $this->session->data['token'];

        //Bootstrap 3
        $this->document->addStyle('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css');
        $this->document->addStyle('https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
        $this->document->addScript('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js');

        $this->template = 'payment/xendit.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
        $this->response->setOutput($this->render());
        
        $this->cancelExpiredOrder();
    }

    public function install()
    {
        $this->load->model('payment/xendit');
        $this->model_payment_xendit->install();

        //$this->load->model('setting/event');
        //$this->model_setting_event->addEvent('xendit', 'admin/view/common/column_left/before', 'payment/xendit/cancelExpiredOrder');
        //$this->model_setting_event->addEvent('xendit', 'admin/view/common/header', 'payment/xendit/cancelExpiredOrder');
    }

    public function uninstall()
    {
        $this->load->model('payment/xendit');
        $this->model_payment_xendit->uninstall();

        //$this->load->model('setting/event');
        //$this->model_setting_event->deleteEventByCode('xendit');
    }

    public function validate()
    {
        return true;
    }

    public function cancelExpiredOrder()
    {
        $this->load->model('payment/xendit');
        $this->load->model('sale/order');

        $bulk_cancel_data = array();
        $expired_orders = $this->model_payment_xendit->getExpiredOrders();

        if ($expired_orders) {
            foreach ($expired_orders as $xendit_order) {
                $order_id = $xendit_order['order_id'];
                $order = $this->model_sale_order->getOrder(
                    $order_id
                );
    
                $bulk_cancel_data[] = array(
                    'id' => $xendit_order['xendit_invoice_id'],
                    'expiry_date' => $xendit_order['xendit_expiry_date'],
                    'order_number' => $order_id,
                    'amount' => (int)$order['total']
                );
    
                $this->model_payment_xendit->expireOrder($order_id);
                $this->model_payment_xendit->addOrderHistory(
                    $order,
                    $order_id,
                    7,
                    'Order cancelled because Xendit invoice expired',
                    false
                );
            }
        }

        if (!empty($bulk_cancel_data)) {
            $response = $this->track_order_cancellation($bulk_cancel_data);
        }
    }

    private function track_order_cancellation($payload)
    {
        $request_url = '/payment/xendit/invoice/bulk-cancel';
        $request_payload = array(
            'invoice_data' => json_encode($payload)
        );
        $request_options = array(
            'store_name' => Xendit::DEFAULT_STORE_NAME
        );

        $api_key = $this->get_api_key();
        Xendit::set_secret_key($api_key['secret_key']);

        $response = Xendit::request($request_url, Xendit::METHOD_POST, $request_payload, $request_options);
        return $response;
    }

    private function get_api_key()
    {
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
