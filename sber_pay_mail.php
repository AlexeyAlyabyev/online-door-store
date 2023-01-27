<?php
// Подключаем библиотеку PHPMailer
// use PHPMailer\PHPMailer\PHPMailer;
// require 'PHPMailer/PHPMailer.php';
// Создаем письмо
if(isset($_POST['phone']) && $_POST['phone']!="" && $_POST['phone']!="+7 (___) ___-__-__"){
	//Подключаемся к бд
	$host = 'localhost';
	$user = 'rumluxsq_ocar1';
	$password = 'EfLrJl2cw';
	$db_name = 'rumluxsq_ocar1';
	$link = mysqli_connect($host, $user, $password, $db_name);
	// Забираем текущее значение номера заказа
	$query = "SELECT * FROM `sber_numbers`";
	$result = mysqli_query($link, $query) or die(mysqli_error($link));
	for ($data = []; $row = mysqli_fetch_assoc($result); $data[] = $row);
	$number = $data[0]["number"];
	print_r($data);
	print_r($result);
	// Создаем запись-связку по номеру и id заказа                                                                    VALUES ('". $adds['name']. "', '". $adds['pass']. "', '". $adds['email']. "')";
	$query = "INSERT INTO `sber_numbers_to_order`(`number`, `orderID`, `customer_name`, `phone`, `amount`, `geo`) VALUES ('". $number. "', '". $_POST['orderId']. "', '". $_POST['name']. "', '". $_POST['phone']. "', '". $_POST['amount']. "', '". $_POST['zoneName']. "')";
	$result = mysqli_query($link, $query) or die(mysqli_error($link));
	print_r($result);
	// Обновляем номер для следующего заказа в таблице
	$new_number = $number + 1;
	$update_query = "UPDATE `sber_numbers` SET `number`=".$new_number." WHERE id=1";
	mysqli_query($link, $update_query);
}
else {
	echo "Incorrect data";
	return;
}