<?php

require __DIR__ . '/../../../config/bootstrap.php';

use Lib\services\Auth_flow;

$phoneNumber = $_SESSION['phoneNumber'];
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
	$otp = $_POST['otp'];
	$auth = new Auth_flow();
	$result = $auth->finish_registration($phoneNumber, $otp);
	$error = $result['error'];
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