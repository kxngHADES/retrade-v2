<?php

require __DIR__ . '/../../../config/bootstrap.php';

use Lib\services\profile_services;

$profile_service = new profile_services();

if ($_SERVER['REQUEST_METHOD'] === "POST"){
	$otp = $_POST['otp'];
	$profile_service->verify_phone_number($_SESSION['uid'], $otp, $_SESSION['phoneNumber']);
}

?>
<!DOCTYPE html>
<html lang=<?= $_SESSION['lang'] ?? 'en'; ?>>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Document</title> <!--Lang verify-->
</head>
<body>
	<form action="" method="post">
		<legend>Veirfy OTP</legend>
		<label>Enter OTP</label><br/>
		<input type="text" name="otp" placeholder="6 digit number"><br/><br/>

		<input type="submit" value="Verify">
	</form>
</body>
</html>