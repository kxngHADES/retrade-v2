<?php

require_once __DIR__ . '/../../../config/bootstrap.php';

use Lib\services\profile_services;

$profile_service = new profile_services();

if ($_SERVER['REQUEST_METHOD'] === "POST"){
	$otp = $_POST['otp'];
	$profile_service->validate_email_otp($_SESSION['email'], $otp, $_SESSION['uid']);
}

?>

<!DOCTYPE html>
<html lang=<?= $_SESSION['lang'] ?? 'en'; ?>>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Document</title>
</head>
<body>
	<form action="" method="post">
		<legend>Enter OTP</legend>
		<input type="text" placeholder="6 digit code" name="otp">
		<button type="submit">Verify</button>
	</form>
</body>
</html>