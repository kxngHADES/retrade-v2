<?php
namespace Lib\services;
require __DIR__ . '/../../config/bootstrap.php';

use Dotenv\Util\Str;
use Lib\db\Database;
use PDO;
use PDOException;
use Stringable;

class Authentication_service {
	private PDO $db;

	public function __construct() {
		$this->db = Database::getConnection();
	}

	// Check if user exists
	public function checkIfEmailExists(string $email): bool {
		$query = "SELECT * FROM users WHERE email = :email LIMIT 1";
		$stmt = $this->db->prepare($query);
		$stmt->execute([':email' => $email]);
		$user = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($user) {
			return true;
		} else {
			return false;
		}

	}

	//User Registration
	public function register(string $firstName, string $lastName, string $email, string $phone, string $password): string|false {

		if (session_status() === PHP_SESSION_NONE){
			session_start();
		}

		try {

			$this->db->beginTransaction();

			$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

			$query = "INSERT INTO users (firstName, lastName, email, phoneNumber, password, is_phone_verified)VALUES (:firstName, :lastName, :email, :phoneNumber, :password, 1)";

			$stmt = $this->db->prepare($query);
			$stmt->execute([
				":firstName" => $firstName,
				":lastName" => $lastName,
				":email" => $email,
				":phoneNumber" => $phone,
				":password" => $hashedPassword
			]);

			$stmt = $this->db->prepare("SELECT BIN_TO_UUID(uid) as uuid, firstName, lastName, email FROM users WHERE email = :email LIMIT 1");
			$stmt->execute([':email' => $email]);
			$user = $stmt->fetch(PDO::FETCH_ASSOC);

			if ($user) {
				$this->db->commit();

				$_SESSION['uid'] = $user['uuid'];
				$_SESSION['firstName'] = $user['firstName'];
				$_SESSION['lastName'] = $user['lastName'];
				$_SESSION['email'] = $user['email'];
				return $user['uuid'];
			} else {
				$this->db->rollBack();

				error_log("Registration failed: User inserted but not found for email: $email");
				return false;
			}

		} catch (PDOException $e){

			if ($this->db->inTransaction()) {
				$this->db->rollBack();
			}

			if ($e->getCode() == 23000){
				error_log("Duplicate user: $email or $phone");
			} else {
				error_log("Registration failed: " . $e->getMessage());
			}
			
			return false;
		}
	}

	public function login(string $email, string $password): array {
		
		if (session_status() === PHP_SESSION_NONE){
			session_start();
		}

		$user = $this->findByEmail($email);

		if (!$user) {
			return [
				'success' => false,
				'error' => 'Invalid email or password',
				'user' => null,
				'action' => null
			];
		}

		if (!$this->verifyPassword($password, $user['password'])) {
			return [
				'success' => false,
				'error' => 'Invalid email or password',
				'user' => null,
				'action' => null
			];
		}

		unset($user['password']);

		try {
			$stmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE email = :email");
			$stmt->execute(['email' => $email]);
		} catch (\PDOException $e) {
			error_log("Failed to update last_login: " . $e->getMessage());
		}

		return [
			'success' => true,
			'error' => null,
			'user' => $user,
			'action' => 'login_success'
		];
	}

	public function findByEmail(string $email): ?array {
		try {
			$sql = "SELECT *, BIN_TO_UUID(uid) as uuid FROM users WHERE email = :email LIMIT 1";
			$stmt = $this->db->prepare($sql);
			$stmt->execute([':email' => $email]);

			$user = $stmt->fetch(\PDO::FETCH_ASSOC);
			if ($user) {
				$user['uid'] = $user['uuid'];
				unset($user['uuid']);
			}

			return $user ?: null;

		} catch (PDOException $e) {
			error_log("Find by email failed: " . $e->getMessage());
			return null;
		}
	}

	public function verifyPassword(string $password, string $hashedPassword): bool {
		return password_verify($password, $hashedPassword);
	}

	public function update_email(string $email, string $uid) {
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			return false;
		}
		$sql = "UPDATE users SET email = :email, is_email_verified = 1 WHERE uid = UUID_TO_BIN(:uid)";

		try{
			$stmt = $this->db->prepare($sql);
			$stmt->execute(["email" => $email, "uid" => $uid]);
			return $stmt->rowCount() >= 0;
		} catch (\PDOException $e){
			error_log("Update email error: " . $e->getMessage());
			return false;
		}
	}

	public function update_user_info(string $firstName, string $lastName, string $uid) {
		$sql = "UPDATE users SET firstName = :firstName, lastName = :lastName WHERE uid = UUID_TO_BIN(:uid)";

		try {
			$stmt = $this->db->prepare($sql);
			$stmt->execute(['firstName' => $firstName, 'lastName' => $lastName, 'uid' => $uid]);
			return $stmt->rowCount() >= 0;
		} catch (\PDOException $e) {
			error_log("Update user info: " . $e->getMessage());
			return false;
		}
	}

	public function update_phone_number(string $phoneNumber, string $uid) {
		$sql = "UPDATE users SET phoneNumber = :phoneNumber, is_phone_verified = 0 WHERE uid = UUID_TO_BIN(:uid)";

		try {
			$stmt = $this->db->prepare($sql);
			$stmt->execute(['phoneNumber' => $phoneNumber, 'uid' => $uid]);
			return $stmt->rowCount() >= 0;
		} catch (\PDOException $e) {
			error_log("Update user info: " . $e->getMessage());
			return false;
		}
	}

	public function verify_phone(string $uid) {
		$sql = "UPDATE users SET is_phone_verified = 1 WHERE uid = UUID_TO_BIN(:uid)";
		try {
			$stmt = $this->db->prepare($sql);
			$stmt->execute(['uid' => $uid]);
			return $stmt->rowCount() >= 0;
		} catch (\PDOException $e) {
			error_log("Update user info: " . $e->getMessage());
			return false;
		}
	}

	// set ID to pending
	public function set_id_to_pending(string $uid) {
		$sql = "UPDATE users SET is_id_verified = 2 WHERE uid = UUID_TO_BIN(:uid)";
		try {
			$stmt = $this->db->prepare($sql);
			$stmt->execute(['uid' => $uid]);
		} catch (\PDOException $e) {
			error_log("Update user info error: " . $e->getMessage());
		}
	}

	
}