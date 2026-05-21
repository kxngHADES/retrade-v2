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

	public function log_dispute(string $reporterId, string $orderId, string $reason, string $description, ?string $paymentRef = null): bool {
		$sql = "INSERT INTO payment_disputes (dispute_id, reporter_id, order_id, payment_reference, dispute_reason, description, status) 
				VALUES (UUID_TO_BIN(UUID()), UUID_TO_BIN(:reporter_id), UUID_TO_BIN(:order_id), :payment_ref, :reason, :description, 'open')";
		
		try {
			$stmt = $this->db->prepare($sql);
			return $stmt->execute([
				'reporter_id' => $reporterId,
				'order_id' => $orderId,
				'payment_ref' => $paymentRef,
				'reason' => $reason,
				'description' => $description
			]);
		} catch (PDOException $e) {
			error_log("Failed to log payment dispute: " . $e->getMessage());
			return false;
		}
	}

	public function get_user_disputes(string $uid): array {
		$sql = "SELECT BIN_TO_UUID(dispute_id) as dispute_id, BIN_TO_UUID(order_id) as order_id, 
					   payment_reference, dispute_reason, description, status, created_at 
				FROM payment_disputes 
				WHERE reporter_id = UUID_TO_BIN(:uid)
				ORDER BY created_at DESC";
		try {
			$stmt = $this->db->prepare($sql);
			$stmt->execute(['uid' => $uid]);
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			error_log("Failed to fetch user disputes: " . $e->getMessage());
			return [];
		}
	}
}
