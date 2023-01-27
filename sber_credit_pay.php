<?php
$host = 'localhost';
$user = 'rumluxsq_ocar1';
$password = 'EfLrJl2cw';
$db_name = 'rumluxsq_ocar1';
$link = mysqli_connect($host, $user, $password, $db_name);
$query = "SELECT * FROM `sber_numbers`";
$result = mysqli_query($link, $query) or die(mysqli_error($link));
for ($data = []; $row = mysqli_fetch_assoc($result); $data[] = $row);

$number = $data[0]["number"];

$user_name = "P500316276369-credit-api";
$password = "dqpBMMWpo8mBhVm";
$orderNumber = $number; // Берем из mysql
$amount = $_POST['amount']*100; // $_POST['amount']
$returnUrl = "https://100-dverei.ru/index.php?route=checkout/success";
$data = "userName=".$user_name."&password=".$password."&orderNumber=".$orderNumber."&amount=".$amount."&returnUrl=".$returnUrl."&currency=643&orderBundle=".$_POST["orderBundle"];
if( $curl = curl_init() ) {
    curl_setopt($curl, CURLOPT_URL, 'https://securepayments.sberbank.ru/sbercredit/register.do');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    $out = curl_exec($curl);
		$out = json_decode($out);
		$out->phone = $_POST["customerPhone"];
		$out->name = $_POST["customerName"];
		$out = json_encode($out);
    echo $out;
    curl_close($curl);
		
		// Обновляем номер для следующего заказа в таблице
		$new_number = $number + 1;
		$update_query = "UPDATE `sber_numbers` SET `number`=".$new_number." WHERE id=1";
		mysqli_query($link, $update_query);
  }