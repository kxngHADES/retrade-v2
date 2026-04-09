<?php

namespace Lib\services;
require_once __DIR__ . '/../../config/bootstrap.php';

use Lib\db\Database;
use PDO;
use PDOException;
use Lib\services\email_service;

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


	// Update profile image URL


	// Update email


	// Update Phone number


	// Verify email
	public function send_verification_email(string $email) :string|bool {
		if (session_status() === PHP_SESSION_NONE){
			session_start();
		}

		$_SESSION['email'] = $email;

		$email_service = new email_service();

		$redis = \Lib\cache\Redis::getInstance();
		$otp = rand(100000, 999999);
		if ($redis->setEx($email, $otp, (60*20))) {
			if ($email_service->send_otp($email, $otp)) {
				header('Location: /pages/profile/verify_email');
				exit;
			} else {
				return false;
			}
		} else {
			return false;
		}
		
	}

	public function validate_email_otp(string $email, int $otp): string|bool {
		$redis = \Lib\cache\Redis::getInstance();

		if ($redis->verifyEmailOTP($email, $otp)) {
			//Update is_email_verified

			//delete cache
			$redis->deleteUserTemp($email);
			header('Location: /pages/profile');
			exit;
		} else {
			return "Invalid OTP";
		}
	}


	// Verify Phone


	// Verify ID
}
?>