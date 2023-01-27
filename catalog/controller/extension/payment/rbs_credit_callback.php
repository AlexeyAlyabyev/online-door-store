<?php

class ControllerExtensionPaymentRBSCreditCallback extends Controller
{
    public function __construct($registry)
    {
        parent::__construct($registry);
    }

    public function index()
    {
        $this->library('log');
        $logger = new Log('rbs_credit.log');

        //RBS CREDIT[CALLBACK]
        $logger->write("RBS [MODULE_CALLBACK]: ". print_r($this->request->get, true) . "\n\n");

        if (isset($this->request->get['mdOrder'])) {
            $order_id = $this->request->get['mdOrder'];
        } else {
            die('Illegal Access');
        }

        $orderNumber = $this->request->get['orderNumber'];

        $this->load->model('checkout/order');

        $order_number = strstr($orderNumber, '_', true);
        $order_info = $this->model_checkout_order->getOrder($order_number);

//        $logger->write("RBS [MODULE_CALLBACKddd]: ". print_r($order_info, true) . "\n\n");

        if ($order_info) {
            $this->initializeRbs();

            $response = $this->rbsCreditLibrary->get_order_status($order_id);

            $this->db->query("DELETE FROM " . DB_PREFIX . "rbs_orders WHERE oc_order_number = '" . $this->db->escape($order_number) . "'");
            $this->db->query("INSERT INTO " . DB_PREFIX . "rbs_orders SET rbs_bank_amount = '" . $this->db->escape($response['amount']) . "', rbs_order_state = '" . $this->db->escape($response['orderStatus']) . "', oc_order_number = '" . $this->db->escape($order_number) . "'");

            //RBS GET ORDER STATE
            $logger->write("RBS [GET_ORDER_STATUS]: ". print_r($response,true) . "\n\n");

            if (($response['errorCode'] == 0) && (($response['orderStatus'] == 1) || ($response['orderStatus'] == 2))) {

                $this->model_checkout_order->addOrderHistory($order_number, 1); // Pending

                //needs for returnURL
                $this->response->redirect($this->url->link('checkout/success', '', true));
            } else {

                $this->model_checkout_order->addOrderHistory($order_number, 7); // Canceled

                //needs for returnURL
                $this->response->redirect($this->url->link('checkout/failure', '', true));
            }
        }
    }


    /**
     * Инициализация библиотеки rbsCreditLibrary
     */
    private function initializeRbs()
    {
        $this->library('rbsCreditLibrary');
        $this->rbsCreditLibrary = new rbsCreditLibrary();
        $this->rbsCreditLibrary->login = $this->config->get('payment_rbs_credit_merchant_login');
        $this->rbsCreditLibrary->password = $this->config->get('payment_rbs_credit_merchant_password');
        $this->rbsCreditLibrary->mode = $this->config->get('payment_rbs_credit_mode');
        $this->rbsCreditLibrary->currency = 643; //$this->config->get('rbs_currency');
        $this->rbsCreditLibrary->taxSystem = 0; //$this->config->get('rbs_taxSystem');
        $this->rbsCreditLibrary->language = $this->language->get('code');
        $this->rbsCreditLibrary->logging = $this->config->get('payment_rbs_credit_logging');
    }

    /**
     * В версии 2.1 нет метода Loader::library()
     * Своя реализация
     * @param $library
     */
    private function library($library)
    {
        $file = DIR_SYSTEM . 'library/' . str_replace('../', '', (string)$library) . '.php';

        if (file_exists($file)) {
            include_once($file);
        } else {
            trigger_error('Error: Could not load library ' . $file . '!');
            exit();
        }
    }
}