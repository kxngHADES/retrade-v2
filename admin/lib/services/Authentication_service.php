<?php
namespace Lib\services;
require __DIR__ . '/../../config/bootstrap.php';

use Lib\db\Database;
use PDO;
use PDOException;

class Authentication_service{
    private PDO $db;

    public function __construct() {
		$this->db = Database::getConnection();
	}

    public function login(string $email, string $password) {
        if (session_status() === PHP_SESSION_NONE){
            session_start();
        }

        $user = $this->findByEmail($email);

        if (!$user){
            return [
                'success' => false,
                'error' => 'Invalid email or password',
                'user' => null,
                'action' => null
            ];
        }

        if (!$this->verifyPassword($password, $user['password'])){
            return [
				'success' => false,
				'error' => 'Invalid email or password',
				'user' => null,
				'action' => null
			];
        }

        if ($user['is_active'] == 0) {
            return [
				'success' => false,
				'error' => 'Your account is inactive. Please contact a super admin.',
				'user' => null,
				'action' => null
			];
        }

        unset($user['password']);

        try {
			$stmt = $this->db->prepare("UPDATE admins SET last_login = NOW() WHERE email = :email");
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


    # find by email
    public function findByEmail(string $email): ?array {
		try {
			$sql = "SELECT *, BIN_TO_UUID(admin_id) as uuid FROM admins WHERE email = :email LIMIT 1";
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

    public function register(string $firstName, string $lastName, string $email, string $password, int $role = 2): bool {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO admins (firstName, lastName, email, password, rbac_role, is_active) 
                VALUES (:firstName, :lastName, :email, :password, :role, 1)";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':firstName' => $firstName,
                ':lastName' => $lastName,
                ':email' => $email,
                ':password' => $hashedPassword,
                ':role' => $role
            ]);
        } catch (PDOException $e) {
            error_log("Admin registration failed: " . $e->getMessage());
            return false;
        }
    }

    public function verifyPassword(string $password, string $hashedPassword): bool {
		return password_verify($password, $hashedPassword);
	}
}