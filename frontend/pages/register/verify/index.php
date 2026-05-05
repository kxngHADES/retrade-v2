<?php

require __DIR__ . '/../../../config/bootstrap.php';

use Lib\services\Auth_flow;

$phoneNumber = $_SESSION['phoneNumber'];
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
	$auth = new Auth_flow();
	if (isset($_POST['resend'])) {
		$result = $auth->resend_registration_otp($phoneNumber);
		if ($result === true) {
			$error = "A new OTP has been sent.";
		} else {
			$error = $result;
		}
	} else if (isset($_POST['verify'])) {
		$otp = $_POST['otp'];
		$result = $auth->finish_registration($phoneNumber, $otp);
		$error = $result['error'] ?? "An error occurred";
	}
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
	<h2><?= htmlspecialchars($error) ?></h2>
	<form action="" method="post">
		<legend>Veirfy OTP</legend>
		<label>Enter OTP</label><br/>
		<input type="text" name="otp" placeholder="6 digit number"><br/><br/>

		<input type="submit" name="verify" value="Verify">
		<input type="submit" name="resend" value="Resend Code" formnovalidate>
	</form>
</body>
</html>