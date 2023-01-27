<?php

class ControllerExtensionPaymentRbs extends Controller
{
    /**
     * Инициализация языкового пакета
     * @param $registry
     */
    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->load->language('extension/payment/rbs');
        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/extension/payment/rbs.tpl')) {
            $this->have_template = true;
        }
    }

    /**
     * Рендеринг кнопки-ссылки для перехода в метод payment()
     * @return mixed Шаблон кнопки
     */
    public function index()
    {
        $data['action'] = $this->url->link('extension/payment/rbs/payment');
        $data['rbs_button_confirm'] = $this->language->get('rbs_button_confirm');
        return $this->get_template('extension/payment/rbs', $data);
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
     * Регистрация заказа.
     * Переадресация покупателя при успешной регистрации.
     * Вывод ошибки при неуспешной регистрации.
     */
    public function payment()
    {

        // for config settings
        $this->initializeRbs();

        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $order_number = (int)$order_info['order_id'];
        $amount = round($order_info['total'], 2) * 100;

        $return_url = $this->url->link('extension/payment/rbs/callback');


        // here we will collect data for orderBundle
        $orderBundle = [];

        $orderBundle['customerDetails'] = array(
            'email' => $order_info['email'],
            'phone' => preg_match('/[7]\d{9}/', $order_info['telephone']) ? $order_info['telephone'] : ''
        );

        // ITEMS
        foreach ($this->cart->getProducts() as $product) {

            $product_taxSum = $this->tax->getTax($product['price'], $product['tax_class_id']);
            $product_amount = ($product['price'] + $product_taxSum) * $product['quantity'];

            // DEFINE TAX_TYPE
            $item_rate = $product_taxSum / $product['price'] * 100;
            switch ($item_rate) {
                case 20:
                    $tax_type = 6;
                    break;
                case 18:
                    $tax_type = 3;
                    break;
                case 10:
                    $tax_type = 2;
                    break;
                case 0:
                    $tax_type = 1;
                    break;
                default:
                    $tax_type = $this->config->get('rbs_taxType');
            }

            $product_data = array(
                'positionId' => $product['cart_id'],
                'name' => $product['name'],
                'quantity' => array(
                    'value' => $product['quantity'],
                    'measure' => $this->rbs->getDefaultMeasurement(),
                ),
                'itemAmount' => $product_amount * 100,
                'itemCode' => $product['product_id'] . "_" . $product['cart_id'], //fix by PLUG-1740, PLUG-2620
                'tax' => array(
                    // todo: some question taxType
                    'taxType' => $tax_type,
                    'taxSum' => $product_taxSum * 100
                ),
                'itemPrice' => ($product['price'] + $product_taxSum) * 100,
            );

            // FFD 1.05 added
            if ($this->rbs->getFFDVersion() == 'v105') {

                $attributes = array();
                $attributes[] = array(
                    "name" => "paymentMethod",
                    "value" => $this->rbs->getPaymentMethodType()
                );
                $attributes[] = array(
                    "name" => "paymentObject",
                    "value" => $this->rbs->getPaymentObjectType()
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
            $delivery['quantity']['measure'] = 'шт';
            $delivery['itemCode'] = $this->session->data['shipping_method']['code'];
            $delivery['tax']['taxType'] = $this->config->get('payment_rbs_taxType');
            $delivery['tax']['taxSum'] = 0;
            $delivery['itemPrice'] = $this->session->data['shipping_method']['cost'] * 100;

            // FFD 1.05 added
            if ($this->rbs->getFFDVersion() == 'v105') {

                $attributes = array();
                $attributes[] = array(
                    "name" => "paymentMethod",
                    "value" => $this->rbs->getPaymentMethodType(true)
                );
                $attributes[] = array(
                    "name" => "paymentObject",
                    "value" => 4
                );

                $delivery['itemAttributes']['attributes'] = $attributes;
            }

            $orderBundle['cartItems']['items'][] = $delivery;
        }

        // DISCOUNT CALCULATE
        $discount = $this->rbs->discountHelper->discoverDiscount($amount,$orderBundle['cartItems']['items']);
        if($discount > 0) { 
            $this->rbs->discountHelper->setOrderDiscount($discount);
            $recalculatedPositions = $this->rbs->discountHelper->normalizeItems($orderBundle['cartItems']['items']);
            $recalculatedAmount = $this->rbs->discountHelper->getResultAmount();
            $orderBundle['cartItems']['items'] = $recalculatedPositions;
        }

        $currency_code = $this->rbs->currency_code2num[$order_info['currency_code']];

        $response = $this->rbs->register_order($order_number, $amount, $return_url, $currency_code, $orderBundle);

        if (isset($response['errorCode'])) {
            $this->document->setTitle($this->language->get('error_title'));

            $data['header'] = $this->load->controller('common/header');
            $data['column_left'] = $this->load->controller('common/column_left');
            $data['column_right'] = $this->load->controller('common/column_right');
            $data['content_top'] = $this->load->controller('common/content_top');
            $data['button_continue'] = $this->language->get('error_continue');

            $data['heading_title'] = $this->language->get('error_title') . ' #' . $response['errorCode'];
            $data['text_error'] = $response['errorMessage'];
            $data['continue'] = $this->url->link('checkout/cart');

            $data['content_bottom'] = $this->load->controller('common/content_bottom');
            $data['footer'] = $this->load->controller('common/footer');

            $this->response->setOutput($this->get_template('error/rbs', $data));
        } else {
            $this->response->redirect($response['formUrl']);
        }
    }

    /**
     * Инициализация библиотеки RBS
     */
    private function initializeRbs()
    {
        $this->library('rbs');
        $this->rbs = new RBS();
        $this->rbs->login = $this->config->get('payment_rbs_merchantLogin');
        $this->rbs->password = $this->config->get('payment_rbs_merchantPassword');
        $this->rbs->stage = $this->config->get('payment_rbs_stage');
        $this->rbs->mode = $this->config->get('payment_rbs_mode');
        $this->rbs->logging = $this->config->get('payment_rbs_logging');
        $this->rbs->currency = $this->config->get('payment_rbs_currency');
        $this->rbs->taxSystem = $this->config->get('payment_rbs_taxSystem');
        $this->rbs->taxType = $this->config->get('payment_rbs_taxSystem');
        $this->rbs->ofd_status = $this->config->get('payment_rbs_ofd_status');

        $this->rbs->ffd_version = $this->config->get('payment_rbs_ffdVersion');
        $this->rbs->paymentMethodType = $this->config->get('payment_rbs_paymentMethodType');
        $this->rbs->paymentObjectType = $this->config->get('payment_rbs_paymentObjectType');
        $this->rbs->paymentMethodTypeDelivery = $this->config->get('payment_rbs_paymentMethodTypeDelivery');

        $c_locale = substr($this->language->get('code'), 0, 2);
        $this->rbs->language = ($c_locale == "ru" || $c_locale == "en") ? $c_locale : "ru";
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
    public function callback()
    {
        if (isset($this->request->get['orderId'])) {
            $order_id = $this->request->get['orderId'];
        } else {
            die('Illegal Access');
        }



        $this->load->model('checkout/order');
        $order_number = $this->session->data['order_id'];
        $order_info = $this->model_checkout_order->getOrder($order_number);

				// ------------------------------------------------------Статус оплаты В ЛИДАХ БИТРИКС НАЧАЛО-----------------------------------------

				// формируем URL в переменной $queryUrl
				$queryUrl = 'https://b24-rceipv.bitrix24.ru/rest/1/8zxb3x5g2mgo4i7n/crm.lead.update.json';

				// формируем параметры для создания лида в переменной $queryData
				$queryData = http_build_query(array(
					'ID' => $order_info['fax'],
					'fields' => array(
						"STATUS_DESCRIPTION" => "ЗАКАЗ ОПЛАЧЕН ЧЕРЕЗ КОРЗИНУ",
					),
					'params' => array("REGISTER_SONET_EVENT" => "Y")
				));

				// обращаемся к Битрикс24 при помощи функции curl_exec
				$curl = curl_init();
				curl_setopt_array($curl, array(
					CURLOPT_SSL_VERIFYPEER => 0,
					CURLOPT_POST => 1,
					CURLOPT_HEADER => 0,
					CURLOPT_RETURNTRANSFER => 1,
					CURLOPT_URL => $queryUrl,
					CURLOPT_POSTFIELDS => $queryData,
				));
				$result = curl_exec($curl);
				curl_close($curl);
				$result = json_decode($result, 1);
				if (array_key_exists('error', $result)) echo "Ошибка при сохранении лида: ".$result['error_description']."<br/>";
				
				// ------------------------------------------------------Статус оплаты В ЛИДАХ БИТРИКС КОНЕЦ-----------------------------------------

        if ($order_info) {
            $this->initializeRbs();

            $response = $this->rbs->get_order_status($order_id);
            if (($response['errorCode'] == 0) && (($response['orderStatus'] == 1) || ($response['orderStatus'] == 2))) {

                // set order status
                $this->model_checkout_order->addOrderHistory($order_number, $this->config->get('payment_rbs_order_status_id'));

                $this->response->redirect($this->url->link('checkout/success', '', true));
            } else {
                $this->response->redirect($this->url->link('checkout/failure', '', true));
            }
        }
    }

}