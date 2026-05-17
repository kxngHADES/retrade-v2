<?php

namespace Lib\services;
require_once __DIR__ . '/../../config/bootstrap.php';

use Lib\services\Authentication_service;


class Auth_flow {
    public function login(array $userData) {
		$auth = new Authentication_service();

		$email = $userData['email'];
		$password = $userData['password'];
		$result = $auth->login($email, $password);

		if (!$result['success']) {
			return [
				"success" => false,
				"error" => "Login failed: " . $result['error']
			];
		}

		$user = $result['user'];

		$_SESSION['uid'] = $user['uid'];
		$_SESSION['email'] = $user['email'];
		$_SESSION['firstName'] = $user['firstName'];
		$_SESSION['lastName'] = $user['lastName'];
		$_SESSION['phoneNumber'] = $user['phoneNumber'];

		header('Location: /');
		exit;
	}
}