<?php

class ControllerExtensionPaymentRBSCredit extends Controller
{
    private $error = array();

    /**
     * Вывод и сохранение настроек
     */
    public function index()
    {
        $this->load->language('extension/payment/rbs_credit');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');

        // Сохранение настроек через POST запрос
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('payment_rbs_credit', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');

            //todo SET CALLBACK ON the paymentgate
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', 'SSL'));
        }

        // Заголовок страницы
        $data['heading_title'] = $this->language->get('heading_title');

        // Хлебные крошки
        $data['breadcrumbs'] = array();
        array_push($data['breadcrumbs'],
            array(  // Главная
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL')
            ),
            array(  // Оплата
                'text' => $this->language->get('text_payment'),
                'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], 'SSL')
            ),
            array(  // Оплата через {{банк}}
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/payment/rbs_credit', 'user_token=' . $this->session->data['user_token'], 'SSL')
            )
        );

        // Кнопки сохранения и отмены
        $data['action'] = $this->url->link('extension/payment/rbs_credit', 'user_token=' . $this->session->data['user_token'], 'SSL');
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], 'SSL');

        // Кнопки
        $data['rbs_credit_button_save'] = $this->language->get('button_save');
        $data['rbs_credit_button_cancel'] = $this->language->get('button_cancel');

        // Заголовок панели
        $data['text_settings'] = $this->language->get('text_settings');

        // Статус модуля: Включен/Выключен
        $data['entry_status'] = $this->language->get('status');
        $data['status_enabled'] = $this->language->get('status_enabled');
        $data['status_disabled'] = $this->language->get('status_disabled');

        if (isset($this->request->post['payment_rbs_credit_status'])) {
            $data['payment_rbs_credit_status'] = $this->request->post['payment_rbs_credit_status'];
        } else {
            $data['payment_rbs_credit_status'] = $this->config->get('payment_rbs_credit_status');
        }
        // echo   $this->config->get('merchantPassword'); die;
        // $data['payment_rbs_status'] = $data['payment_rbs_status'] = $this->config->get('payment_rbs_status');

        // Логин мерчанта
        $data['entry_merchant_login'] = $this->language->get('rbs_credit_merchant_login');
        $data['payment_rbs_credit_merchant_login'] = $this->config->get('payment_rbs_credit_merchant_login');

        // Пароль мерчанта
        $data['entry_merchant_password'] = $this->language->get('rbs_credit_merchant_password');
        $data['payment_rbs_credit_merchant_password'] = $this->config->get('payment_rbs_credit_merchant_password');

        // Режим работы модуля: Тестовый/Боевой
        $data['entry_mode'] = $this->language->get('rbs_credit_mode');
        $data['rbs_credit_mode_test'] = $this->language->get('rbs_credit_mode_test');
        $data['rbs_credit_mode_prod'] = $this->language->get('rbs_credit_mode_prod');
        $data['payment_rbs_credit_mode'] = $this->config->get('payment_rbs_credit_mode');

        $data['entry_rbs_credit_returnUrl'] = "_returnUrl";
        $data['entry_rbs_credit_failUrl'] = "_failUrl";

        $data['payment_rbs_credit_returnUrl'] = $this->config->get('payment_rbs_credit_returnUrl');
        $data['payment_rbs_credit_failUrl'] = $this->config->get('payment_rbs_credit_failUrl');

//        $this->load->model('localisation/order_status');
//        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        // Тип кредитования
//        $data['entry_type'] = 'Тип кредитования'; $this->language->get('rbs_credit_type');
//        $data['rbs_credit_type_credit'] = 'Кредит'; //$this->language->get('rbs_credit_type_CREDIT');
//        $data['rbs_credit_type_installment'] = 'Кредит без переплаты'; //$this->language->get('rbs_credit_type_INSTALLMENT');
//        $data['rbs_credit_type'] = $this->config->get('rbs_credit_type');

        // Логирование
        $data['entry_logging'] = $this->language->get('rbs_credit_logging');
        $data['rbs_credit_logging_enabled'] = $this->language->get('rbs_credit_logging_enabled');
        $data['rbs_credit_logging_disabled'] = $this->language->get('rbs_credit_logging_disabled');
        $data['payment_rbs_credit_logging'] = $this->config->get('payment_rbs_credit_logging');

        //
        $data['payment_rbs_credit_sort_order'] = $this->config->get('payment_rbs_credit_sort_order');


        // Хедер, футер, левое меню для отрисовки страницы настроек модуля
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        // Рендеринг шаблона
        $this->response->setOutput($this->load->view('extension/payment/rbs_credit', $data));
    }

    /**
     * Валидация данных.
     * В данном случае проверка прав на редактирование настроек платежного модуля.
     * @return bool
     */
    private function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/payment/rbs_credit')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        return !$this->error;
    }
    public function install() {
        $this->load->model('extension/payment/rbs_credit');
        $this->model_extension_payment_rbs_credit->install();
    }

    public function uninstall() {
    }

}