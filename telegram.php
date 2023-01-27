<?php

/* https://api.telegram.org/bot1084295246:AAFKNXWVWkjNkXgWNXkb99_kGwyBcKNVbYo/getUpdates,
где, XXXXXXXXXXXXXXXXXXXXXXX - токен вашего бота, полученный ранее */

$name = $_POST['name'];
$phone = $_POST['phone'];
$page = $_POST['page'];
$form = $_POST['form_subject'];
$connection_method = $_POST['connection_method'];
$utm_campaing = $_POST['utm_campaing'];
$token = "1084295246:AAFKNXWVWkjNkXgWNXkb99_kGwyBcKNVbYo";
$chat_id = "-1001269969681";

$arr = array(
	'Телефон: ' => $phone,
  'Имя: ' => $name,   
  'Страница' => $page,
  'Форма' => $form,
	'Рекламная компания' => $utm_campaing,
	'Способ связи' => $connection_method,
);

$correct_phone = (substr($phone, 0, 1) == "+" || substr($phone, 0, 1) == "7" || substr($phone, 0, 1) == "8");
foreach($arr as $key => $value) {
  $txt .= "<b>".$key."</b> ".$value."%0A";
};

session_start();

if ($_POST['spam_check'] == md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT']) && (strlen($name) > 2 || !isset($name)) && strlen($phone) < 20 && $correct_phone == 1 && (!isset($_SESSION['client_request_is_sent']) || $_SESSION['client_request_is_sent'] < time()-3600)) {

		// ------------------------------------------------------ЗАЯВКИ В БИТРИКС НАЧАЛО-----------------------------------------

	// формируем URL в переменной $queryUrl
	$queryUrl = 'https://b24-rceipv.bitrix24.ru/rest/7109/700tstol1i2x2ki9/crm.lead.add.json';

	// формируем параметры для создания лида в переменной $queryData
	$queryData = http_build_query(array(
		'fields' => array(
			'NAME' => $name,   // сохраняем имя
			"PHONE" => array(array('VALUE' => $phone, 'VALUE_TYPE' => 'WORK')),
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
	
	// ------------------------------------------------------ЗАЯВКИ В БИТРИКС КОНЕЦ-----------------------------------------
	$sendToTelegram = fopen("https://api.telegram.org/bot{$token}/sendMessage?chat_id={$chat_id}&parse_mode=html&text={$txt}","r");

	$_SESSION['client_request_is_sent'] = time();
}

if ($sendToTelegram) {
  header('Location: zayavka-prinyata');
} else {
	header('Content-Type: text/html; charset=utf-8');
  echo "<p>Неправильные данные или слишком большое количество попыток отправить заявку.</p>";
  echo "<a href='" . $_SERVER['HTTP_REFERER'] . "'>Вернуться назад</a>";
}
?>