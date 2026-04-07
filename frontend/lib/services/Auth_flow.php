<?php 

require_once __DIR__ . '/../../config/bootstrap.php';

namespace Lib\services;

use GrahamCampbell\ResultType\Success;
use Lib\services\Authentication_service;
use Lib\services\sms_services;



class Auth_flow {

	public function start_registration_flow(array $userData):string|bool {

		$authService = new Authentication_service();

		$email = $userData['email'];

		if ($authService->checkIfEmailExists($email)){
			return "Email already exists";
		}

		$redis = \Lib\cache\Redis::getInstance();
		$userData['otp'] = rand(1000000, 999999);
		$stored = $redis->storeUserTemp($userData);

		if ($stored) {
			$sms = new sms_services();
			$success = $sms->send_otp($userData['phoneNumber'], $userData['otp']);
			if ($success) {
				return true;
			} else {
				error_log("Failed to send otp sms");
				return false;
			}
		} else {
			error_log("Failed to store user data.");
			return false;
		}
	}


	public function finish_registration(string $phoneNumber ,int $otp) {
		// TODO

		// check if phone number and otp match redis

		// if not match return error

		// if match get all stored user data in temp and assign variable

		// call authservice->register

		//redirect to PROJECT_ROOT (domain.com/)

	}
}