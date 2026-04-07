<?php

require __DIR__ . '/../../config/bootstrap.php';

namespace Lib\services;

use Lib\db\Database;
use PDO;
use PDOException;

class Authentication_service {
	private PDO $db;

	public function __construct() {
		$this->db = Database::getConnection();
	}

	//User Registration
	public function register(string $firstName, string $lastName, string $email, string $phone, string $password): string|false {

		if (session_status() === PHP_SESSION_NONE){
			session_start();
		}

		try {

			$this->db->beginTransaction();

			$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

			$query = "INSERT INTO users (firstName, lastName, email, phoneNumber, password, is_phone_verified)VALUES (:firstName, :lastName, :email, :phoneNumber, :password, 0)";

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
}