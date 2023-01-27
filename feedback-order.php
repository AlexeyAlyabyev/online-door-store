<?php

define('CRM_HOST', 'b24-rceipv.bitrix24.ru'); // Домен срм системы
define('CRM_PORT', '443'); 
define('CRM_PATH', '/crm/configs/import/lead.php'); 
define('CRM_LOGIN', 'ikulis@inbox.ru');  // логин
define('CRM_PASSWORD', 'alewf3k'); // пароль

// формируем URL в переменной $queryUrl
$queryUrl = 'https://b24-rceipv.bitrix24.ru/rest/1/8hrhjveq1rvsc0rq/crm.deal.update.json';

// формируем параметры для создания лида в переменной $queryData
$queryData = http_build_query(array(
    'id' => $_POST['request_id'],
    'fields' => array(
        'UF_CRM_1636614330307' => $_POST['rating-all'],
        'UF_CRM_1636614370885' => $_POST['rating-manager'],
        'UF_CRM_1636614385437' => $_POST['rating-zamer'],
        'UF_CRM_1636614418227' => $_POST['rating-montaj'],
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
if (array_key_exists('error', $result)) 
    echo "Ошибка при сохранении лида: ".$result['error_description']."<br/>";
