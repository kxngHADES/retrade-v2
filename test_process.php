<?php
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['payment_session_id'] = 1;
$_POST['card_name'] = 'test';
$_POST['card_number'] = '1234123412341234';
$_POST['exp_date'] = '12/25';
$_POST['cvv'] = '123';
$_SESSION['uid'] = '475d9c31-4094-11f1-beee-32d150405fde';
require 'frontend/pages/pay/process.php';
