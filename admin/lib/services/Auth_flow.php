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
		$_SESSION['rbac_role'] = $user['rbac_role'];

		header('Location: /dashboard/');
		exit;
	}

    public function register(array $userData) {
        $auth = new Authentication_service();

        $firstName = $userData['firstName'] ?? '';
        $lastName = $userData['lastName'] ?? '';
        $email = $userData['email'] ?? '';
        $password = $userData['password'] ?? '';
        $role = (int)($userData['role'] ?? 2);

        if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
            return [
                "success" => false,
                "error" => "All fields are required."
            ];
        }

        $success = $auth->register($firstName, $lastName, $email, $password, $role);

        if (!$success) {
            return [
                "success" => false,
                "error" => "Registration failed. Email might already exist."
            ];
        }

        return [
            "success" => true,
            "message" => "Admin account created successfully. You can now login."
        ];
    }
}