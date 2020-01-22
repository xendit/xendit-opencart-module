<?php
require_once(DIR_SYSTEM . 'library/xendit.php');

class Controllerpaymentxenditcc extends Controller
{
    private $error = array();
    const EXT_ID_PREFIX = 'xendit_opencart_';
    
    public function index()
    {
        $this->load->model('setting/setting');
        $this->load->language('payment/xenditcc');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('localisation/order_status');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('xenditcc', $this->request->post);
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
            'href' => $this->url->link('payment/xenditcc', 'token=' . $this->session->data['token'], true)
        );

        $data['heading_title'] = $this->language->get('heading_title');

        $data['entry_status'] = $this->language->get('entry_status');

        $data['button_save'] = $this->language->get('button_save');

        $data['action'] = $this->url->link('payment/xenditcc', 'token=' . $this->session->data['token'], true);
        $data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'] . '&type=payment', true);

        if (isset($this->request->post['xenditcc_status'])) {
            $data['xenditcc_status'] = $this->request->post['xenditcc_status'];
            $data['xendit_debug'] = 'request_post';
        } elseif ($this->config->has('xenditcc_status')) {
            $data['xenditcc_status'] = $this->config->get('xenditcc_status');
            $data['xendit_debug'] = 'have config';
        } else {
            $data['xenditcc_status'] = false;
            $data['xendit_debug'] = 'failover';
        }

        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_edit'] = $this->language->get('text_edit');
        $data['token'] = $this->session->data['token'];
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('payment/xenditcc.tpl', $data));
    }

    public function install()
    {
        $this->load->model('payment/xenditcc');
        $this->model_payment_xenditcc->install();
    }

    public function uninstall()
    {
        $this->load->model('payment/xenditcc');
        $this->model_payment_xenditcc->uninstall();
    }

    public function validate()
    {
        return true;
    }

    public function action()
    {
        if ($this->config->get('xenditcc_status')) {
            $this->load->model('payment/xenditcc');

            $this->load->language('payment/xenditcc');

            $xenditcc_order = $this->model_payment_xenditcc->getOrder($this->request->get['order_id']);

            if (!empty($xenditcc_order) && $xenditcc_order["payment_method"] === "Credit Card") {
                $xenditcc_order['total_formatted'] = $this->currency->format($xenditcc_order['total'], $xenditcc_order['currency_code'], false);

                $xenditcc_order['total_refunded'] = $this->model_payment_xenditcc->getTotalRefunded($xenditcc_order['order_id']);
                $xenditcc_order['total_refunded_formatted'] = $this->currency->format($xenditcc_order['total_refunded'], $xenditcc_order['currency_code'], false);

                $xenditcc_order['total_available'] = $xenditcc_order['total'] - $xenditcc_order['total_refunded'];
                $xenditcc_order['total_available_formatted'] = $this->currency->format($xenditcc_order['total_available'], $xenditcc_order['currency_code'], false);

                $xenditcc_order['refund_history'] = $this->model_payment_xenditcc->getRefundHistory($xenditcc_order['order_id']);
                foreach ($xenditcc_order['refund_history'] as $key => $val) {
                    $xenditcc_order['refund_history'][$key]['amount_formatted'] = $this->currency->format($val['amount'], $xenditcc_order['currency_code'], false);
                }

                $data['xenditcc_order'] = $xenditcc_order;

                $data['text_payment_info'] = $this->language->get('text_payment_info');
                $data['text_order_total'] = $this->language->get('text_order_total');
                $data['text_order_available'] = $this->language->get('text_order_available');
                $data['text_total_refunded'] = $this->language->get('text_total_refunded');
                $data['text_refund_history'] = $this->language->get('text_refund_history');
                $data['text_column_amount'] = $this->language->get('text_column_amount');
                $data['text_column_date_added'] = $this->language->get('text_column_date_added');
                $data['text_refund'] = $this->language->get('text_refund');
                $data['btn_refund_xendit'] = $this->language->get('btn_refund_xendit');
                $data['btn_refund_manual'] = $this->language->get('btn_refund_manual');
                $data['text_confirm_refund'] = $this->language->get('text_confirm_refund');

                $data['order_id'] = $this->request->get['order_id'];
                $data['token'] = $this->request->get['token'];
                return $this->load->view('payment/xenditcc_order.tpl', $data);
            }
        }
    }

    public function refundManual()
    {
        $this->load->model('payment/xenditcc');
        $json = array();

        if (isset($this->request->post['order_id']) && !empty($this->request->post['order_id'])) {
            $xenditcc_order = $this->model_payment_xenditcc->getOrder($this->request->post['order_id']);

            $xendit_charge_id = $xenditcc_order['xendit_charge_id'];
            $amount = $xenditcc_order['total'] - $this->model_payment_xenditcc->getTotalRefunded($xenditcc_order['order_id']);
            $environment = $this->config->get('xendit_environment');

            if ($amount > 0) {
                $this->model_payment_xenditcc->addRefund('', $xendit_charge_id, $this->request->post['order_id'], $amount, $environment);
                $this->model_payment_xenditcc->addOrderHistory(
                    true,
                    $this->request->post['order_id'],
                    11,
                    'Refund successful'
                );

                $data['created'] = date('Y-m-d H:i');
                $data['amount'] = $this->currency->format($amount, $xenditcc_order['currency_code'], false);;

                $data['total_available_formatted'] = $this->currency->format(0, $xenditcc_order['currency_code'], false);
                $data['total_refunded_formatted'] = $this->currency->format($xenditcc_order['total'], $xenditcc_order['currency_code'], false);

                $json['msg'] = "success!";
                $json['data'] = $data;
                $json['error'] = false;
            } else {
                $json['msg'] = "You can't refund";
                $json['error'] = true;
            }
        }

        $this->response->setOutput(json_encode($json));
    }

    public function refundXendit()
    {
        $this->load->model('payment/xenditcc');
        $json = array();

        $order_id = $this->request->post['order_id'];
        $amount = $this->request->post['refund_amount'];

        if (isset($order_id) && !empty($order_id) && isset($amount) && !empty($amount) && $amount > 0) {
            $xenditcc_order = $this->model_payment_xenditcc->getOrder($order_id);

            $xendit_charge_id = $xenditcc_order['xendit_charge_id'];
            $environment = $this->config->get('xendit_environment');

            $api_key = $this->get_api_key();
            Xendit::set_secret_key($api_key['secret_key']);
            Xendit::set_public_key($api_key['public_key']);
            
            $store_name = $this->config->get('config_name');
            $request_options = array(
                'store_name' => $store_name
            );
            $request_payload = array(
                'external_id' => self::EXT_ID_PREFIX . $order_id,
                'amount' => $amount
            );
            $request_url = '/payment/xendit/credit-card/charges/' . $xendit_charge_id . '/refund';

            try {
                $response = Xendit::request($request_url, Xendit::METHOD_POST, $request_payload, $request_options);
                if (isset($response['error_code'])) {
                    $json['error'] = $response['message'];
                } else {
                    $this->model_payment_xenditcc->addRefund('', $xendit_charge_id, $order_id, $amount, $environment);

                    $this->model_payment_xenditcc->addOrderHistory(
                        true,
                        $order_id,
                        11,
                        'Refund successful'
                    );

                    $data['created'] = date('Y-m-d H:i');
                    $data['amount'] = $this->currency->format($amount, $xenditcc_order['currency_code'], false);;

                    $data['total_available_formatted'] = $this->currency->format(0, $xenditcc_order['currency_code'], false);
                    $data['total_refunded_formatted'] = $this->currency->format($xenditcc_order['total'], $xenditcc_order['currency_code'], false);

                    $json['msg'] = "success!";
                    $json['data'] = $data;
                    $json['error'] = false;
                }
            } catch (Exception $e) {
                $json['msg'] = "You can't refund";
                $json['error'] = true;
            }
        } else {
            $json['msg'] = "You can't refund";
            $json['error'] = true;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
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
