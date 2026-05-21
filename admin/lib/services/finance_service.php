<?php
namespace Lib\services;

require_once __DIR__ . '/../../config/bootstrap.php';

use Lib\db\Database;
use PDO;
use PDOException;

class finance_service{

    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    # Disputes

    # escrow controls
    public function getAllEscrowRecords(): array {
        $sql = "SELECT 
                    BIN_TO_UUID(e.escrow_id) as escrow_id,
                    BIN_TO_UUID(e.payment_id) as payment_id,
                    BIN_TO_UUID(e.uid) as uid,
                    u.firstName, u.lastName, u.email,
                    e.amount, e.status, e.escrow_date, e.paid_out_date
                FROM escrow e
                JOIN users u ON e.uid = u.uid
                ORDER BY e.escrow_date DESC";
        try {
            return $this->db->query($sql)->fetchAll();
        } catch (PDOException $e) {
            error_log("Failed to fetch escrow: " . $e->getMessage());
            return [];
        }
    }

    public function updateEscrowStatus(string $escrowId, string $status): bool {
        if (!in_array($status, ['released', 'refunded'])) {
            return false;
        }

        $sql = "UPDATE escrow 
                SET status = :status, 
                    paid_out_date = CASE WHEN :status = 'released' THEN CURRENT_TIMESTAMP ELSE paid_out_date END
                WHERE escrow_id = UUID_TO_BIN(:id)";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute(['status' => $status, 'id' => $escrowId]);
        } catch (PDOException $e) {
            error_log("Failed to update escrow: " . $e->getMessage());
            return false;
        }
    }

    # Disputes
    public function getAllDisputes(): array {
        $sql = "SELECT 
                    BIN_TO_UUID(d.dispute_id) as dispute_id,
                    BIN_TO_UUID(d.reporter_id) as reporter_id,
                    BIN_TO_UUID(d.order_id) as order_id,
                    u.firstName, u.lastName, u.email,
                    d.payment_reference, d.dispute_reason, d.description,
                    d.status, d.created_at
                FROM payment_disputes d
                JOIN users u ON d.reporter_id = u.uid
                ORDER BY d.created_at DESC";
        try {
            return $this->db->query($sql)->fetchAll();
        } catch (PDOException $e) {
            error_log("Failed to fetch disputes: " . $e->getMessage());
            return [];
        }
    }

    public function resolveDispute(string $disputeId, string $status, string $notes): bool {
        $sql = "UPDATE payment_disputes 
                SET status = :status, admin_resolution_notes = :notes 
                WHERE dispute_id = UUID_TO_BIN(:id)";
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'status' => $status,
                'notes' => $notes,
                'id' => $disputeId
            ]);
        } catch (PDOException $e) {
            error_log("Failed to resolve dispute: " . $e->getMessage());
            return false;
        }
    }

    public function getDisputeDetails(string $disputeId): ?array {
        $sql = "SELECT 
                    BIN_TO_UUID(d.dispute_id) as dispute_id,
                    BIN_TO_UUID(d.reporter_id) as reporter_id,
                    u.firstName, u.lastName, u.email,
                    d.payment_reference, d.dispute_reason, d.description,
                    d.status, d.admin_resolution_notes, d.created_at
                FROM payment_disputes d
                JOIN users u ON d.reporter_id = u.uid
                WHERE d.dispute_id = UUID_TO_BIN(:id)";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $disputeId]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log("Failed to fetch dispute details: " . $e->getMessage());
            return null;
        }
    }
}