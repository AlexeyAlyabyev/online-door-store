<?php

class ControllerExtensionPaymentRBSCredit extends Controller
{
    /**
     * Инициализация языкового пакета
     * @param $registry
     */
    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->load->language('extension/payment/rbs_credit');
        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/extension/payment/rbs_credit.twig')) {
            $this->have_template = true;
        }
    }

    /**
     * Рендеринг кнопки-ссылки для перехода в метод payment()
     * @return mixed Шаблон кнопки
     */
    public function index()
    {
        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $order_number = (int)$order_info['order_id'];
        $amount = $order_info['total'] * 100;

        $result = $this->_registerOrder();

        $data['amount_string'] = number_format($amount / 100, 2, '.', ' ');;
        $data['payment_link'] = $result['formUrl'];

        $data['action'] = $this->url->link('extension/payment/rbs_credit/payment');
        $data['button_confirm'] = $this->language->get('button_confirm');
        return $this->get_template('extension/payment/rbs_credit', $data);
    }



    public function _registerOrder() {

        $this->initializeRbs();
        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $order_number = (int)$order_info['order_id'];
        $amount = $order_info['total'] * 100;

        $return_url = $this->url->link('extension/payment/rbs_credit_callback/');

        $orderBundle = [];

        // ITEMS
        foreach ($this->cart->getProducts() as $product) {

            $product_taxSum = $this->tax->getTax($product['price'], $product['tax_class_id']);
            $product_price = ( $product['price']  ) * $product['quantity'];
            $product_amount = $product_price * $product['quantity'];

            $product_data = array(
                'positionId' => $product['cart_id'],
                'name' => $product['name'],
                'quantity' => array(
                    'value' => $product['quantity'],
                    //todo fix piece
                    'measure' => "piece"
                ),
                'itemAmount' => $product_amount * 100,
                'itemCode' => $product['product_id'],
                'tax' => array(
                    // todo: some question taxType
                    'taxType' => $this->config->get('rbs_taxType'),
                    'taxSum' => $product_taxSum * 100
                ),
                'itemPrice' => $product_price * 100,
            );

            // FFD 1.05 added
            if ($this->rbsCreditLibrary->getFFDVersion() == 'v105') {

                $attributes = array();
                $attributes[] = array(
                    "name" => "paymentMethod",
                    "value" => $this->rbsCreditLibrary->getPaymentMethodType()
                );
                $attributes[] = array(
                    "name" => "paymentObject",
                    "value" => $this->rbsCreditLibrary->getPaymentObjectType()
                );

                $product_data['itemAttributes']['attributes'] = $attributes;
            }

            $orderBundle['cartItems']['items'][] = $product_data;
        }

        // DELIVERY
        if (isset($this->session->data['shipping_method']['cost']) && $this->session->data['shipping_method']['cost'] > 0) {

            $delivery['positionId'] = 'delivery';
            $delivery['name'] = $this->session->data['shipping_method']['title'];
            $delivery['itemAmount'] = $this->session->data['shipping_method']['cost'] * 100;
            $delivery['quantity']['value'] = 1;
            //todo fix piece
            $delivery['quantity']['measure'] = 'piece';
            $delivery['itemCode'] = $this->session->data['shipping_method']['code'];
            $delivery['tax']['taxType'] = $this->config->get('rbs_taxType');
            $delivery['tax']['taxSum'] = 0;
            $delivery['itemPrice'] = $this->session->data['shipping_method']['cost'] * 100;

            // FFD 1.05 added
            if ($this->rbsCreditLibrary->getFFDVersion() == 'v105') {

                $attributes = array();
                $attributes[] = array(
                    "name" => "paymentMethod",
                    "value" => 4
                );
                $attributes[] = array(
                    "name" => "paymentObject",
                    "value" => 4
                );

                $delivery['itemAttributes']['attributes'] = $attributes;
            }

            $orderBundle['cartItems']['items'][] = $delivery;
        }


        $customer_phone = preg_replace("/(\W*)/", "", $order_info['telephone']);

        $orderBundle['customerDetails'] = array(
            'email' => $order_info['email'],
            'phone' => $customer_phone
        );
        $orderBundle['installments'] = array(
            'productType' => 'CREDIT',
            'productID' => 10
        );

        return $this->rbsCreditLibrary->register_order($order_number, $amount, $return_url, $customer_phone, $orderBundle);

    }


    /**
     * Отрисовка шаблона
     * @param $template     Шаблон вместе с корневой папкой
     * @param $data         Данные
     * @return mixed        Отрисованный шаблон
     */
    private function get_template($template, $data)
    {
        return $this->load->view($template, $data);
    }

    /**
     * Инициализация библиотеки rbsCredit
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

        $this->rbsCreditLibrary->returnUrl = $this->config->get('payment_rbs_credit_returnUrl');
        $this->rbsCreditLibrary->failUrl = $this->config->get('payment_rbs_credit_failUrl');
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

    /**
     * Колбек для возвращения покупателя из ПШ в магазин.
     */
//    public function callback()
//    {
//        if (isset($this->request->get['orderId'])) {
//            $order_id = $this->request->get['orderId'];
//        } else {
//            die('Illegal Access');
//        }
//
//        $this->load->model('checkout/order');
//        $order_number = $this->session->data['order_id'];
//        $order_info = $this->model_checkout_order->getOrder($order_number);
//
//        if ($order_info) {
//            $this->initializeRbs();
//
//            $response = $this->rbsCreditLibrary->get_order_status($order_id);
//            if (($response['errorCode'] == 0) && (($response['orderStatus'] == 1) || ($response['orderStatus'] == 2))) {
//
//                // set order status
////                $this->model_checkout_order->addOrderHistory($order_number, $this->config->get('payment_rbs_order_status_id'));
//                $this->model_checkout_order->addOrderHistory($order_number, 1);
//
//                $this->response->redirect($this->url->link('checkout/success', '', true));
//            } else {
//                $this->response->redirect($this->url->link('checkout/failure', '', true));
//            }
//        }
//    }

}