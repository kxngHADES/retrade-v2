<?php

require_once __DIR__ . '/../../config/bootstrap.php';
use Lib\services\profile_services;

$profile_service = new profile_services();


$firstName = $_SESSION['firstName'] ?? null;
$lastName = $_SESSION['lastName'] ?? null;
$email = $_SESSION['email'] ?? null;
$phoneNumber = $_SESSION['phoneNumber'] ?? null;

$email_verifiection = $profile_service->is_email_verified($_SESSION['uid']) ? "Verified" : "Unverified";
$phone_verifiecation = $profile_service->is_phone_verified($_SESSION['uid']) ? "Verified" : "Unverified";
$id_verified = $profile_service->is_id_verified($_SESSION['uid']) ? "Verified" : "Unverified";

?>
<!DOCTYPE html>
<html lang=<?= $_SESSION['lang'] ?? 'en'; ?>>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Document</title>
</head>
<body>

	<!--Profile Picture-->

	<!--Personal Info-->
	<form action="" method="post">
		<legend>Personal Info</legend><br/>
		<label>First Name:</label><br/>
		<input type="text" value="<?= $firstName ?>" name="firstName"><br/><br/>

		<label>Last Name:</label><br/>
		<input type="text" value="<?= $lastName ?>" name="lastName"><br/><br/>
	</form>
	<br/><br/><br/><br/><br/><br/>

	<!--Verification info-->
	<form action="" method="post">
		<legend>Verification Info</legend><br/>
		<label>Email:</label>&emsp;&emsp;&emsp;<label><?= $email_verifiection ?></label><br/>
		<input type="email" value="<?= $email ?>" name="email"><br/><br/>

		<label>Phone Number</label>&emsp;&emsp;&emsp;<label><?= $phone_verifiecation ?></label><br/>
		<input type="text" value="<?= $phoneNumber ?>" name="phoneNumber">
	</form>

	<h3>ID: <?= $id_verified ?></h3>
</body>
</html>