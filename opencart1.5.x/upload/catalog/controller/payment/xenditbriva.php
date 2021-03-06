<?php

class ControllerPaymentXenditBRIVA extends Controller {
    public function index() {
        $this->load->language('payment/xendit');

        $this->data['environment'] = $this->config->get('payment_xendit_environment');
        $this->data['text_instructions'] = $this->language->get('text_instructions');
        $this->data['text_test_instructions'] = $this->language->get('text_test_instructions');
        $this->data['button_confirm'] = $this->language->get('button_confirm');
        $this->data['invoice_hash'] = 'bri';

        $this->template = 'default/template/payment/xendit.tpl';
        $this->render();
    }
}