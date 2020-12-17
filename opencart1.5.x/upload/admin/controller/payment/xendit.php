<?php

class ControllerPaymentXendit extends Controller
{
    private $error = array();
    private static $placeholder_sensitive_information = '********';

    public function index()
    {
        $this->load->model('setting/setting');
        $this->language->load('payment/xendit');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('localisation/order_status');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            foreach ($this->request->post as $key => &$value) {
                if ($value === self::$placeholder_sensitive_information) {
                    /**
                     * if value is placeholder, replace with current config value
                     * 
                     * To prevent placeholder value stored as config value
                    */
                    $value = $this->config->get($key);
                }
            }
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
            $this->data['xendit_test_public_key'] = $this->config->get('payment_xendit_test_public_key') === '' ? 
                $this->config->get('payment_xendit_test_public_key') :
                self::$placeholder_sensitive_information;
        } else {
            $this->data['xendit_test_public_key'] = '';
        }

        if (isset($this->request->post['payment_xendit_live_public_key'])) {
            $this->data['xendit_live_public_key'] = $this->request->post['payment_xendit_live_public_key'];
        } elseif ($this->config->has('payment_xendit_live_public_key')) {
            $this->data['xendit_live_public_key'] = $this->config->get('payment_xendit_live_public_key') === '' ? 
                $this->config->get('payment_xendit_live_public_key') :
                self::$placeholder_sensitive_information;
        } else {
            $this->data['xendit_live_public_key'] = '';
        }

        if (isset($this->request->post['payment_xendit_test_secret_key'])) {
            $this->data['xendit_test_secret_key'] = $this->request->post['payment_xendit_test_secret_key'];
        } elseif ($this->config->has('payment_xendit_test_secret_key')) {
            $this->data['xendit_test_secret_key'] = $this->config->get('payment_xendit_test_secret_key') === '' ? 
                $this->config->get('payment_xendit_test_secret_key') :
                self::$placeholder_sensitive_information;
        } else {
            $this->data['xendit_test_secret_key'] = '';
        }

        if (isset($this->request->post['payment_xendit_live_secret_key'])) {
            $this->data['xendit_live_secret_key'] = $this->request->post['payment_xendit_live_secret_key'];
        } elseif ($this->config->has('payment_xendit_live_secret_key')) {
            $this->data['xendit_live_secret_key'] = $this->config->get('payment_xendit_live_secret_key') === '' ? 
                $this->config->get('payment_xendit_live_secret_key') :
                self::$placeholder_sensitive_information;
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
    }

    public function install()
    {
        $this->load->model('payment/xendit');
        $this->model_payment_xendit->install();
    }

    public function uninstall()
    {
        $this->load->model('payment/xendit');
        $this->model_payment_xendit->uninstall();
    }

    public function validate()
    {
        return true;
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
