<?php

require_once __DIR__ . '/../../config/bootstrap.php';

use Lib\services\Auth_flow;

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	if ($_POST['password'] !== $_POST['confirmPassword']){
		$error = "Passwords do not match";
	} else {
		$auth = new Auth_flow();
		$result = $auth->start_registration_flow($_POST);
		if ($result === true) {
			$_SESSION['phoneNumber'] = $_POST['phoneNumber'];
			header('Location: /pages/register/verify');
			exit;
		} elseif (is_string($result)) {
			$error = $result;
		} else {
			$error = "Registration temporarily unavailable. Please try again later.";
		}
	}
}
?>
<!DOCTYPE html>
<html lang=<?= $_SESSION['lang'] ?? 'en'; ?>>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?= trans('Register'); ?></title>
</head>
<body>
	<form action="" method="post">
		<legend><?= trans('Register'); ?></legend>
		<label><?= $error; ?></label> <!--Display error-->
		<br/>
		<label>First Name: </label>
		<br/><input type="text" placeholder="first name" name="firstName" required><br/><br/>

		<label>Last Name:  </label>
		<br/><input type="text" placeholder="last name" name="lastName" required><br/><br/>

		<label>Email:      </label>
		<br/><input type="email" placeholder="user@example.com" name="email" required><br/><br/>

		<label>Phone Number:</label>
		<br/><input type="text" placeholder="+27712345689" name="phoneNumber" required><br/><br/>

		<label>Password: </label>
		<br/><input type="password" name="password" required><br/><br/>

		<label>Confirm password:</label>
		<br/><input type="password" name="confirmPassword" required><br/><br/>

		<input type="submit" value="Register">
	</form>
</body>
</html>