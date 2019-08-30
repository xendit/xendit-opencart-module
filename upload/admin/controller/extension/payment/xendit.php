<?php

class ControllerExtensionPaymentXendit extends Controller
{
    private $error = array();

    public function index()
    {
        $this->load->model('setting/setting');
        $this->load->language('extension/payment/xendit');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('localisation/order_status');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('payment_xendit', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
        }

        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/payment/xendit', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['heading_title'] = $this->language->get('heading_title');

        $data['text_test_mode'] = $this->language->get('text_test_mode');
        $data['text_live_mode'] = $this->language->get('text_live_mode');

        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_mode'] = $this->language->get('entry_mode');
        $data['entry_public_key'] = $this->language->get('entry_public_key');
        $data['entry_secret_key'] = $this->language->get('entry_secret_key');

        $data['help_mode'] = $this->language->get('help_mode');
        $data['help_public_key'] = $this->language->get('help_public_key');
        $data['help_secret_key'] = $this->language->get('help_secret_key');

        $data['button_save'] = $this->language->get('button_save');

        $data['action'] = $this->url->link('extension/payment/xendit', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

        if (isset($this->request->post['payment_xendit_status'])) {
            $data['xendit_status'] = $this->request->post['payment_xendit_status'];
        } elseif ($this->config->has('payment_xendit_status')) {
            $data['xendit_status'] = $this->config->get('payment_xendit_status');
        } else {
            $data['xendit_status'] = false;
        }

        if (isset($this->request->post['payment_xendit_environment'])) {
            $data['xendit_environment'] = $this->request->post['payment_xendit_environment'];
        } elseif ($this->config->has('payment_xendit_environment')) {
            $data['xendit_environment'] = $this->config->get('payment_xendit_environment');
        } else {
            $data['xendit_environment'] = 'test';
        }

        if (isset($this->request->post['payment_xendit_test_public_key'])) {
            $data['xendit_test_public_key'] = $this->request->post['payment_xendit_test_public_key'];
        } elseif ($this->config->has('payment_xendit_test_public_key')) {
            $data['xendit_test_public_key'] = $this->config->get('payment_xendit_test_public_key');
        } else {
            $data['xendit_test_public_key'] = '';
        }

        if (isset($this->request->post['payment_xendit_live_public_key'])) {
            $data['xendit_live_public_key'] = $this->request->post['payment_xendit_live_public_key'];
        } elseif ($this->config->has('payment_xendit_live_public_key')) {
            $data['xendit_live_public_key'] = $this->config->get('payment_xendit_live_public_key');
        } else {
            $data['xendit_live_public_key'] = '';
        }

        if (isset($this->request->post['payment_xendit_test_secret_key'])) {
            $data['xendit_test_secret_key'] = $this->request->post['payment_xendit_test_secret_key'];
        } elseif ($this->config->has('payment_xendit_test_secret_key')) {
            $data['xendit_test_secret_key'] = $this->config->get('payment_xendit_test_secret_key');
        } else {
            $data['xendit_test_secret_key'] = '';
        }

        if (isset($this->request->post['payment_xendit_live_secret_key'])) {
            $data['xendit_live_secret_key'] = $this->request->post['payment_xendit_live_secret_key'];
        } elseif ($this->config->has('payment_xendit_live_secret_key')) {
            $data['xendit_live_secret_key'] = $this->config->get('payment_xendit_live_secret_key');
        } else {
            $data['xendit_live_secret_key'] = '';
        }

        $data['token'] = $this->session->data['user_token'];
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/xendit', $data));
    }

    public function install()
    {
        $this->load->model('extension/payment/xendit');
        $this->model_extension_payment_xendit->install();

        $this->load->model('setting/event');
        $this->model_setting_event->addEvent('xendit', 'admin/view/common/column_left/before', 'extension/payment/xendit/cancelExpiredOrder');
    }

    public function uninstall()
    {
        $this->load->model('extension/payment/xendit');
        $this->model_extension_payment_xendit->uninstall();

        $this->load->model('setting/event');
        $this->model_setting_event->deleteEventByCode('xendit');
    }

    public function validate()
    {
        return true;
    }

    public function cancelExpiredOrder($eventRoute, &$data)
    {
        $data['menus'][] = array(
            'id' => 'menu-xendit',
            'icon' => 'fa fa-shopping-cart fa-fw',
            'name' => 'Xendittt',
            'href' => $this->url->link('extension/payment/xendit'),
            'children' => array()
        );
    }
}
