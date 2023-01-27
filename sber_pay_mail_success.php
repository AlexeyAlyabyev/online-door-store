<?php
// Подключаем библиотеку PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
require 'PHPMailer/PHPMailer.php';

$host = 'localhost';
$user = 'rumluxsq_ocar1';
$password = 'EfLrJl2cw';
$db_name = 'rumluxsq_ocar1';
$link = mysqli_connect($host, $user, $password, $db_name);
$query = "SELECT * FROM `sber_numbers_to_order` WHERE `orderID`='".$_POST['orderId']."'";
$result = mysqli_query($link, $query) or die(mysqli_error($link));
for ($data = []; $row = mysqli_fetch_assoc($result); $data[] = $row);
print_r($result);
print_r($data);
$mail = new PHPMailer();
$mail->setFrom('all@100-dverei.ru', 'Успешная оплата заказа'); // от кого (email и имя)
$mail->addAddress('info@100-dverei.ru', '100 Дверей');  // кому (email и имя)
$mail->Subject = 'Успешная оплата заказа';                         // тема письма
$mail->CharSet = "utf-8";
// html текст письма
$mail->msgHTML('
	<html>
	<head>
		<title>Заявка</title>
		<meta charset="UTF-8" />
	</head>
	<body>
		<p>Заказ под номером '.$data[0]["number"].' успешно оплачен!</p>
		<p>ID заказа - '.$_POST['orderId'].'</p>
		<p>Имя покупателя - '.$data[0]["customer_name"].'</p>
		<p>Телефон - '.$data[0]["phone"].'</p>
		<p>Сумма - '.$data[0]["amount"].'</p>
	</body>
	</html>
');
// Отправляем
if ($mail->send()) {
	echo 'Письмо отправлено!';
	print_r($data);
} else {
	echo 'Ошибка: ' . $mail->ErrorInfo;
}
