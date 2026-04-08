<?php

require_once __DIR__ . '/../../config/bootstrap.php';
use Lib\services\Auth_flow;

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$auth = new Auth_flow;

	$result = $auth->login($_POST);

	if (!$result['success']) {
		$error = $result['error'];
	}
}

?>
<!DOCTYPE html>
<html lang=<?= $_SESSION['lang'] ?? 'en'; ?>>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Document</title> <!--Add login-->
</head>
<body>
	<form action="" method="post">
		<legend>Login</legend>

		<label><?= $error; ?></label>
		<label>Email:</label><br/>
		<input type="email" name="email" placeholder="user@example.com"><br/><br/>

		<label>Password:</label><br/>
		<input type="password" name="password"><br/><br/>

		<input type="submit" value="Login">
	</form>
</body>
</html>