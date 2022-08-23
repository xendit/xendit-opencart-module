<?php

class ControllerExtensionPaymentXenditCC extends Controller {
    private $error = array();
    const XENDIT_CODE = 'cc';
    
    public function index() {
        $this->load->model('setting/setting');
        $this->load->language('extension/payment/xenditcc');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('localisation/order_status');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('payment_xenditcc', $this->request->post);
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
            'href' => $this->url->link('extension/payment/xenditcc', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['heading_title'] = $this->language->get('heading_title');

        $data['entry_status'] = $this->language->get('entry_status');

        $data['button_save'] = $this->language->get('button_save');

        $data['action'] = $this->url->link('extension/payment/xenditcc', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

        if (isset($this->request->post['payment_xenditcc_status'])) {
            $data['xenditcc_status'] = $this->request->post['payment_xenditcc_status'];
            $data['xendit_debug'] = 'request_post';
        } elseif ($this->config->has('payment_xenditcc_status')) {
            $data['xenditcc_status'] = $this->config->get('payment_xenditcc_status');
            $data['xendit_debug'] = 'have config';
        } else {
            $data['xenditcc_status'] = false;
            $data['xendit_debug'] = 'failover';
        }

        $data['token'] = $this->session->data['user_token'];
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/xenditcc', $data));
    }

    public function install() {
        if ($this->user->hasPermission('modify', 'extension/extension')) {
            $this->load->model('extension/payment/xenditcc');
            $this->model_extension_payment_xenditcc->install();
        }
    }

    public function uninstall() {
        $this->load->model('extension/payment/xendit');
        $this->model_extension_payment_xendit->removePermission(self::XENDIT_CODE);
    }

    public function validate() {
        return true;
    }
}
