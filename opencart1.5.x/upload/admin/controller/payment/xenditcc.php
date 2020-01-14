<?php

class ControllerPaymentXenditCC extends Controller {
    private $error = array();

    public function index() {
        $this->load->model('setting/setting');
        $this->language->load('payment/xenditcc');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('localisation/order_status');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('xenditcc', $this->request->post);
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
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('payment/xenditcc', 'token=' . $this->session->data['token'], 'SSL')
        );

        $this->data['heading_title'] = $this->language->get('heading_title');

        $this->data['entry_status'] = $this->language->get('entry_status');

        $this->data['text_enabled'] = $this->language->get('text_enabled');
        $this->data['text_disabled'] = $this->language->get('text_disabled');
        $this->data['text_edit'] = $this->language->get('text_edit');

        $this->data['button_save'] = $this->language->get('button_save');
        $this->data['button_cancel'] = $this->language->get('button_cancel');

        $this->data['action'] = $this->url->link('payment/xenditcc', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

        if (isset($this->request->post['xenditcc_status'])) {
            $this->data['xenditcc_status'] = $this->request->post['xenditcc_status'];
            $this->data['xendit_debug'] = 'request_post';
        } elseif ($this->config->has('xenditcc_status')) {
            $this->data['xenditcc_status'] = $this->config->get('xenditcc_status');
            $this->data['xendit_debug'] = 'have config';
        } else {
            $this->data['xenditcc_status'] = false;
            $this->data['xendit_debug'] = 'failover';
        }

        $this->data['token'] = $this->session->data['token'];
        
        //Bootstrap 3
        $this->document->addStyle('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css');
        $this->document->addStyle('https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
        $this->document->addScript('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js');

        $this->template = 'payment/xenditcc.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
        $this->response->setOutput($this->render());
    }

    public function install() {
        if ($this->user->hasPermission('modify', 'extension/extension')) {
            $this->load->model('payment/xenditcc');
            $this->model_payment_xenditcc->install();
        }
    }

    public function uninstall() {
        if ($this->user->hasPermission('modify', 'extension/extension')) {
            $this->load->model('payment/xenditcc');
            $this->model_payment_xenditcc->uninstall();
        }
    }

    public function validate() {
        return true;
    }
}