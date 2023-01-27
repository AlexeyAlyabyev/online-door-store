<?php

header('Content-Type: text/html; charset=utf-8');

$token = "1084295246:AAFKNXWVWkjNkXgWNXkb99_kGwyBcKNVbYo"; // Токен подключения к Api telegram
$chat_id = "-1001269969681"; // Идентификатор чата

$mailbox = '{imap.yandex.ru:993/imap/ssl}INBOX'; // Ссылка на imap Яндекса
if (!$inbox = imap_open($mailbox, 'info@100-dverei.ru', 'rkmeacotsgvdqeay')) { // Попытка подключения к imap
    throw new Exception(imap_last_error());
}

foreach(imap_search($inbox,'UNSEEN FROM noreply@turbo.yandex.ru') as $msg_number) { // Обработка новых писем с турбо-страниц

	$text = imap_body($inbox, $msg_number);  
	$text = substr($text, strpos($text, "Content-Transfer-Encoding: base64") + strlen("Content-Transfer-Encoding: base64"), strlen($text) - (strpos($text, "Content-Transfer-Encoding: base64") + strlen("Content-Transfer-Encoding: base64")));
	$text = substr($text, 0, -(strlen($text) - strpos($text, "--==============="))); // Получаем тело письма и удаляем его заголовки, которые не относятся к содержанию

	$msg = base64_decode($text); // декодируем содержимое

	$msg = substr($msg, strpos($msg, "Телеф"), strpos($msg, "Коммент") - strpos($msg, "Телеф"));  // Обрезаем тело письмо, чтобы оно содержало толшько данные о телефоне и имени

	$msg = preg_replace('/[\x00-\x1F\x7F]/u', '', $msg);  // Удаляем спецсимволы из пиьма
	$msg = str_replace(array("<p>"), "", $msg);
	$msg = str_replace(array("</p>"), "%0A", $msg);
	$msg = str_replace(array("<br />"), ": ", $msg);  // Заменяем неподдерживаемы телегой теги на символы переноса и :

	$phone = substr($msg, strpos($msg, " ") + 1, strpos($msg, "%0A") - strpos($msg, " ") - 1);
	$name = substr($msg, strpos($msg, "Имя: ") + strlen("Имя: "), strlen($msg) - strpos($msg, "Имя: ") - (strlen($msg) - strpos($msg, "%")));
	echo $phone;
	echo $name;

	$msg = "Заявка с турбо-страницы%0A".$msg;
	
	// ---------------------------- BITRIX24 ---------------------------------------------------

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

// ---------------------------- BITRIX24 ---------------------------------------------------

	$sendToTelegram = fopen("https://api.telegram.org/bot{$token}/sendMessage?chat_id={$chat_id}&parse_mode=html&text={$msg}","r");

	if ($sendToTelegram) { 
		echo "Success";
	} else {
		echo "Error";
	}
}