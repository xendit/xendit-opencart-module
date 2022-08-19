<?php

class ControllerExtensionPaymentXenditBRIVA extends Controller {
    private $error = array();

    const XENDIT_CODE = 'briva';

    public function index() {
        $this->load->model('setting/setting');
        $this->load->language('extension/payment/xendit' . self::XENDIT_CODE);

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('localisation/order_status');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('payment_xendit' . self::XENDIT_CODE, $this->request->post);
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
            'href' => $this->url->link('extension/payment/xendit' . self::XENDIT_CODE, 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['heading_title'] = $this->language->get('heading_title');

        $data['entry_status'] = $this->language->get('entry_status');

        $data['button_save'] = $this->language->get('button_save');

        $data['action'] = $this->url->link('extension/payment/xendit' . self::XENDIT_CODE, 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

        if (isset($this->request->post['payment_xendit' . self::XENDIT_CODE . '_status'])) {
            $data['xendit_status'] = $this->request->post['payment_xendit' . self::XENDIT_CODE . '_status'];
        } elseif ($this->config->has('payment_xendit' . self::XENDIT_CODE . '_status')) {
            $data['xendit_status'] = $this->config->get('payment_xendit' . self::XENDIT_CODE . '_status');
        } else {
            $data['xendit_status'] = false;
        }

        $data['xendit_code'] = self::XENDIT_CODE;

        $data['token'] = $this->session->data['user_token'];
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/xenditinvoiceva', $data));
    }

    public function install() {
        if ($this->user->hasPermission('modify', 'extension/extension')) {
            $this->load->model('extension/payment/xendit' . self::XENDIT_CODE);
            $this->model_extension_payment_xenditbriva->install();
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
