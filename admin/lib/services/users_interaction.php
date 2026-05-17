<?php
namespace Lib\services;
require_once __DIR__ . '/../../config/bootstrap.php';

use Lib\db\Database;
use PDO;
use PDOException;

class users_interaction {
	private PDO $db;

	public function __construct() {
		$this->db = Database::getConnection();
	}


	public function banUser(string $uid, string $reason): bool {
		$expiresAt = null;

		switch (strtolower($reason)) {
			case 'scamming':
			case 'fraud':
			case 'fake_delivery':
				// Permanent ban for severe C2C infractions
				$expiresAt = null; 
				break;
			case 'harassment':
			case 'inappropriate_behavior':
				// 30 days for harassment or inappropriate conduct
				$expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));
				break;
			case 'spamming':
				// 7 days for spamming
				$expiresAt = date('Y-m-d H:i:s', strtotime('+7 days'));
				break;
			case 'minor_infraction':
				// 1 day temporary ban / timeout
				$expiresAt = date('Y-m-d H:i:s', strtotime('+1 day'));
				break;
			default:
				// Suspended pending investigation (Default 7-days)
				$expiresAt = date('Y-m-d H:i:s', strtotime('+7 days'));
				break;
		}

		$sql = "UPDATE users SET is_banned = 1, ban_expires_at = :expiresAt WHERE uid = UUID_TO_BIN(:uid)";

		try {
			$stmt = $this->db->prepare($sql);
			$stmt->execute([
				'expiresAt' => $expiresAt,
				'uid' => $uid
			]);
			return $stmt->rowCount() > 0;
		} catch (PDOException $e) {
			error_log("Failed to ban user: " . $e->getMessage());
			return false;
		}
	}


    # Fraud



    # User management

}
