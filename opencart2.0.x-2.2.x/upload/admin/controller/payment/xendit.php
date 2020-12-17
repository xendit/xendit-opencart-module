<?php

class Controllerpaymentxendit extends Controller
{
    private $error = array();
    private static $placeholder_sensitive_information = '********';

    public function index()
    {
        $this->load->model('setting/setting');
        $this->load->language('payment/xendit');

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
            'href' => $this->url->link('payment/xendit', 'token=' . $this->session->data['token'], true)
        );

        $data['heading_title'] = $this->language->get('heading_title');
        
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_edit'] = $this->language->get('text_edit');
        
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

        $data['action'] = $this->url->link('payment/xendit', 'token=' . $this->session->data['token'], true);
        $data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'] . '&type=payment', true);

        if (isset($this->request->post['xendit_status'])) {
            $data['xendit_status'] = $this->request->post['xendit_status'];
        } elseif ($this->config->has('xendit_status')) {
            $data['xendit_status'] = $this->config->get('xendit_status');
        } else {
            $data['xendit_status'] = false;
        }

        if (isset($this->request->post['xendit_environment'])) {
            $data['xendit_environment'] = $this->request->post['xendit_environment'];
        } elseif ($this->config->has('xendit_environment')) {
            $data['xendit_environment'] = $this->config->get('xendit_environment');
        } else {
            $data['xendit_environment'] = 'test';
        }

        if (isset($this->request->post['xendit_test_public_key'])) {
            $data['xendit_test_public_key'] = $this->request->post['xendit_test_public_key'];
        } elseif ($this->config->has('xendit_test_public_key')) {
            $data['xendit_test_public_key'] = $this->config->get('xendit_test_public_key') === '' ?
                $this->config->get('xendit_test_public_key') :
                self::$placeholder_sensitive_information;
        } else {
            $data['xendit_test_public_key'] = '';
        }

        if (isset($this->request->post['xendit_live_public_key'])) {
            $data['xendit_live_public_key'] = $this->request->post['xendit_live_public_key'];
        } elseif ($this->config->has('xendit_live_public_key')) {
            $data['xendit_live_public_key'] = $this->config->get('xendit_live_public_key') === '' ?
                $this->config->get('xendit_live_public_key') :
                self::$placeholder_sensitive_information;
        } else {
            $data['xendit_live_public_key'] = '';
        }

        if (isset($this->request->post['xendit_test_secret_key'])) {
            $data['xendit_test_secret_key'] = $this->request->post['xendit_test_secret_key'];
        } elseif ($this->config->has('xendit_test_secret_key')) {
            $data['xendit_test_secret_key'] = $this->config->get('xendit_test_secret_key') === '' ?
                $this->config->get('xendit_test_secret_key') :
                self::$placeholder_sensitive_information;
        } else {
            $data['xendit_test_secret_key'] = '';
        }

        if (isset($this->request->post['xendit_live_secret_key'])) {
            $data['xendit_live_secret_key'] = $this->request->post['xendit_live_secret_key'];
        } elseif ($this->config->has('xendit_live_secret_key')) {
            $data['xendit_live_secret_key'] = $this->config->get('xendit_live_secret_key') === '' ?
                $this->config->get('xendit_live_secret_key') :
                self::$placeholder_sensitive_information;
        } else {
            $data['xendit_live_secret_key'] = '';
        }

        $data['token'] = $this->session->data['token'];
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('payment/xendit.tpl', $data));
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
