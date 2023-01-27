<?php

/**
 * Интеграция платежного шлюза RBS с OpenCart
 */
class RBS
{
    public $currency_code2num = array(
        'USD' => '840',
        'UAH' => '980',
        'RUB' => '643',
        'RON' => '946',
        'KZT' => '398',
        'KGS' => '417',
        'JPY' => '392',
        'GBR' => '826',
        'EUR' => '978',
        'CNY' => '156',
        'BYR' => '974',
        'BYN' => '933'
    );

    /** @var string $test       Адрес тестового шлюза */
    private $test_url = 'https://3dsec.sberbank.ru/payment/rest/';

    /** @var string $prod_url   Адрес боевого шлюза*/
    private $prod_url = 'https://securepayments.sberbank.ru/payment/rest/';

    /** @var string $language   Версия страницы оплаты*/
    private $language = 'ru';

    private $defaultMeasurement = "шт";

    /** @var string $version    Версия плагина*/
    private $version = '2.7.2';

    /** @var string $login      Логин продавца*/
    private $login;

    /** @var string $password   Пароль продавца */
    private $password;

    /** @var string $mode       Режим работы модуля (test/prod) */
    private $mode;

    /** @var string $stage      Стадийность платежа (one/two) */
    private $stage;

    /** @var boolean $logging   Логгирование (1/0) */
    private $logging;

    /** @var string $currency   Числовой код валюты в ISO 4217 */
    private $currency;

    private $ofd_status;
    private $ffd_version;
    private $paymentMethodType;
    private $paymentObjectType;
    private $paymentMethodTypeDelivery;

    /** @var integer $taxSystem  Код системы налогообложения */
    public $taxSystem;
    public $taxType;

    public $discountHelper;

    public function __construct()
    {
        $this->library('rbs_discount');
        $this->discountHelper = new rbsDiscount();
    }

    /**
     * @return mixed
     */
    public function getFFDVersion()
    {
        return $this->ffd_version;
    }

    /**
     * @param $delivery
     * @return mixed
     */
    public function getPaymentMethodType($delivery = false)
    {
        if ($delivery) {
            return $this->paymentMethodTypeDelivery;
        }

        return $this->paymentMethodType;
    }

    /**
     * @return mixed
     */
    public function getPaymentObjectType()
    {
        return $this->paymentObjectType;
    }

    /**
     * @return string
     */
    public function getDefaultMeasurement()
    {
        return $this->defaultMeasurement;
    }


    /**
     * Магический метод, который заполняет инстанс
     *
     * @param $property
     * @param $value
     * @return $this
     */
    public function __set($property, $value)
    {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        }
        return $this;
    }

    /**
     * Регистрация заказа в ПШ
     *
     * @param string $order_number Номер заказа в магазине
     * @param integer $amount Сумма заказа
     * @param integer $currency_code
     * @param string $return_url Страница в магазине, на которую необходимо вернуть пользователя
     * @param null $orderBundle
     * @return mixed[] Ответ ПШ
     */
    public function register_order($order_number, $amount, $return_url, $currency_code = 643, $orderBundle = null)
    {

        $jsonParams = array(
            'CMS:' => 'opencart 3.x',
            'Module-Version: ' => $this->version
        );

        if (!empty($orderBundle)) {
            array_push($jsonParams, $orderBundle['customerDetails']);
        }

        $data = array(
            'orderNumber' => $order_number . "_" . time(),
            'amount' => $amount,
            'returnUrl' => $return_url,
            'jsonParams' => json_encode($jsonParams)
        );

        // fix only PLUG-2251
        //ini_set('serialize_precision', 14);
        //ini_set('precision', 14);

        if ($this->currency != 0) {
            $data['currency'] = $this->currency;
        } else {
            $data['currency'] = $currency_code;
        }

        if ($this->ofd_status && !empty($orderBundle)) {
            $data['taxSystem'] = $this->taxSystem;

            $data['orderBundle']['orderCreationDate'] = date('c');
            $data['orderBundle'] = json_encode($orderBundle);
        }


        return $this->gateway($this->stage == 'two' ? 'registerPreAuth.do' : 'register.do', $data);
    }

    /**
     * Формирование запроса в платежный шлюз и парсинг JSON-ответа
     *
     * @param string $method Метод запроса в ПШ
     * @param mixed[] $data Данные в запросе
     * @return mixed[]
     */
    private function gateway($method, $data) {

        // Добавления логина и пароля продавца к каждому запросу
        $data['userName'] = $this->login;
        $data['password'] = $this->password;
        $data['language'] = $this->language;

        // Выбор адреса ПШ в зависимости от выбранного режима
        if ($this->mode == 'test') {
            $url = $this->test_url;
        } else {
            $url = $this->prod_url;
        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_VERBOSE => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
//            CURLOPT_TIMEOUT => 60,
            CURLOPT_URL => $url.$method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data, '', '&'),
            CURLOPT_HTTPHEADER => array('CMS: OpenCart 3.x', 'Module-Version: ' . $this->version),
//            CURLOPT_SSLVERSION => 6,
//            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            CURLOPT_ENCODING, "gzip",
            CURLOPT_ENCODING, '',
        ));

        $response = curl_exec($curl);
        if ($this->logging) {
            $this->logger($url, $method, $data, $response);
        }
        $response = json_decode($response, true);
        curl_close($curl);

        return $response;
    }

    /**
     * Логирование запроса и ответа от ПШ
     *
     * @param string $url
     * @param string $method
     * @param mixed[] $request
     * @param mixed[] $response
     * @return integer
     */
    private function logger($url, $method, $request, $response) {
        $this->library('log');
        $file_name = date("y-m-d") . "_rbspayment.log";
        $logger = new Log($file_name);
        $logger->write("RBS PAYMENT: ".$url.$method."\nREQUEST: ".json_encode($request). "\nRESPONSE: ".$response."\n\n");
    }


    /**
     * Статус заказа в ПШ
     *
     * @param string $orderId Идентификатор заказа в ПШ
     * @return mixed[] Ответ ПШ
     */
    public function get_order_status($orderId) {
        return $this->gateway('getOrderStatusExtended.do', array('orderId' => $orderId));
    }

    /**
     * В версии 2.1 нет метода Loader::library()
     * Своя реализация
     * @param $library
     */
    private function library($library) {
        $file = DIR_SYSTEM . 'library/' . str_replace('../', '', (string)$library) . '.php';

        if (file_exists($file)) {
            include_once($file);
        } else {
            trigger_error('Error: Could not load library ' . $file . '!');
            exit();
        }
    }
}