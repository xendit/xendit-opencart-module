<?php

class ControllerExtensionPaymentXenditCC extends Controller {
    public function index() {
        $this->load->language('extension/payment/xenditcc');

        $data['environment'] = $this->config->get('payment_xendit_environment');
        $data['text_instructions'] = $this->language->get('text_instructions');
        $data['text_test_instructions'] = $this->language->get('text_test_instructions');
        $data['invoice_hash'] = 'credit_card';
        $data['button_confirm'] = $this->language->get('button_confirm');
		$data['text_loading'] = $this->language->get('text_loading');

        return $this->load->view('extension/payment/xendit', $data);
    }
}