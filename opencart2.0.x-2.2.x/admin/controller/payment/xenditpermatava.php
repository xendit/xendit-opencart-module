<?php

class Controllerpaymentxenditpermatava extends Controller {
    private $error = array();

    const XENDIT_CODE = 'permatava';

    public function index() {
        $this->load->model('setting/setting');
        $this->load->language('payment/xendit' . self::XENDIT_CODE);

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('localisation/order_status');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('xendit' . self::XENDIT_CODE, $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'] . '&type=payment', true));
        }

        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('extension/payment', 'token=' . $this->session->data['token'] . '&type=payment', true)
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('payment/xendit' . self::XENDIT_CODE, 'token=' . $this->session->data['token'], true)
        );

        $data['heading_title'] = $this->language->get('heading_title');

        $data['entry_status'] = $this->language->get('entry_status');

        $data['button_save'] = $this->language->get('button_save');

        $data['action'] = $this->url->link('payment/xendit' . self::XENDIT_CODE, 'token=' . $this->session->data['token'], true);
        $data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'] . '&type=payment', true);

        if (isset($this->request->post['xendit' . self::XENDIT_CODE . '_status'])) {
            $data['xendit_status'] = $this->request->post['xendit' . self::XENDIT_CODE . '_status'];
        } elseif ($this->config->has('xendit' . self::XENDIT_CODE . '_status')) {
            $data['xendit_status'] = $this->config->get('xendit' . self::XENDIT_CODE . '_status');
        } else {
            $data['xendit_status'] = false;
        }

        $data['xendit_code'] = self::XENDIT_CODE;
        
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_edit'] = $this->language->get('text_edit');
        $data['token'] = $this->session->data['token'];
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('payment/xenditinvoiceva.tpl', $data));
    }

    public function install() {
        if ($this->user->hasPermission('modify', 'extension/extension')) {
            $this->load->model('payment/xendit' . self::XENDIT_CODE);
            $this->model_payment_xenditpermatava->install();
        }
    }

    public function uninstall() {
        if ($this->user->hasPermission('modify', 'extension/extension')) {
            $this->load->model('payment/xendit' . self::XENDIT_CODE);
            $this->model_payment_xenditpermatava->uninstall();
        }
    }

    public function validate() {
        return true;
    }
}