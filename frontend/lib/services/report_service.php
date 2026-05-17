<?php
namespace Lib\services;
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/ApiService.php';

use Lib\db\Database;
use PDO;
use PDOException;
use Lib\services\ApiService;

class Report_service {
	private PDO $db;

	public function __construct() {
		$this->db = Database::getConnection();
	}


	public function report_user(string $reporterId, string $targetUserId, string $reason, string $description = ''): bool {
		$sql = "INSERT INTO user_reports (reporter_id, report_type, target_reference_id, reason, description) 
				VALUES (UUID_TO_BIN(:reporter_id), 'user', :target_user_id, :reason, :description)";
		
		try {
			$stmt = $this->db->prepare($sql);
			$stmt->execute([
				'reporter_id' => $reporterId,
				'target_user_id' => $targetUserId,
				'reason' => $reason,
				'description' => $description
			]);
			$success = $stmt->rowCount() > 0;
			
			if ($success) {
				$api = new ApiService();
				$api->send_fraud_report_to_graph($reporterId, $targetUserId, $reason, $description);
			}

			return $success;
		} catch (PDOException $e) {
			error_log("Failed to insert user report: " . $e->getMessage());
			return false;
		}
	}
}
