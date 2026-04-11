<?php

require_once __DIR__ . '/../../config/bootstrap.php';
use Lib\services\profile_services;

$profile_service = new profile_services();

$error = "";

$firstName = $_SESSION['firstName'] ?? null;
$lastName = $_SESSION['lastName'] ?? null;
$email = $_SESSION['email'] ?? null;
$phoneNumber = $_SESSION['phoneNumber'] ?? null;

$email_verifiection = $profile_service->is_email_verified($_SESSION['uid']) ? "Verified" : "Unverified";
$phone_verifiecation = $profile_service->is_phone_verified($_SESSION['uid']) ? "Verified" : "Unverified";
$id_verified = $profile_service->is_id_verified($_SESSION['uid']) ? "Verified" : "Unverified";



// Change user information
if ($_SERVER['REQUEST_METHOD'] === "POST") {
	if (isset($_POST['user_info'])){
		$firstName = $_POST["firstName"];
		$lastName = $_POST["lastName"];
		try {
			// $profile_service->change_user_info($firstName, $lastName);
		} catch (Exception $e) {
			$error = "Failed to change First/Last name";
		}
	}
}

// Change Phone Number
if ($_SERVER['REQUEST_METHOD'] === "POST") {
	if(isset($_POST['phone_form'])){
		$phoneNumber = $_POST['phoneNumber'];
		try{
			//$profile_service->change_phone_number($phoneNumber);
		} catch (Exception $e) {
			$error = "Failed to send OTP";
		}
	}
}

// Change Email address/Verify email address
if ($_SERVER['REQUEST_METHOD'] === "POST") {
	if(isset($_POST['email_form'])){
		$email = $_POST['email'];
		try{
			$profile_service->send_verification_email($email);
		} catch (Exception $e) {
			$error = "Failed to send email";
		}
	}
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

	<!--Profile Picture-->

	<!--Personal Info-->
	<form action="" method="post">
		<legend>Personal Info</legend><br/>
		<label>First Name:</label><br/>
		<input type="text" value="<?= $firstName ?>" name="firstName"><br/><br/>

		<label>Last Name:</label><br/>
		<input type="text" value="<?= $lastName ?>" name="lastName"><br/><br/>

		<button name="user_info" value="Save changes" type="submit">Save changes</button>
	</form>
	<br/><br/><br/><br/><br/><br/>


	<form action="" method="post">
		<label>Phone Number</label>&emsp;&emsp;&emsp;<label><?= $phone_verifiecation ?></label><br/>
		<input type="text" value="<?= $phoneNumber ?>" name="phoneNumber">
		<button type="submit" name="phone_form">Save Change</button>
	</form>

	<br/><br/><br/><br/><br/><br/>
	<!--Email Verification-->
	<form action="" method="post">
		<label>Email:</label>&emsp;&emsp;&emsp;<label><?= $email_verifiection ?></label><br/>
		<input type="email" value="<?= $email ?>" name="email">
		<button type="submit" name="email_form">Verify email</button>
	</form>

	<h3>ID: <?= $id_verified ?></h3>
</body>
</html>