<?php

namespace Lib\services;
require_once __DIR__ . '/../../config/bootstrap.php';

use Exception;
use Lib\db\Database;
use PDO;
use PDOException;
use Lib\services\email_service;
use Lib\services\Authentication_service;
use Lib\services\sms_services;

class profile_services {
	private PDO $db;

	public function __construct()
	{
		$this->db = Database::getConnection();
	}

	public function get_profile_info(string $uid) {
		$query = "SELECT * FROM users WHERE uid = UUID_TO_BIN(:uid)";
		$stmt = $this->db->prepare($query);
		$stmt->execute([":uid" => $uid]);
		$user = $stmt->fetch(PDO::FETCH_ASSOC);
	}

	public function is_email_verified(string $uid): bool {
		$query = "SELECT is_email_verified FROM users WHERE uid = UUID_TO_BIN(:uid) AND is_email_verified = 1 LIMIT 1";
		$stmt = $this->db->prepare($query);
		$stmt->execute(["uid" => $uid]);
		return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
	}

	public function is_phone_verified(string $uid): bool {
		$query = "SELECT is_phone_verified FROM users WHERE uid = UUID_TO_BIN(:uid) AND is_phone_verified = 1 LIMIT 1";
		$stmt = $this->db->prepare($query);
		$stmt->execute(["uid" => $uid]);
		return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
	}

	public function is_id_verified(string $uid): bool {
		$query = "SELECT is_id_verified FROM users WHERE uid = UUID_TO_BIN(:uid) AND is_id_verified = 1 LIMIT 1";
		$stmt = $this->db->prepare($query);
		$stmt->execute(["uid" => $uid]);
		return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
	}


	// Updaete personal information
	public function change_user_info(string $firstName, string $lastName, string $uid) {
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}

		$auth = new Authentication_service();

		$auth->update_user_info($firstName, $lastName, $uid);
		header('Location: /pages/profile');
		exit;

	}


	// Update profile image URL



	// Update Phone number
	public function change_phone_number(string $phoneNumber, string $uid) {
		if (session_status() === PHP_SESSION_NONE) {
			session_status();
		}

		$auth = new Authentication_service();

		$auth->update_phone_number($phoneNumber, $uid);

		//send otp and store in redis session
		$redis = \Lib\cache\Redis::getInstance();
		$otp = rand(100000, 999999);
		$redis->setEx($phoneNumber, $otp, (60*20));

		$sms = new sms_services();
		$success = $sms->send_otp($phoneNumber, $otp);
		header('Location: /pages/profile/verify_phone');
		exit;
	}

	public function verify_phone_number(string $uid, $otp, string $phoneNumber) {
		$redis = \Lib\cache\Redis::getInstance();
		
		try {
			$redis->verifyEmailOTP($phoneNumber, $otp); //similar logic so just reused emailOTP verification with just a differnt key value
			$auth = new Authentication_service();
			$auth->verify_phone($uid);

			$redis->deleteUserTemp($phoneNumber);
			header('Location: /pages/profile');
			exit;
		} catch (Exception $e) {
			error_log("Verify phone number error: " . $e->getMessage());
			return "Invalid OTP";
		}
	}


	// Verify/Update email
	public function send_verification_email(string $email) {
		if (session_status() === PHP_SESSION_NONE){
			session_start();
		}

		$_SESSION['email'] = $email;

		$email_service = new email_service();

		$redis = \Lib\cache\Redis::getInstance();
		try {
			$otp = rand(100000, 999999);
			$redis->setEx($email, $otp, (60*20));
			$email_service->send_otp($email, $otp);
			header('Location: /pages/profile/verify_email');
			exit;
		} catch (Exception $e){
			error_log("Email verification error: " . $e->getMessage());
		}
		
	}

	public function validate_email_otp(string $email, int $otp, string $uid) {
		$redis = \Lib\cache\Redis::getInstance();

		try {
			$redis->verifyEmailOTP($email, $otp);
			//Update is_email_verified
			$auth = new Authentication_service();
			$auth->update_email($email, $uid);

			//delete cache
			$redis->deleteUserTemp($email);
			header('Location: /pages/profile');
			exit;
		} catch (Exception $e) {
			error_log("Verify emauk error: " . $e->getMessage());
			return "Invalid OTP";
		}
	}


	// Verify Phone


	// Verify ID
}
?>