<?php

class ControllerPaymentXenditBRIVA extends Controller {
    private $error = array();

    const XENDIT_CODE = 'briva';

    public function index() {
        $this->load->model('setting/setting');
        $this->language->load('payment/xendit' . self::XENDIT_CODE);

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('localisation/order_status');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('xendit' . self::XENDIT_CODE, $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');

            $this->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
        }

        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_payment'),
            'href'      => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('payment/xendit' . self::XENDIT_CODE, 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $this->data['heading_title'] = $this->language->get('heading_title');

        $this->data['entry_status'] = $this->language->get('entry_status');

        $this->data['button_save'] = $this->language->get('button_save');
        $this->data['button_cancel'] = $this->language->get('button_cancel');

        $this->data['text_enabled'] = $this->language->get('text_enabled');
        $this->data['text_disabled'] = $this->language->get('text_disabled');
        $this->data['text_edit'] = $this->language->get('text_edit');

        $this->data['action'] = $this->url->link('payment/xendit' . self::XENDIT_CODE, 'token=' . $this->session->data['token'], 'SSL');
        $this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

        if (isset($this->request->post['xendit' . self::XENDIT_CODE . '_status'])) {
            $this->data['xendit_status'] = $this->request->post['xendit' . self::XENDIT_CODE . '_status'];
        } elseif ($this->config->has('xendit' . self::XENDIT_CODE . '_status')) {
            $this->data['xendit_status'] = $this->config->get('xendit' . self::XENDIT_CODE . '_status');
        } else {
            $this->data['xendit_status'] = false;
        }

        if (isset($this->request->post['xendit' . self::XENDIT_CODE . '_sort_order'])) {
			$this->data['xendit_sort_order'] = $this->request->post['xendit' . self::XENDIT_CODE . '_sort_order'];
		} else {
			$this->data['xendit_sort_order'] = $this->config->get('xendit' . self::XENDIT_CODE . '_sort_order');
		}

        $this->data['xendit_code'] = self::XENDIT_CODE;

        $this->data['token'] = $this->session->data['token'];

        //Bootstrap 3
        $this->document->addStyle('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css');
        $this->document->addStyle('https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
        $this->document->addScript('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js');

        $this->template = 'payment/xenditinvoiceva.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
        $this->response->setOutput($this->render());
    }

    public function install() {
        if ($this->user->hasPermission('modify', 'extension/extension')) {
            $this->load->model('payment/xendit' . self::XENDIT_CODE);
            $this->model_payment_xenditbriva->install();
        }
    }

    public function uninstall() {
        if ($this->user->hasPermission('modify', 'extension/extension')) {
            $this->load->model('payment/xendit' . self::XENDIT_CODE);
            $this->model_payment_xenditbriva->uninstall();
        }
    }

    public function validate() {
        return true;
    }
}