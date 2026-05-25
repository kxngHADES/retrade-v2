<?php 


namespace Lib\services;
require_once __DIR__ . '/../../config/bootstrap.php';

use GrahamCampbell\ResultType\Success;
use Lib\services\Authentication_service;
use Lib\services\sms_services;



class Auth_flow {

	public function start_registration_flow(array $userData):string|bool {
		if (session_status() === PHP_SESSION_NONE){
			session_start();
		}

		$authService = new Authentication_service();

		$email = $userData['email'];

		if ($authService->checkIfEmailExists($email)){
			return "Email already exists";
		}

		$redis = \Lib\cache\Redis::getInstance();
		$userData['otp'] = rand(100000, 999999);
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

	public function resend_registration_otp(string $phoneNumber): string|bool {
		$redis = \Lib\cache\Redis::getInstance();
		$userData = $redis->getUserTemp($phoneNumber);

		if (!$userData) {
			return "Registration session expired. Please register again.";
		}

		$userData['otp'] = rand(100000, 999999);
		$stored = $redis->storeUserTemp($userData);

		if ($stored) {
			$sms = new sms_services();
			$success = $sms->send_otp($userData['phoneNumber'], $userData['otp']);
			if ($success) {
				return true;
			} else {
				error_log("Failed to resend otp sms");
				return "Failed to resend OTP sms.";
			}
		} else {
			error_log("Failed to update user data.");
			return "Failed to update OTP.";
		}
	}

	


	public function finish_registration(string $phoneNumber ,int $otp) {
		$redis = \Lib\cache\Redis::getInstance();
		
		$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
		if ($redis->isRateLimited('verify_otp_reg', $ip, 5, 300)) {
			return [
				"success" => false,
				"error" => "Too many verification attempts. Please try again in 5 minutes.",
				"action" => null
			];
		}

		// check if phone number and otp match redis
		if (!$redis->verifyUserTemp($phoneNumber, $otp)) {
			return [
				"success" => false,
				"error" => "Invalid OTP",
				"action" => null
			];
		}
		// if match get all stored user data in temp and assign variable
		$userData = $redis->getUserTemp($phoneNumber);
		if (!$userData){
			return [
				"success" => false,
				"error" => "User data not found please restart the regisration process",
				"action" => null
			];
		}

		$firstName = $userData['firstName'];
		$lastName = $userData['lastName'];
		$email = $userData['email'];
		$phoneNumber = $userData['phoneNumber'];
		$password = $userData['password'];

		// call authservice->register
		$auth = new Authentication_service();
		$uuid = $auth->register($firstName, $lastName, $email, $phoneNumber, $password);

		if ($uuid === false) {
			return [
				"sucess" => false,
				"error" => "Registration failed. Please try again.",
				"action" => null
			];
		}

		$redis->deleteUserTemp($phoneNumber);

		//redirect to PROJECT_ROOT (domain.com/)
		header('Location: /');
		exit;
	}




	// Login flow
	public function login(array $userData) {
		$redis = \Lib\cache\Redis::getInstance();
		$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

		if ($redis->isRateLimited('login', $ip, 5, 300)) {
			return [
				"success" => false,
				"error" => "Too many login attempts. Please try again in 5 minutes."
			];
		}

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