<?php

/* https://api.telegram.org/bot1084295246:AAFKNXWVWkjNkXgWNXkb99_kGwyBcKNVbYo/getUpdates,
где, XXXXXXXXXXXXXXXXXXXXXXX - токен вашего бота, полученный ранее */

$name = $_POST['name'];
$phone = $_POST['phone'];
$page = $_POST['page'];
$form = $_POST['form_subject'];
$token = "1084295246:AAFKNXWVWkjNkXgWNXkb99_kGwyBcKNVbYo";
$chat_id = "-336655231";
$arr = array(
	'Телефон: ' => $phone,
  'Имя: ' => $name,  
  'Страница' => $page,
  'Форма' => $form
);

foreach($arr as $key => $value) {
  $txt .= "<b>".$key."</b> ".$value."%0A";
};

$sendToTelegram = fopen("https://api.telegram.org/bot{$token}/sendMessage?chat_id={$chat_id}&parse_mode=html&text={$txt}","r");

if ($sendToTelegram) {
  header('Location: zayavka-prinyata');
} else {
  echo "Error";
}
?>